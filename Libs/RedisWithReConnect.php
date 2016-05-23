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
namespace TaskSchedule\Libs;

/**
 * 封装了下phpRedis客户端，处理网络异常导致phpRedis抛出异常，加入自动重连机制。
 */
class RedisWithReConnect
{    
    
    /**
     * @var str redis的ip地址
     */
    protected $_ip = null;
    
    /**
     * @var int redis的端口
     */
    protected  $_port = null;
    
    /**
     * @var int redis客户端的超时设定
     */
    protected $_timeout = null;
    
    /**
     * @var obj redis连接.
     */
    protected $_redis = null;
    
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
     * 构造函数，完成初始化，连接到redis。
     * @param int $ip ip
     * @param int $port 端口
     * @param int $timeout 客户端网络读超时时间
     */
    public function __construct($ip, $port, $timeout = 2)
    {
        $in = compact('ip', 'port', 'timeout');
        logDebug('enter RedisWithReConnect::__construct ', $in);
        $this->_ip = $ip;
        $this->_port = $port;
        $this->_timeout = $timeout;
        $this->connect();
    }
    
    
    /**
     * 连接redis。
     * @return obj or false
     */
    public function connect()
    {
        logDebug('enter RedisWithReConnect::connect ');
        if(false == $this->_checkConnect()){
            return $this->_connect();
        }
        return $this->_redis;
    }
    
    
    /**
     * 检车redis连接是否正常。
     * @return bool
     */
    protected function _checkConnect()
    {
        logDebug('enter RedisWithReConnect::_checkConnect ');
        if(empty($this->_redis)){
            logTrace('RedisWithReConnect::_checkConnect return false');
            return false;    
        }
        if(!($this->_redis instanceof \Redis)){
            logTrace('RedisWithReConnect::_checkConnect return false');
            return false;
        }
        try{
            $this->_redis->ping();
        }
        catch(\RedisException $e){
            $this->_errorTime = time();
            $this->_errorNo = $e->getCode();
            $this->_errorMsg = $e->getMessage();
            logTrace('RedisWithReConnect::_checkConnect return false');
            return false;
        }
        logTrace('RedisWithReConnect::_checkConnect return true');
        return true;
    }
    
    
    /**
     * 连接redis。
     * @return obj or false
     */
    protected function _connect()
    {
        $this->_redis = null;
        try{
            $this->_redis = new \Redis();
            //$this->_redis->setOption(\Redis::OPT_READ_TIMEOUT, $this->_timeout);//神坑，setOption是在connect之后才能用。
            logDebug('RedisWithReConnect::_connect before connect redis', array($this->_ip, $this->_port, $this->_timeout));
            $this->_redis->connect($this->_ip, $this->_port, $this->_timeout);
            $this->_redis->setOption(\Redis::OPT_READ_TIMEOUT, $this->_timeout);
        }
        catch(\RedisException $e){
            $this->_errorTime = time();
            $this->_errorNo = $e->getCode();
            $this->_errorMsg = $e->getMessage();
            logDebug('RedisWithReConnect::_connect return false', var_export($this, true));
            return false;
        }
        logDebug('RedisWithReConnect::_connect return ', var_export($this->_redis, true));
        return $this->_redis;
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
     * 除了connect和close外的所有redis命令通过这个函数中转。
     * @param int $method redis命令
     * @param int $args 命令的参数
     * @return bool
     */
    public function __call($method, $args)
    {
        $in = compact('method', 'args');
        logDebug('enter RedisWithReConnect::__call ', $in);
        $method = strtolower($method);
        $connectCmd = array('connect', 'pconnect', 'close');
        if(in_array($method, $connectCmd)){
            return false;
        }
        if(!method_exists('Redis', $method)){
            return false;
        }
        try{
            $ret = call_user_func_array(array($this->_redis, $method), $args);
        }
        catch(\RedisException $e){
            $this->_errorTime = time();
            $this->_errorNo = $e->getCode();
            $this->_errorMsg = $e->getMessage();
            logTrace("RedisWithReConnect::_call  $method failed ", var_export($this, true));
            sleep(1);
            $this->_connect();
            logDebug('RedisWithReConnect::_call return false');
            return false;
        }
        $this->_accessTime = time();
        logDebug('RedisWithReConnect::_call return ', $ret);
        return $ret;
    }

    
}