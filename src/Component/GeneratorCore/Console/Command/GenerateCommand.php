<?php

declare(strict_types=1);

namespace LongTermSupport\OpenApiGenerator\Component\GeneratorCore\Console\Command;

use LogicException;
use LongTermSupport\OpenApiGenerator\Component\GeneratorCore\Console\Loader\ConfigLoaderInterface;
use LongTermSupport\OpenApiGenerator\Component\GeneratorCore\Console\Loader\SchemaLoaderInterface;
use LongTermSupport\OpenApiGenerator\Component\GeneratorCore\Registry\Registry;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

abstract class GenerateCommand extends Command
{
    public function __construct(
        protected ConfigLoaderInterface $configLoader,
        protected SchemaLoaderInterface $schemaLoader,
    ) {
        parent::__construct();
    }

    protected function configure(): void
    {
        $this->setName('generate');
        $this->setDescription('Generate a set of classes and normalizers given a specific OpenAPI file');
        $this->addOption('config-file', 'c', InputOption::VALUE_REQUIRED, 'File to use for configuration', '.php-openapi');
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        throw new LogicException(\sprintf('Override %s::execute() in a subclass.', static::class));
    }

    /**
     * @param array<string, mixed> $options
     *
     * @return array<int|string, Registry>
     */
    protected function registries(array $options): array
    {
        $registries = [];
        if (\array_key_exists($fileKey = $this->configLoader->fileKey(), $options)) {
            if (!\is_string($options[$fileKey]) || !\is_string($options['directory'])) {
                throw new LogicException('Config file key and directory must be strings');
            }

            $localRegistry = $this->newRegistry($options[$fileKey], $options);
            $localRegistry->addSchema($this->schemaLoader->resolve($options[$fileKey], $options));
            $localRegistry->addOutputDirectory($options['directory']);

            $registries[] = $localRegistry;
        } else {
            if (!\is_array($options['mapping'])) {
                throw new LogicException('Expected mapping to be an array');
            }

            $mapping = $options['mapping'];
            foreach ($mapping as $schema => $schemaOptions) {
                if (!\is_array($schemaOptions)) {
                    throw new LogicException('Expected schema options to be an array');
                }

                $stringKeyedSchemaOptions = [];
                foreach ($schemaOptions as $optionKey => $optionValue) {
                    if (!\is_string($optionKey)) {
                        throw new LogicException('Expected schema option keys to be strings, got ' . get_debug_type($optionKey));
                    }

                    $stringKeyedSchemaOptions[$optionKey] = $optionValue;
                }

                $mappedSchema   = $this->schemaLoader->resolve((string)$schema, $stringKeyedSchemaOptions);
                $mappedRegistry = $this->newRegistry((string)$schema, $stringKeyedSchemaOptions);

                if (!\array_key_exists($hash = $mappedRegistry->getOptionsHash(), $registries)) {
                    $registries[$hash] = $mappedRegistry;
                }

                if (!\is_string($stringKeyedSchemaOptions['directory'] ?? null)) {
                    throw new LogicException('Schema directory must be a string');
                }

                $registries[$hash]->addSchema($mappedSchema);
                $registries[$hash]->addOutputDirectory($stringKeyedSchemaOptions['directory']);
            }
        }

        return $registries;
    }

    /** @param array<string, mixed> $options */
    protected function newRegistry(string $schemaFile, array $options): Registry
    {
        return new Registry();
    }
}
