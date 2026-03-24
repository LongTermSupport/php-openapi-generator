<?php

declare(strict_types=1);

namespace LongTermSupport\OpenApiGenerator\Component\GeneratorCore\Guesser;

use LogicException;
use LongTermSupport\OpenApiGenerator\Component\GeneratorCore\Guesser\Guess\Type;
use LongTermSupport\OpenApiGenerator\Component\GeneratorCore\JsonSchema\Model\JsonSchema;
use LongTermSupport\OpenApiGenerator\Component\GeneratorCore\Registry\Registry;
use LongTermSupport\OpenApiGenerator\Component\OpenApiRuntime\Reference;
use Symfony\Component\Serializer\Normalizer\DenormalizerInterface;

class ReferenceGuesser implements ClassGuesserInterface, GuesserInterface, TypeGuesserInterface, ChainGuesserAwareInterface
{
    use ChainGuesserAwareTrait;
    use GuesserResolverTrait;

    public function __construct(DenormalizerInterface $denormalizer)
    {
        $this->denormalizer = $denormalizer;
    }

    public function supportObject(mixed $object): bool
    {
        return $object instanceof Reference;
    }

    public function guessClass(mixed $object, string $name, string $reference, Registry $registry): void
    {
        if (!$object instanceof Reference) {
            throw new LogicException('Expected Reference, got ' . get_debug_type($object));
        }

        if ($object->isInCurrentDocument()) {
            return;
        }

        $mergedReference = $object->getMergedUri()->__toString();

        if (!$registry->getSchema($mergedReference) instanceof \LongTermSupport\OpenApiGenerator\Component\GeneratorCore\Registry\Schema) {
            $schema = $registry->getSchema($object->getOriginUri()->__toString());
            if ($schema instanceof \LongTermSupport\OpenApiGenerator\Component\GeneratorCore\Registry\Schema) {
                $schema->addReference($object->getMergedUri()->withFragment('')->__toString());
            }
        }

        $mergedWithoutFragment = $object->getMergedUri()->withFragment('')->__toString();
        $this->chainGuesser->guessClass(
            $this->resolve($object, $this->getSchemaClass()),
            $name,
            $mergedWithoutFragment === $mergedReference ? $mergedWithoutFragment . '#' : $mergedReference,
            $registry
        );
    }

    public function guessType(mixed $object, string $name, string $reference, Registry $registry): Type
    {
        if (!$object instanceof Reference) {
            throw new LogicException('Expected Reference, got ' . get_debug_type($object));
        }

        $resolved = $this->resolve($object, $this->getSchemaClass());
        $classKey = $object->getMergedUri()->__toString();

        if ($classKey === $object->getMergedUri()->withFragment('')->__toString()) {
            $classKey .= '#';
        }

        if ($registry->hasClass($classKey)) {
            $class = $registry->getClass($classKey);
            if (!$class instanceof Guess\ClassGuess) {
                throw new LogicException('Expected ClassGuess, got ' . get_debug_type($class));
            }

            $name = $class->getName();
        }

        return $this->chainGuesser->guessType($resolved, $name, $classKey, $registry);
    }

    protected function getSchemaClass(): string
    {
        return JsonSchema::class;
    }
}
