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
 * 入口代码，必须加载。初始化共用变量，如:logger。
 * @author liuyin<1053734086@qq.com>
 */

/*
var_dump(getcwd()); exit;
require_once './Autoloader.php';
*/

require_once __DIR__.DIRECTORY_SEPARATOR.'Autoloader.php';

function logDebug($hint, $vars = ''){
    $logger = new \TaskSchedule\Core\Logger(2);
    $logger->debug($hint, $vars);
}

function logTrace($hint, $vars = ''){
    $logger = new \TaskSchedule\Core\Logger(2);
    $logger->trace($hint, $vars);
}

function logNotice($hint, $vars = ''){
    $logger = new \TaskSchedule\Core\Logger(2);
    $logger->notice($hint, $vars);
}

function logWarnning($hint, $vars = ''){
    $logger = new \TaskSchedule\Core\Logger(2);
    $logger->warnning($hint, $vars);
}

function logFatal($hint, $vars = ''){
    $logger = new \TaskSchedule\Core\Logger(2);
    $logger->fatal($hint, $vars);
}
