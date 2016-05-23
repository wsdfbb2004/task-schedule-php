<?php

namespace Pheanstalk;

/**
 * A job in a beanstalkd server
 *
 * @author Paul Annesley
 * @package Pheanstalk
 * @licence http://www.opensource.org/licenses/mit-license.php
 */
class Job
{
    const STATUS_READY = 'ready';
    const STATUS_RESERVED = 'reserved';
    const STATUS_DELAYED = 'delayed';
    const STATUS_BURIED = 'buried';

    private $_id;
    private $_data;
    private $_conn;

    /**
     * @param Pheanstalk  $client  The Pheanstalk Object
     * @param int         $id      The job ID
     * @param string      $data    The job data
     */
    public function __construct($client, $id, $data)
    {
        $this->_client = $client;
        $this->_id = (int) $id;
        $this->_data = $data;
    }

    /**
     * The job ID, unique on the beanstalkd server.
     * @return int
     */
    public function getId()
    {
        return $this->_id;
    }

    /**
     * The job data.
     * @return string
     */
    public function getData()
    {
        return $this->_data;
    }

    public function getClient()
    {
        return $this->_client;
    }

    public function delete()
    {
        $this->_client->delete($this);
    }

    public function release(
        $priority = PheanstalkInterface::DEFAULT_PRIORITY,
        $delay = PheanstalkInterface::DEFAULT_DELAY
    )
    {
        $this->_client->release($this, $priority, $delay);
    }

    public function bury($priority = PheanstalkInterface::DEFAULT_PRIORITY)
    {
        $this->_client->bury($this, $priority);
    }

    public function kick()
    {
        $this->_client->kickJob($this);
    }

    public function touch()
    {
        $this->_client->touch($this);
    }

    public function stats()
    {
        return $this->_client->statsJob($this);
    }
}
