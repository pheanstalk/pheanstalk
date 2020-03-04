#!/usr/bin/env php
<?php

define('BASE_DIR', realpath(__DIR__.'/..'));
define('PHAR_FILENAME', 'pheanstalk.phar');
define('PHAR_FULLPATH', BASE_DIR.'/'.PHAR_FILENAME);

// ----------------------------------------

reexecute_if_phar_readonly($argv);
delete_existing_pheanstalk_phar();
build_pheanstalk_phar();
verify_pheanstalk_phar();
exit(0);

// ----------------------------------------

// See: http://www.php.net/manual/en/phar.configuration.php#ini.phar.readonly
function reexecute_if_phar_readonly($argv)
{
    if (ini_get('phar.readonly') && !in_array('--ignore-readonly', $argv)) {
        $command = sprintf(
            'php -d phar.readonly=0 %s --ignore-readonly',
            implode(' ', $argv)
        );

        echo "Phar configured readonly in php.ini; attempting to re-execute:\n";
        echo "$command\n";

        passthru($command, $exitStatus);
        exit($exitStatus);
    }
}

function delete_existing_pheanstalk_phar()
{
    if (file_exists(PHAR_FULLPATH)) {
        printf("- Deleting existing %s\n", PHAR_FILENAME);
        unlink(PHAR_FULLPATH);
    }
}

function build_pheanstalk_phar()
{
    printf("- Building %s from %s\n", PHAR_FILENAME, BASE_DIR);
    $phar = new Phar(PHAR_FULLPATH);
    $phar->buildFromDirectory(BASE_DIR);
    $phar->setStub(
        $phar->createDefaultStub('vendor/autoload.php')
    );
}

function verify_pheanstalk_phar()
{
    $phar = new Phar(PHAR_FULLPATH);
    printf("- %s built with %d files.\n", PHAR_FILENAME, $phar->count());
}
