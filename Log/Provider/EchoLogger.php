<?php

namespace Bunny\Log\Provider;

/**
 * echo日志实现
 */
class EchoLogger {

    /**
     * 记录日志
     *
     * @param string $level 日志级别
     * @param string $title 日志标题
     * @param string|array $info 日志内容
     * @param string $fileDir 日志文件存放路径
     * @param string $fileName 日志文件名称
     *
     * @retrun bool 成功返回true，失败返回false
     */
    public static function record(string $level, string $title, $info) :bool {
        try{
            $date = date('Y/m/d H:i:s', time());
            $infoJSON = json_encode($info, JSON_UNESCAPED_UNICODE);
            $log = "[".$date."] - ".$level." - ".$title." - ".$infoJSON.PHP_EOL;
            echo $log;
            return true;
        } catch (\Exception $e) {
            return false;
        }
    }
}