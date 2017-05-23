<?php

namespace Bunny\Database\Dao;

use MysqliDb;
use Bunny\Config\Config;

/**
 * 扩展Mysqli的适配
 */
class MysqliDao{

    /**
     * @var mysqliDb
     */
    private $mysqli;

    /*
     * @var array host,username,password,db,port,charset
     */
    private $config;

    public function __construct(array $config){
        $this->config = $config;
    }

    /**
     * 通过配置文件初始化MysqliDao对象的静态方法
     *
     * @param string $tableName 设置默认使用的表名
     */
    public static function create(string $tableName, string $tableId) :PdoDao {
        $dbConfig = Config::getConfig('database')['MysqliDao'];
        $dao = new MysqliDao($dbConfig);
        return $dao->setTable($tableName)->setTableId($tableId);
    }

    /**
     * 获取原始mysqli对象,本DAO中实现则基于这个方法实现懒加载
     *
     * @return mysqliDb
     */
    public function mysqli() :mysqliDb {
        if(empty($this->mysqli)){
            $this->mysqli = (new MysqliDb($this->config))->getInstance();
        }
        return $this->mysqli;
    }

    /**
	 * 删除方法
     *
	 * @param  array $where
     *
	 * @return int
	 */
	public function delete($where) :int {
        $mysqli = $this->mysqli();
        if (empty($where) == false) {
            foreach ($where as $key => $value) {
                $obj = $mysql->where($key, $value);
            }
            return $mysqli->delete($this->tableName);
        }else{
            return false;
        }
	}

    /**
     * 查询方法
     *
     * @param string $sql
     * @param array $where
     *
     * @return array
     */
    public function query(string $sql, $where = array()) :array {
        if(empty($where)){
            return $this->mysqli()->rawQuery($sql);
        }
        return $this->mysqli()->rawQuery($sql, $where);
	}

    // ========== 设置表信息 ==========

    /**
     * @var string $tableId
     */
    private $tableId = 'id';

    /**
     * 设置表主键
     *
     * @param string $tableIdName
     *
     * @return Bunny\Database\Dao\MysqliDao
     */
    public function setTableId(string $tableIdName) :MysqliDao{
        $this->tableId = $tableIdName;
        return $this;
    }

    /**
     * @var string $tableName
     */
    private $tableName;

    /**
     * 设置表名
     *
     * @param string $tableName
     *
     * @return Bunny\Database\Dao\MysqliDao
     */
    public function setTable(string $tableName) :MysqliDao {
        $this->tableName = $tableName;
        return $this;
    }

    // ========== 常见操作封装 ==========

    /**
     * 添加方法
     *
     * @param array $data
     *
     * @return int
     */
    public function insert(array $data) :int {
        return $this->mysqli()->insert($this->tableName, $data);
	}

    /**
	 * 删除指定ID数据
	 * @param  string|int $id
	 * @return int
	 */
	public function deleteById($id) :int {
        return $this->mysqli()->where($this->tableId, $id)->delete($this->tableName);
	}

    /**
	 * 修改方法。
	 * @param  array $set
	 * @param  string|array|\Closure $where
	 * @return int
	 */
	public function update(array $set, $where = array()) :int {
        $mysqli = $this->mysqli();
        if (empty($where) == false) {
            foreach ($where as $key => $value) {
                $mysqli->where($key, $value);
            }
        }
        return $mysqli->update($this->tableName, $set);
	}

    /**
	 * 查询指定ID数据
	 * @param  string|int $id
	 * @return array
	 */
    public function fetchById($id) :array {
        return $this->mysqli()->where($this->tableId, $id)->get($this->tableName);
	}

    /**
	 * 查询所有记录
	 * @param array $order
	 * @return array
	 */
	public function fetchAll($order = array()) {
        $mysqli = $this->mysqli();
        if(empty($order) == false){
            foreach($order as $key => $value){
                $mysqli->orderBy($key, $value);
            }
        }
        return $mysqli->get($this->tableName);
	}

    /**
	 * 查询记录总数
	 * @param array $where
	 * @return int
	 */
	public function count(array $where = array()) :int {
        $mysqli = $this->mysqli();
        if (empty($where) == false) {
            foreach ($where as $key => $value) {
                $mysqli->where($key, $value);
            }
        }
        $count = $mysqli->getValue($this->tableName, 'count(1)');
        return (int)$count;
	}

    /**
	 * 查找指定ID数据是否存在
	 * @param string $id
	 * @return bool
	 */
	public function existById(string $id) :bool {
        $count = $this->count(array($this->tableId => $id));
        return $count > 0 ? true : false;
	}

    /**
	 * 查找指定条件数据是否存在
	 * @param string $id
	 * @return bool
	 */
	public function exist(array $where = array()) :bool {
        $count = $this->count($where);
        return $count > 0 ? true : false;
	}

    /**
	 * 查询指定条件的数据
     *
	 * @param array $where 查询条件 array('name' => 'zbait')
	 * @param array $order 排序条件 array('id' => 'desc')
	 * @param int $fetchNum 查询结果记录数
	 * @param int $skipNum 跳过记录数
     *
	 * @return array
	 */
	public function fetch($where, $order = array(), $skipNum = 0, $fetchNum = -1) :array {
        if (empty($where)) {
            return array();
        }
        $mysqli = $this->mysqli();
        foreach ($where as $key => $value) {
            $mysqli->where($key, $value);
        }
		if (empty($order) == false) {
			foreach ($order as $key => $value) {
				$mysqli->orderBy($key, $value);
			}
		}
		$limits = array();
		if($fetchNum > 0){
			if($skipNum >= 0){
                array_push($limits, $skipNum);
			}
			array_push($limits, $fetchNum);
		}
		if(empty($limits)){
            return $mysqli->get($this->tableName);
        }
		return $mysqli->get($this->tableName, $limits);
	}

    /**
     * 添加多数据
     *
     * @param array $multiInsertData 插入数据
     * @param array $dataKeys 插入列
     */
    public function insertMulti(array $multiInsertData, array $dataKeys = null){
		return $this->mysqli()->insertMulti($this->tableName, $multiInsertData, $dataKeys);
	}

    /**
     * 关闭mysqli连接
     */
    public function close(){
        if(!empty($this->mysqli)){
            $this->mysqli->__destruct();
            $this->mysqli = null;
        }
	}
}