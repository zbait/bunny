<?php

namespace Bunny\Client;

use redis as Client;

/**
 * redis client
 */
class RedisClient {

    /**
     * 获取redis client实例
     *
     * @param array $server array('host' => '127.0.0.1', 'port' => 6379, 'index' => 0)
     *
     * @return \redis
     */
    public static function create(array $server = array()) :\redis {
        $client = new Client();
        $configDefault = array(
            'host' => '127.0.0.1',
            'port' => '6379',
            'password' => '',
            'index' => '0',
            'timeout' => '0'
        );
        $config = array_merge($configDefault, $server);
        if(!$client->connect($config['host'], (int)$config['port'], (int)$config['timeout'])){
            throw new \Exception('redis connection failed!');
        }
        if($config['password'] && !$client->auth($config['password'])){
            throw new \Exception('redis password is wrong!');
        }
        if($config['index']){
            $client->select((int)$config['index']);
        }
        return $client;
    }
}