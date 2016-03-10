<?php
/**
 * Created by PhpStorm.
 * User: yuzhenfan
 * Date: 16/3/1
 * Time: 下午8:09
 */
namespace Zan\Framework\Store\Database\Mysql;

use Zan\Framework\Store\Database\Mysql\SqlMap;
use Zan\Framework\Store\Database\Mysql\FutureQuery;
use Zan\Framework\Store\Database\Mysql\QueryResult;
class QueryExecuter
{
    /**
     * @var \mysqli
     */
    private $db;

    private $sql;

    private $sqlMap;

    private $callback;

    private $data;

    public function __construct()
    {
        $this->setDb();
    }

    private function setDb()
    {
        if (null == $this->db) {
            //todo connectionManage
            $db = new \mysqli();
            $config = array(
                'host' => '127.0.0.1',
                'user' => 'root',
                'password' => '',
                'database' => 'test',
                'port' => '3306',
            );
            $db->connect($config['host'], $config['user'], $config['password'], $config['database'], $config['port']);
            $db->autocommit(true);
            $this->db = $db;
        }
        return $this->db;
    }

    /**
     * @return \mysqli
     */
    public function getDb()
    {
        return $this->db;
    }

    public function execute($sid, $data, $options)
    {
        $sqlMap = $this->getSqlMap()->getSql($sid, $data, $options);
        $this->sqlMap = $sqlMap;
        $this->sql = $sqlMap['sql'];
        $this->doQuery();
        return $this;
    }

    private function doQuery()
    {
        $result = $this->db->query($this->sql, MYSQLI_ASYNC);
        if ($result === false) {
            //todo throw error
        }
    }

    public function onQueryReady()
    {
        if (null === $this->sqlMap) {
            return false;
        }
        switch ($this->sqlMap['sql_type']) {
            case 'INSERT' :
                return $this->insert();
                break;
            case 'UPDATE' :
                return $this->update();
                break;
            case 'DELETE' :
                return $this->delete();
                break;
            case 'SELECT' :
                return $this->select();
                break;
        }
    }

    private function select()
    {
        if ($result = $this->db->reap_async_query()) {
            $return = [];
            while ($data = $result->fetch_assoc()) {
                $return[] = $data;
            }
            if (is_object($result)) {
                mysqli_free_result($result);
            }
            return $this->queryResult($return);
        } else {
            //todo throw error
        }
    }

    private function insert()
    {
        if ($this->db->reap_async_query()) {
            return $this->db->insert_id;
        } else {
            //todo throw error
        }
    }


    private function update()
    {
        return $this->db->reap_async_query();
    }

    private function delete()
    {
        return $this->db->reap_async_query();
    }

    private function queryResult($result)
    {
        return new QueryResult($result);
    }

    private function getSqlMap()
    {
        if (null == $this->sqlMap) {
            $this->sqlMap = $this->createSqlMap();
        }
        return $this->sqlMap;
    }

    private function createSqlMap()
    {
        return new SqlMap();
    }


}