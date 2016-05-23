<?php
/**
 * This file is part of php-task-schedule.
 *
 * Licensed under The MIT License
 * For full copyright and license information, please see the MIT-LICENSE.txt
 * Redistributions of files must retain the above copyright notice.
 *
 * @author liuyin<1053734086@qq.com>
 * @copyright liuyin<1053734086@qq.com>
 * @link http://www.xxxxxxxx.net/
 * @license http://www.opensource.org/licenses/mit-license.php MIT License
 */
namespace TaskSchedule\Core\TaskResults;

/**
 * 将任务执行结果保存在mysql中。
 */
class MysqlTaskResults implements TaskResultsInterface
{
    
    /**
     * @var obj mysql连接，用于存储任务执行结果
     */
    protected $_mysqlConn = null;
    
    /**
     * @const 任务结果表在的表名
     */
    protected $_tableName = null;
    
    
    
    public function __construct($mysqli, $tableName = 'taskResults')
    {
        $this->_tableName = $tableName;
        $this->_mysqlConn = $mysqli;
    }
    
    
    public function record($taskId, $type, $func, $args, $result, $execTimeBegin, $execTimeEnd, $scheduleExecTime)
    {
        if(is_array($args) || is_null($args) || is_bool($args)){
            $args = json_encode($args);
        }
        if(is_array($result)|| is_null($result) || is_bool($result)){
            $result = json_encode($result);
        }
        //这个生成记录执行结果的sql的地方代码写的糙一点，
        $sql = $this->_genRecordSql($taskId, $type, $func, $args, $result, $execTimeBegin, $execTimeEnd, $scheduleExecTime);
        if(empty($sql)){
            return false;
        }
        
        $res = $this->_mysqlConn->query($sql);
        return $res;
    }
    
    
    protected function _genRecordSql($taskId, $type, $func, $args, $result, $execTimeBegin, $execTimeEnd, $scheduleExecTime)
    {
        //var_dump($args, $result); exit;
        $sql = "insert into ". $this->_tableName. " (taskId, type, func, args, result, execTimeBegin, execTimeEnd, scheduleExecTime) values "
                . " (". "$taskId, $type, '$func', '$args', '$result', $execTimeBegin, $execTimeEnd, $scheduleExecTime" . ")";
        logDebug('MysqlTaskResults::_genRecordSql return: '. $sql);
        return $sql;
    }
    
    
    public function getByTaskId($taskId)
    {
        $sql = $this->_genGetByTaskIdSql($taskId);
        if(empty($sql)){
            return false;
        }
        $queryRes = $this->_mysqlConn->query($sql);
        if(empty($res)){
            return false;
        }
        $ret = array();
        while($row = mysqli_fetch_assoc($queryRes)){
            $ret[] = $row;
        }
        return $ret;
    }

    
    protected function _genGetByTaskIdSql($taskId)
    {
        $sql = "select * from ". $this->_tableName. " where taskId=$taskId" ;
        return $sql;
    }
    
    
    
}