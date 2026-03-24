<?php

declare(strict_types=1);

namespace LongTermSupport\OpenApiGenerator\Component\OpenApiCommon\Registry;

use LongTermSupport\OpenApiGenerator\Component\GeneratorCore\Registry\Schema as BaseSchema;
use LongTermSupport\OpenApiGenerator\Component\GeneratorCore\Registry\SchemaInterface;
use LongTermSupport\OpenApiGenerator\Component\OpenApiCommon\Guesser\Guess\OperationGuess;
use LongTermSupport\OpenApiGenerator\Component\OpenApiCommon\Guesser\Guess\SecuritySchemeGuess;

class Schema extends BaseSchema implements SchemaInterface
{
    /** @var OperationGuess[] */
    private array $operations = [];

    /** @var SecuritySchemeGuess[] List of SecuritySchemes associated to this schema */
    private array $securitySchemes = [];

    /** @var array<string> */
    private array $neededModels = [];

    /** @var array<string, array<string>> */
    private array $operationRelations = [];

    public function __construct(string $origin, string $namespace, string $directory)
    {
        parent::__construct($origin, $namespace, $directory, '');
    }

    public function addSecurityScheme(string $reference, SecuritySchemeGuess $securityScheme): void
    {
        $this->securitySchemes[urldecode($reference)] = $securityScheme;
    }

    public function getSecurityScheme(string $reference): ?SecuritySchemeGuess
    {
        $reference = urldecode($reference);

        if (\array_key_exists($reference, $this->securitySchemes)) {
            return $this->securitySchemes[$reference];
        }

        if (\array_key_exists($reference . '#', $this->securitySchemes)) {
            return $this->securitySchemes[$reference . '#'];
        }

        return null;
    }

    /**
     * @return SecuritySchemeGuess[]
     */
    public function getSecuritySchemes(): array
    {
        return $this->securitySchemes;
    }

    public function addOperation(string $reference, OperationGuess $operationGuess): void
    {
        $this->operations[urldecode($reference)] = $operationGuess;
    }

    /**
     * @return OperationGuess[]
     */
    public function getOperations(): array
    {
        return $this->operations;
    }

    public function initOperationRelations(string $model): void
    {
        if (!\array_key_exists($model, $this->operationRelations)) {
            $this->operationRelations[$model] = [];
        }
    }

    public function addOperationRelation(string $model, string $needs): void
    {
        $this->initOperationRelations($model);

        if (!\in_array($needs, $this->operationRelations[$model], true)) {
            $this->operationRelations[$model][] = $needs;
        }
    }

    public function filterRelations(): void
    {
        foreach ($this->operationRelations as $operation => $operationRelations) {
            $this->neededModels[] = $operation;
            $this->fetchRelatedModels($operationRelations);
        }

        foreach ($this->getClasses() as $class) {
            if (!\in_array($class->getName(), $this->neededModels, true)) {
                $this->removeClass($class->getReference());
            }
        }
    }

    /** @param array<string> $models */
    private function fetchRelatedModels(array $models): void
    {
        if ([] === $models) {
            return;
        }

        foreach ($models as $model) {
            if (\in_array($model, $this->neededModels, true)) {
                continue;
            }

            $this->neededModels[] = $model;
            $this->fetchRelatedModels($this->relations[$model] ?? []);
        }
    }
}
