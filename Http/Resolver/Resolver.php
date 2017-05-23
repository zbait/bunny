<?php

namespace Bunny\Http\Resolver;

use Bunny\Http\Request;
/**
 * 抽象解析器，负责分发到指定的解析器并解析
 */
class Resolver{

    /**
     * @var array 路由配置
     */
    private $router;

    /**
     * 解析器构造函数
     *
     * @param array $router 路由配置，没有则使用默认解析规则
     */
    public function __construct(array $router = array()){
        $this->router = $router;
    }

    /**
     * 解析请求路径
     *
     * @param Request $request 请求
     */
    public function parse(Request $request){
        $uri = $request->getRequestPath();
        if(empty($this->router)){
            $actionInfo =  ActionResolver::parse($uri);
        }else{
            $actionInfo = RouterResolver::parse($uri, $this->router);
        }
        $request->setAttrs(array_merge($request->getAttrs(), $actionInfo));
    }
}