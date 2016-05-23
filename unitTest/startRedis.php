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
 
/**
 * 一个使用redisTaskScheduler+redisTasks的demo
 */

require_once __DIR__.DIRECTORY_SEPARATOR.'..'.DIRECTORY_SEPARATOR.'Init.php';

$ip = '10.10.9.77';
$port = '6379';
$timeout = 2;
$redisClient = new \TaskSchedule\Libs\RedisWithReConnect($ip, $port, $timeout);
$scheduler = new \TaskSchedule\Core\TaskScheduler\RedisTaskScheduler($redisClient);
$tasks = new \TaskSchedule\Core\Tasks\RedisTasks($redisClient);
$runner = new \TaskSchedule\Core\TaskRunner();
$timeEvent = new \TaskSchedule\Core\TimeEvent\TimeEvent($scheduler, $tasks, $runner);

$timeSpace = 2;
$type = \TaskSchedule\Core\Tasks\TasksInterface::RUN_REPEATED;
$func = 'helloWorld';
$timeEvent->add($timeSpace, $type, $func, $args = null);
sleep(3);

$timeEvent->loop(5);


