<?php

declare(strict_types=1);

namespace LongTermSupport\OpenApiGenerator\Component\GeneratorCore\JsonSchema\Normalizer;

use ArrayObject;
use LogicException;
use LongTermSupport\OpenApiGenerator\Component\GeneratorCore\JsonSchema\Runtime\Normalizer\CheckArray;
use LongTermSupport\OpenApiGenerator\Component\GeneratorCore\JsonSchema\Runtime\Normalizer\ValidatorTrait;
use LongTermSupport\OpenApiGenerator\Component\OpenApiRuntime\Reference;
use Symfony\Component\Serializer\Normalizer\DenormalizerAwareInterface;
use Symfony\Component\Serializer\Normalizer\DenormalizerAwareTrait;
use Symfony\Component\Serializer\Normalizer\DenormalizerInterface;
use Symfony\Component\Serializer\Normalizer\NormalizerAwareInterface;
use Symfony\Component\Serializer\Normalizer\NormalizerAwareTrait;
use Symfony\Component\Serializer\Normalizer\NormalizerInterface;

class JsonSchemaNormalizer implements DenormalizerInterface, NormalizerInterface, DenormalizerAwareInterface, NormalizerAwareInterface
{
    use DenormalizerAwareTrait;
    use NormalizerAwareTrait;
    use CheckArray;
    use ValidatorTrait;

    public function supportsDenormalization(mixed $data, string $type, ?string $format = null, array $context = []): bool
    {
        return \LongTermSupport\OpenApiGenerator\Component\GeneratorCore\JsonSchema\Model\JsonSchema::class === $type;
    }

    public function supportsNormalization(mixed $data, ?string $format = null, array $context = []): bool
    {
        return $data instanceof \LongTermSupport\OpenApiGenerator\Component\GeneratorCore\JsonSchema\Model\JsonSchema;
    }

    public function denormalize(mixed $data, string $type, ?string $format = null, array $context = []): mixed
    {
        $object = new \LongTermSupport\OpenApiGenerator\Component\GeneratorCore\JsonSchema\Model\JsonSchema();
        if (null === $data || !\is_array($data)) {
            return $object;
        }

        /** @var array<string, mixed> $data */
        /** @var array<string, mixed> $context */
        if (isset($data['$ref'])) {
            if (!\is_string($data['$ref'])) {
                throw new LogicException('Expected string, got ' . get_debug_type($data['$ref']));
            }

            if (!\is_string($context['document-origin'])) {
                throw new LogicException('Expected string, got ' . get_debug_type($context['document-origin']));
            }

            return new Reference($data['$ref'], $context['document-origin']);
        }

        if (isset($data['$recursiveRef'])) {
            if (!\is_string($data['$recursiveRef'])) {
                throw new LogicException('Expected string, got ' . get_debug_type($data['$recursiveRef']));
            }

            if (!\is_string($context['document-origin'])) {
                throw new LogicException('Expected string, got ' . get_debug_type($context['document-origin']));
            }

            return new Reference($data['$recursiveRef'], $context['document-origin']);
        }

        if (\array_key_exists('multipleOf', $data) && \is_int($data['multipleOf'])) {
            $data['multipleOf'] = (float)$data['multipleOf'];
        }

        if (\array_key_exists('maximum', $data) && \is_int($data['maximum'])) {
            $data['maximum'] = (float)$data['maximum'];
        }

        if (\array_key_exists('exclusiveMaximum', $data) && \is_int($data['exclusiveMaximum'])) {
            $data['exclusiveMaximum'] = (float)$data['exclusiveMaximum'];
        }

        if (\array_key_exists('minimum', $data) && \is_int($data['minimum'])) {
            $data['minimum'] = (float)$data['minimum'];
        }

        if (\array_key_exists('exclusiveMinimum', $data) && \is_int($data['exclusiveMinimum'])) {
            $data['exclusiveMinimum'] = (float)$data['exclusiveMinimum'];
        }

        if (\array_key_exists('$recursiveAnchor', $data) && \is_int($data['$recursiveAnchor'])) {
            $data['$recursiveAnchor'] = (bool)$data['$recursiveAnchor'];
        }

        if (\array_key_exists('deprecated', $data) && \is_int($data['deprecated'])) {
            $data['deprecated'] = (bool)$data['deprecated'];
        }

        if (\array_key_exists('readOnly', $data) && \is_int($data['readOnly'])) {
            $data['readOnly'] = (bool)$data['readOnly'];
        }

        if (\array_key_exists('writeOnly', $data) && \is_int($data['writeOnly'])) {
            $data['writeOnly'] = (bool)$data['writeOnly'];
        }

        if (\array_key_exists('uniqueItems', $data) && \is_int($data['uniqueItems'])) {
            $data['uniqueItems'] = (bool)$data['uniqueItems'];
        }

        if (\array_key_exists('definitions', $data) && null !== $data['definitions']) {
            /** @var array<string, bool|\LongTermSupport\OpenApiGenerator\Component\GeneratorCore\JsonSchema\Model\JsonSchema> $values */
            $values = [];
            /** @var array<mixed> $definitionsArr */
            $definitionsArr = $data['definitions'];
            foreach ($definitionsArr as $key => $value) {
                $value_1 = $value;
                if (\is_array($value)) {
                    $value_1 = $this->denormalizer->denormalize($value, \LongTermSupport\OpenApiGenerator\Component\GeneratorCore\JsonSchema\Model\JsonSchema::class, 'json', $context);
                } elseif (\is_bool($value)) {
                    $value_1 = $value;
                }

                /** @var bool|\LongTermSupport\OpenApiGenerator\Component\GeneratorCore\JsonSchema\Model\JsonSchema $value_1 */
                $values[(string)$key] = $value_1;
            }

            $object->setDefinitions($values);
        } elseif (\array_key_exists('definitions', $data) && null === $data['definitions']) {
            $object->setDefinitions(null);
        }

        if (\array_key_exists('dependencies', $data) && null !== $data['dependencies']) {
            /** @var array<string, bool|list<string>|\LongTermSupport\OpenApiGenerator\Component\GeneratorCore\JsonSchema\Model\JsonSchema> $values_1 */
            $values_1 = [];
            /** @var array<mixed> $dependenciesArr */
            $dependenciesArr = $data['dependencies'];
            foreach ($dependenciesArr as $key_1 => $value_2) {
                $value_3 = $value_2;
                if (\is_array($value_2) && $this->isOnlyNumericKeys($value_2)) {
                    $values_2 = $value_2;
                    $value_3  = $values_2;
                } elseif (\is_array($value_2)) {
                    $value_3 = $this->denormalizer->denormalize($value_2, \LongTermSupport\OpenApiGenerator\Component\GeneratorCore\JsonSchema\Model\JsonSchema::class, 'json', $context);
                } elseif (\is_bool($value_2)) {
                    $value_3 = $value_2;
                }

                /** @var bool|list<string>|\LongTermSupport\OpenApiGenerator\Component\GeneratorCore\JsonSchema\Model\JsonSchema $value_3 */
                $values_1[(string)$key_1] = $value_3;
            }

            $object->setDependencies($values_1);
        } elseif (\array_key_exists('dependencies', $data) && null === $data['dependencies']) {
            $object->setDependencies(null);
        }

        if (\array_key_exists('additionalItems', $data) && null !== $data['additionalItems']) {
            $value_5 = $data['additionalItems'];
            if (\is_array($data['additionalItems'])) {
                $value_5 = $this->denormalizer->denormalize($data['additionalItems'], \LongTermSupport\OpenApiGenerator\Component\GeneratorCore\JsonSchema\Model\JsonSchema::class, 'json', $context);
            } elseif (\is_bool($data['additionalItems'])) {
                $value_5 = $data['additionalItems'];
            }

            /** @var bool|\LongTermSupport\OpenApiGenerator\Component\GeneratorCore\JsonSchema\Model\JsonSchema $value_5 */
            $object->setAdditionalItems($value_5);
        } elseif (\array_key_exists('additionalItems', $data) && null === $data['additionalItems']) {
            $object->setAdditionalItems(null);
        }

        if (\array_key_exists('unevaluatedItems', $data) && null !== $data['unevaluatedItems']) {
            $value_6 = $data['unevaluatedItems'];
            if (\is_array($data['unevaluatedItems'])) {
                $value_6 = $this->denormalizer->denormalize($data['unevaluatedItems'], \LongTermSupport\OpenApiGenerator\Component\GeneratorCore\JsonSchema\Model\JsonSchema::class, 'json', $context);
            } elseif (\is_bool($data['unevaluatedItems'])) {
                $value_6 = $data['unevaluatedItems'];
            }

            /** @var bool|\LongTermSupport\OpenApiGenerator\Component\GeneratorCore\JsonSchema\Model\JsonSchema $value_6 */
            $object->setUnevaluatedItems($value_6);
        } elseif (\array_key_exists('unevaluatedItems', $data) && null === $data['unevaluatedItems']) {
            $object->setUnevaluatedItems(null);
        }

        if (\array_key_exists('items', $data) && null !== $data['items']) {
            $value_7 = $data['items'];
            if (\is_array($data['items']) && $this->isOnlyNumericKeys($data['items'])) {
                $values_3 = [];
                foreach ($data['items'] as $value_8) {
                    $value_9 = $value_8;
                    if (\is_array($value_8)) {
                        $value_9 = $this->denormalizer->denormalize($value_8, \LongTermSupport\OpenApiGenerator\Component\GeneratorCore\JsonSchema\Model\JsonSchema::class, 'json', $context);
                    } elseif (\is_bool($value_8)) {
                        $value_9 = $value_8;
                    }

                    $values_3[] = $value_9;
                }

                $value_7 = $values_3;
            } elseif (\is_array($data['items'])) {
                $value_7 = $this->denormalizer->denormalize($data['items'], \LongTermSupport\OpenApiGenerator\Component\GeneratorCore\JsonSchema\Model\JsonSchema::class, 'json', $context);
            } elseif (\is_bool($data['items'])) {
                $value_7 = $data['items'];
            }

            /** @var bool|list<bool|\LongTermSupport\OpenApiGenerator\Component\GeneratorCore\JsonSchema\Model\JsonSchema>|\LongTermSupport\OpenApiGenerator\Component\GeneratorCore\JsonSchema\Model\JsonSchema $value_7 */
            $object->setItems($value_7);
        } elseif (\array_key_exists('items', $data) && null === $data['items']) {
            $object->setItems(null);
        }

        if (\array_key_exists('contains', $data) && null !== $data['contains']) {
            $value_10 = $data['contains'];
            if (\is_array($data['contains'])) {
                $value_10 = $this->denormalizer->denormalize($data['contains'], \LongTermSupport\OpenApiGenerator\Component\GeneratorCore\JsonSchema\Model\JsonSchema::class, 'json', $context);
            } elseif (\is_bool($data['contains'])) {
                $value_10 = $data['contains'];
            }

            /** @var bool|\LongTermSupport\OpenApiGenerator\Component\GeneratorCore\JsonSchema\Model\JsonSchema $value_10 */
            $object->setContains($value_10);
        } elseif (\array_key_exists('contains', $data) && null === $data['contains']) {
            $object->setContains(null);
        }

        if (\array_key_exists('additionalProperties', $data) && null !== $data['additionalProperties']) {
            $value_11 = $data['additionalProperties'];
            if (\is_array($data['additionalProperties'])) {
                $value_11 = $this->denormalizer->denormalize($data['additionalProperties'], \LongTermSupport\OpenApiGenerator\Component\GeneratorCore\JsonSchema\Model\JsonSchema::class, 'json', $context);
            } elseif (\is_bool($data['additionalProperties'])) {
                $value_11 = $data['additionalProperties'];
            }

            /** @var bool|\LongTermSupport\OpenApiGenerator\Component\GeneratorCore\JsonSchema\Model\JsonSchema $value_11 */
            $object->setAdditionalProperties($value_11);
        } elseif (\array_key_exists('additionalProperties', $data) && null === $data['additionalProperties']) {
            $object->setAdditionalProperties(null);
        }

        if (\array_key_exists('unevaluatedProperties', $data) && null !== $data['unevaluatedProperties']) {
            /** @var array<string, bool|\LongTermSupport\OpenApiGenerator\Component\GeneratorCore\JsonSchema\Model\JsonSchema> $values_4 */
            $values_4 = [];
            /** @var array<mixed> $unevaluatedPropertiesArr */
            $unevaluatedPropertiesArr = $data['unevaluatedProperties'];
            foreach ($unevaluatedPropertiesArr as $key_2 => $value_12) {
                $value_13 = $value_12;
                if (\is_array($value_12)) {
                    $value_13 = $this->denormalizer->denormalize($value_12, \LongTermSupport\OpenApiGenerator\Component\GeneratorCore\JsonSchema\Model\JsonSchema::class, 'json', $context);
                } elseif (\is_bool($value_12)) {
                    $value_13 = $value_12;
                }

                /** @var bool|\LongTermSupport\OpenApiGenerator\Component\GeneratorCore\JsonSchema\Model\JsonSchema $value_13 */
                $values_4[(string)$key_2] = $value_13;
            }

            $object->setUnevaluatedProperties($values_4);
        } elseif (\array_key_exists('unevaluatedProperties', $data) && null === $data['unevaluatedProperties']) {
            $object->setUnevaluatedProperties(null);
        }

        if (\array_key_exists('properties', $data) && null !== $data['properties']) {
            /** @var array<string, bool|\LongTermSupport\OpenApiGenerator\Component\GeneratorCore\JsonSchema\Model\JsonSchema> $values_5 */
            $values_5 = [];
            /** @var array<mixed> $propertiesArr */
            $propertiesArr = $data['properties'];
            foreach ($propertiesArr as $key_3 => $value_14) {
                $value_15 = $value_14;
                if (\is_array($value_14)) {
                    $value_15 = $this->denormalizer->denormalize($value_14, \LongTermSupport\OpenApiGenerator\Component\GeneratorCore\JsonSchema\Model\JsonSchema::class, 'json', $context);
                } elseif (\is_bool($value_14)) {
                    $value_15 = $value_14;
                }

                /** @var bool|\LongTermSupport\OpenApiGenerator\Component\GeneratorCore\JsonSchema\Model\JsonSchema $value_15 */
                $values_5[(string)$key_3] = $value_15;
            }

            $object->setProperties($values_5);
        } elseif (\array_key_exists('properties', $data) && null === $data['properties']) {
            $object->setProperties(null);
        }

        if (\array_key_exists('patternProperties', $data) && null !== $data['patternProperties']) {
            /** @var array<string, bool|\LongTermSupport\OpenApiGenerator\Component\GeneratorCore\JsonSchema\Model\JsonSchema> $values_6 */
            $values_6 = [];
            /** @var array<mixed> $patternPropertiesArr */
            $patternPropertiesArr = $data['patternProperties'];
            foreach ($patternPropertiesArr as $key_4 => $value_16) {
                $value_17 = $value_16;
                if (\is_array($value_16)) {
                    $value_17 = $this->denormalizer->denormalize($value_16, \LongTermSupport\OpenApiGenerator\Component\GeneratorCore\JsonSchema\Model\JsonSchema::class, 'json', $context);
                } elseif (\is_bool($value_16)) {
                    $value_17 = $value_16;
                }

                /** @var bool|\LongTermSupport\OpenApiGenerator\Component\GeneratorCore\JsonSchema\Model\JsonSchema $value_17 */
                $values_6[(string)$key_4] = $value_17;
            }

            $object->setPatternProperties($values_6);
        } elseif (\array_key_exists('patternProperties', $data) && null === $data['patternProperties']) {
            $object->setPatternProperties(null);
        }

        if (\array_key_exists('dependentSchemas', $data) && null !== $data['dependentSchemas']) {
            /** @var array<string, bool|\LongTermSupport\OpenApiGenerator\Component\GeneratorCore\JsonSchema\Model\JsonSchema> $values_7 */
            $values_7 = [];
            /** @var array<mixed> $dependentSchemasArr */
            $dependentSchemasArr = $data['dependentSchemas'];
            foreach ($dependentSchemasArr as $key_5 => $value_18) {
                $value_19 = $value_18;
                if (\is_array($value_18)) {
                    $value_19 = $this->denormalizer->denormalize($value_18, \LongTermSupport\OpenApiGenerator\Component\GeneratorCore\JsonSchema\Model\JsonSchema::class, 'json', $context);
                } elseif (\is_bool($value_18)) {
                    $value_19 = $value_18;
                }

                /** @var bool|\LongTermSupport\OpenApiGenerator\Component\GeneratorCore\JsonSchema\Model\JsonSchema $value_19 */
                $values_7[(string)$key_5] = $value_19;
            }

            $object->setDependentSchemas($values_7);
        } elseif (\array_key_exists('dependentSchemas', $data) && null === $data['dependentSchemas']) {
            $object->setDependentSchemas(null);
        }

        if (\array_key_exists('propertyNames', $data) && null !== $data['propertyNames']) {
            $value_20 = $data['propertyNames'];
            if (\is_array($data['propertyNames'])) {
                $value_20 = $this->denormalizer->denormalize($data['propertyNames'], \LongTermSupport\OpenApiGenerator\Component\GeneratorCore\JsonSchema\Model\JsonSchema::class, 'json', $context);
            } elseif (\is_bool($data['propertyNames'])) {
                $value_20 = $data['propertyNames'];
            }

            /** @var bool|\LongTermSupport\OpenApiGenerator\Component\GeneratorCore\JsonSchema\Model\JsonSchema $value_20 */
            $object->setPropertyNames($value_20);
        } elseif (\array_key_exists('propertyNames', $data) && null === $data['propertyNames']) {
            $object->setPropertyNames(null);
        }

        if (\array_key_exists('if', $data) && null !== $data['if']) {
            $value_21 = $data['if'];
            if (\is_array($data['if'])) {
                $value_21 = $this->denormalizer->denormalize($data['if'], \LongTermSupport\OpenApiGenerator\Component\GeneratorCore\JsonSchema\Model\JsonSchema::class, 'json', $context);
            } elseif (\is_bool($data['if'])) {
                $value_21 = $data['if'];
            }

            /** @var bool|\LongTermSupport\OpenApiGenerator\Component\GeneratorCore\JsonSchema\Model\JsonSchema $value_21 */
            $object->setIf($value_21);
        } elseif (\array_key_exists('if', $data) && null === $data['if']) {
            $object->setIf(null);
        }

        if (\array_key_exists('then', $data) && null !== $data['then']) {
            $value_22 = $data['then'];
            if (\is_array($data['then'])) {
                $value_22 = $this->denormalizer->denormalize($data['then'], \LongTermSupport\OpenApiGenerator\Component\GeneratorCore\JsonSchema\Model\JsonSchema::class, 'json', $context);
            } elseif (\is_bool($data['then'])) {
                $value_22 = $data['then'];
            }

            /** @var bool|\LongTermSupport\OpenApiGenerator\Component\GeneratorCore\JsonSchema\Model\JsonSchema $value_22 */
            $object->setThen($value_22);
        } elseif (\array_key_exists('then', $data) && null === $data['then']) {
            $object->setThen(null);
        }

        if (\array_key_exists('else', $data) && null !== $data['else']) {
            $value_23 = $data['else'];
            if (\is_array($data['else'])) {
                $value_23 = $this->denormalizer->denormalize($data['else'], \LongTermSupport\OpenApiGenerator\Component\GeneratorCore\JsonSchema\Model\JsonSchema::class, 'json', $context);
            } elseif (\is_bool($data['else'])) {
                $value_23 = $data['else'];
            }

            /** @var bool|\LongTermSupport\OpenApiGenerator\Component\GeneratorCore\JsonSchema\Model\JsonSchema $value_23 */
            $object->setElse($value_23);
        } elseif (\array_key_exists('else', $data) && null === $data['else']) {
            $object->setElse(null);
        }

        if (\array_key_exists('allOf', $data) && null !== $data['allOf']) {
            $values_8 = [];
            /** @var array<mixed> $allOfArr */
            $allOfArr = $data['allOf'];
            foreach ($allOfArr as $value_24) {
                $value_25 = $value_24;
                if (\is_array($value_24)) {
                    $value_25 = $this->denormalizer->denormalize($value_24, \LongTermSupport\OpenApiGenerator\Component\GeneratorCore\JsonSchema\Model\JsonSchema::class, 'json', $context);
                } elseif (\is_bool($value_24)) {
                    $value_25 = $value_24;
                }

                $values_8[] = $value_25;
            }

            /** @var list<bool|\LongTermSupport\OpenApiGenerator\Component\GeneratorCore\JsonSchema\Model\JsonSchema> $values_8 */
            $object->setAllOf($values_8);
        } elseif (\array_key_exists('allOf', $data) && null === $data['allOf']) {
            $object->setAllOf(null);
        }

        if (\array_key_exists('anyOf', $data) && null !== $data['anyOf']) {
            $values_9 = [];
            /** @var array<mixed> $anyOfArr */
            $anyOfArr = $data['anyOf'];
            foreach ($anyOfArr as $value_26) {
                $value_27 = $value_26;
                if (\is_array($value_26)) {
                    $value_27 = $this->denormalizer->denormalize($value_26, \LongTermSupport\OpenApiGenerator\Component\GeneratorCore\JsonSchema\Model\JsonSchema::class, 'json', $context);
                } elseif (\is_bool($value_26)) {
                    $value_27 = $value_26;
                }

                $values_9[] = $value_27;
            }

            /** @var list<bool|\LongTermSupport\OpenApiGenerator\Component\GeneratorCore\JsonSchema\Model\JsonSchema> $values_9 */
            $object->setAnyOf($values_9);
        } elseif (\array_key_exists('anyOf', $data) && null === $data['anyOf']) {
            $object->setAnyOf(null);
        }

        if (\array_key_exists('oneOf', $data) && null !== $data['oneOf']) {
            $values_10 = [];
            /** @var array<mixed> $oneOfArr */
            $oneOfArr = $data['oneOf'];
            foreach ($oneOfArr as $value_28) {
                $value_29 = $value_28;
                if (\is_array($value_28)) {
                    $value_29 = $this->denormalizer->denormalize($value_28, \LongTermSupport\OpenApiGenerator\Component\GeneratorCore\JsonSchema\Model\JsonSchema::class, 'json', $context);
                } elseif (\is_bool($value_28)) {
                    $value_29 = $value_28;
                }

                $values_10[] = $value_29;
            }

            /** @var list<bool|\LongTermSupport\OpenApiGenerator\Component\GeneratorCore\JsonSchema\Model\JsonSchema> $values_10 */
            $object->setOneOf($values_10);
        } elseif (\array_key_exists('oneOf', $data) && null === $data['oneOf']) {
            $object->setOneOf(null);
        }

        if (\array_key_exists('not', $data) && null !== $data['not']) {
            $value_30 = $data['not'];
            if (\is_array($data['not'])) {
                $value_30 = $this->denormalizer->denormalize($data['not'], \LongTermSupport\OpenApiGenerator\Component\GeneratorCore\JsonSchema\Model\JsonSchema::class, 'json', $context);
            } elseif (\is_bool($data['not'])) {
                $value_30 = $data['not'];
            }

            /** @var bool|\LongTermSupport\OpenApiGenerator\Component\GeneratorCore\JsonSchema\Model\JsonSchema $value_30 */
            $object->setNot($value_30);
        } elseif (\array_key_exists('not', $data) && null === $data['not']) {
            $object->setNot(null);
        }

        if (\array_key_exists('contentMediaType', $data) && null !== $data['contentMediaType']) {
            /** @var scalar $contentMediaTypeVal */
            $contentMediaTypeVal = $data['contentMediaType'];
            $object->setContentMediaType((string)$contentMediaTypeVal);
        } elseif (\array_key_exists('contentMediaType', $data) && null === $data['contentMediaType']) {
            $object->setContentMediaType(null);
        }

        if (\array_key_exists('contentEncoding', $data) && null !== $data['contentEncoding']) {
            /** @var scalar $contentEncodingVal */
            $contentEncodingVal = $data['contentEncoding'];
            $object->setContentEncoding((string)$contentEncodingVal);
        } elseif (\array_key_exists('contentEncoding', $data) && null === $data['contentEncoding']) {
            $object->setContentEncoding(null);
        }

        if (\array_key_exists('contentSchema', $data) && null !== $data['contentSchema']) {
            $value_31 = $data['contentSchema'];
            if (\is_array($data['contentSchema'])) {
                $value_31 = $this->denormalizer->denormalize($data['contentSchema'], \LongTermSupport\OpenApiGenerator\Component\GeneratorCore\JsonSchema\Model\JsonSchema::class, 'json', $context);
            } elseif (\is_bool($data['contentSchema'])) {
                $value_31 = $data['contentSchema'];
            }

            /** @var bool|\LongTermSupport\OpenApiGenerator\Component\GeneratorCore\JsonSchema\Model\JsonSchema $value_31 */
            $object->setContentSchema($value_31);
        } elseif (\array_key_exists('contentSchema', $data) && null === $data['contentSchema']) {
            $object->setContentSchema(null);
        }

        if (\array_key_exists('$id', $data) && null !== $data['$id']) {
            /** @var scalar $dollarIdVal */
            $dollarIdVal = $data['$id'];
            $object->setDollarId((string)$dollarIdVal);
        } elseif (\array_key_exists('$id', $data) && null === $data['$id']) {
            $object->setDollarId(null);
        }

        if (\array_key_exists('$schema', $data) && null !== $data['$schema']) {
            /** @var scalar $dollarSchemaVal */
            $dollarSchemaVal = $data['$schema'];
            $object->setDollarSchema((string)$dollarSchemaVal);
        } elseif (\array_key_exists('$schema', $data) && null === $data['$schema']) {
            $object->setDollarSchema(null);
        }

        if (\array_key_exists('$anchor', $data) && null !== $data['$anchor']) {
            /** @var scalar $dollarAnchorVal */
            $dollarAnchorVal = $data['$anchor'];
            $object->setDollarAnchor((string)$dollarAnchorVal);
        } elseif (\array_key_exists('$anchor', $data) && null === $data['$anchor']) {
            $object->setDollarAnchor(null);
        }

        if (\array_key_exists('$ref', $data) && null !== $data['$ref']) {
            /** @var scalar $dollarRefVal */
            $dollarRefVal = $data['$ref'];
            $object->setDollarRef((string)$dollarRefVal);
        } elseif (\array_key_exists('$ref', $data) && null === $data['$ref']) {
            $object->setDollarRef(null);
        }

        if (\array_key_exists('$recursiveRef', $data) && null !== $data['$recursiveRef']) {
            /** @var scalar $dollarRecursiveRefVal */
            $dollarRecursiveRefVal = $data['$recursiveRef'];
            $object->setDollarRecursiveRef((string)$dollarRecursiveRefVal);
        } elseif (\array_key_exists('$recursiveRef', $data) && null === $data['$recursiveRef']) {
            $object->setDollarRecursiveRef(null);
        }

        if (\array_key_exists('$recursiveAnchor', $data) && null !== $data['$recursiveAnchor']) {
            /** @var bool $dollarRecursiveAnchorVal */
            $dollarRecursiveAnchorVal = $data['$recursiveAnchor'];
            $object->setDollarRecursiveAnchor($dollarRecursiveAnchorVal);
        } elseif (\array_key_exists('$recursiveAnchor', $data) && null === $data['$recursiveAnchor']) {
            $object->setDollarRecursiveAnchor(null);
        }

        if (\array_key_exists('$vocabulary', $data) && null !== $data['$vocabulary']) {
            /** @var array<string, bool> $values_11 */
            $values_11 = [];
            /** @var array<mixed> $vocabularyArr */
            $vocabularyArr = $data['$vocabulary'];
            foreach ($vocabularyArr as $key_6 => $value_32) {
                /** @var bool $value_32 */
                $values_11[(string)$key_6] = $value_32;
            }

            $object->setDollarVocabulary($values_11);
        } elseif (\array_key_exists('$vocabulary', $data) && null === $data['$vocabulary']) {
            $object->setDollarVocabulary(null);
        }

        if (\array_key_exists('$comment', $data) && null !== $data['$comment']) {
            /** @var scalar $dollarCommentVal */
            $dollarCommentVal = $data['$comment'];
            $object->setDollarComment((string)$dollarCommentVal);
        } elseif (\array_key_exists('$comment', $data) && null === $data['$comment']) {
            $object->setDollarComment(null);
        }

        if (\array_key_exists('$defs', $data) && null !== $data['$defs']) {
            /** @var array<string, bool|\LongTermSupport\OpenApiGenerator\Component\GeneratorCore\JsonSchema\Model\JsonSchema> $values_12 */
            $values_12 = [];
            /** @var array<mixed> $defsArr */
            $defsArr = $data['$defs'];
            foreach ($defsArr as $key_7 => $value_33) {
                $value_34 = $value_33;
                if (\is_array($value_33)) {
                    $value_34 = $this->denormalizer->denormalize($value_33, \LongTermSupport\OpenApiGenerator\Component\GeneratorCore\JsonSchema\Model\JsonSchema::class, 'json', $context);
                } elseif (\is_bool($value_33)) {
                    $value_34 = $value_33;
                }

                /** @var bool|\LongTermSupport\OpenApiGenerator\Component\GeneratorCore\JsonSchema\Model\JsonSchema $value_34 */
                $values_12[(string)$key_7] = $value_34;
            }

            $object->setDollarDefs($values_12);
        } elseif (\array_key_exists('$defs', $data) && null === $data['$defs']) {
            $object->setDollarDefs(null);
        }

        if (\array_key_exists('format', $data) && null !== $data['format']) {
            /** @var scalar $formatVal */
            $formatVal = $data['format'];
            $object->setFormat((string)$formatVal);
        } elseif (\array_key_exists('format', $data) && null === $data['format']) {
            $object->setFormat(null);
        }

        if (\array_key_exists('title', $data) && null !== $data['title']) {
            /** @var scalar $titleVal */
            $titleVal = $data['title'];
            $object->setTitle((string)$titleVal);
        } elseif (\array_key_exists('title', $data) && null === $data['title']) {
            $object->setTitle(null);
        }

        if (\array_key_exists('description', $data) && null !== $data['description']) {
            /** @var scalar $descriptionVal */
            $descriptionVal = $data['description'];
            $object->setDescription((string)$descriptionVal);
        } elseif (\array_key_exists('description', $data) && null === $data['description']) {
            $object->setDescription(null);
        }

        if (\array_key_exists('default', $data) && null !== $data['default']) {
            $object->setDefault($data['default']);
        } elseif (\array_key_exists('default', $data) && null === $data['default']) {
            $object->setDefault(null);
        }

        if (\array_key_exists('deprecated', $data) && null !== $data['deprecated']) {
            /** @var bool $deprecatedVal */
            $deprecatedVal = $data['deprecated'];
            $object->setDeprecated($deprecatedVal);
        } elseif (\array_key_exists('deprecated', $data) && null === $data['deprecated']) {
            $object->setDeprecated(null);
        }

        if (\array_key_exists('readOnly', $data) && null !== $data['readOnly']) {
            /** @var bool $readOnlyVal */
            $readOnlyVal = $data['readOnly'];
            $object->setReadOnly($readOnlyVal);
        } elseif (\array_key_exists('readOnly', $data) && null === $data['readOnly']) {
            $object->setReadOnly(null);
        }

        if (\array_key_exists('writeOnly', $data) && null !== $data['writeOnly']) {
            /** @var bool $writeOnlyVal */
            $writeOnlyVal = $data['writeOnly'];
            $object->setWriteOnly($writeOnlyVal);
        } elseif (\array_key_exists('writeOnly', $data) && null === $data['writeOnly']) {
            $object->setWriteOnly(null);
        }

        if (\array_key_exists('examples', $data) && null !== $data['examples']) {
            $values_13 = [];
            /** @var array<mixed> $examplesArr */
            $examplesArr = $data['examples'];
            foreach ($examplesArr as $value_35) {
                $values_13[] = $value_35;
            }

            $object->setExamples($values_13);
        } elseif (\array_key_exists('examples', $data) && null === $data['examples']) {
            $object->setExamples(null);
        }

        if (\array_key_exists('multipleOf', $data) && null !== $data['multipleOf']) {
            /** @var scalar $multipleOfVal */
            $multipleOfVal = $data['multipleOf'];
            $object->setMultipleOf((float)$multipleOfVal);
        } elseif (\array_key_exists('multipleOf', $data) && null === $data['multipleOf']) {
            $object->setMultipleOf(null);
        }

        if (\array_key_exists('maximum', $data) && null !== $data['maximum']) {
            /** @var scalar $maximumVal */
            $maximumVal = $data['maximum'];
            $object->setMaximum((float)$maximumVal);
        } elseif (\array_key_exists('maximum', $data) && null === $data['maximum']) {
            $object->setMaximum(null);
        }

        if (\array_key_exists('exclusiveMaximum', $data) && null !== $data['exclusiveMaximum']) {
            /** @var scalar $exclusiveMaximumVal */
            $exclusiveMaximumVal = $data['exclusiveMaximum'];
            $object->setExclusiveMaximum((float)$exclusiveMaximumVal);
        } elseif (\array_key_exists('exclusiveMaximum', $data) && null === $data['exclusiveMaximum']) {
            $object->setExclusiveMaximum(null);
        }

        if (\array_key_exists('minimum', $data) && null !== $data['minimum']) {
            /** @var scalar $minimumVal */
            $minimumVal = $data['minimum'];
            $object->setMinimum((float)$minimumVal);
        } elseif (\array_key_exists('minimum', $data) && null === $data['minimum']) {
            $object->setMinimum(null);
        }

        if (\array_key_exists('exclusiveMinimum', $data) && null !== $data['exclusiveMinimum']) {
            /** @var scalar $exclusiveMinimumVal */
            $exclusiveMinimumVal = $data['exclusiveMinimum'];
            $object->setExclusiveMinimum((float)$exclusiveMinimumVal);
        } elseif (\array_key_exists('exclusiveMinimum', $data) && null === $data['exclusiveMinimum']) {
            $object->setExclusiveMinimum(null);
        }

        if (\array_key_exists('maxLength', $data) && null !== $data['maxLength']) {
            /** @var scalar $maxLengthVal */
            $maxLengthVal = $data['maxLength'];
            $object->setMaxLength((int)$maxLengthVal);
        } elseif (\array_key_exists('maxLength', $data) && null === $data['maxLength']) {
            $object->setMaxLength(null);
        }

        if (\array_key_exists('minLength', $data) && null !== $data['minLength']) {
            /** @var scalar $minLengthVal */
            $minLengthVal = $data['minLength'];
            $object->setMinLength((int)$minLengthVal);
        } elseif (\array_key_exists('minLength', $data) && null === $data['minLength']) {
            $object->setMinLength(null);
        }

        if (\array_key_exists('pattern', $data) && null !== $data['pattern']) {
            /** @var scalar $patternVal */
            $patternVal = $data['pattern'];
            $object->setPattern((string)$patternVal);
        } elseif (\array_key_exists('pattern', $data) && null === $data['pattern']) {
            $object->setPattern(null);
        }

        if (\array_key_exists('maxItems', $data) && null !== $data['maxItems']) {
            /** @var scalar $maxItemsVal */
            $maxItemsVal = $data['maxItems'];
            $object->setMaxItems((int)$maxItemsVal);
        } elseif (\array_key_exists('maxItems', $data) && null === $data['maxItems']) {
            $object->setMaxItems(null);
        }

        if (\array_key_exists('minItems', $data) && null !== $data['minItems']) {
            /** @var scalar $minItemsVal */
            $minItemsVal = $data['minItems'];
            $object->setMinItems((int)$minItemsVal);
        } elseif (\array_key_exists('minItems', $data) && null === $data['minItems']) {
            $object->setMinItems(null);
        }

        if (\array_key_exists('uniqueItems', $data) && null !== $data['uniqueItems']) {
            /** @var bool $uniqueItemsVal */
            $uniqueItemsVal = $data['uniqueItems'];
            $object->setUniqueItems($uniqueItemsVal);
        } elseif (\array_key_exists('uniqueItems', $data) && null === $data['uniqueItems']) {
            $object->setUniqueItems(null);
        }

        if (\array_key_exists('maxContains', $data) && null !== $data['maxContains']) {
            /** @var scalar $maxContainsVal */
            $maxContainsVal = $data['maxContains'];
            $object->setMaxContains((int)$maxContainsVal);
        } elseif (\array_key_exists('maxContains', $data) && null === $data['maxContains']) {
            $object->setMaxContains(null);
        }

        if (\array_key_exists('minContains', $data) && null !== $data['minContains']) {
            /** @var scalar $minContainsVal */
            $minContainsVal = $data['minContains'];
            $object->setMinContains((int)$minContainsVal);
        } elseif (\array_key_exists('minContains', $data) && null === $data['minContains']) {
            $object->setMinContains(null);
        }

        if (\array_key_exists('maxProperties', $data) && null !== $data['maxProperties']) {
            /** @var scalar $maxPropertiesVal */
            $maxPropertiesVal = $data['maxProperties'];
            $object->setMaxProperties((int)$maxPropertiesVal);
        } elseif (\array_key_exists('maxProperties', $data) && null === $data['maxProperties']) {
            $object->setMaxProperties(null);
        }

        if (\array_key_exists('minProperties', $data) && null !== $data['minProperties']) {
            /** @var scalar $minPropertiesVal */
            $minPropertiesVal = $data['minProperties'];
            $object->setMinProperties((int)$minPropertiesVal);
        } elseif (\array_key_exists('minProperties', $data) && null === $data['minProperties']) {
            $object->setMinProperties(null);
        }

        if (\array_key_exists('required', $data) && null !== $data['required']) {
            /** @var list<string> $values_14 */
            $values_14 = [];
            /** @var array<mixed> $requiredArr */
            $requiredArr = $data['required'];
            foreach ($requiredArr as $value_36) {
                /** @var string $value_36 */
                $values_14[] = $value_36;
            }

            $object->setRequired($values_14);
        } elseif (\array_key_exists('required', $data) && null === $data['required']) {
            $object->setRequired(null);
        }

        if (\array_key_exists('dependentRequired', $data) && null !== $data['dependentRequired']) {
            /** @var array<string, list<string>> $values_15 */
            $values_15 = [];
            /** @var array<mixed> $dependentRequiredArr */
            $dependentRequiredArr = $data['dependentRequired'];
            foreach ($dependentRequiredArr as $key_8 => $value_37) {
                $values_16 = [];
                /** @var array<mixed> $value_37 */
                foreach ($value_37 as $value_38) {
                    /** @var string $value_38 */
                    $values_16[] = $value_38;
                }

                $values_15[(string)$key_8] = $values_16;
            }

            $object->setDependentRequired($values_15);
        } elseif (\array_key_exists('dependentRequired', $data) && null === $data['dependentRequired']) {
            $object->setDependentRequired(null);
        }

        if (\array_key_exists('const', $data) && null !== $data['const']) {
            /** @var scalar $constVal */
            $constVal = $data['const'];
            $object->setConst((string)$constVal);
        } elseif (\array_key_exists('const', $data) && null === $data['const']) {
            $object->setConst(null);
        }

        if (\array_key_exists('enum', $data) && null !== $data['enum']) {
            /** @var list<string> $values_17 */
            $values_17 = [];
            /** @var array<mixed> $enumArr */
            $enumArr = $data['enum'];
            foreach ($enumArr as $value_39) {
                /** @var string $value_39 */
                $values_17[] = $value_39;
            }

            $object->setEnum($values_17);
        } elseif (\array_key_exists('enum', $data) && null === $data['enum']) {
            $object->setEnum(null);
        }

        if (\array_key_exists('type', $data) && null !== $data['type']) {
            $value_40 = $data['type'];
            if (\is_array($data['type']) && $this->isOnlyNumericKeys($data['type'])) {
                $values_18 = $data['type'];
                $value_40  = $values_18;
            } elseif (isset($data['type'])) {
                $value_40 = $data['type'];
            }

            $object->setType($value_40);
        } elseif (\array_key_exists('type', $data) && null === $data['type']) {
            $object->setType(null);
        }

        return $object;
    }

    /**
     * @return array<mixed>|string|int|float|bool|ArrayObject<string, mixed>|null
     */
    public function normalize(mixed $data, ?string $format = null, array $context = []): array|string|int|float|bool|ArrayObject|null
    {
        if (!$data instanceof \LongTermSupport\OpenApiGenerator\Component\GeneratorCore\JsonSchema\Model\JsonSchema) {
            throw new LogicException('Expected JsonSchema, got ' . get_debug_type($data));
        }

        $dataArray = [];
        if ($data->isInitialized('definitions') && null !== $data->getDefinitions()) {
            $values = [];
            foreach ($data->getDefinitions() as $key => $value) {
                $value_1 = $value;
                if (\is_object($value)) {
                    $value_1 = $this->normalizer->normalize($value, 'json', $context);
                } elseif (\is_bool($value)) {
                    $value_1 = $value;
                }

                $values[$key] = $value_1;
            }

            $dataArray['definitions'] = $values;
        } else {
            $dataArray['definitions'] = null;
        }

        if ($data->isInitialized('dependencies') && null !== $data->getDependencies()) {
            $values_1 = [];
            foreach ($data->getDependencies() as $key_1 => $value_2) {
                $value_3 = $value_2;
                if (\is_object($value_2)) {
                    $value_3 = $this->normalizer->normalize($value_2, 'json', $context);
                } elseif (\is_bool($value_2)) {
                    $value_3 = $value_2;
                } elseif (\is_array($value_2)) {
                    $values_2 = $value_2;
                    $value_3  = $values_2;
                }

                $values_1[$key_1] = $value_3;
            }

            $dataArray['dependencies'] = $values_1;
        } else {
            $dataArray['dependencies'] = null;
        }

        if ($data->isInitialized('additionalItems') && null !== $data->getAdditionalItems()) {
            $value_5 = $data->getAdditionalItems();
            if (\is_object($data->getAdditionalItems())) {
                $value_5 = $this->normalizer->normalize($data->getAdditionalItems(), 'json', $context);
            } elseif (\is_bool($data->getAdditionalItems())) {
                $value_5 = $data->getAdditionalItems();
            }

            $dataArray['additionalItems'] = $value_5;
        } else {
            $dataArray['additionalItems'] = null;
        }

        if ($data->isInitialized('unevaluatedItems') && null !== $data->getUnevaluatedItems()) {
            $value_6 = $data->getUnevaluatedItems();
            if (\is_object($data->getUnevaluatedItems())) {
                $value_6 = $this->normalizer->normalize($data->getUnevaluatedItems(), 'json', $context);
            } elseif (\is_bool($data->getUnevaluatedItems())) {
                $value_6 = $data->getUnevaluatedItems();
            }

            $dataArray['unevaluatedItems'] = $value_6;
        } else {
            $dataArray['unevaluatedItems'] = null;
        }

        if ($data->isInitialized('items') && null !== $data->getItems()) {
            $value_7 = $data->getItems();
            if (\is_object($data->getItems())) {
                $value_7 = $this->normalizer->normalize($data->getItems(), 'json', $context);
            } elseif (\is_bool($data->getItems())) {
                $value_7 = $data->getItems();
            } elseif (\is_array($data->getItems())) {
                $values_3 = [];
                foreach ($data->getItems() as $value_8) {
                    $value_9 = $value_8;
                    if (\is_object($value_8)) {
                        $value_9 = $this->normalizer->normalize($value_8, 'json', $context);
                    } elseif (\is_bool($value_8)) {
                        $value_9 = $value_8;
                    }

                    $values_3[] = $value_9;
                }

                $value_7 = $values_3;
            }

            $dataArray['items'] = $value_7;
        } else {
            $dataArray['items'] = null;
        }

        if ($data->isInitialized('contains') && null !== $data->getContains()) {
            $value_10 = $data->getContains();
            if (\is_object($data->getContains())) {
                $value_10 = $this->normalizer->normalize($data->getContains(), 'json', $context);
            } elseif (\is_bool($data->getContains())) {
                $value_10 = $data->getContains();
            }

            $dataArray['contains'] = $value_10;
        } else {
            $dataArray['contains'] = null;
        }

        if ($data->isInitialized('additionalProperties') && null !== $data->getAdditionalProperties()) {
            $value_11 = $data->getAdditionalProperties();
            if (\is_object($data->getAdditionalProperties())) {
                $value_11 = $this->normalizer->normalize($data->getAdditionalProperties(), 'json', $context);
            } elseif (\is_bool($data->getAdditionalProperties())) {
                $value_11 = $data->getAdditionalProperties();
            }

            $dataArray['additionalProperties'] = $value_11;
        } else {
            $dataArray['additionalProperties'] = null;
        }

        if ($data->isInitialized('unevaluatedProperties') && null !== $data->getUnevaluatedProperties()) {
            $values_4 = [];
            foreach ($data->getUnevaluatedProperties() as $key_2 => $value_12) {
                $value_13 = $value_12;
                if (\is_object($value_12)) {
                    $value_13 = $this->normalizer->normalize($value_12, 'json', $context);
                } elseif (\is_bool($value_12)) {
                    $value_13 = $value_12;
                }

                $values_4[$key_2] = $value_13;
            }

            $dataArray['unevaluatedProperties'] = $values_4;
        } else {
            $dataArray['unevaluatedProperties'] = null;
        }

        if ($data->isInitialized('properties') && null !== $data->getProperties()) {
            $values_5 = [];
            foreach ($data->getProperties() as $key_3 => $value_14) {
                $value_15 = $value_14;
                if (\is_object($value_14)) {
                    $value_15 = $this->normalizer->normalize($value_14, 'json', $context);
                } elseif (\is_bool($value_14)) {
                    $value_15 = $value_14;
                }

                $values_5[$key_3] = $value_15;
            }

            $dataArray['properties'] = $values_5;
        } else {
            $dataArray['properties'] = null;
        }

        if ($data->isInitialized('patternProperties') && null !== $data->getPatternProperties()) {
            $values_6 = [];
            foreach ($data->getPatternProperties() as $key_4 => $value_16) {
                $value_17 = $value_16;
                if (\is_object($value_16)) {
                    $value_17 = $this->normalizer->normalize($value_16, 'json', $context);
                } elseif (\is_bool($value_16)) {
                    $value_17 = $value_16;
                }

                $values_6[$key_4] = $value_17;
            }

            $dataArray['patternProperties'] = $values_6;
        } else {
            $dataArray['patternProperties'] = null;
        }

        if ($data->isInitialized('dependentSchemas') && null !== $data->getDependentSchemas()) {
            $values_7 = [];
            foreach ($data->getDependentSchemas() as $key_5 => $value_18) {
                $value_19 = $value_18;
                if (\is_object($value_18)) {
                    $value_19 = $this->normalizer->normalize($value_18, 'json', $context);
                } elseif (\is_bool($value_18)) {
                    $value_19 = $value_18;
                }

                $values_7[$key_5] = $value_19;
            }

            $dataArray['dependentSchemas'] = $values_7;
        } else {
            $dataArray['dependentSchemas'] = null;
        }

        if ($data->isInitialized('propertyNames') && null !== $data->getPropertyNames()) {
            $value_20 = $data->getPropertyNames();
            if (\is_object($data->getPropertyNames())) {
                $value_20 = $this->normalizer->normalize($data->getPropertyNames(), 'json', $context);
            } elseif (\is_bool($data->getPropertyNames())) {
                $value_20 = $data->getPropertyNames();
            }

            $dataArray['propertyNames'] = $value_20;
        } else {
            $dataArray['propertyNames'] = null;
        }

        if ($data->isInitialized('if') && null !== $data->getIf()) {
            $value_21 = $data->getIf();
            if (\is_object($data->getIf())) {
                $value_21 = $this->normalizer->normalize($data->getIf(), 'json', $context);
            } elseif (\is_bool($data->getIf())) {
                $value_21 = $data->getIf();
            }

            $dataArray['if'] = $value_21;
        } else {
            $dataArray['if'] = null;
        }

        if ($data->isInitialized('then') && null !== $data->getThen()) {
            $value_22 = $data->getThen();
            if (\is_object($data->getThen())) {
                $value_22 = $this->normalizer->normalize($data->getThen(), 'json', $context);
            } elseif (\is_bool($data->getThen())) {
                $value_22 = $data->getThen();
            }

            $dataArray['then'] = $value_22;
        } else {
            $dataArray['then'] = null;
        }

        if ($data->isInitialized('else') && null !== $data->getElse()) {
            $value_23 = $data->getElse();
            if (\is_object($data->getElse())) {
                $value_23 = $this->normalizer->normalize($data->getElse(), 'json', $context);
            } elseif (\is_bool($data->getElse())) {
                $value_23 = $data->getElse();
            }

            $dataArray['else'] = $value_23;
        } else {
            $dataArray['else'] = null;
        }

        if ($data->isInitialized('allOf') && null !== $data->getAllOf()) {
            $values_8 = [];
            foreach ($data->getAllOf() as $value_24) {
                $value_25 = $value_24;
                if (\is_object($value_24)) {
                    $value_25 = $this->normalizer->normalize($value_24, 'json', $context);
                } elseif (\is_bool($value_24)) {
                    $value_25 = $value_24;
                }

                $values_8[] = $value_25;
            }

            $dataArray['allOf'] = $values_8;
        } else {
            $dataArray['allOf'] = null;
        }

        if ($data->isInitialized('anyOf') && null !== $data->getAnyOf()) {
            $values_9 = [];
            foreach ($data->getAnyOf() as $value_26) {
                $value_27 = $value_26;
                if (\is_object($value_26)) {
                    $value_27 = $this->normalizer->normalize($value_26, 'json', $context);
                } elseif (\is_bool($value_26)) {
                    $value_27 = $value_26;
                }

                $values_9[] = $value_27;
            }

            $dataArray['anyOf'] = $values_9;
        } else {
            $dataArray['anyOf'] = null;
        }

        if ($data->isInitialized('oneOf') && null !== $data->getOneOf()) {
            $values_10 = [];
            foreach ($data->getOneOf() as $value_28) {
                $value_29 = $value_28;
                if (\is_object($value_28)) {
                    $value_29 = $this->normalizer->normalize($value_28, 'json', $context);
                } elseif (\is_bool($value_28)) {
                    $value_29 = $value_28;
                }

                $values_10[] = $value_29;
            }

            $dataArray['oneOf'] = $values_10;
        } else {
            $dataArray['oneOf'] = null;
        }

        if ($data->isInitialized('not') && null !== $data->getNot()) {
            $value_30 = $data->getNot();
            if (\is_object($data->getNot())) {
                $value_30 = $this->normalizer->normalize($data->getNot(), 'json', $context);
            } elseif (\is_bool($data->getNot())) {
                $value_30 = $data->getNot();
            }

            $dataArray['not'] = $value_30;
        } else {
            $dataArray['not'] = null;
        }

        if ($data->isInitialized('contentMediaType') && null !== $data->getContentMediaType()) {
            $dataArray['contentMediaType'] = $data->getContentMediaType();
        } else {
            $dataArray['contentMediaType'] = null;
        }

        if ($data->isInitialized('contentEncoding') && null !== $data->getContentEncoding()) {
            $dataArray['contentEncoding'] = $data->getContentEncoding();
        } else {
            $dataArray['contentEncoding'] = null;
        }

        if ($data->isInitialized('contentSchema') && null !== $data->getContentSchema()) {
            $value_31 = $data->getContentSchema();
            if (\is_object($data->getContentSchema())) {
                $value_31 = $this->normalizer->normalize($data->getContentSchema(), 'json', $context);
            } elseif (\is_bool($data->getContentSchema())) {
                $value_31 = $data->getContentSchema();
            }

            $dataArray['contentSchema'] = $value_31;
        } else {
            $dataArray['contentSchema'] = null;
        }

        $dataArray['$id'] = $data->isInitialized('dollarId') && null !== $data->getDollarId() ? $data->getDollarId() : null;

        if ($data->isInitialized('dollarSchema') && null !== $data->getDollarSchema()) {
            $dataArray['$schema'] = $data->getDollarSchema();
        } else {
            $dataArray['$schema'] = null;
        }

        if ($data->isInitialized('dollarAnchor') && null !== $data->getDollarAnchor()) {
            $dataArray['$anchor'] = $data->getDollarAnchor();
        } else {
            $dataArray['$anchor'] = null;
        }

        $dataArray['$ref'] = $data->isInitialized('dollarRef') && null !== $data->getDollarRef() ? $data->getDollarRef() : null;

        if ($data->isInitialized('dollarRecursiveRef') && null !== $data->getDollarRecursiveRef()) {
            $dataArray['$recursiveRef'] = $data->getDollarRecursiveRef();
        } else {
            $dataArray['$recursiveRef'] = null;
        }

        if ($data->isInitialized('dollarRecursiveAnchor') && null !== $data->getDollarRecursiveAnchor()) {
            $dataArray['$recursiveAnchor'] = $data->getDollarRecursiveAnchor();
        } else {
            $dataArray['$recursiveAnchor'] = null;
        }

        if ($data->isInitialized('dollarVocabulary') && null !== $data->getDollarVocabulary()) {
            $values_11 = [];
            foreach ($data->getDollarVocabulary() as $key_6 => $value_32) {
                $values_11[$key_6] = $value_32;
            }

            $dataArray['$vocabulary'] = $values_11;
        } else {
            $dataArray['$vocabulary'] = null;
        }

        if ($data->isInitialized('dollarComment') && null !== $data->getDollarComment()) {
            $dataArray['$comment'] = $data->getDollarComment();
        } else {
            $dataArray['$comment'] = null;
        }

        if ($data->isInitialized('dollarDefs') && null !== $data->getDollarDefs()) {
            $values_12 = [];
            foreach ($data->getDollarDefs() as $key_7 => $value_33) {
                $value_34 = $value_33;
                if (\is_object($value_33)) {
                    $value_34 = $this->normalizer->normalize($value_33, 'json', $context);
                } elseif (\is_bool($value_33)) {
                    $value_34 = $value_33;
                }

                $values_12[$key_7] = $value_34;
            }

            $dataArray['$defs'] = $values_12;
        } else {
            $dataArray['$defs'] = null;
        }

        $dataArray['format'] = $data->isInitialized('format') && null !== $data->getFormat() ? $data->getFormat() : null;

        $dataArray['title'] = $data->isInitialized('title') && null !== $data->getTitle() ? $data->getTitle() : null;

        if ($data->isInitialized('description') && null !== $data->getDescription()) {
            $dataArray['description'] = $data->getDescription();
        } else {
            $dataArray['description'] = null;
        }

        $dataArray['default'] = $data->isInitialized('default') && null !== $data->getDefault() ? $data->getDefault() : null;

        if ($data->isInitialized('deprecated') && null !== $data->getDeprecated()) {
            $dataArray['deprecated'] = $data->getDeprecated();
        } else {
            $dataArray['deprecated'] = null;
        }

        $dataArray['readOnly'] = $data->isInitialized('readOnly') && null !== $data->getReadOnly() ? $data->getReadOnly() : null;

        if ($data->isInitialized('writeOnly') && null !== $data->getWriteOnly()) {
            $dataArray['writeOnly'] = $data->getWriteOnly();
        } else {
            $dataArray['writeOnly'] = null;
        }

        if ($data->isInitialized('examples') && null !== $data->getExamples()) {
            $values_13             = $data->getExamples();
            $dataArray['examples'] = $values_13;
        } else {
            $dataArray['examples'] = null;
        }

        if ($data->isInitialized('multipleOf') && null !== $data->getMultipleOf()) {
            $dataArray['multipleOf'] = $data->getMultipleOf();
        } else {
            $dataArray['multipleOf'] = null;
        }

        $dataArray['maximum'] = $data->isInitialized('maximum') && null !== $data->getMaximum() ? $data->getMaximum() : null;

        if ($data->isInitialized('exclusiveMaximum') && null !== $data->getExclusiveMaximum()) {
            $dataArray['exclusiveMaximum'] = $data->getExclusiveMaximum();
        } else {
            $dataArray['exclusiveMaximum'] = null;
        }

        $dataArray['minimum'] = $data->isInitialized('minimum') && null !== $data->getMinimum() ? $data->getMinimum() : null;

        if ($data->isInitialized('exclusiveMinimum') && null !== $data->getExclusiveMinimum()) {
            $dataArray['exclusiveMinimum'] = $data->getExclusiveMinimum();
        } else {
            $dataArray['exclusiveMinimum'] = null;
        }

        if ($data->isInitialized('maxLength') && null !== $data->getMaxLength()) {
            $dataArray['maxLength'] = $data->getMaxLength();
        } else {
            $dataArray['maxLength'] = null;
        }

        if ($data->isInitialized('minLength') && null !== $data->getMinLength()) {
            $dataArray['minLength'] = $data->getMinLength();
        } else {
            $dataArray['minLength'] = null;
        }

        $dataArray['pattern'] = $data->isInitialized('pattern') && null !== $data->getPattern() ? $data->getPattern() : null;

        $dataArray['maxItems'] = $data->isInitialized('maxItems') && null !== $data->getMaxItems() ? $data->getMaxItems() : null;

        $dataArray['minItems'] = $data->isInitialized('minItems') && null !== $data->getMinItems() ? $data->getMinItems() : null;

        if ($data->isInitialized('uniqueItems') && null !== $data->getUniqueItems()) {
            $dataArray['uniqueItems'] = $data->getUniqueItems();
        } else {
            $dataArray['uniqueItems'] = null;
        }

        if ($data->isInitialized('maxContains') && null !== $data->getMaxContains()) {
            $dataArray['maxContains'] = $data->getMaxContains();
        } else {
            $dataArray['maxContains'] = null;
        }

        if ($data->isInitialized('minContains') && null !== $data->getMinContains()) {
            $dataArray['minContains'] = $data->getMinContains();
        } else {
            $dataArray['minContains'] = null;
        }

        if ($data->isInitialized('maxProperties') && null !== $data->getMaxProperties()) {
            $dataArray['maxProperties'] = $data->getMaxProperties();
        } else {
            $dataArray['maxProperties'] = null;
        }

        if ($data->isInitialized('minProperties') && null !== $data->getMinProperties()) {
            $dataArray['minProperties'] = $data->getMinProperties();
        } else {
            $dataArray['minProperties'] = null;
        }

        if ($data->isInitialized('required') && null !== $data->getRequired()) {
            $values_14             = $data->getRequired();
            $dataArray['required'] = $values_14;
        } else {
            $dataArray['required'] = null;
        }

        if ($data->isInitialized('dependentRequired') && null !== $data->getDependentRequired()) {
            $values_15 = [];
            foreach ($data->getDependentRequired() as $key_8 => $value_37) {
                $values_16 = [];
                foreach ($value_37 as $value_38) {
                    $values_16[] = $value_38;
                }

                $values_15[$key_8] = $values_16;
            }

            $dataArray['dependentRequired'] = $values_15;
        } else {
            $dataArray['dependentRequired'] = null;
        }

        $dataArray['const'] = $data->isInitialized('const') && null !== $data->getConst() ? $data->getConst() : null;

        if ($data->isInitialized('enum') && null !== $data->getEnum()) {
            $values_17         = $data->getEnum();
            $dataArray['enum'] = $values_17;
        } else {
            $dataArray['enum'] = null;
        }

        if ($data->isInitialized('type') && null !== $data->getType()) {
            $value_40 = $data->getType();
            if (\is_array($data->getType())) {
                $values_18 = $data->getType();
                $value_40  = $values_18;
            } elseif (!\is_null($data->getType())) {
                $value_40 = $data->getType();
            }

            $dataArray['type'] = $value_40;
        } else {
            $dataArray['type'] = null;
        }

        return $dataArray;
    }

    public function getSupportedTypes(?string $format = null): array
    {
        return [\LongTermSupport\OpenApiGenerator\Component\GeneratorCore\JsonSchema\Model\JsonSchema::class => false];
    }
}
