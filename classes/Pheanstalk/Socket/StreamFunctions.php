<?php

namespace Pheanstalk\Socket;
use Pheanstalk\ISocket;

/**
 * Wrapper around PHP stream functions.
 * Facilitates mocking/stubbing stream operations in unit tests.
 *
 * @author Paul Annesley
 * @package Pheanstalk
 * @licence http://www.opensource.org/licenses/mit-license.php
 */
class StreamFunctions implements ISocket
{
    private static $_instance;

    /**
     * Singleton accessor.
     */
    public static function instance()
    {
        if (empty(self::$_instance)) {
            self::$_instance = new self;
        }

        return self::$_instance;
    }

    /**
     * Sets an alternative or mocked instance.
     */
    public function setInstance($instance)
    {
        self::$_instance = $instance;
    }

    /**
     * Unsets the instance, so a new one will be created.
     */
    public function unsetInstance()
    {
        self::$_instance = null;
    }

    // ----------------------------------------

    public function feof($handle)
    {
        return feof($handle);
    }

    public function fgets($handle, $length = null)
    {
        if (isset($length)) {
            return fgets($handle, $length);
        } else {
            return fgets($handle);
        }
    }

    public function fopen($filename, $mode)
    {
        return fopen($filename, $mode);
    }

    public function fread($handle, $length)
    {
        return fread($handle, $length);
    }

    public function fsockopen($hostname, $port = -1, &$errno = null, &$errstr = null, $timeout = null)
    {
        // Warnings (e.g. connection refused) suppressed;
        // return value, $errno and $errstr should be checked instead.
        return @fsockopen($hostname, $port, $errno, $errstr, $timeout);
    }

    public function fwrite($handle, $string, $length = null)
    {
        if (isset($length)) {
            return fwrite($handle, $string, $length);
        } else {
            return fwrite($handle, $string);
        }
    }

    public function stream_set_timeout($stream, $seconds, $microseconds = 0)
    {
        return stream_set_timeout($stream, $seconds, $microseconds);
    }


    /**
     * Writes data to the socket.
     *
     * @param string $data
     *
     * @return void
     */
    public function write($data)
    {
        // TODO: Implement write() method.
    }


    /**
     * Reads up to $length bytes from the socket.
     *
     * @return string
     */
    public function read($length)
    {
        // TODO: Implement read() method.
    }


    /**
     * Reads up to the next new-line, or $length - 1 bytes.
     * Trailing whitespace is trimmed.
     *
     * @param int
     */
    public function getLine($length = null)
    {
        // TODO: Implement getLine() method.
    }
}
