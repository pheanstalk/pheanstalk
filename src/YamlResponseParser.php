<?php

namespace Pheanstalk;

use Pheanstalk\Contract\ResponseInterface;
use Pheanstalk\Contract\ResponseParserInterface;
use Pheanstalk\Exception\ClientException;
use Pheanstalk\Response\ArrayResponse;

/**
 * A response parser for commands that return a subset of YAML.
 *
 * Expected response is 'OK', 'NOT_FOUND' response is also handled.
 * Parser expects either a YAML list or dictionary, depending on mode.
 *
 * @author  Paul Annesley
 */
class YamlResponseParser implements ResponseParserInterface
{
    const MODE_LIST = 'list';
    const MODE_DICT = 'dict';

    private $mode;

    /**
     * @param string $mode self::MODE_*
     */
    public function __construct(string $mode)
    {
        if (!in_array($mode, [self::MODE_DICT, self::MODE_LIST])) {
            throw new \InvalidArgumentException('Invalid mode');
        }
        $this->mode = $mode;
    }

    public function parseResponse(string $responseLine, ?string $responseData): ArrayResponse
    {
        if ($responseLine === ResponseInterface::RESPONSE_NOT_FOUND) {
            throw new Exception\ServerException(sprintf(
                'Server reported %s',
                $responseLine
            ));
        }

        if (!preg_match('#^OK \d+$#', $responseLine)) {
            throw new Exception\ServerException(sprintf(
                'Unhandled response: "%s"',
                $responseLine
            ));
        }

        $lines = array_filter(explode("\n", $responseData), function ($line) {
            return !empty($line) && $line !== '---';
        });

        return $this->mode === self::MODE_LIST ? $this->parseList($lines) : $this->parseDictionary($lines);
    }

    private function parseList(array $lines): ArrayResponse
    {
        $data = [];
        foreach ($lines as $line) {
            if (strncmp($line, '- ', 2) !== 0) {
                throw new ClientException("YAML parse error for line: $line" . print_r($lines, true));
            }
            $data[] = substr($line, 2);
        }

        return new ArrayResponse('OK', $data);
    }
    private function parseDictionary(array $lines): ArrayResponse
    {
        $data = [];
        foreach ($lines as $line) {
            if (!preg_match('#(\S+):\s*(.*)#', $line, $matches)) {
                throw new ClientException("YAML parse error for line: $line");
            }
            $data[$matches[1]] = $matches[2];
        }
        return new ArrayResponse('OK', $data);
    }
}
