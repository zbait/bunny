<?php

namespace Bunny\Http\Mvc;

use Bunny\Config\Config;

/**
 * 业务配置文件使用
 */
trait ConfigTrait{


    /**
     * @var array
     */
    private $config;

    /**
     * 获取config业务配置文件
     *
     * @return array
     */
    public function getConfig() :array {
        if(empty($this->config)){
            $this->config = Config::getConfig('config');
        }
        return $this->config;
    }

    /**
     * 获取配置文件中的指定名称值
     *
     * @param string $name
     *
     * @return mixed
     */
    public function getConfigValue(string $name){
        return $this->getConfig()[$name];
    }


}