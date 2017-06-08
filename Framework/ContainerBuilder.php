<?php

namespace Bunny\Framework;

use Bunny\Container\Container;
use Bunny\Config\Config;

class ContainerBuilder{

    /**
     * @return Container
     */
    public static function create(string $rootPath, string $env, string $configFileName){
        $c = new Container();
        //project
        $c->set(BunnyConst::PATH_ROOT, $rootPath);
        $c->set(BunnyConst::ENV, $env);
        //config
        $c->set(BunnyConst::SERVER_CONFIG, Config::getConfig($configFileName, true, $rootPath.'/config/'));
        $c->set(BunnyConst::CONFIG, Config::getConfig('config', true, $rootPath.'/config/'));
        //logger
        $c->register(BunnyConst::SERVER_LOGGER,array('Bunny\Log\Provider\EchoLogger'));
        $c->register(BunnyConst::LOGGER,array('Bunny\Log\Logger', 'bunny', Config::getConfig('log', true, $rootPath.'/config/')));
        return $c;
    }

}