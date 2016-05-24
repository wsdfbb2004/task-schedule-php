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
 * 保存任务内容的容器，实现taskid=>任务内容的映射
 */
interface TasksInterface
{    
    
    /**
     * 周期性任务，重复执行无数次
     */
    const RUN_REPEATED = 1;
    
    /**
     * 一次性性任务
     */
    const RUN_ONCE = 2;
    
    /**
     * 周期性任务,但是有执行次数限制
     */
    const RUN_REPEATED_LIMIT = 3;
    
    /**
     * 正常等待运行状态
     */
    const STATUS_READY = 1;
    
    /**
     * 伪删除状态。等待最后一次被执行后，将被移除。
     */
    const STATUS_FAKEUNSET = 2;
    
    /**
     * 添加任务
     * @param int $taskId 任务ID
     * @param int $type 任务类型，一次性任务或长期定期任务
     * @param int $limit 如果是限制次数的周期任务，为执行任务的次数；其余case，此参数无效
     * @param str $func 任务回调函数
     * @param str $args 运行参数
     * @param str $timeInterval 如果是周期性任务，为指定运行时间间隔。
     * @return bool 
     */
    public function set($taskId, $type, $limit, $func, $args = null, $timeInterval = 2);
    
    /**
     * 获得任务内容
     * @param int $taskId
     * @return list($type, $limit, $status, $func, $args, $timeInterval)
     */
    public function get($taskId);
    
    /**
     * 伪删除任务
     * @param int $taskId
     * @return bool
     */
    public function fakeUnset($taskId);
    
    /**
     * 删除任务
     * @param int $taskId
     * @return bool
     */
    public function realUnset($taskId);
    
    /**
     * 清除所有任务
     * @return void
     */
    public function unsetAll();
    
    /**
     * 获取最大的任务Id
     * @return void
     */
    public function getMaxTaskId();
    
}