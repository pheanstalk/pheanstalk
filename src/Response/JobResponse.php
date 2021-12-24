<?php
declare(strict_types=1);

namespace Pheanstalk\Response;

use Pheanstalk\Contract\JobIdInterface;
use Pheanstalk\Contract\JobResponseInterface;
use Pheanstalk\ResponseType;

class JobResponse implements JobResponseInterface
{

    public function __construct(
        private readonly ResponseType $type,
        private readonly JobIdInterface $id, private readonly string $data)
    {

    }
    public function getId(): JobIdInterface
    {
        return $this->id;
    }

    public function getData(): string
    {
        return $this->data;
    }

    public function getResponseType(): ResponseType
    {
        return $this->type;
    }
}
