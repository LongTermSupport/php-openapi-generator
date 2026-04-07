<?php

declare(strict_types=1);

namespace LongTermSupport\OpenApiGenerator\Component\OpenApi3\Guesser\OpenApiSchema;

use LogicException;
use LongTermSupport\OpenApiGenerator\Component\GeneratorCore\Generator\Naming;
use LongTermSupport\OpenApiGenerator\Component\GeneratorCore\Guesser\ChainGuesserAwareInterface;
use LongTermSupport\OpenApiGenerator\Component\GeneratorCore\Guesser\ChainGuesserAwareTrait;
use LongTermSupport\OpenApiGenerator\Component\GeneratorCore\Guesser\Guess\MultipleType;
use LongTermSupport\OpenApiGenerator\Component\GeneratorCore\Guesser\Guess\Type;
use LongTermSupport\OpenApiGenerator\Component\GeneratorCore\Guesser\GuesserInterface;
use LongTermSupport\OpenApiGenerator\Component\GeneratorCore\Guesser\GuesserResolverTrait;
use LongTermSupport\OpenApiGenerator\Component\GeneratorCore\Guesser\TypeGuesserInterface;
use LongTermSupport\OpenApiGenerator\Component\GeneratorCore\Registry\Registry;
use LongTermSupport\OpenApiGenerator\Component\OpenApi3\JsonSchema\Model\Schema;
use LongTermSupport\OpenApiGenerator\Component\OpenApiRuntime\Reference;
use Symfony\Component\Serializer\Normalizer\DenormalizerInterface;

class AnyOfReferencefGuesser implements ChainGuesserAwareInterface, GuesserInterface, TypeGuesserInterface
{
    use ChainGuesserAwareTrait;
    use GuesserResolverTrait;

    public function __construct(
        DenormalizerInterface $denormalizer,
        protected Naming $naming,
        protected string $schemaClass,
    ) {
        $this->denormalizer = $denormalizer;
    }

    public function supportObject(mixed $object): bool
    {
        // getAnyOf() PHPDoc says Schema[]|Reference[] but runtime items may be OpenApiRuntime\Reference
        return $object instanceof Schema && \is_array($object->getAnyOf()) && $object->getAnyOf()[0] instanceof Reference;
    }

    public function guessType(mixed $object, string $name, string $reference, Registry $registry): Type
    {
        if (!\is_object($object)) {
            throw new LogicException('Expected object, got ' . get_debug_type($object));
        }

        $type = new MultipleType($object);
        if ($object instanceof Schema) {
            $mapping               = null;
            $supportsDiscriminator = false;
            $discriminator         = $object->getDiscriminator();
            if ($discriminator instanceof \LongTermSupport\OpenApiGenerator\Component\OpenApi3\JsonSchema\Model\Discriminator && null !== $discriminator->getPropertyName()) {
                $supportsDiscriminator = true;
                $type->setDiscriminatorProperty($discriminator->getPropertyName());
                $discriminatorMapping = $discriminator->getMapping();
                if (null !== $discriminatorMapping) {
                    $mapping = array_flip(\is_array($discriminatorMapping) ? $discriminatorMapping : iterator_to_array($discriminatorMapping));
                }
            }

            $anyOfs = $object->getAnyOf();
            if (null !== $anyOfs) {
                foreach ($anyOfs as $index => $anyOf) {
                    if (null === $anyOf) {
                        continue;
                    }

                    $anyOfSchema    = $anyOf;
                    $anyOfReference = $reference . '/anyOf/' . $index;

                    // Runtime items may be OpenApiRuntime\Reference despite PHPDoc saying OpenApi3\JsonSchema\Model\Reference
                    if ($anyOf instanceof Reference) {
                        $mergedUri = $anyOf->getMergedUri();

                        $anyOfReference  = $mergedUri->__toString();
                        $anyOfNoFragment = $mergedUri->withFragment('')->__toString();
                        if ($anyOfReference === $anyOfNoFragment) {
                            $anyOfReference .= '#';
                        }

                        $anyOfSchema = $this->resolve($anyOf, $this->schemaClass);
                    }

                    if (!\is_object($anyOfSchema)) {
                        continue;
                    }

                    if ($anyOfSchema instanceof Schema && null !== $anyOfSchema->getType()) {
                        $anyOfType = $this->chainGuesser->guessType($anyOfSchema, $name, $anyOfReference, $registry);
                        if ($supportsDiscriminator && $anyOf instanceof Reference) {
                            $anyOfMergedUri = $anyOf->getMergedUri();

                            $objectRef = '#' . $anyOfMergedUri->getFragment();
                            $type->addType($anyOfType, null !== $mapping ? ($mapping[$objectRef] ?? $objectRef) : $objectRef);
                        } else {
                            $type->addType($anyOfType);
                        }
                    }
                }
            }
        }

        return $type;
    }
}
