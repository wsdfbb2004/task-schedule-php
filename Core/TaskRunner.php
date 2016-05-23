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
 
namespace TaskSchedule\Core;

/**
 * 任务执行器，将任务转发到实际的handler上，实现任务名到handler的映射。作用类似于router。
 */
class TaskRunner
{    

    /**
     * @var obj 存储任务执行结果的容器
     */
    protected $_taskResults = null;
    
    
    //public function __construct()
    public function __construct(TaskResults\TaskResultsInterface $taskResults = null)
    {
        $this->mountHandler();
        $this->_taskResults = $taskResults;
    }
    
    
    /**
     * 挂载handler，建立任务名和handler的映射关系。
     * @param str $func 任务函数名
     * @param arr $args 参数
     * @return bool
     */
    protected function mountHandler()
    {
        // 载入该服务下的所有文件
        foreach(glob(__DIR__ . '/Handlers/'.'/*.php') as $php_file)
        {
            require_once $php_file;
        }
                
    }
    
    
    /**
     * 执行任务
     * @param str $func 任务函数名
     * @param arr $args 参数
     * @param int $taskId 任务Id
     * @param int $type 任务类型
     * @param int $scheduleExecTime 预计执行时间
     * @return bool 
     */
    public function run($func, $args, $taskId=null, $type=null, $scheduleExecTime = null)
    {
        $in = compact('func', 'args');
        logDebug('enter TaskRunner::run', $in);
        // 检查类是否存在
        $handlerFuncName = "\\TaskSchedule\\Core\\Handlers\\". $func;
        if(!function_exists($handlerFuncName))
        {
            return;
        }
        $execTimeBegin = time();
        $res = $handlerFuncName($args);
        $execTimeEnd = time();
        if(!empty($this->_taskResults) && ($this->_taskResults instanceof TaskResults\TaskResultsInterface)){
            $this->_taskResults->record($taskId, $type, $func, $args, $res, $execTimeBegin, $execTimeEnd, $scheduleExecTime);
        }
        return $res;
    }
    
    
}