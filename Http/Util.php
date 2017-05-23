<?php

namespace Bunny\Http;

/**
 * HTTP Utils
 */
class Util {

    /**
     * 获取array的value。
     *
     * @param array $arr 存储数据的array
     * @param string $name 属性名
     * @param string|array $defaultValue 默认值
     *
     * @return string|array 属性值，如果为空则使用默认值
     */
    public static function getArrayValue(array $arr, string $name, $defaultValue = null){
        return (isset($arr[$name]) && !empty($arr[$name])) ? $arr[$name] : $defaultValue;
    }

    /**
     * 通过反射执行类方法
     *
     * @param string $className 类名
     * @param string $methodName 方法名
     * @param array $params 参数集合
     *
     * @return mixed 方法执行结果
     *
     * @deprecated
     */
    public static function reflect(string $className, string $methodName, array $params = array()){
        if(!class_exists($className)) {
            throw new \Exception('class '.$className.' is not found!', 404);
        }
        $class = new \ReflectionClass($className);
        $instance  = $class->newInstanceArgs($params);
        if (!$class->hasMethod($methodName)) {
            throw new \Exception('class '.$className.' have not '.$methodName.' method', 404);
        }
        $method = $class->getmethod($methodName);
        return $method->invoke($instance);
    }
}