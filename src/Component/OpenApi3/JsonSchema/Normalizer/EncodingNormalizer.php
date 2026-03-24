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

class EncodingNormalizer implements DenormalizerInterface, NormalizerInterface, DenormalizerAwareInterface, NormalizerAwareInterface
{
    use DenormalizerAwareTrait;
    use NormalizerAwareTrait;
    use CheckArray;
    use ValidatorTrait;

    public function supportsDenormalization(mixed $data, string $type, ?string $format = null, array $context = []): bool
    {
        return \LongTermSupport\OpenApiGenerator\Component\OpenApi3\JsonSchema\Model\Encoding::class === $type;
    }

    public function supportsNormalization(mixed $data, ?string $format = null, array $context = []): bool
    {
        return $data instanceof \LongTermSupport\OpenApiGenerator\Component\OpenApi3\JsonSchema\Model\Encoding;
    }

    /**
     * @return object
     */
    public function denormalize(mixed $data, string $type, ?string $format = null, array $context = []): mixed
    {
        $object = new \LongTermSupport\OpenApiGenerator\Component\OpenApi3\JsonSchema\Model\Encoding();
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

        if (\array_key_exists('contentType', $data) && null !== $data['contentType']) {
            $object->setContentType(TypeValidator::assertString($data['contentType'], 'contentType'));
        } elseif (\array_key_exists('contentType', $data) && null === $data['contentType']) {
            $object->setContentType(null);
        }

        if (\array_key_exists('headers', $data) && null !== $data['headers']) {
            $values = new ArrayObject([], ArrayObject::ARRAY_AS_PROPS);
            if (\is_array($data['headers'])) {
                foreach ($data['headers'] as $key => $value) {
                    $values[$key] = $this->denormalizer->denormalize($value, \LongTermSupport\OpenApiGenerator\Component\OpenApi3\JsonSchema\Model\Header::class, 'json', $context);
                }
            }

            $object->setHeaders($values);
        } elseif (\array_key_exists('headers', $data) && null === $data['headers']) {
            $object->setHeaders(null);
        }

        if (\array_key_exists('style', $data) && null !== $data['style']) {
            $object->setStyle($data['style']);
        } elseif (\array_key_exists('style', $data) && null === $data['style']) {
            $object->setStyle(null);
        }

        if (\array_key_exists('explode', $data) && null !== $data['explode']) {
            $object->setExplode($data['explode']);
        } elseif (\array_key_exists('explode', $data) && null === $data['explode']) {
            $object->setExplode(null);
        }

        if (\array_key_exists('allowReserved', $data) && null !== $data['allowReserved']) {
            $object->setAllowReserved($data['allowReserved']);
        } elseif (\array_key_exists('allowReserved', $data) && null === $data['allowReserved']) {
            $object->setAllowReserved(null);
        }

        return $object;
    }

    /**
     * @return array<string, mixed>|string|int|float|bool|ArrayObject<string, mixed>|null
     */
    public function normalize(mixed $object, ?string $format = null, array $context = []): array|string|int|float|bool|ArrayObject|null
    {
        if (!$object instanceof \LongTermSupport\OpenApiGenerator\Component\OpenApi3\JsonSchema\Model\Encoding) {
            throw new LogicException('Expected Encoding, got ' . get_debug_type($object));
        }

        $data = [];
        if ($object->isInitialized('contentType') && null !== $object->getContentType()) {
            $data['contentType'] = $object->getContentType();
        }

        if ($object->isInitialized('headers') && null !== $object->getHeaders()) {
            $values = [];
            foreach ($object->getHeaders() as $key => $value) {
                $values[$key] = $this->normalizer->normalize($value, 'json', $context);
            }

            $data['headers'] = $values;
        }

        if ($object->isInitialized('style') && null !== $object->getStyle()) {
            $data['style'] = $object->getStyle();
        }

        if ($object->isInitialized('explode') && null !== $object->getExplode()) {
            $data['explode'] = $object->getExplode();
        }

        if ($object->isInitialized('allowReserved') && null !== $object->getAllowReserved()) {
            $data['allowReserved'] = $object->getAllowReserved();
        }

        return $data;
    }

    public function getSupportedTypes(?string $format = null): array
    {
        return [\LongTermSupport\OpenApiGenerator\Component\OpenApi3\JsonSchema\Model\Encoding::class => false];
    }
}
