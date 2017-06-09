<?php

namespace Bunny\Framework;

use Bunny\Config\Config;

/**
 * 业务配置文件应用
 */
trait ConfigAware{

    /**
     * 获取config业务配置文件
     *
     * @return array
     */
    public function getConfig() :array {
        return Config::getConfig('config');
    }

    /**
     * 获取配置文件中的指定名称值
     *
     * @param string $name
     *
     * @return mixed
     */
    public function getConfigValue(string $name){
        $config = Config::getConfig('config');
        return $config[$name];
    }


}