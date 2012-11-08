<?php

if (in_array(@$_ENV['HOME'], array('/home/pda'))) {
    define('VENDOR_DIR', __DIR__.'/../../../../../vendor');
} else {
    define('VENDOR_DIR', __DIR__.'/../vendor');
}

if (file_exists($file = __DIR__.'/autoload.php')) {
        require_once $file;
} elseif (file_exists($file = __DIR__.'/autoload.php.dist')) {
        require_once $file;
}
