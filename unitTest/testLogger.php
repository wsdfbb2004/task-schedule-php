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
 * 测试Logger类
 */

require_once '../Autoloader.php';
//echo 123; exit;

//$logger = new \TaskSchedule\Core\Logger();
use TaskSchedule\Core\Logger;
$logger = new Logger();
echo "\n----------------\n"; 
var_dump($logger);
echo "\n----------------\n";
//exit;

$hint = 'my first hint: ';
$orderId = '11898923213';
$orderDetail = array(
    'buyer' => 'xxx buyer',
    'seller' => 'yyy seller',
);
$vars = compact('orderId', 'orderDetail');
$logger->debug($hint, $vars);

$hint = 'my second hint: ';
$orderId = '11090909090';
$orderDetail = array(
    'buyer' => 'xxx2 buyer',
    'seller' => 'yyy2 seller',
);
$vars = compact('orderId', 'orderDetail');
$logger->debug($hint, $vars);

