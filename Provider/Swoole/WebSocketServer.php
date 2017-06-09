<?php

namespace Bunny\Provider\Swoole;

// bunny
use Bunny\Config\Config;
use Bunny\Framework\EchoAware;
use Bunny\Client\RedisClient;
use Bunny\Provider\Swoole\Client;

// swoole
use Swoole\Websocket\Server as swoole_websocket_server;
use Swoole\Http\Request as swoole_http_request;
use Swoole\Http\response as swoole_http_response;

/**
 * WebSocket服务器应用.支持集群间广播通信
 */
class WebSocketServer{

    use EchoAware;

    /**
     * @var array 配置信息
     */
	public $config;

    /**
     * @var Swoole\Websocket\Server WebSocket服务
     */
    public $server;

    /**
     * @var array 集群连接客户端 array('host' => client)
     */
	private $clients = array();

	public $nodeFDs = array();

    /**
     * 初始化服务器信息
     * 1.初始化WebSocket服务
     * 2.获取集群节点
     * 3.清空之前的绑定信息
     */
    public function __construct(){
        $config = Config::getConfig('websocket');
        $this->config = $config;
        //启动清理绑定数据
        $redis = RedisClient::create($config['redis']);
        $host = $config['server']['host'];
        $allowIP = $config['server']['allowIP'];
        $port = $config['server']['port'];
        $keyList = $redis->keys("*{$host}*");
        foreach ($keyList as $key => $value) {
        	$redis->del($value);
        }
        //创建Swoole WebSocket Server
		$this->server = new swoole_websocket_server($allowIP, $port, SWOOLE_PROCESS);
		$this->server->set($config['swoole']);
    }


    /**
     * 初始化集群节点链接客户端
     * 1.初始化未初始化的客户端
     * 2.检查已有客户端是否可用
     * @return array 集群节点的客户端链接集合
     */
    public function initNodes(){
		foreach ($this->config['nodes'] as $host => $port) {
			$isConnected = false;
			if(isset($this->clients[$host])){
				$client_exist = $this->clients[$host];
				$result = $client_exist->send('ping');
				//TODO:如果发现内存占用比较大，则设置缓冲区大小
				$client_exist->recv();
				$this->debug('ping cluster node', $result);
				if($result > 0){
					$this->debug('ping cluster node', 'old connection is ok');
					$isConnected = true;
				}else{
					$client_exist->disconnect();
					unset($this->clients[$host]);
				}
			}
			if($isConnected == true){
				$this->debug('ping cluster node', "connect cluster node {$host} is ok");
				continue;
			}else{
				//TODO:如果host是一个无法ping通的IP则会阻塞
				$client = new Client($host, $port);
	            if (!$client->connect()) {
	            	$this->debug('init cluster node client', "connect cluster node {$host} {$port} faild");
	            	//连接失败后函数结束会自动触发析构函数进行释放
	            }else{
	                $this->clients[$host] = $client;
	                $this->debug('init cluster node client', "connect cluster node {$host} {$port} successed");
	            }
			}
        }
        $nodes_length = count($this->clients);
        $this->debug('init nodes ', "init {$nodes_length} cluster node clients");
        return $this->clients;
    }

    /**
     * WebSocekt服务器端启动
     * 1.初始化服务器信息
     * 2.注册关键事件处理逻辑
     */
	public function run(){
		$this->server->on('WorkerStart', array($this, 'onWorkerStart'));
		//客户端连接事件
		$this->server->on('open', array($this, 'onOpen'));
		//客户端通信事件
		$this->server->on('message', array($this, 'onMessage'));
		//客户端关闭事件
		$this->server->on('close', array($this, 'onClose'));
		//HTTP请求
		$this->server->on('request', array($this, 'onRequest'));
		//$this->server->on('handshake', array($this, 'onHandshake'));
		echo "WebSocket Server is start ...".PHP_EOL;
		$this->server->start();
	}

    final public function onWorkerStart(){
        $this->debug('WorkerStart', 'server process start success');
        // if($worker_id >= $this->workerNum) {
        //     swoole_set_process_name("php websocket task worker");
        // } else {
        //     swoole_set_process_name("php websocket event worker");
        // }
    }

    final public function onOpen(swoole_websocket_server $_server , swoole_http_request $request){
        $fd = $request->fd;
        $this->debug('open', 'one client has connected');
        //存储集群节点客户端的fd
        $header = $request->header;
        if(isset($header['user-agent']) && ($header['user-agent'] === 'cluster_client')){
            $this->nodeFDs[$fd] = $fd;
        }
    }

    /**
     * 消息处理
     * 1.校验消息格式
     * 2.解析消息
     * 3.执行消息处理类
     * 4.异常处理，当出现错误时直接回复异常到客户端
     */
    final public function onMessage(swoole_websocket_server $_server , $frame){
        $data = $frame->data;
        $fd = $frame->fd;
        $this->debug('message', array(
            'fd' => $fd,
            'data' => $data
        ));
        try {
            if($data == 'ping'){
                $_server->push($fd, 'pong');
                return;
            }
            //校验消息格式 - {action:'myAction',data:strdata}
            $msg = json_decode($data, true);
            if(empty($msg) || (!isset($msg['action']) || !isset($msg['data']))){
                throw new \Exception('data format error : it is json with action and data property?');
            }
            //路由解析
            $router = Config::getConfig('websocket_router', false);
            $action = $msg['action'];
            $info = $msg['data'];
            if(!isset($router[$action])){
                throw new \Exception('router error : your action can not found in router?');
            }
            $className = $router[$action]['className'];
            $methodName = $router[$action]['methodName'];
            if (!class_exists($className)) {
                throw new \Exception('router error : your action class can not found?');
            }
            //初始化相应对象，并执行对应类和方法
            $class = new $className($this, $_server, $frame);
            call_user_func_array(array($class, $methodName), array($msg['data']));
        } catch (Exception $e) {
            $ret = array(
                'event' => 'error',
                'data' => $e->getMessage()
            );
            $this->debug('message error', $ret);
            //TODO:对于恶意扫描和攻击请求，建议直接关闭
            $_server->push($fd, json_encode($ret, JSON_UNESCAPED_UNICODE));
        }
    }

    final public function onClose(swoole_websocket_server $_server , $fd){
        $this->debug('close', 'fd is '.$fd);
        //不处理集群客户端
        if(isset($this->nodeFds[$fd])){
            return;
        }
        try{
            $config = $this->config;
            //获取关闭事件回调函数并执行
            $callbacks = $config['closeCallback'];
            foreach ($callbacks as $className => $methodName) {
		        $class = new $className($this, $_server, $fd, true);
	        	call_user_func_array(array($class, $methodName), array());
            }
            //清除对应绑定数据
            $redis = RedisClient::create($config['redis']);
            $host = $config['server']['host'];
            $redis->del($host.':'.$fd);
            $groupKey = $redis->get($host.':'.$fd.':group');
            $redis->del($host.':'.$fd.':group');
            $result = $redis->lrem($groupKey, $fd, 1);
            $this->debug('onClose lrem result', $result);
        } catch (Exception $e) {
            $this->debug('close', 'exception :'.$e->getMessage());
        }
    }

    final public function onRequest(swoole_http_request $request, swoole_http_response $response){
        //$server = $request->server;
        //if(isset($server['request_uri']) && (strpos($server['request_uri'], 'wslist') > 0)){
        //JSON support
        //$config = $this->getConfig();
        //ws list config
        //array('ws://127.0.0.1:9501', 'ws://localhost:9502')
        //    $wsList = $config['ws_list'];
        //	$wsListJson = json_encode($wsList);
        //	$response->end("success({$wsListJson});");
        //}else{
        $response->end("<h1>This is Swoole WebSocket Server</h1>");
        //}
    }
    final public function onHandshake(swoole_http_request $request, swoole_http_response $response){
        $this->debug('handshake', 'handshake method');
        return true;
        //自定义握手规则，没有设置则用系统内置的（只支持version:13的）
        if (!isset($this->request->header['sec-websocket-key'])){
            //'Bad protocol implementation: it is not RFC6455.'
            $this->response->end();
            return false;
        }
        if (0 === preg_match('#^[+/0-9A-Za-z]{21}[AQgw]==$#', $this->request->header['sec-websocket-key'])
            || 16 !== strlen(base64_decode($this->request->header['sec-websocket-key']))
        ){
            //Header Sec-WebSocket-Key is illegal;
            $this->response->end();
            return false;
        }
        $key = base64_encode(sha1($this->request->header['sec-websocket-key']
            . '258EAFA5-E914-47DA-95CA-C5AB0DC85B11',
            true));
        $headers = array(
            'Upgrade'               => 'websocket',
            'Connection'            => 'Upgrade',
            'Sec-WebSocket-Accept'  => $key,
            'Sec-WebSocket-Version' => '13',
            'KeepAlive'             => 'off',

        );
        foreach ($headers as $key => $val){
            $this->response->header($key, $val);
        }
        $this->response->status(101);
        $this->response->end();
        return true;
    }
}