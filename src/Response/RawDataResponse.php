<?php
declare(strict_types=1);

namespace Pheanstalk\Response;

use Pheanstalk\Contract\ResponseInterface;
use Pheanstalk\ResponseType;

class RawDataResponse implements ResponseInterface
{

    public function __construct(private readonly ResponseType $type, private readonly string $data)
    {

    }

    public function getResponseType(): ResponseType
    {
        return $this->type;
    }

    public function getData(): string
    {
        return $this->data;
    }
}
