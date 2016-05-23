<?php

namespace Pheanstalk;

class PoolTest extends \PHPUnit_Framework_TestCase
{
    public function testUsePut()
    {
        $tube = $this->_randomString();
        $txt = $this->_randomString();

        $pool = new PheanstalkPool(array("localhost:11300", "error_host:11300"));
        $pool->useTube($tube);
        $pool->put($txt);

        $client = new Pheanstalk("localhost", 11300);
        $client->watch($tube);
        $client->ignore('default');
        $job = $client->reserve();

        $this->assertEquals($job->getData(), $txt);
    }

    public function testUsePutWithLowLatency()
    {
        $tube = $this->_randomString();
        $txt = $this->_randomString();

        // sudo tc qdisc del dev eth0 root
        // sudo tc qdisc add dev eth0 root netem delay 1010ms
        $msec = 10;
        $pool = new PheanstalkPool(array("localhost:11300", "error_host:11300"), $socketTimeoutSec=0, $socketTimeoutMsec=$msec);
        $pool->useTube($tube);
        for($i = 0; $i < 100; $i++) {
            $start_at = microtime(TRUE);
            $pool->put($txt);
            $delta = microtime(TRUE) - $start_at;
            $this->assertEquals($delta <= $msec + 1 / 100, TRUE);
        }
    }

    public function testWatchReserve()
    {
        $tube = $this->_randomString();
        $txt = $this->_randomString();

        $client = new Pheanstalk("localhost", 11300);
        $client->useTube($tube);
        $client->put($txt);

        $pool = new PheanstalkPool(array("localhost:11300", "error_host:11300"));
        $pool->watch($tube);
        $pool->ignore('default');
        $job = $pool->reserve(0);

        $this->assertEquals($job->getData(), $txt);
    }

    /**
     * @expectedException \Pheanstalk\Exception\SocketException
     */
    public function testPutAllDown()
    {
        $pool = new PheanstalkPool(array("error_host:11300", "error_host:11301"));
        $pool->put("asdf");
    }

    /**
     * @expectedException \Pheanstalk\Exception\SocketException
     */
    public function testReserveAllDown()
    {
        $pool = new PheanstalkPool(array("error_host:11300", "error_host:11301"));
        $pool->reserve(0);
    }

    public function testRealUsingWatching()
    {
        $tube1 = $this->_randomString();
        $tube2 = $this->_randomString();
        $tube3 = $this->_randomString();
        $txt1 = $this->_randomString();
        $txt2 = $this->_randomString();
        $txt3 = $this->_randomString();

        $c1 = new Pheanstalk("localhost", 11300);
        $c1->useTube($tube1);
        $c2 = new Pheanstalk("localhost", 11308);
        $c2->useTube($tube2);

        $pool = new PheanstalkPool(array("localhost:11300", "localhost:11308"));

        $c1->put($txt1);
        $pool->watch($tube1);
        $pool->ignore('default');
        $job1 = $pool->reserve(0);

        $this->assertEquals($job1->getData(), $txt1);
        $this->assertEquals(
            $job1->getClient()->listTubesWatched(),
            array($tube1)
        );
        $pool->watch($tube2);

        $c2->put($txt2);
        $job2 = $pool->reserve(0);
        $this->assertEquals(
            $job2->getClient()->listTubesWatched(),
            array($tube1, $tube2)
        );

        $pool->useTube($tube3);
        $pool->put($txt3);
        $pool->watch($tube3);
        $job3 = $pool->reserve(0);
        $this->assertEquals(
            $job3->getClient()->listTubeUsed(),
            $tube3
        );
    }

    private function _randomString()
    {
        return md5(time() . mt_rand(0,1000));
    }
}
?>
