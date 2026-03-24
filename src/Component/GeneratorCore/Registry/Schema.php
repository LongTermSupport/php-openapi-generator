<?php

declare(strict_types=1);

namespace LongTermSupport\OpenApiGenerator\Component\GeneratorCore\Registry;

use LogicException;
use LongTermSupport\OpenApiGenerator\Component\GeneratorCore\Generator\File;
use LongTermSupport\OpenApiGenerator\Component\GeneratorCore\Guesser\Guess\ArrayType;
use LongTermSupport\OpenApiGenerator\Component\GeneratorCore\Guesser\Guess\ClassGuess;
use LongTermSupport\OpenApiGenerator\Component\GeneratorCore\Guesser\Guess\ObjectType;

class Schema implements SchemaInterface
{
    /** @var array<string, list<string>> Relation between models */
    protected array $relations = [];

    private readonly string $origin;

    /** @var array<ClassGuess> List of classes associated to this schema */
    private array $classes = [];

    /** @var array<string> A list of references this schema is registered to */
    private array $references;

    /** @var array<File> A list of references this schema is registered to */
    private array $files = [];

    private mixed $parsed;

    /**
     * @param string $namespace Namespace wanted for this schema
     * @param string $directory Directory where to put files
     * @param string $rootName  Name of the root object in the schema (if needed)
     */
    public function __construct(
        string $origin,
        private readonly string $namespace,
        private readonly string $directory,
        private readonly string $rootName,
    ) {
        $this->origin     = $this->fixPath($origin);
        $this->references = [$this->origin];
    }

    public function getOrigin(): string
    {
        return $this->origin;
    }

    public function getNamespace(): string
    {
        return $this->namespace;
    }

    public function getDirectory(): string
    {
        return $this->directory;
    }

    public function getRootName(): string
    {
        return $this->rootName;
    }

    public function addClass(string $reference, ClassGuess $class): void
    {
        $this->classes[urldecode($reference)] = $class;
    }

    public function removeClass(string $reference): void
    {
        unset($this->classes[urldecode($reference)]);
    }

    public function getClass(string $reference): ?ClassGuess
    {
        $reference = urldecode($reference);

        if (\array_key_exists($reference, $this->classes)) {
            return $this->classes[$reference];
        }

        if (\array_key_exists($reference . '#', $this->classes)) {
            return $this->classes[$reference . '#'];
        }

        return null;
    }

    /**
     * @return array{0: ClassGuess, 1: string}|null
     */
    public function findPropertyClass(string $sourceObject, string $propertyObject): ?array
    {
        $referencePart = \sprintf('%s/properties/%s', $sourceObject, $propertyObject);

        foreach ($this->classes as $class) {
            if (str_contains($class->getReference(), $referencePart)) {
                return [$class, $class->getReference()];
            }
        }

        return null;
    }

    /**
     * @return ClassGuess[]
     */
    public function getClasses(): array
    {
        return $this->classes;
    }

    public function addFile(File $file): void
    {
        $this->files[] = $file;
    }

    /**
     * @return array<File>
     */
    public function getFiles(): array
    {
        return $this->files;
    }

    public function addReference(string $reference): void
    {
        $this->references[] = $reference;
    }

    public function hasReference(string $reference): bool
    {
        return \in_array($reference, $this->references, true);
    }

    public function getParsed(): mixed
    {
        return $this->parsed;
    }

    public function setParsed(mixed $parsed): void
    {
        $this->parsed = $parsed;
    }

    public function addRelation(string $model, string $needs): void
    {
        if ($needs === $model) {
            return;
        }

        if (!\array_key_exists($model, $this->relations)) {
            $this->relations[$model] = [];
        }

        $this->relations[$model][] = $needs;
    }

    public function relationExists(string $model): bool
    {
        return \array_key_exists($model, $this->relations);
    }

    public function addClassRelations(ClassGuess $classGuess): void
    {
        $baseModel = $classGuess->getName();
        if ($this->relationExists($baseModel)) {
            return;
        }

        foreach ($classGuess->getProperties() as $property) {
            // second condition is here to avoid mapping PHP classes such as \DateTime
            if (($objectType = $property->getType()) instanceof ObjectType
                && !str_starts_with($objectType->getClassName(), '\\')) {
                $this->addRelation($baseModel, $objectType->getClassName());
            }

            if (($arrayType = $property->getType()) instanceof ArrayType
                && ($itemType = $arrayType->getItemType()) instanceof ObjectType
                && !str_starts_with($itemType->getClassName(), '\\')) {
                $this->addRelation($baseModel, $itemType->getClassName());
            }
        }
    }

    private function fixPath(string $path): string
    {
        if ('\\' === \DIRECTORY_SEPARATOR) {
            $path = lcfirst(str_replace(\DIRECTORY_SEPARATOR, '/', $path));
        }

        $result = \Safe\preg_replace('#([^:]){1}/{2,}#', '$1/', $path);
        if (!\is_string($result)) {
            throw new LogicException('Expected string, got ' . get_debug_type($result));
        }

        $path = $result;

        if ('/' === $path) {
            return '/';
        }

        $pathParts = [];
        foreach (explode('/', rtrim($path, '/')) as $part) {
            if ('.' === $part) {
                continue;
            }

            if ('..' === $part && [] !== $pathParts) {
                array_pop($pathParts);
                continue;
            }

            $pathParts[] = $part;
        }

        return implode('/', $pathParts);
    }
}
