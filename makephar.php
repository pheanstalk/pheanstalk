<?php
    @unlink(__DIR__ . 'pheanstalk.phar');

    $phar = new Phar(__DIR__ . '/pheanstalk.phar');
    $phar->buildFromDirectory(__DIR__ . '/classes');

    $bootstrapFile = file_get_contents(__DIR__ . '/pheanstalk_init.php');
    $bootstrapFile = str_replace('<?php', '', $bootstrapFile);

    $stub .= "<?php" . PHP_EOL;
    $stub .= "Phar::mapPhar();" . PHP_EOL;
    $stub .= $bootstrapFile . PHP_EOL;
    $stub .= "__HALT_COMPILER();" . PHP_EOL;
    $phar->setStub($stub);
?>
