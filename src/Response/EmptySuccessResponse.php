<?php
declare(strict_types=1);

namespace Pheanstalk\Response;

use Pheanstalk\Contract\ResponseInterface;
use Pheanstalk\ResponseType;

class EmptySuccessResponse implements ResponseInterface
{
    public function __construct(private readonly ResponseType $type)
    {

    }

    public function getResponseType(): ResponseType
    {
        return $this->type;
    }
}
