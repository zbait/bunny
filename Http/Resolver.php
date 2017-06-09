<?php

namespace Bunny\Http;

use Bunny\Http\Request;
/**
 * HTTP请求解析器
 */
class Resolver{

    /**
     * @var array 路由配置
     */
    private $router;

    /**
     * 解析器构造函数
     *
     * @param array $router 路由配置
     */
    public function __construct(array $router){
        $this->router = $router;
    }

    /**
     * 解析请求路径
     *
     * @param Request $request 请求
     */
    public function parse(Request $request){
        $uri = $request->getRequestPath();
        if(!isset($this->router[$uri])){
            throw new \Exception('Route '.$uri.' is not found!', 404);
        }
        $classRoute = $this->router[$uri];
        if(!isset($classRoute['className']) || !isset($classRoute['methodName'])){
            throw new \Exception('Route '.$uri.' miss className or methodName!', 404);
        }
        //获取类名，方法名，视图名
        $className = $classRoute['className'];
        $methodName = $classRoute['methodName'];
        if(!isset($classRoute['viewName']) || empty($classRoute['viewName'])){
            $viewName = '';
        }else{
            $viewName = $classRoute['viewName'];
        }
        $request->setAttrs(array_merge($request->getAttrs(), array(
            'className' => $className,
            'methodName' => $methodName,
            'viewName' => $viewName
        )));
    }
}