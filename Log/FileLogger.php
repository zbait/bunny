<?php

namespace Bunny\Log;

/**
 * 文件日志实现
 * 1.追加日志内容到指定文件
 * 2.按日期生成日志文件目录
 */
class FileLogger {


    /**
     * @var string 日志文件名称
     */
    private $fileName;

    /**
     * @var string 日志文件存放路径
     */
    private $fileDir;

    public function __construct(string $fileDir, string $name = 'bunny'){
        $this->fileName = $name;
        $this->fileDir = $fileDir;
    }

    /**
     * 记录日志
     *
     * @param string $level 日志级别
     * @param string $title 日志标题
     * @param string|array $info 日志内容
     *
     * @retrun bool 成功返回true，失败返回false
     */
    public function record(string $level, string $title, $info) :bool {
        try{
            $dir = $this->fileDir.'/'.date('Y-m-d') .'/';
            if(!is_dir($dir)){
                mkdir($dir, 0777, true);
            }
            $file = $dir.$this->fileName.'.log';
            $date = date('Y/m/d H:i:s', time());
            $infoJSON = json_encode($info, JSON_UNESCAPED_UNICODE);
            $log = "[".$date."] - ".$level." - ".$title." - ".$infoJSON.PHP_EOL;
            file_put_contents($file, $log, FILE_APPEND);
            return true;
        } catch (\Exception $e) {
            return false;
        }
    }
}