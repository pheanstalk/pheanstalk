<?php
declare(strict_types=1);

require __DIR__ . '/../vendor/autoload.php';
define('SERVER_HOST', getenv('SERVER_HOST') ?: 'localhost');
define('SERVER_PORT', getenv('SERVER_PORT') ?: 11300);

if (!class_exists(\Pheanstalk\Tests\BaseTestCase::class)) {
    die('Something is wrong with the autoloader');
}