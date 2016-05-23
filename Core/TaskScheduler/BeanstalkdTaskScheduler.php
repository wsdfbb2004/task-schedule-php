<?php
/**
 * This file is part of php-task-schedule.
 *
 * Licensed under The MIT License
 * For full copyright and license information, please see the MIT-LICENSE.txt
 * beanstalktributions of files must retain the above copyright notice.
 *
 * @author liuyin<1053734086@qq.com>
 * @copyright liuyin<1053734086@qq.com>
 * @link http://www.xxxxxxxx.net/
 * @license http://www.opensource.org/licenses/mit-license.php MIT License
 */
namespace TaskSchedule\Core\TaskScheduler;

/**
 * 将任务调度顺序保存在beanstalkd中, todo, 未完成。
 * 
 * 优点：因为支持上层多个机器上的TimeEvent/TaskRunner共享一个beanstalkd实例中的TaskScheduler，利于TimeEvent集群部署/横向扩展。
 * 缺点：网络IO较多。
 * 摒弃了beanstalkd的任务超时/失败重试机制，即：reserve之后，立即delete，只保留DELAYED和READY态，不用RESERVED和BURIED态。
 * 优点：代码简洁，避免如下case：
 * 1. 一个任务在reserve之后，如果不bury/delete, 如果执行超时会被调度多次，在该任务为周期任务的时候是很可怕的。
 * 2. 一个周期任务在reserve之后，如果bury之后， 因为执行时间过长/失败，将保留为BURIED态。在不明确该BURIED态是因为某个消费者执行超时 还是因为执行失败 导致的情况下，
 * 如果消费者都不管，会阻塞后续调度该任务；如果一个消费者手贱管了，可能会被调度多次。
 * 缺点：
 * 1.任务某次执行失败后不会重试
 * 2.delete+renew任务因为是两个操作，周期任务调度有可能丢失。不过beanstalkd貌似也没有一个原子化的操作来实现delete+renew
 */
 
use Pheanstalk\PheanstalkInterface;
use Pheanstalk\Job;

/*
 * 暂时不考虑beanstalkd server端错误，导致Pheanstalk抛出异常。
 **/

class BeanstalkdTaskScheduler implements TaskSchedulerInterface
{    
    
    /**
     * @var obj beanstalk连接，用于存储优先队列
     */
    protected $_beanstalk = null;
    
    /**
     * @var 优先队列在beanstalkd中的tube/key名
     */
    protected $_key = null;
    
    /**
     * @var arr 消费者reserve的任务。
     * array(
     *      $taskId => $job,
     * )
     */
    protected $_jobReserved = array();
    
    
    /*
     * 注意pheanstalk有可能抛出异常。。。外面注意catch下。。。
     * */
    public function __construct(\Pheanstalk\Pheanstalk $beanstalk, $tube = 'priorQueque')
    //public function __construct($beanstalk, $tube = 'priorQueque')
    {
        $this->_key = $tube;
        $this->_beanstalk = $beanstalk;
        $this->_beanstalk->watch($tube);
        $this->_beanstalk->useTube($tube);
    
    }
    
    
        
    public function insert($taskId, $prior)
    {
        $data = compact('taskId', 'prior');
        logDebug("enter BeanstalkTaskScheduler:insert, ", $data);
        $delay = 0 - $prior - time();
        $delay = ($delay > 0) ? $delay : 0;
        try{
            $jobId = $this->_beanstalk->put(json_encode($data), PheanstalkInterface::DEFAULT_PRIORITY, $delay);
            logDebug("enter BeanstalkTaskScheduler:insert return true, ", $jobId);
            return true;
        }
        catch(\Exception $e){
            $errHint = array(
                'errmsg' => $e->getMessage(),
                'errno' => $e->getCode(),
                'errline' => $e->getLine(),
            );
            logDebug("enter BeanstalkTaskScheduler:insert return false, ", $errHint);
            return false;
        }
    }
    
    
    public function extractTop($timeout = null)
    {
        logDebug("enter BeanstalkTaskScheduler:extractTop, timeout: ", $timeout);
        try{
            $job = $this->_beanstalk->reserve($timeout);
            if(false == $job){
                logDebug("BeanstalkTaskScheduler:extractTop, reserve failed ");
                return false;
            }
            //$jobId = $job->getId();
            $data = $job->getData();
            logDebug("BeanstalkTaskScheduler:extractTop, reserve success, get data: ", $data);
            if(is_array($data) && isset($data['taskId']) && isset($data['prior'])){
                $this->_jobReserved[$data['taskId']] = $job;
            }
            $this->_beanstalk->delete($job);
            unset($this->_jobReserved[$data['taskId']]) ;
            
            $data = json_decode($data, true);
            if(!is_array($data) || !isset($data['taskId']) || !isset($data['prior'])){
                return false;
            }
            return array($data['taskId'], $data['prior']);
        }
        catch(\Exception $e){
            $errHint = array(
                'errmsg' => $e->getMessage(),
                'errno' => $e->getCode(),
                'errline' => $e->getLine(),
            );
            logDebug("enter BeanstalkTaskScheduler:insert return false, ", $errHint);
            return false;
        }
    }
    
    
    /*
     * 注意，beanstalkd比较蛋疼，没有直接删除一个tube的接口。坑啊。。。
     * 只好写死循环将一个tube内的所有job逐个清除。。。
     * */
    public function clearAll()
    {
        while(($top = $this->extractTop()));
    }
    
    
    /*
     * 注意，beanstalkd比较蛋疼，只有reserve该jobid的消费者才能delete任务调度。坑啊。。。
     * */
    public function del($taskId)
    {
        logDebug("enter BeanstalkTaskScheduler:del, taskId: ", $taskId);
        if(empty($this->_jobReserved[$taskId])){
            return false;
        }
        try{
            $this->_beanstalk->delete($this->_jobReserved[$taskId]);
        }
        catch(\Exception $e){
            $errHint = array(
                'errmsg' => $e->getMessage(),
                'errno' => $e->getCode(),
                'errline' => $e->getLine(),
            );
            logDebug("enter BeanstalkTaskScheduler:insert return false, ", $errHint);
            return false;
        }
    }
    
    
    /*
     * 抛出底层beanstalk连接。
     * */
    public function getBeanstalkConn()
    {
        return $this->_beanstalk;
    }
    
    
}