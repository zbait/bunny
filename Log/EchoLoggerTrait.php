<?php

namespace Bunny\Log;

use Bunny\Log\Provider\EchoLogger;

/**
 * 服务器端记录日志
 */
trait EchoLoggerTrait{

    /**
     * 记录日志
     *
     * @param string $title 日志标题
     * @param string|array $info 日志内容
     */
    public function debug(string $title, $info){
        EchoLogger::record('DEBUG', $title, $info);
    }
}