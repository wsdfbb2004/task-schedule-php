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
 * 一个使用redisTaskScheduler+redisTasks+MysqlTaskResults的demo
 */

require_once __DIR__.DIRECTORY_SEPARATOR.'..'.DIRECTORY_SEPARATOR.'Init.php';

$mysqlIp = '10.10.10.107';
$mysqlPort = '3306';
$mysqlUser = 'root';
$mysqlPwd = '123456';
$mysqlDbName = 'PersonPay';
$mysqlTimeout = 2;
$mysqlClient = new \TaskSchedule\Libs\MysqlWithReConnect($mysqlIp, $mysqlPort, $mysqlUser, $mysqlPwd, $mysqlDbName, $mysqlTimeout);
$taskResultTable = 'taskResults';
$taskResult = new \TaskSchedule\Core\TaskResults\MysqlTaskResults($mysqlClient, $taskResultTable);


//echo 123; exit;


$redisIp = '10.10.9.77';
$redisPort = '6379';
$redisTimeout = 2;
$redisClient = new \TaskSchedule\Libs\RedisWithReConnect($redisIp, $redisPort, $redisTimeout);

//$scheduler = new \TaskSchedule\Core\TaskScheduler\RedisTaskScheduler($redisClient);
$scheduler = new \TaskSchedule\Core\TaskScheduler\RedisTaskSchedulerWithTransaction($redisClient);
$tasks = new \TaskSchedule\Core\Tasks\RedisTasks($redisClient);
$runner = new \TaskSchedule\Core\TaskRunner($taskResult);
$timeEvent = new \TaskSchedule\Core\TimeEvent\TimeEvent($scheduler, $tasks, $runner);

//echo 456; exit;


$timeSpace = 2;
$type = \TaskSchedule\Core\Tasks\TasksInterface::RUN_REPEATED;
$func = 'helloWorld';
$limit = null;

//注意，在add任务之前，先初始化下maxTaskId。
$timeEvent->add($timeSpace, $type, $limit, $func, $args = null);
//exit;

sleep(3);

$timeEvent->loop(5);

