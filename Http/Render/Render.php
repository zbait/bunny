<?php

namespace Bunny\Http\Render;

use Bunny\Http\Response;
use Bunny\Http\Render\ApiRender;
use Bunny\Http\Render\PhtmlRender;
use Bunny\Http\Render\TwigRender;
use Bunny\Config\Config;

/**
 * 渲染器
 */
class Render{

    /**
     * 渲染响应对象内容。
     *
     * @param Response 响应对象
     *
     * @return Response 响应对象
     */
    public static function parse(Response $response){
        //TODO:支持渲染驱动配置 api|phtml|template
        if($response->isApi() || empty($response->getView())){
            return ApiRender::parse($response);
        }

        if(!empty(Config::getGlobal(Config::TEMPLATE))){
            switch(Config::getGlobal(Config::TEMPLATE)){
            case 'twig':
                $response->view($response->getView().'.twig');
                return TwigRender::parse($response);
            default:
                return PhtmlRender::parse($response);
            }
        }
        return PhtmlRender::parse($response);
    }
}