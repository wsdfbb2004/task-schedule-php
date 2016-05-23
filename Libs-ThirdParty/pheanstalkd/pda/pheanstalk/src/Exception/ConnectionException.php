<?php

namespace Pheanstalk\Exception;

/**
 * An exception relating to the client connection to the beanstalkd server
 *
 * @author Paul Annesley
 * @package Pheanstalk
 * @licence http://www.opensource.org/licenses/mit-license.php
 */
class ConnectionException
    extends ClientException
{
    /**
     * @param int    $errno  The connection error code
     * @param string $errstr The connection error message
     * @param int    $code   The custom error code
     */
    public function __construct($errno, $errstr, $code = 0)
    {
        parent::__construct(sprintf('Socket error %d: %s', $errno, $errstr), $code);
    }
}
