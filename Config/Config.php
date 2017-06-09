<?php

namespace Bunny\Config;

class Config {

    /**
     * @var string 项目根路径
     */
    const PATH_ROOT = 'bunny.path.root';

    /**
     * @var string 运行时环境
     */
    const ENV = 'bunny.env';

    /**
     * @var array 全局变量集合
     */
    private static $context = array(
        //TODO:需要适配vender目录结构
        self::PATH_ROOT => __DIR__.'/../../../',
        self::ENV => 'dev'
    );

    /**
     * 设置全局变量
     *
     * @param string $name
     * @param string $value
     */
    public static function setGlobal(string $name, string $value){
        self::$context[$name] = $value;
    }

    /**
     * 获取全局变量
     *
     * @param string $name
     *
     * @return string
     */
    public static function getGlobal(string $name){
        return isset(self::$context[$name])?self::$context[$name]:'';
    }

    /**
     * 加载配置文件
     *
     * @param string $file 配置文件全名称路径 eg. /usr/local/logs/bunny.log
     */
    private static function loadFile(string $file) :array {
        $config = require $file;
        return $config;
    }

    /**
     * @var array 配置项缓存列表
     */
    private static $cache = array();

    /**
     * 获得配置文件内容。支持多配置文件缓存
     *
     * @param string $fileName 配置文件名称
     * @pram bool $useEnv 是否使用环境
     * @param string $filePath 配置文件所在目录,如果没有则使用默认目录
     */
    public static function getConfig(string $fileName, bool $useEnv = true, string $fileDir = "") :array {
        //获取文件名
        if($useEnv){
            $fileName = $fileName.'_'.self::$context[self::ENV].'.php';
        }else{
            $fileName = $fileName.'.php';
        }
        //获取文件全路径
        if(empty($fileDir)){
            $filePath = self::$context[self::PATH_ROOT].'config/'.$fileName;
        }else{
            $filePath = $fileDir.$fileName;
        }
        //TODO:文件存在判断.考虑文件读写对性能的影响，不进行处理
        //缓存判断
        if(!isset(self::$cache[$filePath])){
            self::$cache[$filePath] = self::loadFile($filePath);
        }
        return self::$cache[$filePath];
    }
}