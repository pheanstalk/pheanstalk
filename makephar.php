<?php
    @unlink(__DIR__ . 'pheanstalk.phar');

    $phar = new Phar(__DIR__ . '/pheanstalk.phar');
    $phar->buildFromDirectory(__DIR__ . '/classes');

    $stub .= "<?php" . PHP_EOL;
    $stub .= "Phar::mapPhar();" . PHP_EOL;
    $stub .= '$pheanstalkClassRoot = "phar://" . __FILE__;';
	$stub .= 'require_once $pheanstalkClassRoot . "/Pheanstalk/ClassLoader.php";';
	$stub .= 'Pheanstalk_ClassLoader::register($pheanstalkClassRoot);';
    $stub .= "__HALT_COMPILER();" . PHP_EOL;
    $phar->setStub($stub);
?>
