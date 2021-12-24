<?php
declare(strict_types=1);

namespace Pheanstalk\Parser;

use Pheanstalk\Contract\CommandInterface;
use Pheanstalk\Contract\ResponseInterface;
use Pheanstalk\Contract\ResponseParserInterface;
use Pheanstalk\ResponseType;

/**
 * Simple parser that tries other parsers in order until one succeeds.
 */
class ChainedParser implements ResponseParserInterface
{
    /**
     * @var list<ResponseParserInterface>
     */
    private readonly array $parsers;

    public function __construct(ResponseParserInterface ...$parsers) {
        $this->parsers = $parsers;
    }
    public function parseResponse(
        CommandInterface $command,
        ResponseType $type,
        array $arguments = [],
        null|string $data = null
    ): null|ResponseInterface {

        foreach($this->parsers as $parser) {
            $result = $parser->parseResponse($command, $type, $arguments, $data);
            if ($result !== null) {
                return $result;
            }
        }
        return null;
    }
}
