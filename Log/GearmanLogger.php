<?php

namespace Bunny\Log\Provider;

use Bunny\Client\GearmanClient;

/**
 * Gearman日志实现
 * 发送日志数据到gearman指定worker
 */
class GearmanLogger {

    /**
     * @var string $workerName 日志处理任务名称
     */
    private $workerName;

    /**
     * @var array $servers Gearman服务器信息 see Bunny\Client\GearmanClient::init()
     */
    private $servers;

    public function __costruct(string $workerName, array $servers){
        $this->workerName = $workerName;
        $this->servers = $servers;
    }

    /**
     * 记录日志
     *
     * @param string|array $info 日志内容
 
     * @return bool 成功返回true，失败返回false
     */
    public function record($info) :bool {
        try{
            $client = GearmanClient::init($this->servers);
            $client->doBackground($this->workerName, json_encode($info));
            return true;
        } catch (\Exception $e) {
            return false;
        }
    }
}