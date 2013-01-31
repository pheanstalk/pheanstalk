#!/usr/bin/env php
<?php

define('BASE_DIR', realpath(__DIR__ . '/..'));
define('PHAR_PATH', BASE_DIR . '/pheanstalk.phar');
define('PHAR_FILENAME', basename(PHAR_PATH));

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
            implode($argv, ' ')
        );

        echo "Phar configured readonly in php.ini; attempting to re-execute:\n";
        echo "$command\n";

        passthru($command, $exitStatus);
        exit($exitStatus);
    }
}

function delete_existing_pheanstalk_phar()
{
    if (file_exists(PHAR_PATH)) {
        printf("- Deleting existing %s\n", PHAR_FILENAME);
        unlink(PHAR_PATH);
    }
}

function build_pheanstalk_phar()
{
    $classDir = BASE_DIR . '/classes';
    printf("- Building %s from %s\n", PHAR_FILENAME, $classDir);
    $phar = new Phar(PHAR_PATH);
    $phar->buildFromDirectory($classDir);
    $phar->setStub(pheanstalk_phar_stub());
}

function pheanstalk_phar_stub()
{
	$pheanstalkInit = BASE_DIR . '/pheanstalk_init.php';
	printf("- Generating Phar stub based on %s\n", basename($pheanstalkInit));
	$stub = file_get_contents($pheanstalkInit);
	$stub = str_replace('<?php', '', $stub);
	$stub = str_replace("dirname(__FILE__) . '/classes';", "'phar://' . __FILE__;", $stub);
	return implode(array(
		'<?php',
		'Phar::mapPhar();',
		$stub,
		'__HALT_COMPILER();'
	), PHP_EOL);
}

function verify_pheanstalk_phar()
{
    $phar = new Phar(PHAR_PATH);
    printf("- %s built with %d files.\n", PHAR_FILENAME, $phar->count());
}
