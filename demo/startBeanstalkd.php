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
 * 一个使用BeanStalkTaskScheduler+redis+RedisTasks+MysqlTaskResults的demo
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

require_once __DIR__.DIRECTORY_SEPARATOR.'..'.DIRECTORY_SEPARATOR.'Libs-ThirdParty' . DIRECTORY_SEPARATOR . 'pheanstalkd'. DIRECTORY_SEPARATOR. 'autoload.php';
//echo 456; exit;

$beanstalkdIp = '10.10.9.77'; 
//$beanstalkdIp = '127.0.0.1';
$beanstalkdPort = 11300;
$beanstalkdConnTimeout = 2;
try
{
    //$pheanstalkClient = 1;
    $pheanstalkClient = new \Pheanstalk\Pheanstalk($beanstalkdIp, $beanstalkdPort, $beanstalkdConnTimeout);
    $scheduler = new \TaskSchedule\Core\TaskScheduler\BeanstalkdTaskScheduler($pheanstalkClient);
    logDebug("demo startBeanstalkd, new BeanstalkdTaskScheduler success");
}
catch(\Exception $e)
{
    $errHint = array(
        'errmsg' => $e->getMessage(),
        'errno' => $e->getCode(),
        'errline' => $e->getLine(),
    );
    logFatal("demo startBeanstalkd, new BeanstalkdTaskScheduler failed", $errHint);
    exit;
}


//echo 456; exit;


$redisIp = '10.10.9.77';
$redisPort = '6379';
$redisTimeout = 2;
$redisClient = new \TaskSchedule\Libs\RedisWithReConnect($redisIp, $redisPort, $redisTimeout);


$tasks = new \TaskSchedule\Core\Tasks\RedisTasks($redisClient);
$runner = new \TaskSchedule\Core\TaskRunner($taskResult);
$timeEvent = new \TaskSchedule\Core\TimeEvent\TimeEvent($scheduler, $tasks, $runner);
logDebug("demo startBeanstalkd, new timeEvent success");



$timeSpace = 2;
$type = \TaskSchedule\Core\Tasks\TasksInterface::RUN_REPEATED;
$func = 'helloWorld';

//注意，在add任务之前，先初始化下maxTaskId。
//$timeEvent->add($timeSpace, $type, $func, $args = null);
logDebug("demo startBeanstalkd, timeEvent add success");
//echo 789; exit;

sleep(3);

$timeEvent->loop(5);

