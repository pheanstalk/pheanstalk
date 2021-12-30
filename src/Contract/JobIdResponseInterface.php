<?php

declare(strict_types=1);

namespace Pheanstalk\Contract;

interface JobIdResponseInterface extends ResponseInterface
{
    public function getId(): JobIdInterface;
}
