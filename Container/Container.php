<?php

namespace Bunny\Container;

use ArrayAccess;

/**
 * 简易容器实现类
 */
class Container implements ArrayAccess{

    /**
     * @var array 容器上下文
     */
    protected static $context = array();

    /**
     * 根据名称获取容器中对象
     *
     * @param string $name
     *
     * @return mixed
     */
    public static function get(string $name){
        return self[$name];
    }

    public function offsetExists($index){
        return isset(self::$context[$index]);
    }

    public function offsetSet($index, $value){
        self::$context[$index] = $value;
    }

    public function offsetGet($index){
        return (isset(self::$context[$index])) ? self::$context[$index] : '';
    }

    public function offsetUnset($index){
        unset(self::$context[$index]);
    }
}