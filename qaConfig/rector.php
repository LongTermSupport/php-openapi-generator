<?php

declare(strict_types=1);

use Rector\Caching\ValueObject\Storage\MemoryCacheStorage;
use Rector\Config\RectorConfig;
use Rector\Php83\Rector\ClassMethod\AddOverrideAttributeToOverriddenMethodsRector;
use Rector\Php84\Rector\Param\ExplicitNullableParamTypeRector;
use Rector\Privatization\Rector\ClassConst\PrivatizeFinalClassConstantRector;
use Rector\Privatization\Rector\ClassMethod\PrivatizeFinalClassMethodRector;
use Rector\Privatization\Rector\MethodCall\PrivatizeLocalGetterToPropertyRector;
use Rector\Privatization\Rector\Property\PrivatizeFinalClassPropertyRector;
use Rector\Set\ValueObject\LevelSetList;
use Rector\Set\ValueObject\SetList;

/*
 * Project-level Rector configuration for PHP OpenAPI Generator.
 *
 * This file REPLACES the default rector-php84.php entirely (see rector.inc.bash).
 * It includes the same php84 sets but skips rules that break cross-component inheritance.
 *
 * Note: `final` is added by CS Fixer (`final_class` rule), not Rector. The CS Fixer
 * override in qaConfig/php_cs.php disables that. But if a class IS final, these Rector
 * rules would privatize protected members, breaking child class overrides:
 *
 * - PrivatizeFinalClassMethodRector: changes protected→private on final classes
 * - PrivatizeFinalClassPropertyRector: changes protected→private properties on final classes
 * - PrivatizeFinalClassConstantRector: changes protected→private constants on final classes
 *
 * - AddOverrideAttributeToOverriddenMethodsRector: adds #[\Override] to methods that
 *   don't actually override a parent — produces fatal errors at runtime.
 *
 * - PrivatizeLocalGetterToPropertyRector: can break when getter is overridden in child.
 */
return static function (RectorConfig $rectorConfig): void {
    // Parallel config — match php-qa-ci defaults
    $cpuThreads = \is_readable('/proc/cpuinfo')
        ? \substr_count((string) \file_get_contents('/proc/cpuinfo'), 'processor')
        : 4;
    $maxProcesses = max(1, (int) floor($cpuThreads / 2));

    $rectorConfig->parallel(
        120,
        $maxProcesses,
        16
    );

    // PHP 8.4 upgrade sets — same as default rector-php84.php
    $rectorConfig->sets([
        LevelSetList::UP_TO_PHP_84,
        SetList::PHP_84,
        SetList::DEAD_CODE,
        SetList::CODE_QUALITY,
        SetList::CODING_STYLE,
        SetList::TYPE_DECLARATION,
        SetList::PRIVATIZATION,
        SetList::EARLY_RETURN,
    ]);

    // PHP 8.4 specific rules
    $rectorConfig->rules([
        ExplicitNullableParamTypeRector::class,
    ]);

    // Skip rules that break cross-component inheritance
    $rectorConfig->skip([
        PrivatizeFinalClassMethodRector::class,
        PrivatizeFinalClassPropertyRector::class,
        PrivatizeFinalClassConstantRector::class,
        AddOverrideAttributeToOverriddenMethodsRector::class,
        PrivatizeLocalGetterToPropertyRector::class,
        // Generated fixture snapshots — Rector must not modify approved golden files
        // NOTE: Rector skip() does NOT expand globs — must expand to literal paths
        ...array_filter(
            glob(__DIR__ . '/../src/Component/OpenApi3/Tests/fixtures/*/expected') ?: [],
            'is_dir',
        ),
        __DIR__ . '/../src/Component/OpenApi3/Tests/client/expected',
        __DIR__ . '/../src/Component/OpenApi3/Tests/client/generated',
        // Runtime templates — copied verbatim into generated code under arbitrary namespaces.
        // Must keep \-prefixed FQCNs; Rector/CS Fixer must not modify them.
        __DIR__ . '/../src/Component/GeneratorCore/Generator/Runtime/data',
        __DIR__ . '/../src/Component/OpenApiCommon/Generator/Runtime/data',
    ]);

    // Use memory cache for performance
    $rectorConfig->cacheClass(MemoryCacheStorage::class);

    // Support for ignoring paths via environment variable
    if (isset($_SERVER['rectorIgnorePaths'])) {
        $ignorePaths = array_filter(array_map('trim', explode("\n", $_SERVER['rectorIgnorePaths'])));
        $rectorConfig->skip($ignorePaths);
    }
};
