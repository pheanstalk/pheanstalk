<?php

/**
 * A Pheanstalk_Socket implementation around a fsockopen() stream.
 *
 * @author Paul Annesley
 * @package Pheanstalk
 * @licence http://www.opensource.org/licenses/mit-license.php
 */
class Pheanstalk_Socket_NativeSocket implements Pheanstalk_Socket
{
	/**
	 * The default timeout for a blocking read on the socket
	 */
	const SOCKET_TIMEOUT = 1;

	private $_socket;

	/**
	 * @param string $host
	 * @param int $port
	 * @param int $connectTimeout
	 */
	public function __construct($host, $port, $connectTimeout)
	{
		if (!$this->_socket = @fsockopen($host, $port, $errno, $errstr, $connectTimeout))
		{
			throw new Pheanstalk_Exception_ConnectionException($errno, $errstr);
		}

		stream_set_timeout($this->_socket, self::SOCKET_TIMEOUT);
	}

	/* (non-phpdoc)
	 * @see Pheanstalk_Socket::write()
	 */
	public function write($data)
	{
		for ($written = 0, $fwrite = 0; $written < strlen($data); $written += $fwrite)
		{
			$fwrite = fwrite($this->_socket, substr($data, $written));

			if ($fwrite === false)
			{
				throw new Pheanstalk_Exception_SocketException('fwrite() returned false');
			}
		}
	}

	/* (non-phpdoc)
	 * @see Pheanstalk_Socket::write()
	 */
	public function read($length)
	{
		$read = 0;
		$parts = array();

		while ($read < $length && !feof($this->_socket))
		{
			$data = fread($this->_socket, $length - $read);
			$read += strlen($data);
			$parts []= $data;
		}

		return implode($parts);
	}

	/* (non-phpdoc)
	 * @see Pheanstalk_Socket::write()
	 */
	public function getLine($length = null)
	{
		do
		{
			$data = isset($length) ?
				fgets($this->_socket, $length) : fgets($this->_socket);

			if (feof($this->_socket))
			{
				throw new Pheanstalk_Exception_ConnectionException(666, "Socket closed by server!");
			}
		}
		while ($data === false);

		return rtrim($data);
	}
}
