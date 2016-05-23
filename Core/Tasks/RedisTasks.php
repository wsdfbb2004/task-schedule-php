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
namespace TaskSchedule\Core\Tasks;

/**
 * 将任务内容保存在Redis中，实现taskid=>任务内容的映射
 * 优点：因为支持上层多个机器上的TimeEvent/TaskRunner共享一个redis集群中的Tasks，利于TimeEvent集群部署/横向扩展。
 * 缺点：网络IO较多。
 */
class RedisTasks implements TasksInterface
{
    
    /**
     * @var obj redis连接，用于存储任务内容
     */
    protected $_redis = null;
    
    /**
     * @const 任务内容在redis中的key名
     */
    protected $_key = null;
    
    /**
     * @const 变量最大任务Id在redis中的key名
     */
    protected $_maxTaskIdKey = null;
    
    
    public function __construct($redis, $key = 'hsetTasks', $maxTaskIdKey = 'maxTaskIdKey')
    {
        $this->_key = $key;
        $this->_maxTaskIdKey = $maxTaskIdKey;
        $this->_redis = $redis;
    }
    
    
    public function set($taskId, $type, $func, $args = null, $timeInterval = 2)
    {
        $taskContent = array(
            'status' => self::STATUS_READY,
            'type' => $type,
            'func' => $func,
            'args' => $args,
            'timeInterval' => $timeInterval,
         );
        return $this->_set($taskId, $taskContent); 
    }
    
    
    protected function _set($taskId, $taskContent)
    {
        $taskContent = json_encode($taskContent);
        //return $this->_redis->hset($this->_key, $taskId, $taskContent);
        $res = $this->_redis->hset($this->_key, $taskId, $taskContent);
        if(empty($res)){
            return false;
        }
        $maxTaskId = $this->_redis->get($this->_maxTaskIdKey);
        if(empty($maxTaskId)){
            //return false;
            $maxTaskId = 1; //先写的糙一点，假如从redis取得最大taskId失败，就给个默认值1.
        }
        if($taskId > $maxTaskId){
            $res = $this->_redis->set($this->_maxTaskIdKey, $taskId);
        }
        return $res;
    }

    
    public function get($taskId)
    {
        $taskContent = $this->_get($taskId);
        return array($taskContent['type'], $taskContent['status'], $taskContent['func'], $taskContent['args'], $taskContent['timeInterval']);
    }
    

    protected function _get($taskId)
    {
        $taskContent = $this->_redis->hget($this->_key, $taskId);
        if(empty($taskContent)){
            return false;
        }
        return ($taskContent = json_decode($taskContent, true));
    }
    
    
    public function fakeUnset($taskId)
    {
        $taskContent = $this->_get($taskId);
        if(empty($taskContent)){
            return true;
        }
        $taskContent['status'] = self::STATUS_FAKEUNSET;
        return $this->_set($taskId, $taskContent);
    }
    

    public function realUnset($taskId)
    {
        return $this->_redis->hdel($this->_key, $taskId);
    }
    

    public function unsetAll()
    {
        return $this->_redis->delete($this->_key);
    }
    
    
    public function getMaxTaskId()
    {
        $maxTaskId = $this->_redis->get($this->_maxTaskIdKey);
        logDebug('RedisTasks::getMaxTaskId return: ', $maxTaskId);
        return $maxTaskId;
    }
    
    
    
}