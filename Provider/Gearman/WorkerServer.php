<?php

namespace Bunny\Provider\Gearman;

// gearman
use GearmanWorker;

// bunny
use Bunny\Framwork\EchoAware;
use Bunny\Config\Config;

/**
 * 基于
 */
class WorkerServer{

    use EchoAware;

    public $config;
	public $workers = array();
	public $childs = array();
	public $pid = null;
	public $restart;

    public function __construct(bool $restart = false) {
        $this->config = Config::getConfig('worker');
        $this->restart = $restart;
    }

	public function run(){
        $this->pid = getmypid();
		//ticks使系统运行产生时间片段，以便配合signal获取
		//兼容PHP < 5.3
		if (!function_exists("pcntl_signal_dispatch")) {
    		declare(ticks=1);
		}
		declare(ticks = 1);
        //设置信号处理函数
		pcntl_signal(SIGCHLD, function($sig){
            //这里收到子进程信号认为进程意外终止
            switch ($sig) {
	        case SIGCHLD:
	            $this->debug('child sig',$sig);
	            break;
            }
            //启动校验程序
            $this->validate();
        });
        //初始化workers
        if(!isset($this->config['workers']) || count($this->config['workers']) === 0){
            throw new \Exception('worker is not found.');
        }
		$this->workers = $this->config['workers'];
        foreach($this->workers as $name => $worker){
            for ($i=0; $i < $worker['num']; $i++) {
                $worker['name'] = $name;
                $this->fork($worker);
			}
        }
        //守护进程
        while (true) {
            sleep(60*10);
        }
	}

    public function fork($worker){
        $pid = pcntl_fork();
        switch ($pid) {
        case -1://error
            throw new \Exception('fork process for your worker faild!');
            break;
        case 0://sub
            //daemon进程处理,出现异常会抛出到主进程console中
            if($worker['daemon']){
                call_user_func(array(new $worker['className'](), $worker['methodName']));
                return;
            }
            //worker进程处理
            $gearmanWorker= new GearmanWorker();
            $server = isset($this->config['server']) ? $this->config['server'] : array('127.0.0.1' => 4730);
            foreach ($server as $host => $port) {
                $gearmanWorker->addServer($host, (int)$port);
            }
            $gearmanWorker->addFunction($worker['name'], array(new $worker['className'](), $worker['methodName']));
            while ($gearmanWorker->work());
            //保证子进程在上下文正确退出
            exit;
            break;
        default://current
            $this->childs[$pid] = $worker;
            $this->debug('worker has init',array(
                'pid' => $pid,
                'name' => $worker['name'],
                'class' => $worker['className'],
                'method' => $worker['methodName']
            ));
            break;
        }
    }

    /**
     * 检查workers中是否有进程终止并重新启动
     */
	public function validate(){
		if(!$this->restart){
			return;
		}
		$this->restart = false;
		//PHP >= 5.3
		if (function_exists("pcntl_signal_dispatch")) {
    		pcntl_signal_dispatch();
		}
		$this->debug('server', "-- start check workers --");
		foreach ($this->childs as $pid => $worker) {
	        $res = pcntl_waitpid($pid, $status, WNOHANG);
	        if($res == 0){
	        	$this->debug('server', "{$worker['name']} {$pid} works well");
	        }
	        if ($res == -1 || $res > 0){
	        	$this->debug('server', "{$worker['name']} {$pid} not found");
	            unset($this->childs[$pid]);
	            //reload
	            $this->fork($worker);
	    	}
		}
		$this->debug('server', "-- end check workers --");
		$this->restart = true;
	}
}