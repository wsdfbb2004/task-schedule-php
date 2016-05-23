<?php
/**
 * This file is part of php-task-schedule.
 *
 * Licensed under The MIT License
 * For full copyright and license information, please see the MIT-LICENSE.txt
 * mysqlitributions of files must retain the above copyright notice.
 *
 * @author liuyin<1053734086@qq.com>
 * @copyright liuyin<1053734086@qq.com>
 * @link http://www.xxxxxxxx.net/
 * @license http://www.opensource.org/licenses/mit-license.php MIT License
 */
namespace TaskSchedule\Libs;

/**
 * 封装了下mysqli客户端，处理网络异常，加入自动重连机制。
 */
class MysqlWithReConnect
{    
    
    /**
     * @var str mysql的ip地址
     */
    protected $_ip = null;
    
    /**
     * @var int mysql的端口
     */
    protected  $_port = null;
    
    /**
     * @var str 用户名
     */
    protected  $_userName = null;
    
    /**
     * @var str 密码
     */
    protected  $_passwd = null;
    
    /**
     * @var str 数据库名
     */
    protected  $_dbName = null;
    
    /**
     * @var int mysqli客户端的连接超时设定
     */
    protected $_connTimeout = null;
    
    /**
     * @var int mysqli客户端的读超时设定
     */
    protected $_readTimeout = null;
    
    /**
     * @var int mysqli客户端的写超时设定
     */
    protected $_writeTimeout = null;
    
    /**
     * @var obj mysqli连接.
     */
    protected $_mysqli = null;
    
    /**
     * @var int 最后一次正常业务逻辑的网络访问时间
     */
    protected $_accessTime = null;
    
    /**
     * @var int 最后一次网络异常的时间
     */
    protected $_errorTime = null;
    
    /**
     * @var int 最后一次网络异常的错误码
     */
    protected $_errorNo = null;
    
    /**
     * @var str 最后一次网络异常的错误文本信息
     */
    protected $_errorMsg = null;
    
    /**
     * 读超时常量
     */
    const MYSQLI_OPT_READ_TIMEOUT = 11;

    /**
     * 写超时常量
     */
    const MYSQLI_OPT_WRITE_TIMEOUT = 12;
    
    
    /**
     * 构造函数，完成初始化，连接到mysqli。
     * @param int $ip ip
     * @param int $port 端口
     * @param int $user 用户名
     * @param int $pwd 密码
     * @param int $db 数据库名
     * @param int $connTimeout 客户端连接超时时间
     * @param int $readTimeout 客户端读超时时间
     * @param int $writeTimeout 客户端写超时时间
     */
    public function __construct($ip, $port, $user, $pwd, $db, $connTimeout = 2, $readTimeout = 2, $writeTimeout = 2)
    {
        $in = compact('ip', 'port', 'user', 'pwd', 'db', 'connTimeout', 'readTimeout', 'writeTimeout');
        logDebug('enter MysqlWithReConnect::__construct ', $in);
        $this->_ip = $ip;
        $this->_port = $port;
        $this->_userName = $user;
        $this->_passwd = $pwd;
        $this->_dbName = $db;
        $this->_connTimeout = $connTimeout;
        $this->_readTimeout = $readTimeout;
        $this->_writeTimeout = $writeTimeout;
        $this->connect();
    }
    
    
    /**
     * 连接mysqli。
     * @return obj or false
     */
    public function connect()
    {
        logDebug('enter MysqlWithReConnect::connect ');
        if(false == $this->_checkConnect()){
            return $this->_connect();
        }
        return $this->_mysqli;
    }
    
    
    /**
     * 检车mysqli连接是否正常。
     * @return bool
     */
    protected function _checkConnect()
    {
        logDebug('enter MysqlWithReConnect::_checkConnect ');
        if(empty($this->_mysqli)){
            logTrace('MysqlWithReConnect::_checkConnect return false');
            return false;    
        }
        if(!($this->_mysqli instanceof \mysqli)){
            logTrace('MysqlWithReConnect::_checkConnect return false');
            return false;
        }

        $ping = $this->_mysqli->ping();
        if(false == $ping){
            $this->_errorTime = time();
            $this->_errorNo = $this->_mysqli->errno;
            $this->_errorMsg = $this->_mysqli->error;
            logTrace('MysqlWithReConnect::_checkConnect return false');
            return false;
        }
        logTrace('MysqlWithReConnect::_checkConnect return true');
        return true;
    }
    
    
    /**
     * 连接mysqli。
     * @return obj or false
     */
    protected function _connect()
    {
        $this->_mysqli = null;

        $mysqli = mysqli_init();
        //logDebug('MysqlWithReConnect::_connect before connect mysqli', array($this->_ip, $this->_port, $this->_connTimeout));
        
        if (!$mysqli->options(MYSQLI_OPT_CONNECT_TIMEOUT, $this->_connTimeout)) {
            logTrace('MysqlWithReConnect::Setting MYSQLI_OPT_CONNECT_TIMEOUT failed');
            return false;
        }
        
        //mysqli似乎无法设置读写超时。。。难道是我的libmysqlclient版本有问题？
        
        $mysqli->options(self::MYSQLI_OPT_READ_TIMEOUT, $this->_readTimeout);
        /*
        if (!$mysqli->options(self::MYSQLI_OPT_READ_TIMEOUT, $this->_readTimeout)) {
            logTrace('MysqlWithReConnect::Setting MYSQLI_OPT_READ_TIMEOUT failed');
            return false;
        }
        */
        $mysqli->options(self::MYSQLI_OPT_WRITE_TIMEOUT, $this->_writeTimeout);
        /*
        if (!$mysqli->options(self::MYSQLI_OPT_WRITE_TIMEOUT, $this->_writeTimeout)) {
            logTrace('MysqlWithReConnect::Setting MYSQLI_OPT_WRITE_TIMEOUT failed');
            return false;
        }
        */        
        
        $connRes = $mysqli->real_connect($this->_ip, $this->_userName, $this->_passwd, $this->_dbName, $this->_port);
        //var_dump($this); exit;
        
        logDebug('MysqlWithReConnect::_connect real_connect ', $connRes);
        if(false === $connRes){
            return false;
        }
        $this->_mysqli = $mysqli;
        return $this->_mysqli;
    }
    
    
    /**
     * 将自身所有属性复制成一个数组，方便调试。
     * @return array
     */
    protected function _toArray()
    {
        $ret = array();
        foreach($this as $k => $v){
            $ret[$k] = $v;
        }
        return $ret;
    }
    
    
    /**
     * 除了connect和close外的所有mysqli命令通过这个函数中转。
     * @param int $method mysqli命令
     * @param int $args 命令的参数
     * @return bool
     */
    public function __call($method, $args)
    {
        $in = compact('method', 'args');
        logDebug('enter MysqlWithReConnect::__call ', $in);
        $method = strtolower($method);
        $allowCmd = array('query', 'begin_transaction', 'commit', 'rollback');
        if(!in_array($method, $allowCmd)){
            return false;
        }
        if(!method_exists('mysqli', $method)){
            return false;
        }

        $ret = call_user_func_array(array($this->_mysqli, $method), $args);
        //var_dump($args); exit;
        
        if(false === $ret){
            $this->_errorTime = time();
            $this->_errorNo = $this->_mysqli->errno;
            $this->_errorMsg = $this->_mysqli->error;
            logTrace("MysqlWithReConnect::_call  $method failed ", var_export($this, true));
            sleep(1);
            $this->_connect();
            logDebug('MysqlWithReConnect::_call return false');
            return false;
        }
        $this->_accessTime = time();
        logDebug('MysqlWithReConnect::_call return ', $ret);
        return $ret;
    }

    
}