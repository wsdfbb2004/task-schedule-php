<?php

namespace Pheanstalk;

class ShardingClient {

    private $_connections;
    private $_using = PheanstalkInterface::DEFAULT_TUBE;
    private $_watching = PheanstalkInterface::DEFAULT_TUBE;

    public function __construct($hosts, $connectTimeout = null) {
        if (!is_array($hosts)) {
            throw new Exception\ClientException("hosts must be array", 30101);
        }
        sort($hosts);

        $this->_connections = array();
        foreach ($hosts as $i => $host) {
            $tmp = explode(":", $host);
            $this->_connections[] = new Pheanstalk($tmp[0], (int)$tmp[1], $connectTimeout);
        }
   }

    private function stringToIndex($str) {
        $total = 0;
        foreach (str_split($str) as $char) {
            $total += ord($char);
        }
        return $total % count($this->_connections);
    }

    private function getConnection($tube) {
        $index = $this->stringToIndex($tube);
        return $this->_connections[$index];
    }

    public function useTube($tube) {
        $this->_using = $tube;
        return $this;
    }

    public function watchOnly($tube) {
        $this->_watching = $tube;
        return $this;
    }

    public function reserve($timeout) {
        $conn = $this->getConnection($this->_watching);
        $conn->watchOnly($this->_watching);
        return $conn->reserve($timeout);
    }

    public function put(
        $data,
        $priority = PheanstalkInterface::DEFAULT_PRIORITY,
        $delay = PheanstalkInterface::DEFAULT_DELAY,
        $ttr = PheanstalkInterface::DEFAULT_TTR
    ) {
        $conn = $this->getConnection($this->_using);
        $conn->useTube($this->_using);
        return $conn->put($data, $priority, $delay, $ttr);
    }
}

?>