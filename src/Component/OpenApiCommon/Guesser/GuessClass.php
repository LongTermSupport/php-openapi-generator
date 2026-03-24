<?php

declare(strict_types=1);

namespace LongTermSupport\OpenApiGenerator\Component\OpenApiCommon\Guesser;

use LongTermSupport\OpenApiGenerator\Component\GeneratorCore\Guesser\Guess\ClassGuess;
use LongTermSupport\OpenApiGenerator\Component\OpenApiCommon\Registry\Registry;
use LongTermSupport\OpenApiGenerator\Component\OpenApiRuntime\Reference;
use Symfony\Component\Serializer\Normalizer\DenormalizerInterface;

class GuessClass
{
    public function __construct(
        private readonly string $schemaClass,
        protected DenormalizerInterface $denormalizer,
    ) {
    }

    public function guessClass(mixed &$schema, string $reference, Registry $registry, ?bool &$array = null): ?ClassGuess
    {
        $array = false;

        if ($schema instanceof Reference) {
            [$reference, $schema] = $this->resolve($schema, $this->schemaClass);
        }

        // PHPStan cannot narrow from a variable class-string instanceof; use method_exists guards
        if ($schema instanceof $this->schemaClass && method_exists($schema, 'getType') && 'array' === $schema->getType()) {
            $array = true;
            $reference .= '/items';
            $items = method_exists($schema, 'getItems') ? $schema->getItems() : null;

            if ($items instanceof Reference) {
                [$reference] = $this->resolve($items, $this->schemaClass);
            }
        }

        return $registry->getClass($reference);
    }

    /** @return array{0: string, 1: mixed} */
    public function resolve(Reference $reference, string $class): array
    {
        $result = $reference;

        do {
            $refString = $reference->getMergedUri()->__toString();
            $result    = $result->resolve(fn ($data): mixed => $this->denormalizer->denormalize($data, $class, 'json', [
                'document-origin' => $result->getMergedUri()->withFragment('')->__toString(),
            ]));
        } while ($result instanceof Reference);

        return [$refString, $result];
    }
}
