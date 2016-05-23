<?php

namespace Pheanstalk;

class ShardingClientTest extends \PHPUnit_Framework_TestCase
{
    protected static function getMethod($name) {
        $class = new \ReflectionClass("Pheanstalk\ShardingClient");
        $method = $class->getMethod($name);
        $method->setAccessible(true);
        return $method;
    }

    public function testStringToIndex()
    {
        $func = self::getMethod("stringToIndex");
        $client = new ShardingClient(array("localhost:11300", "localhost:11301", "localhost:11302", "localhost:11303"));
        $index = $func->invokeArgs($client, array("100"));
        $this->assertEquals($index, 1);

        $index = $func->invokeArgs($client, array("101"));
        $this->assertEquals($index, 2);
    }

    public function testGetConnection()
    {
        $func = self::getMethod("getConnection");
        $client = new ShardingClient(array("localhost:11300", "localhost:11301", "localhost:11302", "localhost:11303"));

        $conn = $func->invokeArgs($client, array("100"));
        $this->assertEquals($conn->getConnection()->getPort(), 11301);

        $conn = $func->invokeArgs($client, array("101"));
        $this->assertEquals($conn->getConnection()->getPort(), 11302);
    }

    public function testCommand()
    {
        $hosts = array("localhost:11300", "localhost:11308", "localhost:11309");
        $client1 = new ShardingClient($hosts);
        $client2 = new ShardingClient($hosts);

        $client1->useTube("100");
        $client1->put("100");

        $client2->watchOnly("100");
        $job = $client2->reserve(0);
        $this->assertEquals($job->getData(), "100");
        $this->assertEquals($job->getClient()->getConnection()->getPort(), 11308);
    }
}
?>
