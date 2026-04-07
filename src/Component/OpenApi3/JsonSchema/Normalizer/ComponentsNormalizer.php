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

class ComponentsNormalizer implements DenormalizerInterface, NormalizerInterface, DenormalizerAwareInterface, NormalizerAwareInterface
{
    use DenormalizerAwareTrait;
    use NormalizerAwareTrait;
    use CheckArray;
    use ValidatorTrait;

    public function supportsDenormalization(mixed $data, string $type, ?string $format = null, array $context = []): bool
    {
        return \LongTermSupport\OpenApiGenerator\Component\OpenApi3\JsonSchema\Model\Components::class === $type;
    }

    public function supportsNormalization(mixed $data, ?string $format = null, array $context = []): bool
    {
        return $data instanceof \LongTermSupport\OpenApiGenerator\Component\OpenApi3\JsonSchema\Model\Components;
    }

    /**
     * @return ($type is class-string<object> ? object : mixed)
     */
    public function denormalize(mixed $data, string $type, ?string $format = null, array $context = []): mixed
    {
        $object = new \LongTermSupport\OpenApiGenerator\Component\OpenApi3\JsonSchema\Model\Components();
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

        if (\array_key_exists('schemas', $data) && null !== $data['schemas']) {
            /** @var ArrayObject<string, \LongTermSupport\OpenApiGenerator\Component\OpenApi3\JsonSchema\Model\Reference|\LongTermSupport\OpenApiGenerator\Component\OpenApi3\JsonSchema\Model\Schema> $values */
            $values = new ArrayObject([], ArrayObject::ARRAY_AS_PROPS);
            /** @var array<mixed> $schemasArr */
            $schemasArr = $data['schemas'];
            foreach ($schemasArr as $key => $value) {
                if (!\is_string($key)) {
                    continue;
                }

                if (1 === \Safe\preg_match('/^[a-zA-Z0-9\.\-_]+$/', $key) && null !== $value) {
                    $value_1 = $value;
                    if (\is_array($value) && isset($value['$ref'])) {
                        $value_1 = $this->denormalizer->denormalize($value, \LongTermSupport\OpenApiGenerator\Component\OpenApi3\JsonSchema\Model\Reference::class, 'json', $context);
                    } elseif (\is_array($value)) {
                        $value_1 = $this->denormalizer->denormalize($value, \LongTermSupport\OpenApiGenerator\Component\OpenApi3\JsonSchema\Model\Schema::class, 'json', $context);
                    }

                    /** @var \LongTermSupport\OpenApiGenerator\Component\OpenApi3\JsonSchema\Model\Reference|\LongTermSupport\OpenApiGenerator\Component\OpenApi3\JsonSchema\Model\Schema $value_1 */
                    $values[$key] = $value_1;
                    continue;
                }
            }

            $object->setSchemas($values);
            unset($data['schemas']);
        } elseif (\array_key_exists('schemas', $data) && null === $data['schemas']) {
            $object->setSchemas(null);
        }

        if (\array_key_exists('responses', $data) && null !== $data['responses']) {
            /** @var ArrayObject<string, \LongTermSupport\OpenApiGenerator\Component\OpenApi3\JsonSchema\Model\Reference|\LongTermSupport\OpenApiGenerator\Component\OpenApi3\JsonSchema\Model\Response> $values_1 */
            $values_1 = new ArrayObject([], ArrayObject::ARRAY_AS_PROPS);
            /** @var array<mixed> $responsesArr */
            $responsesArr = $data['responses'];
            foreach ($responsesArr as $key_1 => $value_2) {
                if (!\is_string($key_1)) {
                    continue;
                }

                if (1 === \Safe\preg_match('/^[a-zA-Z0-9\.\-_]+$/', $key_1) && null !== $value_2) {
                    $value_3 = $value_2;
                    if (\is_array($value_2) && isset($value_2['$ref'])) {
                        $value_3 = $this->denormalizer->denormalize($value_2, \LongTermSupport\OpenApiGenerator\Component\OpenApi3\JsonSchema\Model\Reference::class, 'json', $context);
                    } elseif (\is_array($value_2) && isset($value_2['description'])) {
                        $value_3 = $this->denormalizer->denormalize($value_2, \LongTermSupport\OpenApiGenerator\Component\OpenApi3\JsonSchema\Model\Response::class, 'json', $context);
                    }

                    /** @var \LongTermSupport\OpenApiGenerator\Component\OpenApi3\JsonSchema\Model\Reference|\LongTermSupport\OpenApiGenerator\Component\OpenApi3\JsonSchema\Model\Response $value_3 */
                    $values_1[$key_1] = $value_3;
                    continue;
                }
            }

            $object->setResponses($values_1);
            unset($data['responses']);
        } elseif (\array_key_exists('responses', $data) && null === $data['responses']) {
            $object->setResponses(null);
        }

        if (\array_key_exists('parameters', $data) && null !== $data['parameters']) {
            /** @var ArrayObject<string, \LongTermSupport\OpenApiGenerator\Component\OpenApi3\JsonSchema\Model\Parameter|\LongTermSupport\OpenApiGenerator\Component\OpenApi3\JsonSchema\Model\Reference> $values_2 */
            $values_2 = new ArrayObject([], ArrayObject::ARRAY_AS_PROPS);
            /** @var array<mixed> $parametersArr */
            $parametersArr = $data['parameters'];
            foreach ($parametersArr as $key_2 => $value_4) {
                if (!\is_string($key_2)) {
                    continue;
                }

                if (1 === \Safe\preg_match('/^[a-zA-Z0-9\.\-_]+$/', $key_2) && null !== $value_4) {
                    $value_5 = $value_4;
                    if (\is_array($value_4) && isset($value_4['$ref'])) {
                        $value_5 = $this->denormalizer->denormalize($value_4, \LongTermSupport\OpenApiGenerator\Component\OpenApi3\JsonSchema\Model\Reference::class, 'json', $context);
                    } elseif (\is_array($value_4) && isset($value_4['name'], $value_4['in'])) {
                        $value_5 = $this->denormalizer->denormalize($value_4, \LongTermSupport\OpenApiGenerator\Component\OpenApi3\JsonSchema\Model\Parameter::class, 'json', $context);
                    }

                    /** @var \LongTermSupport\OpenApiGenerator\Component\OpenApi3\JsonSchema\Model\Parameter|\LongTermSupport\OpenApiGenerator\Component\OpenApi3\JsonSchema\Model\Reference $value_5 */
                    $values_2[$key_2] = $value_5;
                    continue;
                }
            }

            $object->setParameters($values_2);
            unset($data['parameters']);
        } elseif (\array_key_exists('parameters', $data) && null === $data['parameters']) {
            $object->setParameters(null);
        }

        if (\array_key_exists('examples', $data) && null !== $data['examples']) {
            /** @var ArrayObject<string, \LongTermSupport\OpenApiGenerator\Component\OpenApi3\JsonSchema\Model\Example|\LongTermSupport\OpenApiGenerator\Component\OpenApi3\JsonSchema\Model\Reference> $values_3 */
            $values_3 = new ArrayObject([], ArrayObject::ARRAY_AS_PROPS);
            /** @var array<mixed> $examplesArr */
            $examplesArr = $data['examples'];
            foreach ($examplesArr as $key_3 => $value_6) {
                if (!\is_string($key_3)) {
                    continue;
                }

                if (1 === \Safe\preg_match('/^[a-zA-Z0-9\.\-_]+$/', $key_3) && null !== $value_6) {
                    $value_7 = $value_6;
                    if (\is_array($value_6) && isset($value_6['$ref'])) {
                        $value_7 = $this->denormalizer->denormalize($value_6, \LongTermSupport\OpenApiGenerator\Component\OpenApi3\JsonSchema\Model\Reference::class, 'json', $context);
                    } elseif (\is_array($value_6)) {
                        $value_7 = $this->denormalizer->denormalize($value_6, \LongTermSupport\OpenApiGenerator\Component\OpenApi3\JsonSchema\Model\Example::class, 'json', $context);
                    }

                    /** @var \LongTermSupport\OpenApiGenerator\Component\OpenApi3\JsonSchema\Model\Example|\LongTermSupport\OpenApiGenerator\Component\OpenApi3\JsonSchema\Model\Reference $value_7 */
                    $values_3[$key_3] = $value_7;
                    continue;
                }
            }

            $object->setExamples($values_3);
            unset($data['examples']);
        } elseif (\array_key_exists('examples', $data) && null === $data['examples']) {
            $object->setExamples(null);
        }

        if (\array_key_exists('requestBodies', $data) && null !== $data['requestBodies']) {
            /** @var ArrayObject<string, \LongTermSupport\OpenApiGenerator\Component\OpenApi3\JsonSchema\Model\Reference|\LongTermSupport\OpenApiGenerator\Component\OpenApi3\JsonSchema\Model\RequestBody> $values_4 */
            $values_4 = new ArrayObject([], ArrayObject::ARRAY_AS_PROPS);
            /** @var array<mixed> $requestBodiesArr */
            $requestBodiesArr = $data['requestBodies'];
            foreach ($requestBodiesArr as $key_4 => $value_8) {
                if (!\is_string($key_4)) {
                    continue;
                }

                if (1 === \Safe\preg_match('/^[a-zA-Z0-9\.\-_]+$/', $key_4) && null !== $value_8) {
                    $value_9 = $value_8;
                    if (\is_array($value_8) && isset($value_8['$ref'])) {
                        $value_9 = $this->denormalizer->denormalize($value_8, \LongTermSupport\OpenApiGenerator\Component\OpenApi3\JsonSchema\Model\Reference::class, 'json', $context);
                    } elseif (\is_array($value_8) && isset($value_8['content'])) {
                        $value_9 = $this->denormalizer->denormalize($value_8, \LongTermSupport\OpenApiGenerator\Component\OpenApi3\JsonSchema\Model\RequestBody::class, 'json', $context);
                    }

                    /** @var \LongTermSupport\OpenApiGenerator\Component\OpenApi3\JsonSchema\Model\Reference|\LongTermSupport\OpenApiGenerator\Component\OpenApi3\JsonSchema\Model\RequestBody $value_9 */
                    $values_4[$key_4] = $value_9;
                    continue;
                }
            }

            $object->setRequestBodies($values_4);
            unset($data['requestBodies']);
        } elseif (\array_key_exists('requestBodies', $data) && null === $data['requestBodies']) {
            $object->setRequestBodies(null);
        }

        if (\array_key_exists('headers', $data) && null !== $data['headers']) {
            /** @var ArrayObject<string, \LongTermSupport\OpenApiGenerator\Component\OpenApi3\JsonSchema\Model\Header|\LongTermSupport\OpenApiGenerator\Component\OpenApi3\JsonSchema\Model\Reference> $values_5 */
            $values_5 = new ArrayObject([], ArrayObject::ARRAY_AS_PROPS);
            /** @var array<mixed> $headersArr */
            $headersArr = $data['headers'];
            foreach ($headersArr as $key_5 => $value_10) {
                if (!\is_string($key_5)) {
                    continue;
                }

                if (1 === \Safe\preg_match('/^[a-zA-Z0-9\.\-_]+$/', $key_5) && null !== $value_10) {
                    $value_11 = $value_10;
                    if (\is_array($value_10) && isset($value_10['$ref'])) {
                        $value_11 = $this->denormalizer->denormalize($value_10, \LongTermSupport\OpenApiGenerator\Component\OpenApi3\JsonSchema\Model\Reference::class, 'json', $context);
                    } elseif (\is_array($value_10)) {
                        $value_11 = $this->denormalizer->denormalize($value_10, \LongTermSupport\OpenApiGenerator\Component\OpenApi3\JsonSchema\Model\Header::class, 'json', $context);
                    }

                    /** @var \LongTermSupport\OpenApiGenerator\Component\OpenApi3\JsonSchema\Model\Header|\LongTermSupport\OpenApiGenerator\Component\OpenApi3\JsonSchema\Model\Reference $value_11 */
                    $values_5[$key_5] = $value_11;
                    continue;
                }
            }

            $object->setHeaders($values_5);
            unset($data['headers']);
        } elseif (\array_key_exists('headers', $data) && null === $data['headers']) {
            $object->setHeaders(null);
        }

        if (\array_key_exists('securitySchemes', $data) && null !== $data['securitySchemes']) {
            /** @var ArrayObject<string, \LongTermSupport\OpenApiGenerator\Component\OpenApi3\JsonSchema\Model\APIKeySecurityScheme|\LongTermSupport\OpenApiGenerator\Component\OpenApi3\JsonSchema\Model\HTTPSecurityScheme|\LongTermSupport\OpenApiGenerator\Component\OpenApi3\JsonSchema\Model\OAuth2SecurityScheme|\LongTermSupport\OpenApiGenerator\Component\OpenApi3\JsonSchema\Model\OpenIdConnectSecurityScheme|\LongTermSupport\OpenApiGenerator\Component\OpenApi3\JsonSchema\Model\Reference> $values_6 */
            $values_6 = new ArrayObject([], ArrayObject::ARRAY_AS_PROPS);
            /** @var array<mixed> $securitySchemesArr */
            $securitySchemesArr = $data['securitySchemes'];
            foreach ($securitySchemesArr as $key_6 => $value_12) {
                if (!\is_string($key_6)) {
                    continue;
                }

                if (1 === \Safe\preg_match('/^[a-zA-Z0-9\.\-_]+$/', $key_6) && null !== $value_12) {
                    $value_13 = $value_12;
                    if (\is_array($value_12) && isset($value_12['$ref'])) {
                        $value_13 = $this->denormalizer->denormalize($value_12, \LongTermSupport\OpenApiGenerator\Component\OpenApi3\JsonSchema\Model\Reference::class, 'json', $context);
                    } elseif (\is_array($value_12) && (isset($value_12['type']) && 'apiKey' === $value_12['type']) && isset($value_12['name']) && (isset($value_12['in']) && \in_array($value_12['in'], ['header', 'query', 'cookie'], true))) {
                        $value_13 = $this->denormalizer->denormalize($value_12, \LongTermSupport\OpenApiGenerator\Component\OpenApi3\JsonSchema\Model\APIKeySecurityScheme::class, 'json', $context);
                    } elseif (\is_array($value_12) && isset($value_12['scheme']) && (isset($value_12['type']) && 'http' === $value_12['type'])) {
                        $value_13 = $this->denormalizer->denormalize($value_12, \LongTermSupport\OpenApiGenerator\Component\OpenApi3\JsonSchema\Model\HTTPSecurityScheme::class, 'json', $context);
                    } elseif (\is_array($value_12) && (isset($value_12['type']) && 'oauth2' === $value_12['type']) && isset($value_12['flows'])) {
                        $value_13 = $this->denormalizer->denormalize($value_12, \LongTermSupport\OpenApiGenerator\Component\OpenApi3\JsonSchema\Model\OAuth2SecurityScheme::class, 'json', $context);
                    } elseif (\is_array($value_12) && (isset($value_12['type']) && 'openIdConnect' === $value_12['type']) && isset($value_12['openIdConnectUrl'])) {
                        $value_13 = $this->denormalizer->denormalize($value_12, \LongTermSupport\OpenApiGenerator\Component\OpenApi3\JsonSchema\Model\OpenIdConnectSecurityScheme::class, 'json', $context);
                    }

                    /** @var \LongTermSupport\OpenApiGenerator\Component\OpenApi3\JsonSchema\Model\APIKeySecurityScheme|\LongTermSupport\OpenApiGenerator\Component\OpenApi3\JsonSchema\Model\HTTPSecurityScheme|\LongTermSupport\OpenApiGenerator\Component\OpenApi3\JsonSchema\Model\OAuth2SecurityScheme|\LongTermSupport\OpenApiGenerator\Component\OpenApi3\JsonSchema\Model\OpenIdConnectSecurityScheme|\LongTermSupport\OpenApiGenerator\Component\OpenApi3\JsonSchema\Model\Reference $value_13 */
                    $values_6[$key_6] = $value_13;
                    continue;
                }
            }

            $object->setSecuritySchemes($values_6);
            unset($data['securitySchemes']);
        } elseif (\array_key_exists('securitySchemes', $data) && null === $data['securitySchemes']) {
            $object->setSecuritySchemes(null);
        }

        if (\array_key_exists('links', $data) && null !== $data['links']) {
            /** @var ArrayObject<string, \LongTermSupport\OpenApiGenerator\Component\OpenApi3\JsonSchema\Model\Link|\LongTermSupport\OpenApiGenerator\Component\OpenApi3\JsonSchema\Model\Reference> $values_7 */
            $values_7 = new ArrayObject([], ArrayObject::ARRAY_AS_PROPS);
            /** @var array<mixed> $linksArr */
            $linksArr = $data['links'];
            foreach ($linksArr as $key_7 => $value_14) {
                if (!\is_string($key_7)) {
                    continue;
                }

                if (1 === \Safe\preg_match('/^[a-zA-Z0-9\.\-_]+$/', $key_7) && null !== $value_14) {
                    $value_15 = $value_14;
                    if (\is_array($value_14) && isset($value_14['$ref'])) {
                        $value_15 = $this->denormalizer->denormalize($value_14, \LongTermSupport\OpenApiGenerator\Component\OpenApi3\JsonSchema\Model\Reference::class, 'json', $context);
                    } elseif (\is_array($value_14)) {
                        $value_15 = $this->denormalizer->denormalize($value_14, \LongTermSupport\OpenApiGenerator\Component\OpenApi3\JsonSchema\Model\Link::class, 'json', $context);
                    }

                    /** @var \LongTermSupport\OpenApiGenerator\Component\OpenApi3\JsonSchema\Model\Link|\LongTermSupport\OpenApiGenerator\Component\OpenApi3\JsonSchema\Model\Reference $value_15 */
                    $values_7[$key_7] = $value_15;
                    continue;
                }
            }

            $object->setLinks($values_7);
            unset($data['links']);
        } elseif (\array_key_exists('links', $data) && null === $data['links']) {
            $object->setLinks(null);
        }

        if (\array_key_exists('callbacks', $data) && null !== $data['callbacks']) {
            /** @var array<string, array<mixed>|\LongTermSupport\OpenApiGenerator\Component\OpenApi3\JsonSchema\Model\Reference> $values_8 */
            $values_8 = [];
            /** @var array<mixed> $callbacksArr */
            $callbacksArr = $data['callbacks'];
            foreach ($callbacksArr as $key_8 => $value_16) {
                if (!\is_string($key_8)) {
                    continue;
                }

                if (1 === \Safe\preg_match('/^[a-zA-Z0-9\.\-_]+$/', $key_8) && null !== $value_16) {
                    $value_17 = $value_16;
                    if (\is_array($value_16) && isset($value_16['$ref'])) {
                        $value_17 = $this->denormalizer->denormalize($value_16, \LongTermSupport\OpenApiGenerator\Component\OpenApi3\JsonSchema\Model\Reference::class, 'json', $context);
                    } elseif (\is_array($value_16)) {
                        $values_9 = [];
                        foreach ($value_16 as $key_9 => $value_18) {
                            if (!\is_string($key_9)) {
                                continue;
                            }

                            if (1 === \Safe\preg_match('/^x-/', $key_9) && null !== $value_18) {
                                $values_9[$key_9] = $value_18;
                                continue;
                            }
                        }

                        $value_17 = $values_9;
                    }

                    /** @var array<mixed>|\LongTermSupport\OpenApiGenerator\Component\OpenApi3\JsonSchema\Model\Reference $value_17 */
                    $values_8[$key_8] = $value_17;
                    continue;
                }
            }

            $object->setCallbacks($values_8);
            unset($data['callbacks']);
        } elseif (\array_key_exists('callbacks', $data) && null === $data['callbacks']) {
            $object->setCallbacks(null);
        }

        foreach ($data as $key_10 => $value_19) {
            if (!\is_string($key_10)) {
                continue;
            }

            if (1 === \Safe\preg_match('/^x-/', $key_10)) {
                $object[$key_10] = $value_19;
            }
        }

        return $object;
    }

    /**
     * @return array<string, mixed>|string|int|float|bool|ArrayObject<string, mixed>|null
     */
    public function normalize(mixed $object, ?string $format = null, array $context = []): array|string|int|float|bool|ArrayObject|null
    {
        if (!$object instanceof \LongTermSupport\OpenApiGenerator\Component\OpenApi3\JsonSchema\Model\Components) {
            throw new LogicException('Expected Components, got ' . get_debug_type($object));
        }

        $data = [];
        if ($object->isInitialized('schemas') && $object->getSchemas() instanceof ArrayObject) {
            $values = [];
            foreach ($object->getSchemas() as $key => $value) {
                if (1 === \Safe\preg_match('/^[a-zA-Z0-9\.\-_]+$/', $key) && null !== $value) {
                    $value_1      = $this->normalizer->normalize($value, 'json', $context);
                    $values[$key] = $value_1;
                    continue;
                }
            }

            $data['schemas'] = $values;
        }

        if ($object->isInitialized('responses') && $object->getResponses() instanceof ArrayObject) {
            $values_1 = [];
            foreach ($object->getResponses() as $key_1 => $value_2) {
                if (1 === \Safe\preg_match('/^[a-zA-Z0-9\.\-_]+$/', (string)$key_1) && null !== $value_2) {
                    $value_3          = $this->normalizer->normalize($value_2, 'json', $context);
                    $values_1[$key_1] = $value_3;
                    continue;
                }
            }

            $data['responses'] = $values_1;
        }

        if ($object->isInitialized('parameters') && $object->getParameters() instanceof ArrayObject) {
            $values_2 = [];
            foreach ($object->getParameters() as $key_2 => $value_4) {
                if (1 === \Safe\preg_match('/^[a-zA-Z0-9\.\-_]+$/', (string)$key_2) && null !== $value_4) {
                    $value_5          = $this->normalizer->normalize($value_4, 'json', $context);
                    $values_2[$key_2] = $value_5;
                    continue;
                }
            }

            $data['parameters'] = $values_2;
        }

        if ($object->isInitialized('examples') && $object->getExamples() instanceof ArrayObject) {
            $values_3 = [];
            foreach ($object->getExamples() as $key_3 => $value_6) {
                if (1 === \Safe\preg_match('/^[a-zA-Z0-9\.\-_]+$/', (string)$key_3) && null !== $value_6) {
                    $value_7          = $this->normalizer->normalize($value_6, 'json', $context);
                    $values_3[$key_3] = $value_7;
                    continue;
                }
            }

            $data['examples'] = $values_3;
        }

        if ($object->isInitialized('requestBodies') && $object->getRequestBodies() instanceof ArrayObject) {
            $values_4 = [];
            foreach ($object->getRequestBodies() as $key_4 => $value_8) {
                if (1 === \Safe\preg_match('/^[a-zA-Z0-9\.\-_]+$/', (string)$key_4) && null !== $value_8) {
                    $value_9          = $this->normalizer->normalize($value_8, 'json', $context);
                    $values_4[$key_4] = $value_9;
                    continue;
                }
            }

            $data['requestBodies'] = $values_4;
        }

        if ($object->isInitialized('headers') && $object->getHeaders() instanceof ArrayObject) {
            $values_5 = [];
            foreach ($object->getHeaders() as $key_5 => $value_10) {
                if (1 === \Safe\preg_match('/^[a-zA-Z0-9\.\-_]+$/', (string)$key_5) && null !== $value_10) {
                    $value_11         = $this->normalizer->normalize($value_10, 'json', $context);
                    $values_5[$key_5] = $value_11;
                    continue;
                }
            }

            $data['headers'] = $values_5;
        }

        if ($object->isInitialized('securitySchemes') && $object->getSecuritySchemes() instanceof ArrayObject) {
            $values_6 = [];
            foreach ($object->getSecuritySchemes() as $key_6 => $value_12) {
                if (1 === \Safe\preg_match('/^[a-zA-Z0-9\.\-_]+$/', (string)$key_6) && null !== $value_12) {
                    $value_13         = $this->normalizer->normalize($value_12, 'json', $context);
                    $values_6[$key_6] = $value_13;
                    continue;
                }
            }

            $data['securitySchemes'] = $values_6;
        }

        if ($object->isInitialized('links') && $object->getLinks() instanceof ArrayObject) {
            $values_7 = [];
            foreach ($object->getLinks() as $key_7 => $value_14) {
                if (1 === \Safe\preg_match('/^[a-zA-Z0-9\.\-_]+$/', (string)$key_7) && null !== $value_14) {
                    $value_15         = $this->normalizer->normalize($value_14, 'json', $context);
                    $values_7[$key_7] = $value_15;
                    continue;
                }
            }

            $data['links'] = $values_7;
        }

        if ($object->isInitialized('callbacks') && null !== $object->getCallbacks()) {
            $values_8 = [];
            foreach ($object->getCallbacks() as $key_8 => $value_16) {
                if (1 === \Safe\preg_match('/^[a-zA-Z0-9\.\-_]+$/', (string)$key_8) && null !== $value_16) {
                    if (\is_object($value_16)) {
                        $value_17 = $this->normalizer->normalize($value_16, 'json', $context);
                    } else {
                        $values_9 = [];
                        foreach ($value_16 as $key_9 => $value_18) {
                            if (1 === \Safe\preg_match('/^x-/', (string)$key_9) && null !== $value_18) {
                                $values_9[$key_9] = $value_18;
                                continue;
                            }
                        }

                        $value_17 = $values_9;
                    }

                    $values_8[$key_8] = $value_17;
                    continue;
                }
            }

            $data['callbacks'] = $values_8;
        }

        foreach ($object as $key_10 => $value_19) {
            if (1 === \Safe\preg_match('/^x-/', $key_10)) {
                $data[$key_10] = $value_19;
            }
        }

        return $data;
    }

    public function getSupportedTypes(?string $format = null): array
    {
        return [\LongTermSupport\OpenApiGenerator\Component\OpenApi3\JsonSchema\Model\Components::class => false];
    }
}
