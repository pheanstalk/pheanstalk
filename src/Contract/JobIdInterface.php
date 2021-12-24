<?php

declare(strict_types=1);


namespace Pheanstalk\Contract;

interface JobIdInterface
{
    /**
     * This is a string to support 64bit numbers on 32bit systems.
     * @return string A numeric string representing the ID.
     */
    public function getId(): string;
}
