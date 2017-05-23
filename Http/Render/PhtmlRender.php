<?php

namespace Bunny\Http\Render;

use Bunny\Http\Response;
use Bunny\Http\Render\Util;

class PhtmlRender{

    /**
     * 渲染响应对象内容。
     *
     * @param Response 响应对象
     *
     * @return Response 响应对象
     */
    public static function parse(Response $response) :Response {
        //设置headers
        $response->setHeader('Content-Type: text/html; charset=UTF-8');
        //设置用户参数
        extract($response->getData(), EXTR_SKIP);
        //设置视图
        $viewName = $response->getView();
        if(!file_exists($viewName)){
            throw new \Exception('view '.$viewName.' is not found!', 404);
        }
        $view = new Util();
        require $viewName;
        return $response->setContent(ob_get_clean());
    }
}