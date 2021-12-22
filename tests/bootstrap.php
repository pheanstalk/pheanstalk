<?php

declare(strict_types=1);

require __DIR__ . '/../vendor/autoload.php';

define('SERVER_HOST', is_string(getenv('SERVER_HOST')) ? getenv('SERVER_HOST') : 'localhost');
const SERVER_PORT = 11300;
