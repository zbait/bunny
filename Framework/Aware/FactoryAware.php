<?php

namespace Bunny\Framework\Aware;

use Bunny\Config\Config;
use Bunny\Database\PdoDao;

use GearmanClient;
use redis;

trait FactoryAware{

    public function getEnv(){
        return Config::getGlobal(Config::ENV);
    }

    public function getRootPath(){
        return Config::getGlobal(Config::PATH_ROOT);
    }

    /**
     * 获取config业务配置文件
     *
     * @return array
     */
    public function getConfig() :array {
        return Config::getConfig();
    }

    /**
     * 获取config框架配置文件
     *
     * @return array
     */
    public function getAppConfig() :array {
        return Config::getConfig('app');
    }

    /**
     * 获取配置文件中的指定名称值
     *
     * @param string $name
     *
     * @return mixed
     */
    public function getConfigValue(string $name){
        return $this->getConfig()[$name];
    }

    /**
     * 获取框架配置文件中的指定名称值
     *
     * @param string $name
     *
     * @return mixed
     */
    public function getAppConfigValue(string $name){
        return $this->getAppConfig()[$name];
    }

    /**
     * 获取redis client实例 $server array('host' => '127.0.0.1', 'port' => 6379, 'index' => 0)
     *
     * @return \redis
     */
    public function getRedis() :redis {
        $server = $this->getAppConfigValue('redis');
        $client = new redis();
        $configDefault = array(
            'host' => '127.0.0.1',
            'port' => '6379',
            'password' => '',
            'index' => '0',
            'timeout' => '0'
        );
        $config = array_merge($configDefault, $server);
        if(!$client->connect($config['host'], (int)$config['port'], (int)$config['timeout'])){
            throw new \Exception('redis connection failed!');
        }
        if($config['password'] && !$client->auth($config['password'])){
            throw new \Exception('redis password is wrong!');
        }
        if($config['index']){
            $client->select((int)$config['index']);
        }
        return $client;
    }

    /**
     * 获取Gearman Client实例 array('host1' => port, 'host2' => port2)
     *
     * @return GearmanClient
     */
    public function getGearman() :GearmanClient{
        $servers = $this->getAppConfigValue('gearman');
        $client = new GearmanClient();
        foreach ($servers as $host => $port) {
            $client->addServer($host, (int)$port);
		}
		return $client;
    }


    /**
     * 通过配置文件初始化PdoDao对象的静态方法
     *
     * @param string $tableName 设置默认使用的表名
     */
    public function getPdo(string $tableName, string $idName = 'id',string $createTimeName = 'create_time', string $updateTimeName = 'update_time') :PdoDao {
        $config = $this->getAppConfigValue('pdo');
        $dbms = $config['driver'];
        $host = $config['host'];
        $dbName = $config['dbName'];
        $user = $config['user'];
        $pass = $config['password'];
        $port = $config['port'];
        $dsn = "$dbms:host=$host;port=$port;dbname=$dbName";
        $dao = new PdoDao($dsn, $user, $pass);
        return $dao->setMetadata($tableName, $idName, $createTimeName, $updateTimeName);
    }
}