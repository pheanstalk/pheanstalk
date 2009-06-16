<?php

/**
 * A connection to a beanstalkd server
 *
 * @author Paul Annesley
 * @package Pheanstalk
 * @licence http://www.opensource.org/licenses/mit-license.php
 */
class Pheanstalk_Connection
{
	const CONNECT_TIMEOUT = 2;
	const CRLF = "\r\n";
	const CRLF_LENGTH = 2;

	// responses which are global errors, mapped to their exception short-names
	private $_errorResponses = array(
		Pheanstalk_Response::RESPONSE_OUT_OF_MEMORY => 'OutOfMemory',
		Pheanstalk_Response::RESPONSE_INTERNAL_ERROR => 'InternalError',
		Pheanstalk_Response::RESPONSE_DRAINING => 'Draining',
		Pheanstalk_Response::RESPONSE_BAD_FORMAT => 'BadFormat',
		Pheanstalk_Response::RESPONSE_UNKNOWN_COMMAND => 'UnknownCommand',
	);

	// responses which are followed by data
	private $_dataResponses = array(
		Pheanstalk_Response::RESPONSE_RESERVED,
		Pheanstalk_Response::RESPONSE_FOUND,
		Pheanstalk_Response::RESPONSE_OK,
	);

	private $_socket;
	private $_hostname;
	private $_port;

	/**
	 * @param string $hostname
	 * @param int $port
	 */
	public function __construct($hostname, $port)
	{
		$this->_hostname = $hostname;
		$this->_port = $port;
	}

	/**
	 * Provides backwards compatibility - older versions used this
	 * class as the "facade" rather than the Pheanstalk class.
	 *
	 * @param string $name
	 * @param array $arguments
	 */
	public function __call($name, $arguments)
	{
		$methods = array(
			'put',
			'reserve',
			'release',
			'delete',
			'bury',
			'kick',
			'getCurrentTube',
			'useTube',
			'getWatchedTubes',
			'watchTube',
			'ignoreTube',
		);

		if (!in_array($name, $methods))
			throw new BadMethodCallException(__METHOD__ . ' not implemented');

		trigger_error(
			sprintf('%s::%s() deprecated, use Pheanstalk::%s()', __CLASS__, $name, $name),
			E_USER_NOTICE
		);

		$pheanstalk = new Pheanstalk($this->_hostname, $this->_port);
		$pheanstalk->setConnection($this);
		return call_user_func_array(array($pheanstalk, $name), $arguments);
	}

	/**
	 * Sets a manually created socket, used for unit testing.
	 * @param Pheanstalk_Socket $socket
	 * @chainable
	 */
	public function setSocket(Pheanstalk_Socket $socket)
	{
		$this->_socket = $socket;
		return $this;
	}

	/**
	 * @param object $command Pheanstalk_Command
	 * @return object Pheanstalk_Response
	 * @throws Pheanstalk_Exception_ClientException
	 */
	public function dispatchCommand($command)
	{
		$socket = $this->_getSocket();

		$socket->write($command->getCommandLine().self::CRLF);

		if ($command->hasData())
		{
			$socket->write($command->getData().self::CRLF);
		}

		$responseLine = $socket->getLine();
		$responseName = preg_replace('#^(\S+).*$#s', '$1', $responseLine);

		if (isset($this->_errorResponses[$responseName]))
		{
			$exception = sprintf(
				'Pheanstalk_Exception_Server%sException',
				$this->_errorResponses[$responseName]
			);

			throw new $exception(sprintf(
				"%s in response to '%s'",
				$responseName,
				$command
			));
		}

		if (in_array($responseName, $this->_dataResponses))
		{
			$dataLength = preg_replace('#^.*\b(\d+)$#', '$1', $responseLine);
			$data = $socket->read($dataLength);

			$crlf = $socket->read(self::CRLF_LENGTH);
			if ($crlf !== self::CRLF)
			{
				throw new Pheanstalk_Exception_ClientException(sprintf(
					'Expected %d bytes of CRLF after %d bytes of data',
					self::CRLF_LENGTH,
					$dataLength
				));
			}
		}
		else
		{
			$data = null;
		}

		return $command
			->getResponseParser()
			->parseResponse($responseLine, $data);
	}

	// ----------------------------------------

	/**
	 * Socket handle for the connection to beanstalkd
	 * @return Pheanstalk_Socket
	 * @throws Pheanstalk_Exception_ConnectionException
	 */
	private function _getSocket()
	{
		if (!isset($this->_socket))
		{
			$this->_socket = new Pheanstalk_Socket_NativeSocket(
				$this->_hostname,
				$this->_port,
				self::CONNECT_TIMEOUT
			);
		}

		return $this->_socket;
	}
}
