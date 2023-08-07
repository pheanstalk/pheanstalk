<?php

declare(strict_types=1);

namespace Pheanstalk\Exception;

use Pheanstalk\Values\ResponseType;

final class UnsupportedResponseException extends ClientException
{
    public function __construct(ResponseType|null $type = null)
    {
        if (isset($type)) {
            parent::__construct("Response type {$type->name} is not supported for this command");
        } else {
            parent::__construct();
        }
    }
}
