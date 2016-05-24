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
 
namespace TaskSchedule\Core\TimeEvent;

use TaskSchedule\Core\Tasks;
use TaskSchedule\Core\TaskScheduler;
use TaskSchedule\Core;

/**
 * 定时任务的内容管理/调度执行
 */
class TimeEvent implements TimeEventInterface
{    
    
    /**
     * 调度器，任务优先队列。
     * @var obj
     */
    protected $_scheduler = null;
    
    /**
     * 任务们。
     * @var obj
     */
    protected $_tasks = null;
    
    /**
     * 执行器。
     * @var obj
     */
    protected $_runner = null;
    
    /**
     * 已经分配的任务id。
     * @var int
     */
    protected $_taskId = 0;
    
    /**
     * 执行loop的起始时间,在刚进入loop时被设置，离开loop时被清空。
     * @var int
     */
    protected $_startTime = null;
    
    
    public function __construct(TaskScheduler\TaskSchedulerInterface $scheduler, Tasks\TasksInterface $tasks, Core\TaskRunner $runner)
    {
        $this->_scheduler = $scheduler; 
        $this->_tasks = $tasks;
        $this->_runner = $runner;
    }
    

    public function __destruct()
    {
        
    }

    
    /*
     * todo:
     * 1.taskId的生成需要重新考虑，现有实现会形成单点，不利于redisTasks分布式部署。
     * */
    public function add($time, $type, $limit, $func, $args = null)
    {
        $in = compact('time', 'type', 'limit', 'func', 'args');
        logDebug('enter TimeEvent::add ', $in);
        $timeNow = time();
        $prior = 0 - ($timeNow + $time);
        //$this->_taskId++;
        $taskId = $this->_tasks->getMaxTaskId();
        //var_dump($taskId); exit;
        if(false === $taskId){
            return false;
        }
        $taskId++;
        $this->_tasks->set($taskId, $type, $limit, $func, $args, $time);
        $this->_scheduler->insert($taskId, $prior);
        return true;
        
    }
    

    public function del($taskId, $atonce = false)
    {
        $in = compact('taskId', 'atonce');
        logDebug('enter TimeEvent::del ', $in);
        //return $this->_tasks->fakeUnset($this->_taskId);
        if(false === $atonce){
            return $this->_tasks->fakeUnset($taskId);
        }
        else{
            $res = $this->_scheduler->del($taskId);
            if(false == $res){
                return false;
            }
            return $this->_tasks->realUnset($taskId);
        }
    }
    
    
    /*
     * todo:
     * 1.如何避免loop因为阻塞io导致hang住迟迟不返回客户端代码？将来考虑使用libEvent或者sigalarm实现runTimeLen?
     * 2.现有设计是将任务调度器scheduler和任务的存储tasks拆开的。
     * 优势为scheduler和tasks可以分别使用不同的底层库实现，保持灵活，例如：BeanstalkScheduler + RedisTasks.
     * 缺陷为scheduler和tasks存在分布式一致性问题，例如：scheduler->extractTop之后,如果tasks->get发现是周期性任务，需要重新scheduler->insert,这中间可能会失败，造成任务调度丢失
     * 那么到底是否有必要将任务调度器scheduler和任务的存储tasks拆开,比较纠结,似乎设计上应该直接将这两者融为一体，写成一个对象，部署在一个实例上。
     * 3.如何将任务分布式的问题：由业务方来决定任务的分布式，类似于memcache的思路？
     * 
     */
    public function loop($runTimeLen)
    {
        logDebug('enter TimeEvent::loop ', $runTimeLen);
        $this->_startTime = time();
        while(true){
            $timeNow = time();
            $TTR = $this->_startTime + $runTimeLen - $timeNow;
            if($TTR < 0){
                return ;
            }
            $ret = $this->_scheduler->extractTop($TTR);
            if(empty($ret)){
                return ;
            }
            list($taskId, $prior) = $ret;
            $timeExec = 0 - $prior;
            if($timeExec > $timeNow){
                $this->_scheduler->insert($taskId, $prior);
                return ;
            }
            $ret = $this->_tasks->get($taskId);
            if(empty($ret)){
                //return ;
                continue; 
            }
            list($type, $limit, $status, $func, $args, $timeInterval) = $ret;
            
            //如果是周期性任务，并且任务不是“伪删除”状态，将其重新加入到调度器中。
            $tasksClass = get_class($this->_tasks);
            if(($tasksClass::RUN_REPEATED == $type)
                && ($tasksClass::STATUS_READY == $status)
            ){
                $this->_scheduler->insert($taskId, (0 - $timeExec - $timeInterval));
            }
            elseif($tasksClass::RUN_REPEATED_LIMIT == $type)
            {
                if(1 >= $limit)
                {
                    $ret = $this->_tasks->realUnset($taskId);
                }
                else
                {
                    $limit--;
                    $this->_scheduler->insert($taskId, (0 - $timeExec - $timeInterval));
                    $this->_tasks->set($taskId, $type, $limit, $func, $args, $timeInterval);
                }
            }
            elseif ($tasksClass::STATUS_READY != $status)
            {
                $ret = $this->_tasks->realUnset($taskId);
            }
            //var_dump($args); exit;
            $this->_runner->run($func, $args, $taskId, $type, $timeExec);
            //return ;
        }            
    }
        

    
}