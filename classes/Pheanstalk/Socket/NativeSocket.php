<?php

/**
 * A Pheanstalk_Socket implementation around a fsockopen() stream.
 *
 * @author Paul Annesley <paul@annesley.cc>
 */
class Pheanstalk_Socket_NativeSocket implements Pheanstalk_Socket
{
	private $_socket;

	/**
	 * @param string $host
	 * @param int $port
	 * @param int $timeout
	 */
	public function __construct($host, $port, $timeout)
	{
		if (!$this->_socket = @fsockopen($host, $port, $errno, $errstr, $timeout))
		{
			throw new Pheanstalk_Exception_ConnectionException($errno, $errstr);
		}

		stream_set_timeout($this->_socket, -1);
	}

	/* (non-phpdoc)
	 * @see Pheanstalk_Socket::write()
	 */
	public function write($data)
	{
		return fwrite($this->_socket, $data);
	}

	/* (non-phpdoc)
	 * @see Pheanstalk_Socket::write()
	 */
	public function read($length)
	{
		return fread($this->_socket, $length);
	}

	/* (non-phpdoc)
	 * @see Pheanstalk_Socket::write()
	 */
	public function getLine($length = null)
	{
		$data = isset($length) ?
			fgets($this->_socket, $length) : fgets($this->_socket);

		return rtrim($data);
	}
}
