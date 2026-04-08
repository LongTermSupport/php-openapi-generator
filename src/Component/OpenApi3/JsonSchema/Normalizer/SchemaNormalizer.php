<?php

declare(strict_types=1);

namespace LongTermSupport\OpenApiGenerator\Component\OpenApi3\JsonSchema\Normalizer;

use ArrayObject;
use LogicException;
use LongTermSupport\OpenApiGenerator\Component\OpenApi3\JsonSchema\Runtime\Normalizer\CheckArray;
use LongTermSupport\OpenApiGenerator\Component\OpenApi3\JsonSchema\Runtime\Normalizer\TypeValidator;
use LongTermSupport\OpenApiGenerator\Component\OpenApi3\JsonSchema\Runtime\Normalizer\ValidatorTrait;
use LongTermSupport\OpenApiGenerator\Component\OpenApiRuntime\Reference;
use Symfony\Component\Serializer\Normalizer\DenormalizerAwareInterface;
use Symfony\Component\Serializer\Normalizer\DenormalizerAwareTrait;
use Symfony\Component\Serializer\Normalizer\DenormalizerInterface;
use Symfony\Component\Serializer\Normalizer\NormalizerAwareInterface;
use Symfony\Component\Serializer\Normalizer\NormalizerAwareTrait;
use Symfony\Component\Serializer\Normalizer\NormalizerInterface;

class SchemaNormalizer implements DenormalizerInterface, NormalizerInterface, DenormalizerAwareInterface, NormalizerAwareInterface
{
    use DenormalizerAwareTrait;
    use NormalizerAwareTrait;
    use CheckArray;
    use ValidatorTrait;

    public function supportsDenormalization(mixed $data, string $type, ?string $format = null, array $context = []): bool
    {
        return \LongTermSupport\OpenApiGenerator\Component\OpenApi3\JsonSchema\Model\Schema::class === $type;
    }

    public function supportsNormalization(mixed $data, ?string $format = null, array $context = []): bool
    {
        return $data instanceof \LongTermSupport\OpenApiGenerator\Component\OpenApi3\JsonSchema\Model\Schema;
    }

    /**
     * @return ($type is class-string<object> ? object : mixed)
     */
    public function denormalize(mixed $data, string $type, ?string $format = null, array $context = []): mixed
    {
        $object = new \LongTermSupport\OpenApiGenerator\Component\OpenApi3\JsonSchema\Model\Schema();
        if (null === $data || false === \is_array($data)) {
            return $object;
        }

        /** @var array<string, mixed> $data */
        /** @var array<string, mixed> $context */
        if (isset($data['$ref'])) {
            return new Reference(TypeValidator::assertString($data['$ref'], '$ref'), TypeValidator::assertString($context['document-origin'], 'context.document-origin'));
        }

        if (isset($data['$recursiveRef'])) {
            return new Reference(TypeValidator::assertString($data['$recursiveRef'], '$recursiveRef'), TypeValidator::assertString($context['document-origin'], 'context.document-origin'));
        }

        if (\array_key_exists('multipleOf', $data) && \is_int($data['multipleOf'])) {
            $data['multipleOf'] = (float)$data['multipleOf'];
        }

        if (\array_key_exists('maximum', $data) && \is_int($data['maximum'])) {
            $data['maximum'] = (float)$data['maximum'];
        }

        if (\array_key_exists('minimum', $data) && \is_int($data['minimum'])) {
            $data['minimum'] = (float)$data['minimum'];
        }

        if (\array_key_exists('title', $data) && null !== $data['title']) {
            /** @var scalar $titleVal */
            $titleVal = $data['title'];
            $object->setTitle((string)$titleVal);
            unset($data['title']);
        } elseif (\array_key_exists('title', $data) && null === $data['title']) {
            $object->setTitle(null);
        }

        if (\array_key_exists('multipleOf', $data) && null !== $data['multipleOf']) {
            /** @var scalar $multipleOfVal */
            $multipleOfVal = $data['multipleOf'];
            $object->setMultipleOf((float)$multipleOfVal);
            unset($data['multipleOf']);
        } elseif (\array_key_exists('multipleOf', $data) && null === $data['multipleOf']) {
            $object->setMultipleOf(null);
        }

        if (\array_key_exists('maximum', $data) && null !== $data['maximum']) {
            /** @var scalar $maximumVal */
            $maximumVal = $data['maximum'];
            $object->setMaximum((float)$maximumVal);
            unset($data['maximum']);
        } elseif (\array_key_exists('maximum', $data) && null === $data['maximum']) {
            $object->setMaximum(null);
        }

        if (\array_key_exists('exclusiveMaximum', $data) && null !== $data['exclusiveMaximum']) {
            /** @var scalar $exclusiveMaximumVal */
            $exclusiveMaximumVal = $data['exclusiveMaximum'];
            $object->setExclusiveMaximum((bool)$exclusiveMaximumVal);
            unset($data['exclusiveMaximum']);
        } elseif (\array_key_exists('exclusiveMaximum', $data) && null === $data['exclusiveMaximum']) {
            $object->setExclusiveMaximum(null);
        }

        if (\array_key_exists('minimum', $data) && null !== $data['minimum']) {
            /** @var scalar $minimumVal */
            $minimumVal = $data['minimum'];
            $object->setMinimum((float)$minimumVal);
            unset($data['minimum']);
        } elseif (\array_key_exists('minimum', $data) && null === $data['minimum']) {
            $object->setMinimum(null);
        }

        if (\array_key_exists('exclusiveMinimum', $data) && null !== $data['exclusiveMinimum']) {
            /** @var scalar $exclusiveMinimumVal */
            $exclusiveMinimumVal = $data['exclusiveMinimum'];
            $object->setExclusiveMinimum((bool)$exclusiveMinimumVal);
            unset($data['exclusiveMinimum']);
        } elseif (\array_key_exists('exclusiveMinimum', $data) && null === $data['exclusiveMinimum']) {
            $object->setExclusiveMinimum(null);
        }

        if (\array_key_exists('maxLength', $data) && null !== $data['maxLength']) {
            /** @var scalar $maxLengthVal */
            $maxLengthVal = $data['maxLength'];
            $object->setMaxLength((int)$maxLengthVal);
            unset($data['maxLength']);
        } elseif (\array_key_exists('maxLength', $data) && null === $data['maxLength']) {
            $object->setMaxLength(null);
        }

        if (\array_key_exists('minLength', $data) && null !== $data['minLength']) {
            /** @var scalar $minLengthVal */
            $minLengthVal = $data['minLength'];
            $object->setMinLength((int)$minLengthVal);
            unset($data['minLength']);
        } elseif (\array_key_exists('minLength', $data) && null === $data['minLength']) {
            $object->setMinLength(null);
        }

        if (\array_key_exists('pattern', $data) && null !== $data['pattern']) {
            /** @var scalar $patternVal */
            $patternVal = $data['pattern'];
            $object->setPattern((string)$patternVal);
            unset($data['pattern']);
        } elseif (\array_key_exists('pattern', $data) && null === $data['pattern']) {
            $object->setPattern(null);
        }

        if (\array_key_exists('maxItems', $data) && null !== $data['maxItems']) {
            /** @var scalar $maxItemsVal */
            $maxItemsVal = $data['maxItems'];
            $object->setMaxItems((int)$maxItemsVal);
            unset($data['maxItems']);
        } elseif (\array_key_exists('maxItems', $data) && null === $data['maxItems']) {
            $object->setMaxItems(null);
        }

        if (\array_key_exists('minItems', $data) && null !== $data['minItems']) {
            /** @var scalar $minItemsVal */
            $minItemsVal = $data['minItems'];
            $object->setMinItems((int)$minItemsVal);
            unset($data['minItems']);
        } elseif (\array_key_exists('minItems', $data) && null === $data['minItems']) {
            $object->setMinItems(null);
        }

        if (\array_key_exists('uniqueItems', $data) && null !== $data['uniqueItems']) {
            /** @var scalar $uniqueItemsVal */
            $uniqueItemsVal = $data['uniqueItems'];
            $object->setUniqueItems((bool)$uniqueItemsVal);
            unset($data['uniqueItems']);
        } elseif (\array_key_exists('uniqueItems', $data) && null === $data['uniqueItems']) {
            $object->setUniqueItems(null);
        }

        if (\array_key_exists('maxProperties', $data) && null !== $data['maxProperties']) {
            /** @var scalar $maxPropertiesVal */
            $maxPropertiesVal = $data['maxProperties'];
            $object->setMaxProperties((int)$maxPropertiesVal);
            unset($data['maxProperties']);
        } elseif (\array_key_exists('maxProperties', $data) && null === $data['maxProperties']) {
            $object->setMaxProperties(null);
        }

        if (\array_key_exists('minProperties', $data) && null !== $data['minProperties']) {
            /** @var scalar $minPropertiesVal */
            $minPropertiesVal = $data['minProperties'];
            $object->setMinProperties((int)$minPropertiesVal);
            unset($data['minProperties']);
        } elseif (\array_key_exists('minProperties', $data) && null === $data['minProperties']) {
            $object->setMinProperties(null);
        }

        if (\array_key_exists('required', $data) && null !== $data['required']) {
            $values = [];
            /** @var array<mixed> $requiredArr */
            $requiredArr = $data['required'];
            foreach ($requiredArr as $value) {
                /** @var string $value */
                $values[] = $value;
            }

            $object->setRequired($values);
            unset($data['required']);
        } elseif (\array_key_exists('required', $data) && null === $data['required']) {
            $object->setRequired(null);
        }

        if (\array_key_exists('enum', $data) && null !== $data['enum']) {
            $values_1 = [];
            /** @var array<mixed> $enumArr */
            $enumArr = $data['enum'];
            foreach ($enumArr as $value_1) {
                $values_1[] = $value_1;
            }

            $object->setEnum($values_1);
            unset($data['enum']);
        } elseif (\array_key_exists('enum', $data) && null === $data['enum']) {
            $object->setEnum(null);
        }

        if (\array_key_exists('type', $data) && null !== $data['type']) {
            // OAS 3.1: type can be an array like ["string", "null"]
            if (\is_array($data['type'])) {
                $types        = $data['type'];
                $hasNull      = \in_array('null', $types, true);
                $nonNullTypes = array_values(array_filter($types, static fn ($t): bool => 'null' !== $t));
                /** @var scalar|null $firstNonNullType */
                $firstNonNullType = $nonNullTypes[0] ?? null;
                $object->setType(null !== $firstNonNullType ? (string)$firstNonNullType : null);
                if ($hasNull) {
                    $object->setNullable(true);
                }
            } else {
                /** @var scalar $typeVal */
                $typeVal = $data['type'];
                $object->setType((string)$typeVal);
            }

            unset($data['type']);
        } elseif (\array_key_exists('type', $data) && null === $data['type']) {
            $object->setType(null);
        }

        if (\array_key_exists('not', $data) && null !== $data['not']) {
            $value_2 = $data['not'];
            if (\is_array($data['not']) && isset($data['not']['$ref'])) {
                $value_2 = $this->denormalizer->denormalize($data['not'], \LongTermSupport\OpenApiGenerator\Component\OpenApi3\JsonSchema\Model\Reference::class, 'json', $context);
            } elseif (\is_array($data['not'])) {
                $value_2 = $this->denormalizer->denormalize($data['not'], \LongTermSupport\OpenApiGenerator\Component\OpenApi3\JsonSchema\Model\Schema::class, 'json', $context);
            }

            /** @var \LongTermSupport\OpenApiGenerator\Component\OpenApi3\JsonSchema\Model\Reference|\LongTermSupport\OpenApiGenerator\Component\OpenApi3\JsonSchema\Model\Schema $value_2 */
            $object->setNot($value_2);
            unset($data['not']);
        } elseif (\array_key_exists('not', $data) && null === $data['not']) {
            $object->setNot(null);
        }

        if (\array_key_exists('allOf', $data) && null !== $data['allOf']) {
            $values_2 = [];
            /** @var array<mixed> $allOfArr */
            $allOfArr = $data['allOf'];
            foreach ($allOfArr as $value_3) {
                $value_4 = $value_3;
                if (\is_array($value_3) && isset($value_3['$ref'])) {
                    $value_4 = $this->denormalizer->denormalize($value_3, \LongTermSupport\OpenApiGenerator\Component\OpenApi3\JsonSchema\Model\Reference::class, 'json', $context);
                } elseif (\is_array($value_3)) {
                    $value_4 = $this->denormalizer->denormalize($value_3, \LongTermSupport\OpenApiGenerator\Component\OpenApi3\JsonSchema\Model\Schema::class, 'json', $context);
                }

                /** @var \LongTermSupport\OpenApiGenerator\Component\OpenApi3\JsonSchema\Model\Reference|\LongTermSupport\OpenApiGenerator\Component\OpenApi3\JsonSchema\Model\Schema $value_4 */
                $values_2[] = $value_4;
            }

            $object->setAllOf($values_2);
            unset($data['allOf']);
        } elseif (\array_key_exists('allOf', $data) && null === $data['allOf']) {
            $object->setAllOf(null);
        }

        if (\array_key_exists('oneOf', $data) && null !== $data['oneOf']) {
            $values_3 = [];
            /** @var array<mixed> $oneOfArr */
            $oneOfArr = $data['oneOf'];
            foreach ($oneOfArr as $value_5) {
                $value_6 = $value_5;
                if (\is_array($value_5) && isset($value_5['$ref'])) {
                    $value_6 = $this->denormalizer->denormalize($value_5, \LongTermSupport\OpenApiGenerator\Component\OpenApi3\JsonSchema\Model\Reference::class, 'json', $context);
                } elseif (\is_array($value_5)) {
                    $value_6 = $this->denormalizer->denormalize($value_5, \LongTermSupport\OpenApiGenerator\Component\OpenApi3\JsonSchema\Model\Schema::class, 'json', $context);
                }

                /** @var \LongTermSupport\OpenApiGenerator\Component\OpenApi3\JsonSchema\Model\Reference|\LongTermSupport\OpenApiGenerator\Component\OpenApi3\JsonSchema\Model\Schema $value_6 */
                $values_3[] = $value_6;
            }

            $object->setOneOf($values_3);
            unset($data['oneOf']);
        } elseif (\array_key_exists('oneOf', $data) && null === $data['oneOf']) {
            $object->setOneOf(null);
        }

        if (\array_key_exists('anyOf', $data) && null !== $data['anyOf']) {
            $values_4 = [];
            /** @var array<mixed> $anyOfArr */
            $anyOfArr = $data['anyOf'];
            foreach ($anyOfArr as $value_7) {
                $value_8 = $value_7;
                if (\is_array($value_7) && isset($value_7['$ref'])) {
                    $value_8 = $this->denormalizer->denormalize($value_7, \LongTermSupport\OpenApiGenerator\Component\OpenApi3\JsonSchema\Model\Reference::class, 'json', $context);
                } elseif (\is_array($value_7)) {
                    $value_8 = $this->denormalizer->denormalize($value_7, \LongTermSupport\OpenApiGenerator\Component\OpenApi3\JsonSchema\Model\Schema::class, 'json', $context);
                }

                /** @var \LongTermSupport\OpenApiGenerator\Component\OpenApi3\JsonSchema\Model\Reference|\LongTermSupport\OpenApiGenerator\Component\OpenApi3\JsonSchema\Model\Schema $value_8 */
                $values_4[] = $value_8;
            }

            $object->setAnyOf($values_4);
            unset($data['anyOf']);
        } elseif (\array_key_exists('anyOf', $data) && null === $data['anyOf']) {
            $object->setAnyOf(null);
        }

        if (\array_key_exists('items', $data) && null !== $data['items']) {
            $value_9 = $data['items'];
            if (\is_array($data['items']) && isset($data['items']['$ref'])) {
                $value_9 = $this->denormalizer->denormalize($data['items'], \LongTermSupport\OpenApiGenerator\Component\OpenApi3\JsonSchema\Model\Reference::class, 'json', $context);
            } elseif (\is_array($data['items'])) {
                $value_9 = $this->denormalizer->denormalize($data['items'], \LongTermSupport\OpenApiGenerator\Component\OpenApi3\JsonSchema\Model\Schema::class, 'json', $context);
            }

            /** @var \LongTermSupport\OpenApiGenerator\Component\OpenApi3\JsonSchema\Model\Reference|\LongTermSupport\OpenApiGenerator\Component\OpenApi3\JsonSchema\Model\Schema $value_9 */
            $object->setItems($value_9);
            unset($data['items']);
        } elseif (\array_key_exists('items', $data) && null === $data['items']) {
            $object->setItems(null);
        }

        if (\array_key_exists('properties', $data) && null !== $data['properties']) {
            /** @var array<string, \LongTermSupport\OpenApiGenerator\Component\OpenApi3\JsonSchema\Model\Reference|\LongTermSupport\OpenApiGenerator\Component\OpenApi3\JsonSchema\Model\Schema> $values_5 */
            $values_5 = [];
            /** @var array<mixed> $propertiesArr */
            $propertiesArr = $data['properties'];
            foreach ($propertiesArr as $key => $value_10) {
                $key      = TypeValidator::assertStringKey($key, 'properties');
                $value_11 = $value_10;
                if (\is_array($value_10) && isset($value_10['$ref'])) {
                    $value_11 = $this->denormalizer->denormalize($value_10, \LongTermSupport\OpenApiGenerator\Component\OpenApi3\JsonSchema\Model\Reference::class, 'json', $context);
                } elseif (\is_array($value_10)) {
                    $value_11 = $this->denormalizer->denormalize($value_10, \LongTermSupport\OpenApiGenerator\Component\OpenApi3\JsonSchema\Model\Schema::class, 'json', $context);
                }

                /** @var \LongTermSupport\OpenApiGenerator\Component\OpenApi3\JsonSchema\Model\Reference|\LongTermSupport\OpenApiGenerator\Component\OpenApi3\JsonSchema\Model\Schema $value_11 */
                $values_5[$key] = $value_11;
            }

            $object->setProperties($values_5);
            unset($data['properties']);
        } elseif (\array_key_exists('properties', $data) && null === $data['properties']) {
            $object->setProperties(null);
        }

        if (\array_key_exists('additionalProperties', $data) && null !== $data['additionalProperties']) {
            $value_12 = $data['additionalProperties'];
            if (\is_array($data['additionalProperties']) && isset($data['additionalProperties']['$ref'])) {
                $value_12 = $this->denormalizer->denormalize($data['additionalProperties'], \LongTermSupport\OpenApiGenerator\Component\OpenApi3\JsonSchema\Model\Reference::class, 'json', $context);
            } elseif (\is_array($data['additionalProperties'])) {
                $value_12 = $this->denormalizer->denormalize($data['additionalProperties'], \LongTermSupport\OpenApiGenerator\Component\OpenApi3\JsonSchema\Model\Schema::class, 'json', $context);
            } elseif (\is_bool($data['additionalProperties'])) {
                $value_12 = $data['additionalProperties'];
            }

            /** @var bool|\LongTermSupport\OpenApiGenerator\Component\OpenApi3\JsonSchema\Model\Reference|\LongTermSupport\OpenApiGenerator\Component\OpenApi3\JsonSchema\Model\Schema $value_12 */
            $object->setAdditionalProperties($value_12);
            unset($data['additionalProperties']);
        } elseif (\array_key_exists('additionalProperties', $data) && null === $data['additionalProperties']) {
            $object->setAdditionalProperties(null);
        }

        if (\array_key_exists('description', $data) && null !== $data['description']) {
            /** @var scalar $descriptionVal */
            $descriptionVal = $data['description'];
            $object->setDescription((string)$descriptionVal);
            unset($data['description']);
        } elseif (\array_key_exists('description', $data) && null === $data['description']) {
            $object->setDescription(null);
        }

        if (\array_key_exists('format', $data) && null !== $data['format']) {
            /** @var scalar $formatVal */
            $formatVal = $data['format'];
            $object->setFormat((string)$formatVal);
            unset($data['format']);
        } elseif (\array_key_exists('format', $data) && null === $data['format']) {
            $object->setFormat(null);
        }

        if (\array_key_exists('default', $data) && null !== $data['default']) {
            /** @var array<mixed>|bool|float|int|string $defaultVal */
            $defaultVal = $data['default'];
            $object->setDefault($defaultVal);
            unset($data['default']);
        } elseif (\array_key_exists('default', $data) && null === $data['default']) {
            $object->setDefault(null);
        }

        if (\array_key_exists('nullable', $data) && null !== $data['nullable']) {
            /** @var scalar $nullableVal */
            $nullableVal = $data['nullable'];
            $object->setNullable((bool)$nullableVal);
            unset($data['nullable']);
        } elseif (\array_key_exists('nullable', $data) && null === $data['nullable']) {
            $object->setNullable(null);
        }

        if (\array_key_exists('discriminator', $data) && null !== $data['discriminator']) {
            /** @var \LongTermSupport\OpenApiGenerator\Component\OpenApi3\JsonSchema\Model\Discriminator $denormDiscriminator */
            $denormDiscriminator = $this->denormalizer->denormalize($data['discriminator'], \LongTermSupport\OpenApiGenerator\Component\OpenApi3\JsonSchema\Model\Discriminator::class, 'json', $context);
            $object->setDiscriminator($denormDiscriminator);
            unset($data['discriminator']);
        } elseif (\array_key_exists('discriminator', $data) && null === $data['discriminator']) {
            $object->setDiscriminator(null);
        }

        if (\array_key_exists('readOnly', $data) && null !== $data['readOnly']) {
            /** @var scalar $readOnlyVal */
            $readOnlyVal = $data['readOnly'];
            $object->setReadOnly((bool)$readOnlyVal);
            unset($data['readOnly']);
        } elseif (\array_key_exists('readOnly', $data) && null === $data['readOnly']) {
            $object->setReadOnly(null);
        }

        if (\array_key_exists('writeOnly', $data) && null !== $data['writeOnly']) {
            /** @var scalar $writeOnlyVal */
            $writeOnlyVal = $data['writeOnly'];
            $object->setWriteOnly((bool)$writeOnlyVal);
            unset($data['writeOnly']);
        } elseif (\array_key_exists('writeOnly', $data) && null === $data['writeOnly']) {
            $object->setWriteOnly(null);
        }

        if (\array_key_exists('example', $data) && null !== $data['example']) {
            $object->setExample($data['example']);
            unset($data['example']);
        } elseif (\array_key_exists('example', $data) && null === $data['example']) {
            $object->setExample(null);
        }

        if (\array_key_exists('externalDocs', $data) && null !== $data['externalDocs']) {
            /** @var \LongTermSupport\OpenApiGenerator\Component\OpenApi3\JsonSchema\Model\ExternalDocumentation $denormExternalDocs */
            $denormExternalDocs = $this->denormalizer->denormalize($data['externalDocs'], \LongTermSupport\OpenApiGenerator\Component\OpenApi3\JsonSchema\Model\ExternalDocumentation::class, 'json', $context);
            $object->setExternalDocs($denormExternalDocs);
            unset($data['externalDocs']);
        } elseif (\array_key_exists('externalDocs', $data) && null === $data['externalDocs']) {
            $object->setExternalDocs(null);
        }

        if (\array_key_exists('deprecated', $data) && null !== $data['deprecated']) {
            /** @var scalar $deprecatedVal */
            $deprecatedVal = $data['deprecated'];
            $object->setDeprecated((bool)$deprecatedVal);
            unset($data['deprecated']);
        } elseif (\array_key_exists('deprecated', $data) && null === $data['deprecated']) {
            $object->setDeprecated(null);
        }

        if (\array_key_exists('xml', $data) && null !== $data['xml']) {
            /** @var \LongTermSupport\OpenApiGenerator\Component\OpenApi3\JsonSchema\Model\XML $denormXml */
            $denormXml = $this->denormalizer->denormalize($data['xml'], \LongTermSupport\OpenApiGenerator\Component\OpenApi3\JsonSchema\Model\XML::class, 'json', $context);
            $object->setXml($denormXml);
            unset($data['xml']);
        } elseif (\array_key_exists('xml', $data) && null === $data['xml']) {
            $object->setXml(null);
        }

        foreach ($data as $key_1 => $value_13) {
            $key_1 = TypeValidator::assertStringKey($key_1, 'Schema');

            if (1 === \Safe\preg_match('/^x-/', $key_1)) {
                $object[$key_1] = $value_13;
            }
        }

        return $object;
    }

    /**
     * @return array<string, mixed>|string|int|float|bool|ArrayObject<string, mixed>|null
     */
    public function normalize(mixed $object, ?string $format = null, array $context = []): array|string|int|float|bool|ArrayObject|null
    {
        if (!$object instanceof \LongTermSupport\OpenApiGenerator\Component\OpenApi3\JsonSchema\Model\Schema) {
            throw new LogicException('Expected Schema, got ' . get_debug_type($object));
        }

        $data = [];
        if ($object->isInitialized('title') && null !== $object->getTitle()) {
            $data['title'] = $object->getTitle();
        }

        if ($object->isInitialized('multipleOf') && null !== $object->getMultipleOf()) {
            $data['multipleOf'] = $object->getMultipleOf();
        }

        if ($object->isInitialized('maximum') && null !== $object->getMaximum()) {
            $data['maximum'] = $object->getMaximum();
        }

        if ($object->isInitialized('exclusiveMaximum') && null !== $object->getExclusiveMaximum()) {
            $data['exclusiveMaximum'] = $object->getExclusiveMaximum();
        }

        if ($object->isInitialized('minimum') && null !== $object->getMinimum()) {
            $data['minimum'] = $object->getMinimum();
        }

        if ($object->isInitialized('exclusiveMinimum') && null !== $object->getExclusiveMinimum()) {
            $data['exclusiveMinimum'] = $object->getExclusiveMinimum();
        }

        if ($object->isInitialized('maxLength') && null !== $object->getMaxLength()) {
            $data['maxLength'] = $object->getMaxLength();
        }

        if ($object->isInitialized('minLength') && null !== $object->getMinLength()) {
            $data['minLength'] = $object->getMinLength();
        }

        if ($object->isInitialized('pattern') && null !== $object->getPattern()) {
            $data['pattern'] = $object->getPattern();
        }

        if ($object->isInitialized('maxItems') && null !== $object->getMaxItems()) {
            $data['maxItems'] = $object->getMaxItems();
        }

        if ($object->isInitialized('minItems') && null !== $object->getMinItems()) {
            $data['minItems'] = $object->getMinItems();
        }

        if ($object->isInitialized('uniqueItems') && null !== $object->getUniqueItems()) {
            $data['uniqueItems'] = $object->getUniqueItems();
        }

        if ($object->isInitialized('maxProperties') && null !== $object->getMaxProperties()) {
            $data['maxProperties'] = $object->getMaxProperties();
        }

        if ($object->isInitialized('minProperties') && null !== $object->getMinProperties()) {
            $data['minProperties'] = $object->getMinProperties();
        }

        if ($object->isInitialized('required') && null !== $object->getRequired()) {
            $values           = $object->getRequired();
            $data['required'] = $values;
        }

        if ($object->isInitialized('enum') && null !== $object->getEnum()) {
            $values_1     = $object->getEnum();
            $data['enum'] = $values_1;
        }

        if ($object->isInitialized('type') && null !== $object->getType()) {
            $data['type'] = $object->getType();
        }

        if ($object->isInitialized('not') && null !== $object->getNot()) {
            $value_2 = $object->getNot();
            if (\is_object($object->getNot())) {
                $value_2 = $this->normalizer->normalize($object->getNot(), 'json', $context);
            }

            $data['not'] = $value_2;
        }

        if ($object->isInitialized('allOf') && null !== $object->getAllOf()) {
            $values_2 = [];
            foreach ($object->getAllOf() as $value_3) {
                $value_4 = $value_3;
                if (\is_object($value_3)) {
                    $value_4 = $this->normalizer->normalize($value_3, 'json', $context);
                }

                $values_2[] = $value_4;
            }

            $data['allOf'] = $values_2;
        }

        if ($object->isInitialized('oneOf') && null !== $object->getOneOf()) {
            $values_3 = [];
            foreach ($object->getOneOf() as $value_5) {
                $value_6 = $value_5;
                if (\is_object($value_5)) {
                    $value_6 = $this->normalizer->normalize($value_5, 'json', $context);
                }

                $values_3[] = $value_6;
            }

            $data['oneOf'] = $values_3;
        }

        if ($object->isInitialized('anyOf') && null !== $object->getAnyOf()) {
            $values_4 = [];
            foreach ($object->getAnyOf() as $value_7) {
                $value_8 = $value_7;
                if (\is_object($value_7)) {
                    $value_8 = $this->normalizer->normalize($value_7, 'json', $context);
                }

                $values_4[] = $value_8;
            }

            $data['anyOf'] = $values_4;
        }

        if ($object->isInitialized('items') && null !== $object->getItems()) {
            $value_9 = $object->getItems();
            if (\is_object($object->getItems())) {
                $value_9 = $this->normalizer->normalize($object->getItems(), 'json', $context);
            }

            $data['items'] = $value_9;
        }

        if ($object->isInitialized('properties') && null !== $object->getProperties()) {
            $values_5 = [];
            foreach ($object->getProperties() as $key => $value_10) {
                $value_11 = $value_10;
                if (\is_object($value_10)) {
                    $value_11 = $this->normalizer->normalize($value_10, 'json', $context);
                }

                $values_5[$key] = $value_11;
            }

            $data['properties'] = $values_5;
        }

        if ($object->isInitialized('additionalProperties') && null !== $object->getAdditionalProperties()) {
            $value_12 = $object->getAdditionalProperties();
            if (\is_object($object->getAdditionalProperties())) {
                $value_12 = $this->normalizer->normalize($object->getAdditionalProperties(), 'json', $context);
            } elseif (\is_bool($object->getAdditionalProperties())) {
                $value_12 = $object->getAdditionalProperties();
            }

            $data['additionalProperties'] = $value_12;
        }

        if ($object->isInitialized('description') && null !== $object->getDescription()) {
            $data['description'] = $object->getDescription();
        }

        if ($object->isInitialized('format') && null !== $object->getFormat()) {
            $data['format'] = $object->getFormat();
        }

        if ($object->isInitialized('default') && null !== $object->getDefault()) {
            $data['default'] = $object->getDefault();
        }

        if ($object->isInitialized('nullable') && null !== $object->getNullable()) {
            $data['nullable'] = $object->getNullable();
        }

        if ($object->isInitialized('discriminator') && $object->getDiscriminator() instanceof \LongTermSupport\OpenApiGenerator\Component\OpenApi3\JsonSchema\Model\Discriminator) {
            $data['discriminator'] = $this->normalizer->normalize($object->getDiscriminator(), 'json', $context);
        }

        if ($object->isInitialized('readOnly') && null !== $object->getReadOnly()) {
            $data['readOnly'] = $object->getReadOnly();
        }

        if ($object->isInitialized('writeOnly') && null !== $object->getWriteOnly()) {
            $data['writeOnly'] = $object->getWriteOnly();
        }

        if ($object->isInitialized('example') && null !== $object->getExample()) {
            $data['example'] = $object->getExample();
        }

        if ($object->isInitialized('externalDocs') && $object->getExternalDocs() instanceof \LongTermSupport\OpenApiGenerator\Component\OpenApi3\JsonSchema\Model\ExternalDocumentation) {
            $data['externalDocs'] = $this->normalizer->normalize($object->getExternalDocs(), 'json', $context);
        }

        if ($object->isInitialized('deprecated') && null !== $object->getDeprecated()) {
            $data['deprecated'] = $object->getDeprecated();
        }

        if ($object->isInitialized('xml') && $object->getXml() instanceof \LongTermSupport\OpenApiGenerator\Component\OpenApi3\JsonSchema\Model\XML) {
            $data['xml'] = $this->normalizer->normalize($object->getXml(), 'json', $context);
        }

        foreach ($object as $key_1 => $value_13) {
            if (1 === \Safe\preg_match('/^x-/', $key_1)) {
                $data[$key_1] = $value_13;
            }
        }

        return $data;
    }

    public function getSupportedTypes(?string $format = null): array
    {
        return [\LongTermSupport\OpenApiGenerator\Component\OpenApi3\JsonSchema\Model\Schema::class => false];
    }
}
