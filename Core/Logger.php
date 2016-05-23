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
 
namespace TaskSchedule\Core;

/**
 * 日志
 */
class Logger
{        
	const DEBUG = 1;
	const TRACE = 2;
	const NOTICE = 4;
	const WARNNING = 8;
	const FATAL = 16;
	
    protected static $_logLevel = array(
            self::DEBUG => 'DEBUG',
            self::TRACE => 'TRACE',
            self::NOTICE => 'NOTICE',
            self::WARNNING => 'WARNNING',
            self::FATAL => 'FATAL',
	);
    
    protected static $_logPath = null;
	
    protected static $_logFile = array(
            self::DEBUG => 'taskschedule.log',
            self::WARNNING => 'taskschedule.log.wf',
    );
    
    protected static $_logFd = null;
    
    protected static $_logid = null;
    
    protected static $_reqUrl = null;
    
    protected static $_reqType = null;
    
    protected static $_clientIp = null;
    
    /*
     *假设php内核在调用文件系统write之前加了缓存，我就先不加缓存了。 
    const CACHESIZEMAX = 1024000;
    protected static $_logCache = '';
    */
    
    protected $_wrapLevel = 1;
    
	//public function __construct($path = null, $logid = null, $url = null, $clientIp = null)
    public function __construct($wrapLevel = 1)
    {
        self::setLogPath(self::$_logPath);
        self::_openLog();
        self::setLogid(self::$_logid);
        //self::setReqUrl(self::$_reqUrl);
        self::setClientIp(self::$_clientIp, self::$_reqUrl, self::$_reqType);
        $this->_wrapLevel = $wrapLevel;
    }	
	
    
	public static function setLogPath($path = null)
	{
	    if(is_null($path) || !is_string($path))
	    {
	        $path = __DIR__ . DIRECTORY_SEPARATOR. '..'. DIRECTORY_SEPARATOR . 'logs';
	    }
	    if(!is_dir($path)){
	        mkdir($path, 0700, true);
	    }
	    self::$_logPath = $path;
	}
	
	
	protected static function _openLog()
	{
	    if(empty(self::$_logPath) || (!is_dir(self::$_logPath)))
	    {
	        return false;
	    }
	    foreach (self::$_logFile as $level => $file)
	    {
	        $file = self::$_logPath. DIRECTORY_SEPARATOR . $file;
	        @$fd = fopen($file, 'a');
	        if(empty($fd))
	        {
	            self::$_logFd = null;
	            return false;
	        }
	        self::$_logFd[$level] = $fd;
	    }
	    return true;
	}
	
	
	public static function setLogid($logid = null)
	{
	    if(is_null($logid) || !is_string($logid))
	    {
	        $logid = uniqid();
	    }
	    self::$_logid = $logid;
	}
	
	
	public static function setReqUrl($url = null)
	{
	    if(is_null($url) || !is_string($url))
	    {
            if(php_sapi_name() !== 'cli')
	        {
	            $url = $_SERVER['REQUEST_URI'];
	        }
	        else
	        {
	            $url = ' ';
	        }
	    }
	    self::$_reqUrl = $url;
	}
	
	
	/*
	 * 这个地方写的很不好，几个变量还有与setReqUrl耦合在一起了
	 * */
	public static function setClientIp($clientIp = null, $url = null, $reqType = null)
	{
	    if(is_null($clientIp) || !is_string($clientIp))
	    {
	        if(php_sapi_name() !== 'cli')
	        {
	            $url = $_SERVER['REQUEST_URI'];
	            $clientIp = $_SERVER['REMOTE_ADDR'];
	            $reqType = 'web';
	        }
	        else
	        {
	            $url = '';
	            $clientIp = '127.0.0.1';
	            $reqType = 'cli';
	        }
	    }
	    self::$_reqUrl = $url;
	    self::$_clientIp = $clientIp;
	    self::$_reqType = $reqType;
	}

	
	public function debug($hint, $vars)
	{
		return $this->put(self::DEBUG, $vars, $hint);
	}

	
	public function notice($hint, $vars)
	{
	    return $this->put(self::NOTICE, $vars, $hint);
	}
	
	
	public function warnning($hint, $vars)
	{
	    return $this->put(self::WARNNING, $vars, $hint);
	}
	
	
	public function fatal($hint, $vars)
	{
	    return $this->put(self::FATAL, $vars, $hint);
	}
	
	
	public function trace($hint, $vars)
	{
	    return $this->put(self::TRACE, $vars, $hint);
	}
	
	
	/**
	 * 日志记录文件
	 * @param str $logLevel 日志级别
	 * @param str $hint 文本提示
	 * @param mix $vars 需要打印的变量
	 * @return bool
	 */
	public function put($logLevel, $vars, $hint = ' ')
	{
		if (!isset(self::$_logLevel[$logLevel]))
		{
		    return false;
		}
		$logLevelMin = min(array_keys(self::$_logFile));
		if($logLevel < $logLevelMin)
		{
		    return true;
		}
		foreach (self::$_logFile as $level => $file)
	    {
	        if($logLevel < $level)
	        {
	            break;
	        }
	        $lastLevel = $level;
	    }
	    $fd = self::$_logFd[$lastLevel];
		
		if(is_resource($vars))
		{ 
		    $vars = "resources is encoded as null";
		}
		else
		{
		    //$vars = json_encode($vars);
		    $vars = @json_encode($vars);
		}
		list($usec, $sec) = explode(" ", microtime());
		$time = date('Y-m-d H:i:s', $sec);
		$usec = floor($usec * 1000000);
		
		$trace = debug_backtrace(DEBUG_BACKTRACE_IGNORE_ARGS);
		if(is_array($trace) && isset($trace[1]['file'])){
		    $file = 'file : '.$trace[$this->_wrapLevel]['file'].'  line: '.$trace[$this->_wrapLevel]['line'];
		}
		
		$logLevel = self::$_logLevel[$logLevel];
		$content = "[{$logLevel}] [{$time} {$usec}] [logid = ". self::$_logid ."] [reqType = ". self::$_reqType . "] [reqUrl = ".self::$_reqUrl. "] [clientIp = ". self::$_clientIp. "] [{$file}] [{$hint},  {$vars}]\n";
		return self::_write($fd, $content);
	}
	
	
	protected static function _write($fd, $data){
		if (empty($data)) return true;
		@$ret = fwrite($fd, $data);
		return $ret;
	}
    
}