<?php

namespace Bunny\Log;

use Bunny\Config\Config;
use Bunny\Log\Logger;

trait LoggerTrait{

    /**
     * @var Bunny\Log\Logger
     */
    protected $log;

    protected function getLog(string $name = '') :Logger {
        if(empty($name)){
            $name = str_ireplace('\\','',__CLASS__);
        }
        if(empty($this->log)){
            $this->log = new Logger($name, Config::getConfig("log"));
        }
        return $this->log;
    }
    /**
     * 设置日志文件名称
     *
     * @param string $fileName
     */
    public function setLogFileName(string $fileName){
        $this->getLog()->setFileName($fileName);
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
        return $this->getLog()->info($title, $info);
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
        return $this->getLog()->debug($title, $info);
    }
}