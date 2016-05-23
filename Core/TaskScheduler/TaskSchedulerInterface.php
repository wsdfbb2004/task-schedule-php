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
 * 保存任务调度顺序的容器，实际是个任务优先队列
 */
interface TaskSchedulerInterface
{    
    /**
     * 将任务添加到调度队列中
     * @param int $taskId 任务ID
     * @param int $prior 任务优先级
     * @return bool 
     */
    public function insert($taskId, $prior);
    
    /**
     * 从调度队列中中提取出最高优先级的任务（如果提取成功，调度队列中会清除这个任务）
     * @param int $timeout 超时时间，单位：秒
     * @return list ($taskId, $prior)
     */
    public function extractTop($timeout = null);
    
    /**
     * 清空任务调度队列
     * @return void
     */
    public function clearAll();
    
    /**
     * 从调度队列中清除taskId
     * @param int $taskId 任务ID
     * @return bool
     */
    //貌似php的优先队列无法实现此接口，除非遍历一遍。。。
    public function del($taskId);
        
}