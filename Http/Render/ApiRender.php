<?php

namespace Bunny\Http\Render;

use Bunny\Http\Response;

class ApiRender{

    /**
     * 渲染响应对象内容。
     *
     * @param Response 响应对象
     *
     * @return Response 响应对象
     */
    public static function parse(Response $response){
        //设置headers
        $response->setHeader('Content-Type: application/json; charset=UTF-8');
        //设置用户参数
        echo json_encode($response->getData(), JSON_UNESCAPED_UNICODE);
        return $response->setContent(ob_get_clean());
    }
}