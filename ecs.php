<?php

declare(strict_types=1);

// ecs.php
use PhpCsFixer\Fixer\ArrayNotation\ArraySyntaxFixer;
use PhpCsFixer\Fixer\ClassNotation\FinalInternalClassFixer;
use PhpCsFixer\Fixer\Import\NoUnusedImportsFixer;
use PhpCsFixer\Fixer\LanguageConstruct\SingleSpaceAroundConstructFixer;
use PhpCsFixer\Fixer\Strict\DeclareStrictTypesFixer;
use Symplify\EasyCodingStandard\Config\ECSConfig;
use Symplify\EasyCodingStandard\ValueObject\Set\SetList;

return static function (ECSConfig $config): void {
    // Parallel
    $config->parallel();

    // Paths

    $config->paths([
        __DIR__ . '/src', __DIR__ . '/tests', __DIR__ . '/ecs.php'
    ]);
    $config->skip([
        __DIR__ . '/tests/snippets'
    ]);
    // A. full sets
    $config->import(SetList::PSR_12);
    $config->import(SetList::STRICT);
    $config->import(SetList::CLEAN_CODE);

    $config->ruleWithConfiguration(ArraySyntaxFixer::class, ['syntax' => 'short']);
    $config->rule(NoUnusedImportsFixer::class);
    $config->rule(DeclareStrictTypesFixer::class);
    $config->ruleWithConfiguration(FinalInternalClassFixer::class, [
        'annotation_exclude' => ['@extensible'],
        'annotation_include' => [],
        'consider_absent_docblock_as_internal_class' => \true
    ]);
    $config->rule(SingleSpaceAroundConstructFixer::class);
    $config->lineEnding("\n");
};
