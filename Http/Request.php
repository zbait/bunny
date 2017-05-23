<?php

namespace Bunny\Http;

use Bunny\Http\Resolver\Resolver;
/**
 * HTTP请求对象
 */
class Request {

    /**
     * @var string 请求地址
     */
    private $uri;

    /**
     * @var array 参数集合
     */
    private $query;


    /**
     * @var array 用户参数集合
     */
    private $attributes = array();


    /**
     * 设置用户参数
     *
     * @param string $name 属性名
     * @param mixed $value 属性值
     */
    public function setAttr(string $name, $value){
        $this->attributes[$name] = $value;
    }

    /**
     * 获取用户参数
     *
     * @param string $name 属性名
     *
     * @return string|array $value 属性值
     */
    public function getAttr(string $name){
        return $this->attributes[$name];
    }

    /**
     * 获取用户所有参数
     *
     * @return array 用户所有参数集合
     */
    public function getAttrs() :array {
        return $this->attributes;
    }

    /**
     * 设置用户参数
     *
     * @param array $attrs
     */
    public function setAttrs(array $attrs){
        $this->attributes = $attrs;
    }

    /**
     * Request构造器
     *
     * @param array $router 路由配置
     * @param string $uri 请求地址
     * @param array $query 请求参数
     */
    public function __construct(string $uri = '', array $query = array()){
        $this->uri = $uri;
        $this->query = $query;
    }

    /**
     * 请求起始时间
     *
     * @return float 请求时间
     */
    public function getTime() :float{
        return $_SERVER['REQUEST_TIME'];
    }

    /**
     * 判断请求是否是POST类型
     *
     * @return bool POST返回true,否则返回false
     */
    public function isPost() :bool {
        return $_SERVER['REQUEST_METHOD'] == 'POST';
    }

    /**
     * 获取请求参数值
     *
     * @param string $name 参数名
     * @param string|array $default 参数不存在的默认参数值
     *
     * @return string|array 参数值
     */
    public function getParam(string $name, $default = ''){
        //获取请求参数
        if(empty($this->query)){
            $this->query = $_REQUEST;
        }
        if(isset($this->query[$name])){
            $value = $this->query[$name];
            //array直接返回，string进行trim
            return is_array($value) ? $value : trim($value);
		}
	    return $default;
    }

    /**
     * 请求路径
     * 1.如果使用自定义url则不进行解析，多用于测试
     * 2.禁止直接访问PHP文件
     *
     * @throws \Exception 直接访问PHP文件时
     */
    public function getRequestPath(){
        if(empty($this->uri)){
            //获取浏览器请求URI
            $reqUri = $_SERVER['REQUEST_URI'];
            //去除参数部分
            $index = strpos($reqUri, '?');
            $this->uri = $index > 0 ? substr($reqUri, 0, $index) : $reqUri;
            //禁止直接访问php文件
            if(stripos($this->uri, '.php') == true ) {
                throw new \Exception('bunny.error.permission', 403);
            }
        }
        return $this->uri;
    }
}