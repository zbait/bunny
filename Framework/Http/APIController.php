<?php

namespace Bunny\Framework\Http;

use Bunny\Http\Request;
use Bunny\Http\Response;
use Bunny\Framework\Http\Controller;

/**
 * API控制器基类
 */
class APIController extends Controller{

    /**
     * 通过默认构造器设置API类型
     */
    public function __construct(Request $request, Response $response) {
        parent::__construct($request, $response);
        $this->response->api();
    }
}