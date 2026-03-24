<?php

declare(strict_types=1);

namespace LongTermSupport\OpenApiGenerator\Component\GeneratorCore\Registry;

use Exception;
use League\Uri\Http;
use LongTermSupport\OpenApiGenerator\Component\GeneratorCore\Guesser\Guess\ClassGuess;

class Registry implements RegistryInterface
{
    /** @var array<string> */
    protected array $outputDirectories = [];

    /** @var array<Schema> */
    protected array $schemas = [];

    public function addOutputDirectory(string $outputDirectory): void
    {
        $this->outputDirectories[] = $outputDirectory;
    }

    /**
     * @return array<string>
     */
    public function getOutputDirectories(): array
    {
        return $this->outputDirectories;
    }

    public function addSchema(Schema $schema): void
    {
        $this->schemas[] = $schema;
    }

    public function getSchema(string $reference): ?Schema
    {
        $reference = $this->fixPath($reference);
        $uri       = Http::new($reference);
        $schemaUri = $uri->withFragment('')->__toString();

        foreach ($this->schemas as $schema) {
            if ($schema->hasReference($schemaUri)) {
                return $schema;
            }
        }

        return null;
    }

    public function getFirstSchema(): Schema
    {
        foreach ($this->schemas as $schema) {
            return $schema;
        }

        throw new Exception('No schema found.');
    }

    /**
     * @return array<Schema>
     */
    public function getSchemas(): array
    {
        return $this->schemas;
    }

    public function hasClass(string $classReference): bool
    {
        return $this->getClass($classReference) instanceof ClassGuess;
    }

    public function getClass(string $classReference): ?ClassGuess
    {
        $schema = $this->getSchema($classReference);

        if (!$schema instanceof Schema) {
            return null;
        }

        return $schema->getClass($classReference);
    }

    public function getOptionsHash(): string
    {
        return md5(\Safe\json_encode([]));
    }

    private function fixPath(string $path): string
    {
        if ('\\' === \DIRECTORY_SEPARATOR) {
            return lcfirst(str_replace(\DIRECTORY_SEPARATOR, '/', $path));
        }

        return $path;
    }
}
