<?php

namespace Bunny\Database;

use PDO;
use PDOStatement;
use Bunny\Config\Config;
use Bunny\Util\Guid;

/**
 * 基于PDO实现的数据访问对象
 * 1.PDO查询行为：
 *   pdo->query  => 返回集合,select
 *   pdo->exec   => 返回影响行数,insert,update,delete
 *   st->execute => 预处理语句,all
 * 这里统一使用st->execute去兼容其它两种
 */
class PdoDao{

    /**
     * @var PDO PDO对象
     */
    private $pdo;

    /**
     * @var string PDO数据源名称 $dbms:host=$host;port=$port;dbname=$dbName
     */
    private $dsn;

    /**
     * @var string 用户名
     */
    private $user;

    /*
     * @var string 密码
     */
    private $pass;

    public function __construct(string $dsn, string $user, string $pass){
        $this->dsn = $dsn;
        $this->user = $user;
        $this->pass = $pass;
    }

    /**
     * 通过配置文件初始化PdoDao对象的静态方法
     *
     * @param string $tableName 设置默认使用的表名
     */
    public static function create(string $tableName) :PdoDao {
        $dbConfig = Config::getConfig('database')['PdoDao'];
        $dbms = $dbConfig['driver'];
        $host = $dbConfig['host'];
        $dbName = $dbConfig['dbName'];
        $user = $dbConfig['user'];
        $pass = $dbConfig['password'];
        $port = $dbConfig['port'];
        $dsn = "$dbms:host=$host;port=$port;dbname=$dbName";
        $dao = new PdoDao($dsn, $user, $pass);
        return $dao->setTable($tableName);
    }

    /**
     * 获取原始pdo对象,本DAO中实现则基于这个方法实现懒加载
     *
     * @return PDO
     */
    public function pdo(){
        if(empty($this->pdo)){
            $this->pdo = new PDO($this->dsn, $this->user, $this->pass);
            //错误模式使用异常模式
            $this->pdo->setAttribute(PDO::ATTR_ERRMODE,PDO::ERRMODE_EXCEPTION);
            $this->pdo->exec("SET CHARACTER SET utf8");
            //TODO:长连接只对拥有进程池概念的apache有用，nginx则无用，这里暂不使用
        }
        return $this->pdo;
    }

    /**
     * 执行SQL语句
     *
     * @param string $sql 要执行的sql，替换符建议使用:name
     * @param array $data 要替换的数据，key需要使用替换符
     *
     * @return PDOStatement
     */
    private function exec(string $sql, array $data) :PDOStatement {
        $params = array();
        foreach($data as $key => $value){
            $params[':'.$key] = $value;
        }
        $st = $this->pdo()->prepare($sql);
        $st->execute($params);
        return $st;
    }

    /**
     * 删除方法。
     *
     * @param string $sql 要执行的sql，替换符建议使用:name
     * @param array $data 要insert的数据，key需要使用替换符
     *
     * @return int 影响行数
     */
    public function delete(string $sql, array $data){
        $st = $this->exec($sql, $data);
        return $st->rowCount();
    }

    /**
     * 查询方法
     *
     * @param string $sql 要执行的sql，替换符建议使用:name
     * @param array $data 要替换的数据，key需要使用替换符
     *
     * @return array 查询结果集
     */
    public function query(string $sql, array $data) :array {
        $st = $this->exec($sql,$data);
        return $st->fetchAll(PDO::FETCH_ASSOC);
    }

    // ========== 设置表信息 ==========

    /**
     * @var string $tableName
     */
    private $tableName;

    /**
     * 设置表名
     *
     * @param string $tableName
     *
     * @return Bunny\Database\Dao\PdoDao
     */
    public function setTable(string $tableName) :PdoDao{
        $this->tableName = $tableName;
        return $this;
    }

    // ========== 常见操作封装 ==========

    /**
     * 添加方法。
     *
     * @param array $data 要insert的数据，key需要使用替换符
     *
     * @return string insert id
     */
    public function insertById(array $data) :string {
        $data['id'] = Guid::get();
        $keys = '';
        $values = '';
        $params = array();
        foreach($data as $key => $value){
            $params[':'.$key] = $value;
            $keys .= $key.',';
            $values .= ':'.$key.',';
        }
        $sql = 'insert into '.$this->tableName.' (';
        $sql .= substr($keys, 0, -1);
        $sql .= ') values (';
        $sql .= substr($values, 0, -1);
        $sql .= ')';
        $st = $this->pdo()->prepare($sql);
        $st->execute($params);
        //TODO:没有处理自增长ID
        //$this->pdo()->lastInsertId();
        return $data['id'];
    }

    /**
     * 删除指定ID数据
     *
     * @param string $id
     */
    public function deleteById(string $id) :string {
        $sql = 'delete from '.$this->tableName.' where id = :id';
        $data = array(
            'id' => $id
        );
        return $this->delete($sql, $data);
    }

    /**
     * 修改方法。
     *
     * @param array $data 要insert的数据，key需要使用替换符
     *
     * @return int 影响行数
     */
    public function updateById(array $data){
        $keyValues = '';
        $params = array();
        foreach($data as $key => $value){
            $params[':'.$key] = $value;
            if($key != 'id'){
                $keyValues .= $key.'=:'.$key.',';
            }
        }
        $sql = 'update '.$this->tableName.' set ';
        $sql .= substr($keyValues, 0, -1);
        $sql .= ' where id=:id';
        $st = $this->pdo()->prepare($sql);
        $st->execute($params);
        return $st->rowCount();
    }

    /**
     * 查询指定ID数据
     *
     * @param string $id
     */
    public function fetchById(string $id) :array {
        $sql = 'select * from '.$this->tableName.' where id = :id';
        $data = array(
            'id' => $id
        );
        $st = $this->exec($sql, $data);
        while($row = $st->fetch(PDO::FETCH_ASSOC)){
            return $row;
        };
        return array();
     }

    /**
     * 查询所有数据
     */
    public function fetchAll() :array {
        $sql = 'select * from '.$this->tableName;
        return $this->query($sql, array());
    }

    /**
	 * 查询记录总数
	 * @param array $where
	 * @return int
	 */
    public function count(array $where = array()) :int {
        $sql = 'select count(1) from '.$this->tableName.$this->getWhereSql($where);
        $ret = $this->query($sql, $where);
        return $ret[0]['count(1)'];
    }

    private function getWhereSql(array $where) :string {
        if(empty($where)){
            return '';
        }
        $sql = ' where 1=1';
        foreach($where as $key => $value){
            $sql .= ' and '.$key.' like :'.$key;
        }
        return $sql;
    }

    /**
	 * 查找指定ID数据是否存在
	 * @param string $id
	 * @return bool
	 */
	public function existById(string $id) :bool {
    }

    /**
	 * 查找指定条件数据是否存在
	 * @param string $id
	 * @return bool
	 */
	public function exist(array $where = array()) :bool {
    }

    /**
	 * 查询指定条件的数据
     *
	 * @param array $where 查询条件 array('name' => 'zbait')
	 * @param array $order 排序条件 array('id' => 'desc')
	 * @param int $startNum 查询结果起始记录数
	 * @param int $recordNum 查询结果记录数
     *
	 * @return array
	 */
	public function fetch($where, $startNum = 0, $recordNum = 1, $order = array()) :array {
        $sql = 'select * from '.$this->tableName.$this->getWhereSql($where).' limit '.$startNum.','.$recordNum;
        return $this->query($sql, $where);
    }

    /**
     * 添加多数据
     *
     * @param array $multiInsertData 插入数据
     * @param array $dataKeys 插入列
     */
    public function insertMulti(array $multiInsertData, array $dataKeys = null){
	}

    /**
     * 关闭PDO连接
     */
    public function close(){
        $this->pdo = null;
    }
}