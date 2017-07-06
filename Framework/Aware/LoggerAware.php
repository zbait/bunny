<?php

namespace Bunny\Framework\Aware;

use Bunny\Config\Config;
use Bunny\Log\FileLogger;
use Bunny\Log\GearmanLogger;

/**
 * 日志处理类
 * 1.文件、gearman两种日志处理机制
 * 2.debug、info日志方法
 * 3.配置文件 see project/config/log.php
 */
trait LoggerAware{

    /**
     * @var object 日志类
     */
    protected $logger;

    /**
     * @var string 日志文件名
     */
    protected $name;

    /**
     * 根据不同的日志配置获取具体的logger实现
     */
    protected function getLog(){
        if(empty($this->logger)){
            $config = Config::getConfig("config_app")['log'];
            //设置文件名称
            $this->name = str_ireplace('\\','',__CLASS__);
            switch($config['driver']){
            case 'file':
                //可以使用单文件存储日志
                if($config['file']['fileName'] == 'bunny'){
                    $this->name = 'bunny';
                }
                $this->logger = new FileLogger(Config::getGlobal(Config::PATH_ROOT).'var/logs/', $this->name);
                break;
            case 'gearman':
                $this->logger = new GearmanLogger($config['gearman']['workerName'], $config['gearman']['servers']);
                break;
            default:
                throw new \Exception('Log driver is not config!');
            }
        }
        return $this->logger;
    }

    /**
     * 适配不同logger的数据格式
     *
     * @param string $title 标题
     * @param object $info 内容
     * @param string $level 级别
     */
    protected function record(string $title, $info, string $level = 'INFO'){
        $config = Config::getConfig("config_app")['log'];
        switch($config['driver']){
        case 'file':
            return $this->getLog()->record($level, $title, $info);
            break;
        case 'gearman':
            $data = array(
                'projectName_name' => $this->config['projectName'],
                'fileName' => $this->name,
                'title' => $title,
                'info' => $info,
                'level' => $level
            );
            return $this->getLog()->record($data);
        }
    }
    /**
     * 根据不同的日志驱动记录日志
     *
     * @param string $title 日志标题
     * @param string|array $info 日志内容
     *
     * @return bool 成功为true,失败为false
     *
     * @throws Exception 当日志驱动不支持时
     */
    public function info(string $title, $info) :bool{
        return $this->record($title, $info, 'INFO');
    }

    /**
     * 记录debug日志
     *
     * @param string $title 日志标题
     * @param string|array $info 日志内容
     *
     * @return bool 成功为true,失败为false
     *
     * @throws Exception 当日志驱动不支持时
     */
    public function debug(string $title, $info) :bool{
        $config = Config::getConfig("app")['log'];
        if(!$config['debug']){
            return true;
        }
        return $this->record($title, $info, 'DEBUG');
    }
}