<?php

declare(strict_types=1);

namespace LongTermSupport\OpenApiGenerator\Component\OpenApiCommon\Console\Loader;

use LogicException;
use LongTermSupport\OpenApiGenerator\Component\GeneratorCore\Console\Loader\SchemaLoader as BaseSchemaLoader;
use LongTermSupport\OpenApiGenerator\Component\GeneratorCore\Console\Loader\SchemaLoaderInterface;
use LongTermSupport\OpenApiGenerator\Component\OpenApiCommon\Registry\Schema;

class SchemaLoader extends BaseSchemaLoader implements SchemaLoaderInterface
{
    protected function newSchema(string $schema, array $options): Schema
    {
        if (!\is_string($options['namespace'])) {
            throw new LogicException('Expected string for namespace, got ' . get_debug_type($options['namespace']));
        }

        if (!\is_string($options['directory'])) {
            throw new LogicException('Expected string for directory, got ' . get_debug_type($options['directory']));
        }

        return new Schema($schema, $options['namespace'], $options['directory']);
    }

    protected function getDefinedOptions(): array
    {
        return [
            'openapi-file',
            'reference',
            'date-format',
            'full-date-format',
            'date-prefer-interface',
            'date-input-format',
            'strict',
            'use-fixer',
            'fixer-config-file',
            'clean-generated',
            'use-cacheable-supports-method',
            'skip-null-values',
            'skip-required-fields',
            'validation',
            'version',
            'whitelisted-paths',
            'endpoint-generator',
            'custom-query-resolver',
            'throw-unexpected-status-code',
            'custom-string-format-mapping',
            'include-null-value',
        ];
    }

    protected function getRequiredOptions(): array
    {
        return [
            'namespace',
            'directory',
        ];
    }
}
