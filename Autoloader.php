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
namespace TaskSchedule;

/**
 * 自动加载类
 * @author walkor<walkor@workerman.net>
 * revised by liuyin<1053734086@qq.com>
 */
class Autoloader
{
    // 应用的初始化目录，作为加载类文件的参考目录
    protected static $_appInitPath = '';
    
    /**
     * 设置应用初始化目录
     * @param string $root_path
     * @return void
     */
    public static function setRootPath($root_path)
    {
          self::$_appInitPath = $root_path;
    }

    /**
     * 根据命名空间加载文件
     * @param string $name
     * @return boolean
     */
    public static function loadByNamespace($name)
    {
        //var_dump($name);
        if(empty(self::$_appInitPath))
        {
            self::$_appInitPath = __DIR__;
        }
        // 相对路径
        $class_path = str_replace('\\', DIRECTORY_SEPARATOR ,$name);
        // 如果不是TaskSchedule命名空间，直接跳出
        $baseSpace = 'TaskSchedule\\';
        $baseSpaceStrLen = strlen($baseSpace);
        if(0 !== strpos($name, $baseSpace))
        {
            return false;
        }
        else 
        {
            $class_path = substr($class_path, $baseSpaceStrLen);
            $class_file = self::$_appInitPath . DIRECTORY_SEPARATOR . $class_path.'.php';
        }
       
        // 找到文件
        if(is_file($class_file))
        {
            // 加载
            require_once($class_file);
            if(class_exists($name, false))
            {
                return true;
            }
        }
        return false;
    }
}
// 设置类自动加载回调函数
spl_autoload_register('\TaskSchedule\Autoloader::loadByNamespace');