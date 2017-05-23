<?php

namespace Bunny\Http;

/**
 * HTTP响应对象
 */
class Response{

    const TYPE_API = 'API';
    const TYPE_ERROR = 'ERROR';
    const TYPE_VIEW = 'VIEW';

    /**
     * response类型
     *
     * @var string TYPE_API|TYPE_ERROR|TYPE_VIEW
     */
    private $Type = self::TYPE_API;

    /**
     * 设置类型为API
     *
     * @return Bunny\Http\Response
     */
    public function api() :Response {
        $this->Type = self::TYPE_API;
        return $this;
    }

    /**
     * 类型是否是API
     *
     * @return bool
     */
    public function isApi() :bool {
        return $this->Type == self::TYPE_API;
    }

    /**
     * 设置类型为错误
     *
     * @return Bunny\Http\Response
     */
    public function error() :Response {
        $this->Type = self::TYPE_ERROR;
        return $this;
    }


    /**
     * @var string 视图名称
     */
    private $viewName;

    /**
     * 设置视图。如果视图为空则设置为API类型
     *
     * @param string $viewName 视图全名称 project/modules/View/index.phtml
     *
     * @return Bunny\Http\Response
     */
    public function view(string $viewName) :Response {
        if(empty($viewName)){
            $this->Type = self::TYPE_API;
        }else{
            $this->Type = self::TYPE_VIEW;
            $this->viewName = $viewName;
        }
        return $this;
    }

    /**
     * 获取视图
     *
     * @return string 视图
     */
    public function getView() :string {
        return $this->viewName;
    }

    /**
     * @var array 业务数据
     */
    private $result = array();

    /**
     * 获取业务数据
     *
     * @param string $name 业务数据名称
     *
     * @reteurn mixed 业务数据
     */
    public function getData(string $name = ''){
        return empty($name) ? $this->result : $this->result[$name];
    }

    /**
     * 添加业务数据
     *
     * @param array $data 业务数据
     *
     * @return Bunny\Http\Response
     */
    public function setData(array $data) :Response {
        $this->result = $data;
        return $this;
    }

    /**
     * 添加业务数据
     *
     * @param string $name 业务数据名称
     * @param mixed $data 业务数据
     * @param bool $isJSON 使用JSON数据
     *
     * @return Bunny\Http\Response
     */
    public function addData(string $name, $data, bool $isJSON = false) :Response {
        if($isJSON){
            $this->result[$name] = json_encode($data,JSON_UNESCAPED_UNICODE);
        }else{
            $this->result[$name] = $data;
        }
        return $this;
    }

    /**
     * 设置API消息类型数据
     *
     * @param int $code 业务编码
     * @param mixed $data 业务消息
     *
     * @return Bunny\Http\Response
     */
    public function msg(int $code, $data){
        $this->api()->addData('code',$code)->addData('data',$data);
        return $this;
    }

    /**
     * @var string response内容
     */
    private $content;

    /**
     * 设置response内容
     *
     * @param string $content response内容
     *
     * @return Bunny\Http\Response
     */
    public function setContent(string $content) :Response {
        $this->content = $content;
        return $this;
    }

    /**
     * 输出内容
     *
     * @return Bunny\Http\Response
     */
    public function sendContent() :Response {
        echo $this->content;
        return $this;
    }

    /**
     * @var array HTTP Headers
     */
    public $headers = array();

    /**
     * 设置HTTP Header
     *
     * @param string $header
     *
     * @return Bunny\Http\Response
     */
    public function setHeader(string $header) :Response {
        $this->headers[] = $header;
        return $this;
    }

    /**
     * 输出Headers
     *
     * @return Bunny\Http\Response
     */
    public function sendHeaders() :Response {
        //已经存在headers
        if (headers_sent()) {
            return $this;
        }
        foreach($this->headers as $header){
            header($header);
        }
        return $this;
    }

    /**
     * 输出response
     */
    public function send(){
        $this->sendHeaders();
        $this->sendContent();
    }
}