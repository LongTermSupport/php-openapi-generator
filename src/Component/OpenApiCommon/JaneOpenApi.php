<?php

declare(strict_types=1);

namespace LongTermSupport\OpenApiGenerator\Component\OpenApiCommon;

use Generator;
use LogicException;
use LongTermSupport\OpenApiGenerator\Component\GeneratorCore\Generator\ChainGenerator;
use LongTermSupport\OpenApiGenerator\Component\GeneratorCore\Generator\Context\Context;
use LongTermSupport\OpenApiGenerator\Component\GeneratorCore\Generator\Naming;
use LongTermSupport\OpenApiGenerator\Component\GeneratorCore\Guesser\ChainGuesser;
use LongTermSupport\OpenApiGenerator\Component\GeneratorCore\Guesser\Validator\ChainValidatorFactory;
use LongTermSupport\OpenApiGenerator\Component\GeneratorCore\Registry\Registry;
use LongTermSupport\OpenApiGenerator\Component\OpenApiCommon\Contracts\WhitelistFetchInterface;
use LongTermSupport\OpenApiGenerator\Component\OpenApiCommon\Guesser\Guess\ClassGuess;
use LongTermSupport\OpenApiGenerator\Component\OpenApiCommon\Guesser\Guess\ParentClass;
use LongTermSupport\OpenApiGenerator\Component\OpenApiCommon\Registry\Registry as OpenApiRegistry;
use LongTermSupport\OpenApiGenerator\Component\OpenApiCommon\Registry\Schema;
use LongTermSupport\OpenApiGenerator\Component\OpenApiCommon\SchemaParser\SchemaParser;
use Symfony\Component\Serializer\Encoder\JsonDecode;
use Symfony\Component\Serializer\Encoder\JsonEncode;
use Symfony\Component\Serializer\Encoder\JsonEncoder;
use Symfony\Component\Serializer\Encoder\YamlEncoder;
use Symfony\Component\Serializer\Normalizer\DenormalizerInterface;
use Symfony\Component\Serializer\Normalizer\NormalizerInterface;
use Symfony\Component\Serializer\Serializer;
use Symfony\Component\Serializer\SerializerInterface;
use Symfony\Component\Yaml\Dumper;
use Symfony\Component\Yaml\Parser;

abstract class JaneOpenApi extends ChainGenerator
{
    protected const OBJECT_NORMALIZER_CLASS = null;

    protected const WHITELIST_FETCH_CLASS = null;

    protected SchemaParser $schemaParser;

    protected Naming $naming;

    protected SerializerInterface|NormalizerInterface|DenormalizerInterface $serializer;

    /**
     * @param class-string $schemaParserClass
     */
    public function __construct(
        string $schemaParserClass,
        protected ChainGuesser $chainGuesser,
        protected bool $strict = true,
    ) {
        $this->serializer = self::buildSerializer();
        if (!$this->serializer instanceof DenormalizerInterface) {
            throw new LogicException('Expected DenormalizerInterface, got ' . get_debug_type($this->serializer));
        }

        $schemaParserInstance = new $schemaParserClass($this->serializer);
        if (!$schemaParserInstance instanceof SchemaParser) {
            throw new LogicException('Expected SchemaParser, got ' . get_debug_type($schemaParserInstance));
        }

        $this->schemaParser = $schemaParserInstance;
        $this->naming       = new Naming();
    }

    public function getSerializer(): SerializerInterface|NormalizerInterface|DenormalizerInterface
    {
        return $this->serializer;
    }

    public static function buildSerializer(): SerializerInterface|DenormalizerInterface|NormalizerInterface
    {
        $encoders = [
            new JsonEncoder(new JsonEncode([JsonEncode::OPTIONS => \JSON_UNESCAPED_SLASHES]), new JsonDecode()),
            new YamlEncoder(new Dumper(), new Parser()),
        ];

        $objectNormalizerClass = static::OBJECT_NORMALIZER_CLASS;
        $objectNormalizer      = new $objectNormalizerClass();
        if (!$objectNormalizer instanceof NormalizerInterface && !$objectNormalizer instanceof DenormalizerInterface) {
            throw new LogicException('Expected NormalizerInterface or DenormalizerInterface, got ' . get_debug_type($objectNormalizer));
        }

        return new Serializer([$objectNormalizer], $encoders);
    }

    /** @param array<string, mixed> $options */
    public static function build(array $options = []): static
    {
        $instance = static::create($options);

        $serializer = $instance->getSerializer();
        if (!$serializer instanceof DenormalizerInterface) {
            throw new LogicException('Expected DenormalizerInterface, got ' . get_debug_type($serializer));
        }

        $generators = static::generators($serializer, $options);

        foreach ($generators as $generator) {
            if (!$generator instanceof \LongTermSupport\OpenApiGenerator\Component\GeneratorCore\Generator\GeneratorInterface) {
                throw new LogicException('Expected GeneratorInterface, got ' . get_debug_type($generator));
            }

            $instance->addGenerator($generator);
        }

        return $instance;
    }

    protected function createContext(Registry $registry): Context
    {
        if (!$registry instanceof OpenApiRegistry) {
            throw new LogicException('Expected OpenApiRegistry, got ' . get_debug_type($registry));
        }

        /** @var array<int, Schema> $schemas */
        $schemas = array_values($registry->getSchemas());

        foreach ($schemas as $schema) {
            $openApiSpec = $this->schemaParser->parseSchema($schema->getOrigin());
            $this->chainGuesser->guessClass($openApiSpec, $schema->getRootName(), $schema->getOrigin() . '#', $registry);
            $schema->setParsed($openApiSpec);
        }

        $serializerForValidator = $this->serializer;
        if (!$serializerForValidator instanceof DenormalizerInterface) {
            throw new LogicException('Expected DenormalizerInterface, got ' . get_debug_type($serializerForValidator));
        }

        $chainValidator = ChainValidatorFactory::create($this->naming, $registry, $serializerForValidator);

        foreach ($schemas as $schema) {
            foreach ($schema->getClasses() as $class) {
                $properties = $this->chainGuesser->guessProperties($class->getObject(), $schema->getRootName(), $class->getReference(), $registry);

                $names = [];
                foreach ($properties as $property) {
                    $deduplicatedName = $this->naming->getDeduplicatedName($property->getName(), $names);

                    $property->setAccessorName($deduplicatedName);
                    $property->setPhpName($this->naming->getPropertyName($deduplicatedName));

                    $property->setType($this->chainGuesser->guessType($property->getObject(), $property->getName(), $property->getReference(), $registry));
                }

                $class->setProperties($properties);
                $schema->addClassRelations($class);

                $extensionsTypes = [];
                foreach ($class->getExtensionsObject() as $pattern => $extensionData) {
                    /** @var array{object: mixed, reference: string} $extensionData */
                    $extensionsTypes[$pattern] = $this->chainGuesser->guessType($extensionData['object'], $class->getName(), $extensionData['reference'], $registry);
                }

                $class->setExtensionsType($extensionsTypes);

                $chainValidator->guess($class->getObject(), $class->getName(), $class);
            }

            $this->hydrateDiscriminatedClasses($schema, $registry);

            // when we have a whitelist, we want to have only needed models to be generated
            if ([] !== $registry->getWhitelistedPaths()) {
                $this->whitelistFetch($schema, $registry);
            }
        }

        return new Context($registry, $this->strict);
    }

    /**
     * @param OpenApiRegistry $registry
     */
    protected function whitelistFetch(Schema $schema, Registry $registry): void
    {
        $whitelistFetchClass = static::WHITELIST_FETCH_CLASS;
        $whitelistedSchema   = new $whitelistFetchClass($schema, self::buildSerializer());
        if (!$whitelistedSchema instanceof WhitelistFetchInterface) {
            throw new LogicException('Expected WhitelistFetchInterface, got ' . get_debug_type($whitelistedSchema));
        }

        foreach ($schema->getOperations() as $operation) {
            $whitelistedSchema->addOperationRelations($operation, $registry);
        }

        $schema->filterRelations();
    }

    protected function hydrateDiscriminatedClasses(Schema $schema, Registry $registry): void
    {
        foreach ($schema->getClasses() as $class) {
            if ($class instanceof ParentClass) { // is parent class
                foreach ($class->getChildReferences() as $reference) {
                    $guess = $registry->getClass($reference);
                    if ($guess instanceof ClassGuess) { // is child class
                        $guess->setParentClass($class);
                    }
                }
            }
        }
    }

    /** @param array<string, mixed> $options */
    abstract protected static function create(array $options = []): static;

    /**
     * @param array<string, mixed> $options
     *
     * @return Generator<int, mixed>
     */
    abstract protected static function generators(DenormalizerInterface $denormalizer, array $options = []): Generator;
}
