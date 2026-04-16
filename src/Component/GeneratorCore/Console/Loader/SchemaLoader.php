<?php

declare(strict_types=1);

namespace LongTermSupport\OpenApiGenerator\Component\GeneratorCore\Console\Loader;

use LogicException;
use LongTermSupport\OpenApiGenerator\Component\GeneratorCore\Registry\Schema;
use Symfony\Component\OptionsResolver\OptionsResolver;

class SchemaLoader implements SchemaLoaderInterface
{
    /** @param array<string, mixed> $options */
    public function resolve(string $schema, array $options = []): Schema
    {
        $optionsResolver = new OptionsResolver();

        $optionsResolver->setDefined($this->getDefinedOptions());
        $optionsResolver->setRequired($this->getRequiredOptions());
        /** @var array<string, mixed> $resolved */
        $resolved = $optionsResolver->resolve($options);

        return $this->newSchema($schema, $resolved);
    }

    /**
     * @param array<string, mixed> $options
     */
    protected function newSchema(string $schema, array $options): Schema
    {
        if (!\is_string($options['namespace'])) {
            throw new LogicException('Expected string, got ' . get_debug_type($options['namespace']));
        }

        if (!\is_string($options['directory'])) {
            throw new LogicException('Expected string, got ' . get_debug_type($options['directory']));
        }

        $rootClassRaw = $options['root-class'];
        if (!\is_string($rootClassRaw)) {
            throw new LogicException('Expected string, got ' . get_debug_type($rootClassRaw));
        }

        $rootClass = $rootClassRaw;

        return new Schema($schema, $options['namespace'], $options['directory'], $rootClass);
    }

    /** @return array<string> */
    protected function getDefinedOptions(): array
    {
        return [
            'json-schema-file',
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
            'custom-string-format-mapping',
            'validation',
            'include-null-value',
        ];
    }

    /** @return array<string> */
    protected function getRequiredOptions(): array
    {
        return [
            'root-class',
            'namespace',
            'directory',
        ];
    }
}
