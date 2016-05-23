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
 * 将任务内容保存在单实例php内存中，使用php数组实现。实现taskid=>任务内容的映射。
 * 优点：不支持上层多个机器上的TimeEvent/TaskRunner。
 * 优点：无网络IO，简单有效。
 */
class PhpTasks implements TasksInterface
{
    
    /**
     * @var arr 保存taskid=>任务内容的映射
     */
    protected $_content;
    
    /**
     * @var int 当前任务Id的最大值
     */
    protected $_maxTaskId = 1;
    
        
    public function __construct()
    {
        $this->_contents = array();
    }
    
    
    public function set($taskId, $type, $func, $args = null, $timeInterval = 2)
    {
        $in = compact('taksId', 'type', 'func', 'args', 'timeInterval');
        logDebug('enter PhpTasks::set ', $in);
        $this->_contents[$taskId] = array(
            'status' => self::STATUS_READY,
            'type' => $type,
            'func' => $func,
            'args' => $args,
            'timeInterval' => $timeInterval,
         );
        
         if($taskId > $this->_maxTaskId){
             $this->_maxTaskId = $taskId;
         }
         return true;
    }
    
       
    public function get($taskId)
    {
        logDebug('enter PhpTasks::get ', $taskId);
        if(empty($this->_contents[$taskId]) || (!is_array($this->_contents[$taskId]))){
            logDebug('PhpTasks::get return false');
            return false;
        }
        $taskContent = $this->_contents[$taskId];
        $ret = array($taskContent['type'], $taskContent['status'], $taskContent['func'], $taskContent['args'], $taskContent['timeInterval']);
        logDebug('PhpTasks::get return ', $ret);
        return $ret;
    }
    
    
    
    public function fakeUnset($taskId)
    {
        logDebug('enter PhpTasks::fakeUnset', $taskId);
        if(!isset($this->_contents[$taskId]))
        {
            return true;
        }
        $this->_contents[$taskId]['status'] = self::STATUS_FAKEUNSET;
        return true;
    }
    

    public function realUnset($taskId)
    {
        logDebug('enter PhpTasks::realUnset', $taskId);
        if(isset($this->_contents[$taskId]))
        {
            unset($this->_contents[$taskId]);
        }
        return true;
    }
    

    public function unsetAll()
    {
        $this->_contents = array();
        return true;
    }
    
    
    public function getMaxTaskId()
    {
        return $this->_maxTaskId;
    }
    
}