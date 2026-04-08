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

class ResponseNormalizer implements DenormalizerInterface, NormalizerInterface, DenormalizerAwareInterface, NormalizerAwareInterface
{
    use DenormalizerAwareTrait;
    use NormalizerAwareTrait;
    use CheckArray;
    use ValidatorTrait;

    public function supportsDenormalization(mixed $data, string $type, ?string $format = null, array $context = []): bool
    {
        return \LongTermSupport\OpenApiGenerator\Component\OpenApi3\JsonSchema\Model\Response::class === $type;
    }

    public function supportsNormalization(mixed $data, ?string $format = null, array $context = []): bool
    {
        return $data instanceof \LongTermSupport\OpenApiGenerator\Component\OpenApi3\JsonSchema\Model\Response;
    }

    /**
     * @return ($type is class-string<object> ? object : mixed)
     */
    public function denormalize(mixed $data, string $type, ?string $format = null, array $context = []): mixed
    {
        $object = new \LongTermSupport\OpenApiGenerator\Component\OpenApi3\JsonSchema\Model\Response();
        if (null === $data || false === \is_array($data)) {
            return $object;
        }

        /** @var array<string, mixed> $data */
        if (isset($data['$ref'])) {
            return new Reference(TypeValidator::assertString($data['$ref'], '$ref'), TypeValidator::assertString($context['document-origin'], 'context.document-origin'));
        }

        if (isset($data['$recursiveRef'])) {
            return new Reference(TypeValidator::assertString($data['$recursiveRef'], '$recursiveRef'), TypeValidator::assertString($context['document-origin'], 'context.document-origin'));
        }

        if (\array_key_exists('description', $data) && null !== $data['description']) {
            $object->setDescription(TypeValidator::assertString($data['description'], 'description'));
            unset($data['description']);
        } elseif (\array_key_exists('description', $data) && null === $data['description']) {
            $object->setDescription(null);
        }

        if (\array_key_exists('headers', $data) && null !== $data['headers']) {
            /** @var array<string, \LongTermSupport\OpenApiGenerator\Component\OpenApi3\JsonSchema\Model\Header|\LongTermSupport\OpenApiGenerator\Component\OpenApi3\JsonSchema\Model\Reference> $values */
            $values = [];
            if (\is_array($data['headers'])) {
                foreach ($data['headers'] as $key => $value) {
                    $key     = TypeValidator::assertStringKey($key, 'headers');
                    $value_1 = $value;
                    if (\is_array($value) && isset($value['$ref'])) {
                        $value_1 = $this->denormalizer->denormalize($value, \LongTermSupport\OpenApiGenerator\Component\OpenApi3\JsonSchema\Model\Reference::class, 'json', $context);
                    } elseif (\is_array($value)) {
                        $value_1 = $this->denormalizer->denormalize($value, \LongTermSupport\OpenApiGenerator\Component\OpenApi3\JsonSchema\Model\Header::class, 'json', $context);
                    }

                    /** @var \LongTermSupport\OpenApiGenerator\Component\OpenApi3\JsonSchema\Model\Header|\LongTermSupport\OpenApiGenerator\Component\OpenApi3\JsonSchema\Model\Reference $value_1 */
                    $values[$key] = $value_1;
                }
            }

            $object->setHeaders($values);
            unset($data['headers']);
        } elseif (\array_key_exists('headers', $data) && null === $data['headers']) {
            $object->setHeaders(null);
        }

        if (\array_key_exists('content', $data) && null !== $data['content']) {
            /** @var array<string, \LongTermSupport\OpenApiGenerator\Component\OpenApi3\JsonSchema\Model\MediaType> $values_1 */
            $values_1 = [];
            if (\is_array($data['content'])) {
                foreach ($data['content'] as $key_1 => $value_2) {
                    $key_1 = TypeValidator::assertStringKey($key_1, 'content');
                    /** @var \LongTermSupport\OpenApiGenerator\Component\OpenApi3\JsonSchema\Model\MediaType $denormMediaType */
                    $denormMediaType  = $this->denormalizer->denormalize($value_2, \LongTermSupport\OpenApiGenerator\Component\OpenApi3\JsonSchema\Model\MediaType::class, 'json', $context);
                    $values_1[$key_1] = $denormMediaType;
                }
            }

            $object->setContent($values_1);
            unset($data['content']);
        } elseif (\array_key_exists('content', $data) && null === $data['content']) {
            $object->setContent(null);
        }

        if (\array_key_exists('links', $data) && null !== $data['links']) {
            /** @var array<string, \LongTermSupport\OpenApiGenerator\Component\OpenApi3\JsonSchema\Model\Link|\LongTermSupport\OpenApiGenerator\Component\OpenApi3\JsonSchema\Model\Reference> $values_2 */
            $values_2 = [];
            if (\is_array($data['links'])) {
                foreach ($data['links'] as $key_2 => $value_3) {
                    $key_2   = TypeValidator::assertStringKey($key_2, 'links');
                    $value_4 = $value_3;
                    if (\is_array($value_3) && isset($value_3['$ref'])) {
                        $value_4 = $this->denormalizer->denormalize($value_3, \LongTermSupport\OpenApiGenerator\Component\OpenApi3\JsonSchema\Model\Reference::class, 'json', $context);
                    } elseif (\is_array($value_3)) {
                        $value_4 = $this->denormalizer->denormalize($value_3, \LongTermSupport\OpenApiGenerator\Component\OpenApi3\JsonSchema\Model\Link::class, 'json', $context);
                    }

                    /** @var \LongTermSupport\OpenApiGenerator\Component\OpenApi3\JsonSchema\Model\Link|\LongTermSupport\OpenApiGenerator\Component\OpenApi3\JsonSchema\Model\Reference $value_4 */
                    $values_2[$key_2] = $value_4;
                }
            }

            $object->setLinks($values_2);
            unset($data['links']);
        } elseif (\array_key_exists('links', $data) && null === $data['links']) {
            $object->setLinks(null);
        }

        foreach ($data as $key_3 => $value_5) {
            $key_3 = TypeValidator::assertStringKey($key_3, 'Response');
            if (1 === \Safe\preg_match('/^x-/', $key_3)) {
                $object[$key_3] = $value_5;
            }
        }

        return $object;
    }

    /**
     * @return array<string, mixed>|string|int|float|bool|ArrayObject<string, mixed>|null
     */
    public function normalize(mixed $object, ?string $format = null, array $context = []): array|string|int|float|bool|ArrayObject|null
    {
        if (!$object instanceof \LongTermSupport\OpenApiGenerator\Component\OpenApi3\JsonSchema\Model\Response) {
            throw new LogicException('Expected Response, got ' . get_debug_type($object));
        }

        $data                = [];
        $data['description'] = $object->getDescription();
        if ($object->isInitialized('headers') && null !== $object->getHeaders()) {
            $values = [];
            foreach ($object->getHeaders() as $key => $value) {
                $value_1 = $value;
                if (\is_object($value)) {
                    $value_1 = $this->normalizer->normalize($value, 'json', $context);
                }

                $values[$key] = $value_1;
            }

            $data['headers'] = $values;
        }

        if ($object->isInitialized('content') && null !== $object->getContent()) {
            $values_1 = [];
            foreach ($object->getContent() as $key_1 => $value_2) {
                $values_1[$key_1] = $this->normalizer->normalize($value_2, 'json', $context);
            }

            $data['content'] = $values_1;
        }

        if ($object->isInitialized('links') && null !== $object->getLinks()) {
            $values_2 = [];
            foreach ($object->getLinks() as $key_2 => $value_3) {
                $value_4 = $value_3;
                if (\is_object($value_3)) {
                    $value_4 = $this->normalizer->normalize($value_3, 'json', $context);
                }

                $values_2[$key_2] = $value_4;
            }

            $data['links'] = $values_2;
        }

        foreach ($object as $key_3 => $value_5) {
            if (1 === \Safe\preg_match('/^x-/', $key_3)) {
                $data[$key_3] = $value_5;
            }
        }

        return $data;
    }

    public function getSupportedTypes(?string $format = null): array
    {
        return [\LongTermSupport\OpenApiGenerator\Component\OpenApi3\JsonSchema\Model\Response::class => false];
    }
}
