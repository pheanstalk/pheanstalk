<?php
declare(strict_types=1);


namespace Pheanstalk\Contract;

interface JobIdInterface
{
    public function getId(): int;
}
