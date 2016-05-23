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
 * 一个使用PhpTaskScheduler+PhpTasks的demo
 */

//require_once __DIR__.DIRECTORY_SEPARATOR.'..'.DIRECTORY_SEPARATOR.'Autoloader.php';
require_once __DIR__.DIRECTORY_SEPARATOR.'..'.DIRECTORY_SEPARATOR.'Init.php';

$scheduler = new \TaskSchedule\Core\TaskScheduler\PhpTaskScheduler();
$tasks = new \TaskSchedule\Core\Tasks\PhpTasks();
$runner = new \TaskSchedule\Core\TaskRunner();
$timeEvent = new \TaskSchedule\Core\TimeEvent\TimeEvent($scheduler, $tasks, $runner);

$timeSpace = 0.1;
$type = \TaskSchedule\Core\Tasks\TasksInterface::RUN_REPEATED;
$func = 'helloWorld';
$timeEvent->add($timeSpace, $type, $func, $args = null);
sleep(3);

$timeEvent->loop(2);

