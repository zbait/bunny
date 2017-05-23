<?php

namespace Bunny\Client;

use GearmanClient as Client;

/**
 * Gearman Client
 */
class GearmanClient {

    /**
     * 获取Gearman Client实例
     *
     * @param array $servers array('host1' => port, 'host2' => port2)
     */
	public static function create(array $servers) :\GearmanClient{
        $client = new Client();
        foreach ($servers as $host => $port) {
            $client->addServer($host, (int)$port);
		}
		return $client;
	}
}
