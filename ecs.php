<?php

declare(strict_types=1);

// ecs.php
use PhpCsFixer\Fixer\ArrayNotation\ArraySyntaxFixer;
use Symplify\EasyCodingStandard\Config\ECSConfig;
use Symplify\EasyCodingStandard\ValueObject\Option;
use Symplify\EasyCodingStandard\ValueObject\Set\SetList;

return static function (ECSConfig $config): void {
    $parameters = $config->parameters();
    // Parallel
    $parameters->set(Option::PARALLEL, true);

    // Paths
    $parameters->set(Option::PATHS, [
        __DIR__ . '/src', __DIR__ . '/tests', __DIR__ . '/ecs.php'



    ]);
    // A. full sets
    $config->import(SetList::PSR_12);
    $config->import(SetList::STRICT);
    $config->import(SetList::CLEAN_CODE);

    // B. standalone rule
    $services = $config->services();
    $services->set(ArraySyntaxFixer::class)
        ->call('configure', [[
            'syntax' => 'short',
        ]]);

    $config->lineEnding("\n");
};
