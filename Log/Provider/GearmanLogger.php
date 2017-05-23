<?php

namespace Bunny\Log\Provider;

use Bunny\Client\GearmanClient;

/**
 * Gearman日志实现
 * 发送日志数据到gearman指定worker
 */
class GearmanLogger {

    /**
     * 记录日志
     *
     * @param string|array $info 日志内容
     * @param string $workerName 日志处理任务名称
     * @param array $servers Gearman服务器信息 see Bunny\Client\GearmanClient::init()
     *
     * @return bool 成功返回true，失败返回false
     */
    public static function record($info, string $workerName, array $servers) :bool {
        try{
            $client = GearmanClient::init($servers);
            $client->doBackground($workerName, json_encode($info));
            return true;
        } catch (\Exception $e) {
            return false;
        }
    }
}