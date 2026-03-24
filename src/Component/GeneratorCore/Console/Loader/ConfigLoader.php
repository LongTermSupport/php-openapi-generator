<?php

declare(strict_types=1);

namespace LongTermSupport\OpenApiGenerator\Component\GeneratorCore\Console\Loader;

use DateTime;
use RuntimeException;
use Symfony\Component\OptionsResolver\OptionsResolver;

class ConfigLoader implements ConfigLoaderInterface
{
    public function fileKey(): string
    {
        return 'json-schema-file';
    }

    /** @return array<string, mixed> */
    public function load(string $path): array
    {
        if (!file_exists($path)) {
            throw new RuntimeException(\sprintf('Config file %s does not exist', $path));
        }

        $options = require $path;

        if (!\is_array($options)) {
            throw new RuntimeException(\sprintf('Invalid config file specified or invalid return type in file %s', $path));
        }

        /** @var array<string, mixed> $typedOptions */
        $typedOptions = $options;

        return $this->resolveConfiguration($typedOptions);
    }

    /**
     * @param array<string, mixed> $options
     *
     * @return array<string, mixed>
     */
    protected function resolveConfiguration(array $options = []): array
    {
        $optionsResolver = new OptionsResolver();
        $optionsResolver->setDefaults($this->resolveConfigurationDefaults());

        if (\array_key_exists($this->fileKey(), $options)) {
            $optionsResolver->setRequired($this->resolveConfigurationRequired());
        } else {
            $optionsResolver->setRequired([
                'mapping',
            ]);
        }

        return $optionsResolver->resolve($options);
    }

    /** @return array<string> */
    protected function resolveConfigurationRequired(): array
    {
        return [
            $this->fileKey(),
            'root-class',
            'namespace',
            'directory',
        ];
    }

    /** @return array<string, mixed> */
    protected function resolveConfigurationDefaults(): array
    {
        return [
            'reference'                     => true,
            'strict'                        => true,
            'date-format'                   => DateTime::RFC3339,
            'full-date-format'              => 'Y-m-d',
            'date-prefer-interface'         => null,
            'date-input-format'             => null,
            'use-fixer'                     => false,
            'fixer-config-file'             => null,
            'clean-generated'               => true,
            'use-cacheable-supports-method' => null,
            'skip-null-values'              => true,
            'skip-required-fields'          => false,
            'custom-string-format-mapping'  => [],
            'validation'                    => false,
            'include-null-value'            => true,
        ];
    }
}
