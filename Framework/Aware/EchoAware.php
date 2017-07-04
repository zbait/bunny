<?php

namespace Bunny\Framework\Aware;

use Bunny\Log\EchoLogger;

/**
 * 服务器端记录日志
 */
trait EchoAware{

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