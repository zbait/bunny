<?php

namespace Bunny\Http\Render;

use Bunny\Http\Response;
use Twig_Loader_Filesystem;
use Twig_Environment;

class TwigRender{

    /**
     * 渲染响应对象内容。
     *
     * @param Response 响应对象
     *
     * @return Response 响应对象
     */
    public static function parse(Response $response) :Response {
        $viewName = $response->getView();
        $splitIndex = strrpos($viewName, "/");
        $folderName = substr($viewName, 0, $splitIndex);
        $fileName = substr($viewName, $splitIndex+1, strlen($viewName));
        $loader = new Twig_Loader_Filesystem($folderName);
        $twig = new Twig_Environment($loader, array(
            'cache' => 'var/cache/twig',
        ));
        $content = $twig->render($fileName, $response->getData());
        return $response->setContent($content);
    }
}