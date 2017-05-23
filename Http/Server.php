<?php

namespace Bunny\Http;

use Bunny\Http\Request;
use Bunny\Http\Response;
use Bunny\Http\Handler;
use Bunny\Http\Resolver\Resolver;
use Bunny\Http\Render\Render;

use Bunny\Config\Config;

/**
 * HTTP Server
 */
class Server{

    /**
     * @var Bunny\Http\Request
     */
    private $request;

    /**
     * @var Bunny\Http\Response
     */
    private $response;

    /**
     * @var Bunny\Http\Resolver\Resolver
     */
    private $resolver;

    /**
     * @var Bunny\Http\Render\Render
     */
    private $render;

    public function __construct(Request $request, Response $response, Resolver $resolver, Render $render){
        $this->request = $request;
        $this->response = $response;
        $this->resolver = $resolver;
        $this->render = $render;
    }

    /**
     * 运行HTTP SERVER
     */
    public function run(){
        //解析
        $this->resolver->parse($this->request);
        //处理
        $this->handle($this->request, $this->response);
        //渲染
        $this->render->parse($this->response);
        //输出
        $this->response->send();
    }

    /**
     * HTTP请求响应处理。执行业务逻辑
     */
    private function handle(){
        $className = $this->request->getAttr('className');
        $methodName = $this->request->getAttr('methodName');
        $viewName = $this->request->getAttr('viewName');
        if (!class_exists($className)) {
            throw new \Exception('class '.$className.' is not found!', 404);
        }
        //设置response视图
        $this->response->view($viewName);
        //执行业务逻辑
        $class = new $className($this->request, $this->response);
        //开启缓冲区
        ob_start();
        //TODO:目前没有传递参数
        //业务方法
        call_user_func_array(array($class, 'before'), array());
        call_user_func_array(array($class, $methodName), array());
        call_user_func_array(array($class, 'after'), array());
    }
}