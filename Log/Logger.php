<?php

namespace Bunny\Log;

use Bunny\Config\Config;
use Bunny\Log\Provider\Filelogger;
use Bunny\Log\Provider\GearmanLogger;

/**
 * 日志处理类
 * 1.文件、gearman两种日志处理机制
 * 2.debug、info日志方法
 * 3.配置文件 see project/config/log.php
 */
class Logger {

    /**
     * @var string 日志类名称，同时作为日志文件名称
     */
    private $name;

    /**
     * @var array 日志配置信息
     */
    private $config = array();

    /**
     * @var string 日志驱动类型
     */
    private $driver = 'file';

    public function __construct(string $name, array $config){
        $this->config = $config;
        $this->driver = $this->config['driver'];
        $this->name = $name;
    }

    /**
     * 设置日志文件名称
     *
     * @param string $fileName
     */
    public function setFileName(string $fileName){
        $this->name = $fileName;
    }

    /**
     * 根据不同的日志驱动记录日志
     *
     * @param string $title 日志标题
     * @param string|array $info 日志内容
     * @param string $level 日志级别，默认为INFO
     *
     * @return bool 成功为true,失败为false
     *
     * @throws Exception 当日志驱动不支持时
     */
    private function record(string $title, $info, string $level = 'INFO'){
        switch($this->driver){
        case 'file':
            return FileLogger::record($level, $title, $info, $this->config['file']['rootPath'], $this->name);
            break;
        case 'gearman':
            $data = array(
                'projectName_name' => $this->config['projectName'],
                'fileName' => $this->name,
                'title' => $title,
                'info' => $info,
                'level' => $level
            );
            return GearmanLogger::record($data, $this->config['gearman']['workerName'], $this->config['gearman']['servers']);
            break;
        case 'echo':
            return EchoLogger::record($level, $title, $info);
        default:
            throw new \Exception('Log driver is not config!');
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
     * 根据不同的日志驱动记录日志
     *
     * @param string $title 日志标题
     * @param string|array $info 日志内容
     *
     * @return bool 成功为true,失败为false
     *
     * @throws Exception 当日志驱动不支持时
     */
    public function debug(string $title, $info) :bool{
        if(!$this->config['debug']){
            return true;
        }
        return $this->record($title, $info, 'DEBUG');
    }
}