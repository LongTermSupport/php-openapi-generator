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

class OpenApiNormalizer implements DenormalizerInterface, NormalizerInterface, DenormalizerAwareInterface, NormalizerAwareInterface
{
    use DenormalizerAwareTrait;
    use NormalizerAwareTrait;
    use CheckArray;
    use ValidatorTrait;

    public function supportsDenormalization(mixed $data, string $type, ?string $format = null, array $context = []): bool
    {
        return \LongTermSupport\OpenApiGenerator\Component\OpenApi3\JsonSchema\Model\OpenApi::class === $type;
    }

    public function supportsNormalization(mixed $data, ?string $format = null, array $context = []): bool
    {
        return $data instanceof \LongTermSupport\OpenApiGenerator\Component\OpenApi3\JsonSchema\Model\OpenApi;
    }

    /**
     * @return ($type is class-string<object> ? object : mixed)
     */
    public function denormalize(mixed $data, string $type, ?string $format = null, array $context = []): mixed
    {
        $object = new \LongTermSupport\OpenApiGenerator\Component\OpenApi3\JsonSchema\Model\OpenApi();
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

        if (\array_key_exists('openapi', $data) && null !== $data['openapi']) {
            /** @var scalar $openapiVal */
            $openapiVal = $data['openapi'];
            $object->setOpenapi((string)$openapiVal);
            unset($data['openapi']);
        } elseif (\array_key_exists('openapi', $data) && null === $data['openapi']) {
            $object->setOpenapi(null);
        }

        if (\array_key_exists('info', $data) && null !== $data['info']) {
            /** @var \LongTermSupport\OpenApiGenerator\Component\OpenApi3\JsonSchema\Model\Info $denormInfo */
            $denormInfo = $this->denormalizer->denormalize($data['info'], \LongTermSupport\OpenApiGenerator\Component\OpenApi3\JsonSchema\Model\Info::class, 'json', $context);
            $object->setInfo($denormInfo);
            unset($data['info']);
        } elseif (\array_key_exists('info', $data) && null === $data['info']) {
            $object->setInfo(null);
        }

        if (\array_key_exists('externalDocs', $data) && null !== $data['externalDocs']) {
            /** @var \LongTermSupport\OpenApiGenerator\Component\OpenApi3\JsonSchema\Model\ExternalDocumentation $denormExternalDocs */
            $denormExternalDocs = $this->denormalizer->denormalize($data['externalDocs'], \LongTermSupport\OpenApiGenerator\Component\OpenApi3\JsonSchema\Model\ExternalDocumentation::class, 'json', $context);
            $object->setExternalDocs($denormExternalDocs);
            unset($data['externalDocs']);
        } elseif (\array_key_exists('externalDocs', $data) && null === $data['externalDocs']) {
            $object->setExternalDocs(null);
        }

        if (\array_key_exists('servers', $data) && null !== $data['servers']) {
            $values = [];
            /** @var array<mixed> $serversArr */
            $serversArr = $data['servers'];
            foreach ($serversArr as $value) {
                /** @var \LongTermSupport\OpenApiGenerator\Component\OpenApi3\JsonSchema\Model\Server $denormServer */
                $denormServer = $this->denormalizer->denormalize($value, \LongTermSupport\OpenApiGenerator\Component\OpenApi3\JsonSchema\Model\Server::class, 'json', $context);
                $values[]     = $denormServer;
            }

            $object->setServers($values);
            unset($data['servers']);
        } elseif (\array_key_exists('servers', $data) && null === $data['servers']) {
            $object->setServers(null);
        }

        if (\array_key_exists('security', $data) && null !== $data['security']) {
            $values_1 = [];
            /** @var array<mixed> $securityArr */
            $securityArr = $data['security'];
            foreach ($securityArr as $value_1) {
                $values_2 = [];
                /** @var array<mixed> $value_1 */
                foreach ($value_1 as $key => $value_2) {
                    $values_3 = [];
                    /** @var array<mixed> $value_2 */
                    foreach ($value_2 as $value_3) {
                        /** @var string $value_3 */
                        $values_3[] = $value_3;
                    }

                    $values_2[(string)$key] = $values_3;
                }

                $values_1[] = $values_2;
            }

            $object->setSecurity($values_1);
            unset($data['security']);
        } elseif (\array_key_exists('security', $data) && null === $data['security']) {
            $object->setSecurity(null);
        }

        if (\array_key_exists('tags', $data) && null !== $data['tags']) {
            $values_4 = [];
            /** @var array<mixed> $tagsArr */
            $tagsArr = $data['tags'];
            foreach ($tagsArr as $value_4) {
                /** @var \LongTermSupport\OpenApiGenerator\Component\OpenApi3\JsonSchema\Model\Tag $denormTag */
                $denormTag  = $this->denormalizer->denormalize($value_4, \LongTermSupport\OpenApiGenerator\Component\OpenApi3\JsonSchema\Model\Tag::class, 'json', $context);
                $values_4[] = $denormTag;
            }

            $object->setTags($values_4);
            unset($data['tags']);
        } elseif (\array_key_exists('tags', $data) && null === $data['tags']) {
            $object->setTags(null);
        }

        if (\array_key_exists('paths', $data) && null !== $data['paths']) {
            $values_5 = [];
            /** @var array<mixed> $pathsArr */
            $pathsArr = $data['paths'];
            foreach ($pathsArr as $key_1 => $value_5) {
                if (1 === \Safe\preg_match('/^\//', (string)$key_1) && \is_array($value_5)) {
                    /** @var \LongTermSupport\OpenApiGenerator\Component\OpenApi3\JsonSchema\Model\PathItem $denormPathItem */
                    $denormPathItem           = $this->denormalizer->denormalize($value_5, \LongTermSupport\OpenApiGenerator\Component\OpenApi3\JsonSchema\Model\PathItem::class, 'json', $context);
                    $values_5[(string)$key_1] = $denormPathItem;
                    continue;
                }

                if (1 === \Safe\preg_match('/^x-/', (string)$key_1) && isset($value_5)) {
                    $values_5[(string)$key_1] = $value_5;
                    continue;
                }
            }

            $object->setPaths($values_5);
            unset($data['paths']);
        } elseif (\array_key_exists('paths', $data) && null === $data['paths']) {
            $object->setPaths(null);
        }

        if (\array_key_exists('components', $data) && null !== $data['components']) {
            /** @var \LongTermSupport\OpenApiGenerator\Component\OpenApi3\JsonSchema\Model\Components $denormComponents */
            $denormComponents = $this->denormalizer->denormalize($data['components'], \LongTermSupport\OpenApiGenerator\Component\OpenApi3\JsonSchema\Model\Components::class, 'json', $context);
            $object->setComponents($denormComponents);
            unset($data['components']);
        } elseif (\array_key_exists('components', $data) && null === $data['components']) {
            $object->setComponents(null);
        }

        foreach ($data as $key_2 => $value_6) {
            if (!\is_string($key_2)) {
                continue;
            }

            if (1 === \Safe\preg_match('/^x-/', $key_2)) {
                $object[$key_2] = $value_6;
            }
        }

        return $object;
    }

    /**
     * @return array<string, mixed>|string|int|float|bool|ArrayObject<string, mixed>|null
     */
    public function normalize(mixed $object, ?string $format = null, array $context = []): array|string|int|float|bool|ArrayObject|null
    {
        if (!$object instanceof \LongTermSupport\OpenApiGenerator\Component\OpenApi3\JsonSchema\Model\OpenApi) {
            throw new LogicException('Expected OpenApi, got ' . get_debug_type($object));
        }

        $data            = [];
        $data['openapi'] = $object->getOpenapi();
        $data['info']    = $this->normalizer->normalize($object->getInfo(), 'json', $context);
        if ($object->isInitialized('externalDocs') && $object->getExternalDocs() instanceof \LongTermSupport\OpenApiGenerator\Component\OpenApi3\JsonSchema\Model\ExternalDocumentation) {
            $data['externalDocs'] = $this->normalizer->normalize($object->getExternalDocs(), 'json', $context);
        }

        if ($object->isInitialized('servers') && null !== $object->getServers()) {
            $values = [];
            foreach ($object->getServers() as $value) {
                $values[] = $this->normalizer->normalize($value, 'json', $context);
            }

            $data['servers'] = $values;
        }

        if ($object->isInitialized('security') && null !== $object->getSecurity()) {
            $values_1 = [];
            foreach ($object->getSecurity() as $value_1) {
                $values_2 = [];
                foreach ($value_1 as $key => $value_2) {
                    $values_3 = [];
                    foreach ($value_2 as $value_3) {
                        $values_3[] = $value_3;
                    }

                    $values_2[$key] = $values_3;
                }

                $values_1[] = $values_2;
            }

            $data['security'] = $values_1;
        }

        if ($object->isInitialized('tags') && null !== $object->getTags()) {
            $values_4 = [];
            foreach ($object->getTags() as $value_4) {
                $values_4[] = $this->normalizer->normalize($value_4, 'json', $context);
            }

            $data['tags'] = $values_4;
        }

        $values_5 = [];
        foreach ($object->getPaths() as $key_1 => $value_5) {
            if (1 === \Safe\preg_match('/^\//', (string)$key_1) && \is_object($value_5)) {
                $values_5[$key_1] = $this->normalizer->normalize($value_5, 'json', $context);
                continue;
            }

            if (1 === \Safe\preg_match('/^x-/', (string)$key_1) && !\is_null($value_5)) {
                $values_5[$key_1] = $value_5;
                continue;
            }
        }

        $data['paths'] = $values_5;
        if ($object->isInitialized('components') && $object->getComponents() instanceof \LongTermSupport\OpenApiGenerator\Component\OpenApi3\JsonSchema\Model\Components) {
            $data['components'] = $this->normalizer->normalize($object->getComponents(), 'json', $context);
        }

        foreach ($object as $key_2 => $value_6) {
            if (1 === \Safe\preg_match('/^x-/', $key_2)) {
                $data[$key_2] = $value_6;
            }
        }

        return $data;
    }

    public function getSupportedTypes(?string $format = null): array
    {
        return [\LongTermSupport\OpenApiGenerator\Component\OpenApi3\JsonSchema\Model\OpenApi::class => false];
    }
}
