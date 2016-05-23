<?php

namespace Pheanstalk;

class DeadlineSoonTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @expectedException \Pheanstalk\Exception\DeadlineSoonException
     * @expectedExceptionMessage deadline soon
     */
    public function testDeadlineSoon()
    {
        $tube = $this->_randomString();
        $client = new Pheanstalk("localhost", 11300);

        $client->useTube($tube);
        $client->watch($tube);
        $client->ignore('default');
        $client->put('12345', 0, 0, 2);

        $job = $client->reserve(0);
        $this->assertFalse($client->reserve(0));
        sleep(1);
        $client->reserve(0);
    }

    /**
     * @expectedException \Pheanstalk\Exception\DeadlineSoonException
     * @expectedExceptionMessage deadline soon
     */
    public function testPoolDeadlineSoon()
    {
        $tube = $this->_randomString();
        $pool = new PheanstalkPool(array("localhost:11300", "localhost:11308"));

        $pool->useTube($tube);
        $pool->watch($tube);
        $pool->ignore('default');
        $pool->put('12345', 0, 0, 2);

        $job = $pool->reserve(0);
        $this->assertFalse($pool->reserve(0));
        sleep(1);
        $pool->reserve(0);
    }

    private function _randomString()
    {
        return md5(time() . mt_rand(0,1000));
    }
}
?>