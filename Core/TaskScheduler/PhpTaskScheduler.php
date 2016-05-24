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
 * 将任务调度顺序保存在单实例php内存中，php SplPriorityQueue实现。
 * 优点：不支持上层多个机器上的TimeEvent/TaskRunner。
 * 优点：无网络IO，简单有效。
 * 回头看下SplPriorityQueue是最大堆还是最小堆,先假定是最大堆。
 */
class PhpTaskScheduler implements TaskSchedulerInterface
{    
    
    /**
     * @var obj php SplPriorityQueue
     */
    protected $_queque = null;
    
    
    public function __construct()
    {
        logDebug('enter phpTaskScheduler::__construct ');
        $this->_queque = $this->_initQueque();
    }
    
    
    protected function _initQueque()
    {
        $queque = new \SplPriorityQueue();
        $queque->setExtractFlags(\SplPriorityQueue::EXTR_BOTH);
        return $queque;
    }
    
        
    public function insert($taskId, $prior)
    {
        $in = compact('taksId', 'prior');
        logDebug('enter phpTaskScheduler::insert ', $in);
        $ret = $this->_queque->insert($taskId, $prior);
        logDebug('phpTaskScheduler::insert return', $ret);
        return $ret;
    }
    
    
    public function extractTop($timeout = null)
    {
        logDebug('enter phpTaskScheduler::extractTop ');
        $top = $this->_queque->extract();
        $ret = array($top['data'], $top['priority']);
        logDebug('phpTaskScheduler::extractTop return', $ret);
        return $ret;
    }
    
    
    public function clearAll()
    {
        $this->_queque = $newQueque = $this->_initQueque();
        if($newQueque){
            $ret = true;
        }
        else{
            $ret = false;
        }
        logDebug('phpTaskScheduler::clearAll return', $ret);
        return $ret;
    }
    
    
    public function del($taskId)
    {
        logDebug('enter phpTaskScheduler::del ', $taskId);
        $newQueque = $this->_initQueque();
        if(!$newQueque){
            return false;
        }
        while(($top = $this->_queque->extract()) && !empty($top)){
            if($top['data'] != $taskId){
                $newQueque->insert($top['data'], $top['priority']);
            }
        }
        $this->_queque = $newQueque;
        return true;
    }
    
    
    
}