<?php

declare(strict_types=1);

// ecs.php
use PhpCsFixer\Fixer\ArrayNotation\ArraySyntaxFixer;
use Symfony\Component\DependencyInjection\Loader\Configurator\ContainerConfigurator;
use Symplify\EasyCodingStandard\ValueObject\Option;
use Symplify\EasyCodingStandard\ValueObject\Set\SetList;

return static function (ContainerConfigurator $containerConfigurator): void {
    $parameters = $containerConfigurator->parameters();
    // Parallel
    $parameters->set(Option::PARALLEL, true);

    // Paths
    $parameters->set(Option::PATHS, [
        __DIR__ . '/src', __DIR__ . '/tests', __DIR__ . '/ecs.php'



    ]);
    // A. full sets
    $containerConfigurator->import(SetList::PSR_12);


    // B. standalone rule
    $services = $containerConfigurator->services();
    $services->set(ArraySyntaxFixer::class)
        ->call('configure', [[
            'syntax' => 'short',
        ]]);
};
