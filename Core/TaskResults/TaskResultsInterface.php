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
 * 保存任务执行结果的容器。
 */
interface TaskResultsInterface
{    
    
    
    /**
     * 记录/新增某个任务Id的执行结果
     * @param int $taskId 任务ID
     * @param int $type 任务类型，一次性任务或长期定期任务
     * @param str $func 任务回调函数
     * @param arr $args 运行参数
     * @param arr $result 执行结果。
     * @param int $execTimeBegin 任务执行的开始时间。
     * @param int $execTimeEnd 任务执行的结束时间。
     * @return bool 
     */
    public function record($taskId, $type, $func, $args, $result, $execTimeBegin, $execTimeEnd, $scheduleExecTime);
    
    /**
     * 获得某个任务Id的执行结果
     * @param int $taskId
     * @return list($type, $status, $func, $args, $result, $execTimeBegin, $execTimeEnd)
     */
    public function getByTaskId($taskId);
    
    
}