<?php

namespace Bunny\Container;

/**
 * 服务容器实现类。
 * 1.服务容器
 * 2.服务注册
 * 3.服务实例化工厂
 */
class Container{

    /**
     * @var array 服务内容
     */
    protected $services = array();

    /**
     * @var array $classMap 服务类模板
     */
    protected $classMap = array();

    /**
     * 服务设置
     *
     * @param string $id 服务标识
     * @param object $service 服务实例，如果为null则重置
     */
    public function set(string $id, $service){
        $this->services[$id] = $service;
        if(null === $service){
            unset($this->services[$id]);
        }
    }

    /**
     * 服务是否被定义
     *
     * @param string $id 服务id
     */
    public function has(string $id){
        if (isset($this->services[$id])) {
            return true;
        }
        if (isset($this->classMap[$id])) {
            return true;
        }
        return false;
    }

    public function get($id){
        $service = null;
        if (isset($this->services[$id])) {
            return $this->services[$id];
        }
        if (isset($this->classMap[$id])) {
            try {
                $args = $this->classMap[$id];
                $service = $this->getInstance($args);
            } catch (\Exception $e) {
                unset($this->services[$id]);
                throw $e;
            }
        }
        return $service;
    }

    /**
     * 服务类模板设置
     *
     * @param string $id 服务标识
     * @param array $args 服务模板类，如果为null则重置 array('className', args1, args2)
     */
    public function register(string $id, array $args){
        $this->classMap[$id] = $args;
        if(null === $args){
            unset($this->classMap[$id]);
        }
    }

    /**
     * 重置整个容器
     */
    public function reset(){
        $this->services = array();
    }

    /**
     * 获取容器所有服务类ID
     */
    public function getServiceIds(){
        return array_unique(array_merge(array_keys($this->classMap), array_keys($this->services)));
    }

    /**
     * 获取类实例,如果参数使用"@"则从容器获取并注入
     */
    public function getInstance() {
        $arguments = func_get_args();
        $className = array_shift($arguments[0]);
        $class = new \ReflectionClass($className);
        foreach($arguments as $index => $args) {
			if($args[0] == '@'){
				$arguments[$index] = $this->get(substr($args, 1, strlen($args)));
			}
		}
        return $class->newInstanceArgs($arguments);
    }
}