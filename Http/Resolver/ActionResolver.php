<?php

namespace Bunny\Http\Resolver;

use Bunny\Http\Util;

/**
 * Action类解析器
 */
class ActionResolver{

    /**
     * 解析请求路径
     * 1.URL格式:        moduleName/controllerName/actionName
     * 2.Controller定义: modules/moduleName/Controller/IndexController
     * 3.Action定义:     modules/moduleName/Controller/IndexController/editAction
     * 4.视图定义:        modules/moduleName/Viw/index/edit.phtml
     * 5.根路径'/':       modules/Index/indexAction
     *
     * @param string $uri 请求路径 request->uri
     *
     * @return array 解析的Action信息 className|methodName|viewName
     */
    public static function parse(string $uri) :array {
        if($uri == '/'){
            $className = 'Index';
            $methodName = 'indexAction';
            $viewName = '';
        }else{
            $reqPathArr = explode('/', $uri);
            //获取模块名，控制器名，行为名
            $module = ucfirst(Util::getArrayValue($reqPathArr, 1, 'index'));
            $controller = ucfirst(Util::getArrayValue($reqPathArr, 2, 'index'));
            $action = Util::getArrayValue($reqPathArr, 3, 'index');
            //获取类名，方法名，视图名
            $className = $module.'\\Controller\\'.$controller.'Controller';
            $methodName =  $action.'Action';
            $viewName = 'modules/'.$module.'/View/'.strtolower($controller.'/'.$action.'.phtml');
        }
       return array(
            'className' => $className,
            'methodName' => $methodName,
            'viewName' => $viewName
        );
    }
}