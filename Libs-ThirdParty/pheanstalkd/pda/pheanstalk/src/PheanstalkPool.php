<?php

namespace Pheanstalk;

class PheanstalkPool {

    private $_connections;
    private $_using = PheanstalkInterface::DEFAULT_TUBE;
    private $_watching = array(PheanstalkInterface::DEFAULT_TUBE => true);

    public function __construct($hosts, $connectTimeout = null, $socketTimeoutSec = 1, $socketTimeoutMsec = 0) {
        if (!is_array($hosts)) {
            throw new ClientException("hosts must be array", 30001);
        }

        $this->_connections = array();
        foreach ($hosts as $i => $host) {
            $tmp = explode(":", $host);
            $this->_connections[] = new Pheanstalk($tmp[0], (int)$tmp[1], $connectTimeout, $socketTimeoutSec, $socketTimeoutMsec);
        }
        shuffle($this->_connections);
    }

    public function useTube($tube) {
        $this->_using = $tube;
        return $this;
    }

    public function watch($tube) {
        $this->_watching[$tube] = true;
        return $this;
    }

    public function ignore($tube) {
        unset($this->_watching[$tube]);
        return $this;
    }

    public function reserve($timeout) {
        $count = 0;
        foreach ($this->_connections as $i => $conn) {
            try {
                $job = $this->_reserveOne($conn, $timeout);
                if ($job) {
                    return $job;
                }
            } catch (Exception\ConnectionException $e) {
                $count++;
                $this->_pushBackConn();
            }
        }
        if ($count == count($this->_connections)) {
            throw new Exception\SocketException("all server down", 30002);
        }
        return false;
    }

    public function put(
        $data,
        $priority = PheanstalkInterface::DEFAULT_PRIORITY,
        $delay = PheanstalkInterface::DEFAULT_DELAY,
        $ttr = PheanstalkInterface::DEFAULT_TTR
    ) {
        foreach ($this->_connections as $i => $conn) {
            try {
                $conn->useTube($this->_using);
                $conn->put($data, $priority, $delay, $ttr);
                return;
            } catch (Exception\ConnectionException $e) {
                $this->_pushBackConn();
            }
        }
        throw new Exception\SocketException("all server down", 30003);
    }

    private function _reserveOne($conn, $timeout) {
        foreach ($this->_watching as $tube => $v) {
            $conn->watch($tube);
        }

        foreach ($conn->listTubesWatched() as $i => $tube) {
            if (!isset($this->_watching[$tube])) {
                $conn->ignore($tube);
            }
        }

        return $conn->reserve($timeout);
    }

    private function _pushBackConn() {
        array_push($this->_connections, array_shift($this->_connections));
    }
}
?>
