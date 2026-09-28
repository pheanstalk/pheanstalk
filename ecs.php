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

return ECSConfig::configure()
    ->withParallel()
    ->withPaths([
        __DIR__ . '/src', __DIR__ . '/tests', __DIR__ . '/ecs.php'
    ])
    ->withSkip([
        __DIR__ . '/tests/snippets'
    ])
    ->withSets([
        SetList::PSR_12,
        SetList::CLEAN_CODE
    ])
    ->withRules([
        NoUnusedImportsFixer::class,
        DeclareStrictTypesFixer::class,
        SingleSpaceAroundConstructFixer::class
    ])
    ->withConfiguredRule(ArraySyntaxFixer::class, ['syntax' => 'short'])
    ->withConfiguredRule(FinalInternalClassFixer::class, [
        'annotation_exclude' => ['@extensible'],
        'annotation_include' => [],
        'consider_absent_docblock_as_internal_class' => \true
    ])
    ->withSpacing(indentation: '    ', lineEnding: '\n')
;
