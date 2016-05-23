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
 * RedisTaskScheduler加上事务控制,保证每一个接口都是原子化的操作。貌似要使用乐观锁实现。
 * 优点：因为支持上层多个机器上的TimeEvent/TaskRunner共享一个redis集群中的TaskScheduler，利于TimeEvent集群部署/横向扩展。
 * 缺点：网络IO较多。
 */
class RedisTaskSchedulerWithTransaction extends RedisTaskScheduler implements TaskSchedulerInterface
{    
    
    public function extractTop()
    {
        $this->_redis->watch($this->_key);
        $top = $this->_redis->zrevrange($this->_key, 0, 0, true);
        if(empty($top))
        {
            $this->_redis->unwatch($this->_key);
            return null;
        }
        $ret = array(key($top), current($top));
        $res = $this->_redis->multi()->zrem($this->_key, key($top))->exec();
        if(empty($res))
        {
            //$this->_redis->unwatch($this->_key);
            return null;
        }
        return $ret;
    }
    
    
    
    
}