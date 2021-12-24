<?php

declare(strict_types=1);

namespace Pheanstalk;

use Pheanstalk\Contract\CommandInterface;
use Pheanstalk\Contract\ResponseInterface;
use Pheanstalk\Contract\ResponseParserInterface;
use Pheanstalk\Contract\SocketFactoryInterface;
use Pheanstalk\Contract\SocketInterface;
use Pheanstalk\Exception\ClientException;
use Pheanstalk\Exception\CommandException;
use Pheanstalk\Exception\ServerBadFormatException;
use Pheanstalk\Exception\ServerDrainingException;
use Pheanstalk\Exception\ServerInternalErrorException;
use Pheanstalk\Exception\ServerOutOfMemoryException;
use Pheanstalk\Exception\ServerUnknownCommandException;
use Pheanstalk\Exception\SocketException;
use Pheanstalk\Parser\ChainedParser;
use Pheanstalk\Parser\CommandParser;
use Pheanstalk\Parser\GlobalExceptionParser;
use Pheanstalk\Response\ArrayResponse;

/**
 * A connection to a beanstalkd server, backed by any type of socket.
 *
 */
class Connection
{
    private const CRLF = "\r\n";
    private const CRLF_LENGTH = 2;

    private SocketInterface|null $socket;
    private readonly ResponseParserInterface $parser;
    public function __construct(
        private readonly SocketFactoryInterface $factory,
        null|ResponseParserInterface $parser = null
    )
    {
        if (isset($parser)) {
            $this->parser = $parser;
        } else {
            // Construct the parser.
            $this->parser = new ChainedParser(
                new GlobalExceptionParser(),
                new CommandParser()
            );
        }
    }

    /**
     * Disconnect the socket.
     * Subsequent socket operations will create a new connection.
     */
    public function disconnect(): void
    {
        if (isset($this->socket)) {
            $this->socket->disconnect();
            $this->socket = null;
        }
    }

    private function readData(SocketInterface $socket, int $length): string
    {
        $result = $socket->read($length);
        if ($socket->read(self::CRLF_LENGTH) !== self::CRLF) {
            throw new Exception\ClientException(sprintf(
                'Expected %u bytes of CRLF after %u bytes of data',
                self::CRLF_LENGTH,
                $length
            ));
        }
        return $result;
    }


    private function sendCommand(CommandInterface $command): void
    {
        $socket = $this->getSocket();
        $to_send = $command->getCommandLine() . self::CRLF;
        if ($command->hasData()) {
            $to_send .= $command->getData() . self::CRLF;
        }
        $socket->write($to_send);
    }

    /**
     * @throws Exception\ClientException
     */
    public function dispatchCommand(CommandInterface $command): ResponseInterface
    {
        $this->sendCommand($command);
        $socket = $this->getSocket();

        // This is always a simple line consisting of a response type name and 0 - 2 optional numerical arguments.
        $responseLine = $socket->getLine();
        $responseParts = explode(' ' , $responseLine);
        $responseType = ResponseType::from(array_shift($responseParts));


        if ($responseType->hasData()) {
            $dataLength = array_pop($responseParts);
            $data = $this->readData($socket, (int)$dataLength);
        }

        $result = $this->parser->parseResponse($command, $responseType, $responseParts, $data ?? null);
        if (!isset($result)) {
            // Failed to parse.
            throw new ClientException('Failed to parse response: ' . $responseLine);
        }
        return $result;
    }

    // ----------------------------------------

    /**
     * Socket handle for the connection to beanstalkd.
     * @throws Exception\ConnectionException
     */
    private function getSocket(): SocketInterface
    {
        if (!isset($this->socket)) {
            $this->socket = $this->factory->create();
        }

        return $this->socket;
    }
}
