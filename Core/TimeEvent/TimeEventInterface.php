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

/**
 * 定时任务的内容管理/调度执行
 */
interface TimeEventInterface
{    
    /**
     * 添加定时任务
     * @param int $time 期望任务将于$time秒后执行,如果是定时任务，每隔$time执行一次
     * @param int $type 任务类型，一次性任务或长期定期任务
     * @param str $func 任务回调函数
     * @return int $taskId 
     */
    public function add($time, $type, $func, $args = null);
    
    /**
     * 删除定时任务
     * @param int $taskId
     * @param bool $atonce true：立即删除任务；false：标记为伪删除，等下次执行完成后再删除
     * @return bool
     */
    public function del($taskId, $atonce = false);
    
    /**
     * 调度并执行任务。
     * 在运行指定时间分片后跳出并返回控制权到客户端代码，客户端代码可以去执行其它逻辑，客户端代码也可以直接退出。
     * @param int $runTimeLen
     * @return bool
     */
    public function loop($runTimeLen);
        
    /**
     * 清除所有定时任务
     * @return void
     */
    //public function delAll();
    
}