task-schedule-php
==========

task-schedule-php是一个php任务调度框架，开发者可以使用这个框架来设置周期任务/一次性的延时任务后期异步执行。
##动机：
* 异步/不阻塞：用户操作的时候，服务器端除了和用户交互外，还会通知一些用户感知不到的外部系统，调用这些外部系统比较耗时，无法实时响应。因为这些任务不是主流程。可以将这些任务暂时存储起来，并将任务按照优先级（一般是任务预期执行的时间）进行排序，然后后期顺序执行这些任务，不阻塞主流程。
* 重复/周期：有些任务任务不仅仅只执行一次，会多次重复性地执行。例如：外部系统的状态发生变化，但是外部系统不会主动地回调/推送通知我们（或者是外部系统主动推送的信道不良），我们会周期性地主动轮询外部系统的状态。
* 延时处理。




##典型的应用场景：
* 乘客发起一次打车请求，服务器端可以先记录下乘客的打车请求，告知用户打车请求已经受理。服务器端后期想办法逐步将乘客的打车请求通知到周围的司机。
* 支付系统在收到银行的支付成功回调通知后，先保证将支付状态写入数据库的主流程。然后想办法调用比较耗时的外部系统，将支付成功的消息推送到用户端App。
* 用户在商户收银台生成一笔支付单后，如果没有在某个时间间隔之内收到银行的支付回调，收银台定期去银行轮询支付结果，直到知道用户在银行已经支付或者是超过最大轮询次数为止。
* 用户在司机结束行程后，如果在30分钟内没有支付，服务端自动发起一次微信代扣请求。





## 运行环境

* PHP最低版本: 5.3.3.
* Redis环境：php需要安装PhpRedis扩展。
* Beanstalkd环境：需要beanstalkd的php客户端代码Pheanstalk，Pheanstalk代码已经内置包含在Libs-ThirdParty目录下。






## 例子

* 例子程序可以在demo目录下找到。
* 直接执行php ./demo/startRedis.php
* 或者php ./demo/startBeanstalkd.php
* 或者php ./demo/startPhp.php即可。




## 使用

##### 在目录Core/Handlers目录下编写任务处理函数。
``
function helloWorld(){
    echo "\n\nhello world\n\n";
    return true;
}
``


##### 实例化任务调度对象

$redisIp = '10.10.9.77';
$redisPort = '6379';
$redisTimeout = 2;
$redisClient = new \TaskSchedule\Libs\RedisWithReConnect($redisIp, $redisPort, $redisTimeout);

$scheduler = new \TaskSchedule\Core\TaskScheduler\RedisTaskSchedulerWithTransaction($redisClient);
$tasks = new \TaskSchedule\Core\Tasks\RedisTasks($redisClient);
$runner = new \TaskSchedule\Core\TaskRunner($taskResult);
$timeEvent = new \TaskSchedule\Core\TimeEvent\TimeEvent($scheduler, $tasks, $runner);


#####生产者将该任务添加到计划中。

$timeSpace = 2;
$type = \TaskSchedule\Core\Tasks\TasksInterface::RUN_REPEATED;
$func = 'helloWorld';

//注意，在add任务之前，先初始化下maxTaskId。
$timeEvent->add($timeSpace, $type, $func, $args = null);



#####消费者获取到任务并执行。

$runTimeLen = 5; 
$timeEvent->loop($runTimeLen);



