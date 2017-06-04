<?php

namespace Bunny\Provider\Swoole;

use Bunny\Provider\Swoole\Event;

/**
 * WebSocket服务端集群节点通知实现类
 */
class Cluster extends Event{

   /**
    * 集群节点通知其他节点后的逻辑实现
    *
    * @param string $data {"action":"clusterNotify","data":"{event:'',data:''}","groupID":"000001"}
    */
	public function notify($data){
        $this->debug('notify - data', $data);
        //判断收到的集群节点通知信息是否包含数据
		if(!isset($data['data'])){
			$this->debug('notify', 'nothing to do');
			return;
		}
		//根据分组信息进行本机广播
		if(isset($data['groupID']) && !empty($data['groupID'])){
			$this->broadcastself($data, $data['groupID']);
		}else{
			$this->broadcastself($data);
		}
	}
}