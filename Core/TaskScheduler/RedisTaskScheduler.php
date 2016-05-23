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
namespace TaskSchedule\Core\TaskScheduler;

/**
 * 将任务调度顺序保存在Redis中
 * 优点：因为支持上层多个机器上的TimeEvent/TaskRunner共享一个redis集群中的TaskScheduler，利于TimeEvent集群部署/横向扩展。
 * 缺点：网络IO较多。
 */
class RedisTaskScheduler implements TaskSchedulerInterface
{    
    
    /**
     * @var obj redis连接，用于存储优先队列
     */
    protected $_redis = null;
    
    /**
     * @var 优先队列在redis中的key名
     */
    protected $_key = null;
    
    
    public function __construct($redis, $key = 'priorQueque')
    {
        $this->_key = $key;
        $this->_redis = $redis;
    }
    
        
    public function insert($taskId, $prior)
    {
        return $this->_redis->zadd($this->_key, $prior, $taskId);
    }
    
    
    public function extractTop($timeout = null)
    {
        $top = $this->_redis->zrevrange($this->_key, 0, 0, true);
        if(empty($top))
        {
            return null;
        }
        $ret = array(key($top), current($top));
        $this->_redis->zrem($this->_key, key($top));
        return $ret;
    }
    
    
    public function clearAll()
    {
        return $this->_redis->delete($this->_key);
    }
    
    
    public function del($taskId)
    {
        return $this->_redis->srem($this->_key, $taskId);
    }
    
    
    /*
     * 抛出底层redis连接。
     * */
    public function getRedisConn(){
        return $this->_redis; 
    }
    
    
}