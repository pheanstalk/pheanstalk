<?php
declare(strict_types=1);

namespace Pheanstalk\Response;

use Pheanstalk\Contract\JobIdInterface;
use Pheanstalk\Contract\JobIdResponseInterface;
use Pheanstalk\Contract\JobResponseInterface;
use Pheanstalk\ResponseType;

class JobIdResponse implements JobIdResponseInterface
{

    public function __construct(
        private readonly ResponseType $type,
        private readonly JobIdInterface $id)
    {

    }
    public function getId(): JobIdInterface
    {
        return $this->id;
    }

    public function getResponseType(): ResponseType
    {
        return $this->type;
    }
}
