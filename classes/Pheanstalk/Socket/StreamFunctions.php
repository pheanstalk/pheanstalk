<?php

/**
 * Wrapper around PHP stream functions.
 * Facilitates mocking/stubbing stream operations in unit tests.
 *
 * @author Paul Annesley
 * @package Pheanstalk
 * @licence http://www.opensource.org/licenses/mit-license.php
 */
class Pheanstalk_Socket_StreamFunctions
{
	private static $_instance;

    private $_socket = NULL;

	/**
	 * Singleton accessor.
	 */
	public static function instance()
	{
		if (empty(self::$_instance))
			self::$_instance = new self;

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
		if (isset($length))
			return fgets($handle, $length);
		else
			return fgets($handle);
	}

	public function fopen($filename, $mode)
	{
		return fopen($filename, $mode);
	}

	public function fread($handle, $length)
	{
		return fread($handle, $length);
	}

	public function fsockopen($hostname, $port = -1, &$errno = null, &$errstr = null, $timeout = null, $persistent = FALSE)
	{
		// Warnings (e.g. connection refused) suppressed;
		// return value, $errno and $errstr should be checked instead.

		// TODO:  we really need a host/port test here - otherwise we're going to reuse an existing 
		// connection when we really want a new connection.


		// thisis only true if we dont have a connection and we want to do persistence
        if ($this->_socket === NULL && $persistent === TRUE ) {
	            $this->_socket = @pfsockopen($hostname, $port, $errno, $errstr, $timeout);
		// otherwise, we dont care if we have the socket - force the connection.
		} elseif ( $persistent === FALSE) {
	            $this->_socket = @fsockopen($hostname, $port, $errno, $errstr, $timeout);			
        }

		return $this->_socket;
	}

	public function fwrite($handle, $string, $length = null)
	{
		if (isset($length))
			return fwrite($handle, $string, $length);
		else
			return fwrite($handle, $string);
	}

	public function stream_set_timeout($stream, $seconds, $microseconds = 0)
	{
		return stream_set_timeout($stream, $seconds, $microseconds);
	}
}
