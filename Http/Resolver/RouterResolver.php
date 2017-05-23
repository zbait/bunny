<?php

namespace Bunny\Http\Resolver;

/**
 * Router类解析器
 */
class RouterResolver{

    /**
     * 解析请求路径
     *
     * @param string $uri 请求路径 request->uri
     * @param array $router router配置 array('path' => array('className' => '', 'methodName' => '', 'viewName' => ''))
     *
     * @return array 解析的Action信息 className|methodName|viewName
     */
    public static function parse(string $uri, array $router) :array {
        if(!isset($router[$uri])){
            throw new \Exception('Route '.$uri.' is not found!', 404);
        }
        $classRoute = $router[$uri];
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
        return array(
            'className' => $className,
            'methodName' => $methodName,
            'viewName' => $viewName
        );
    }
}