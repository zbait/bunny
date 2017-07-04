<?php

namespace Bunny\Framework\Http;

use Bunny\Http\Request;
use Bunny\Http\Response;

use Bunny\Framework\Aware\FactoryAware;
use Bunny\Framework\Aware\LoggerAware;

/**
 * 控制器基础类
 */
class Controller{

    use LoggerAware;
    use FactoryAware;

    /**
     * @var Bunny\Http\Request HTTP请求
     */
    protected $request;

    /**
     * @var Bunny\Http\Response HTTP响应
     */
    protected $response;

    public function __construct(Request $request, Response $response) {
        $this->request = $request;
        $this->response = $response;
    }

    /**
     * 业务前置方法
     */
    public function before(){}

    /**
     * 业务后置方法
     */
    public function after(){}
}