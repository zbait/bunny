<?php

namespace Bunny\Database\Dao;

use Illuminate\Database\Capsule\Manager as Capsule;
use Bunny\Config\Config;

/**
 * Laravel的ORM框架
 */
class LaravelDao{

    public static function init(array $config = array()) :Capsule {
        //使用配置文件
        if(empty($config)){
            $config = Config::getConfig('database')['LaravelDao'];
            $reads = $config['reads'];
            $config['read'] = array(
                'host' => $reads[rand(1,count($reads))],
            );
        }
        //使用自定义配置
        $capsule = new Capsule;
        $capsule->addConnection($config);
        $capsule->setAsGlobal();
        $capsule->bootEloquent();
        return $capsule;
    }
}