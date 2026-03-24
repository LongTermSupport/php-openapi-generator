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

class ResponsesNormalizer implements DenormalizerInterface, NormalizerInterface, DenormalizerAwareInterface, NormalizerAwareInterface
{
    use DenormalizerAwareTrait;
    use NormalizerAwareTrait;
    use CheckArray;
    use ValidatorTrait;

    public function supportsDenormalization(mixed $data, string $type, ?string $format = null, array $context = []): bool
    {
        return \LongTermSupport\OpenApiGenerator\Component\OpenApi3\JsonSchema\Model\Responses::class === $type;
    }

    public function supportsNormalization(mixed $data, ?string $format = null, array $context = []): bool
    {
        return $data instanceof \LongTermSupport\OpenApiGenerator\Component\OpenApi3\JsonSchema\Model\Responses;
    }

    /**
     * @return object
     */
    public function denormalize(mixed $data, string $type, ?string $format = null, array $context = []): mixed
    {
        $object = new \LongTermSupport\OpenApiGenerator\Component\OpenApi3\JsonSchema\Model\Responses();
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

        if (\array_key_exists('default', $data) && null !== $data['default']) {
            $value = $data['default'];
            if (\is_array($data['default']) && isset($data['default']['$ref'])) {
                $value = $this->denormalizer->denormalize($data['default'], \LongTermSupport\OpenApiGenerator\Component\OpenApi3\JsonSchema\Model\Reference::class, 'json', $context);
            } elseif (\is_array($data['default']) && isset($data['default']['description'])) {
                $value = $this->denormalizer->denormalize($data['default'], \LongTermSupport\OpenApiGenerator\Component\OpenApi3\JsonSchema\Model\Response::class, 'json', $context);
            }

            $object->setDefault($value);
            unset($data['default']);
        } elseif (\array_key_exists('default', $data) && null === $data['default']) {
            $object->setDefault(null);
        }

        foreach ($data as $key => $value_1) {
            if (0 !== \Safe\preg_match('/^[1-5](?:\d{2}|XX)$/', (string)$key)) {
                $value_2 = $value_1;
                if (\is_array($value_1) && isset($value_1['$ref'])) {
                    $value_2 = $this->denormalizer->denormalize($value_1, \LongTermSupport\OpenApiGenerator\Component\OpenApi3\JsonSchema\Model\Reference::class, 'json', $context);
                } elseif (\is_array($value_1) && isset($value_1['description'])) {
                    $value_2 = $this->denormalizer->denormalize($value_1, \LongTermSupport\OpenApiGenerator\Component\OpenApi3\JsonSchema\Model\Response::class, 'json', $context);
                }

                $object[$key] = $value_2;
            }

            if (0 !== \Safe\preg_match('/^x-/', (string)$key)) {
                $object[$key] = $value_1;
            }
        }

        return $object;
    }

    /**
     * @return array<string, mixed>|string|int|float|bool|ArrayObject<string, mixed>|null
     */
    public function normalize(mixed $object, ?string $format = null, array $context = []): array|string|int|float|bool|ArrayObject|null
    {
        if (!$object instanceof \LongTermSupport\OpenApiGenerator\Component\OpenApi3\JsonSchema\Model\Responses) {
            throw new LogicException('Expected Responses, got ' . get_debug_type($object));
        }

        $data = [];
        if ($object->isInitialized('default') && null !== $object->getDefault()) {
            $value = $object->getDefault();
            if (\is_object($object->getDefault())) {
                $value = $this->normalizer->normalize($object->getDefault(), 'json', $context);
            } elseif (\is_object($object->getDefault())) {
                $value = $this->normalizer->normalize($object->getDefault(), 'json', $context);
            }

            $data['default'] = $value;
        }

        foreach ($object as $key => $value_1) {
            if (0 !== \Safe\preg_match('/^[1-5](?:\d{2}|XX)$/', $key)) {
                $value_2 = $value_1;
                if (\is_object($value_1)) {
                    $value_2 = $this->normalizer->normalize($value_1, 'json', $context);
                } elseif (\is_object($value_1)) {
                    $value_2 = $this->normalizer->normalize($value_1, 'json', $context);
                }

                $data[$key] = $value_2;
            }

            if (0 !== \Safe\preg_match('/^x-/', $key)) {
                $data[$key] = $value_1;
            }
        }

        return $data;
    }

    public function getSupportedTypes(?string $format = null): array
    {
        return [\LongTermSupport\OpenApiGenerator\Component\OpenApi3\JsonSchema\Model\Responses::class => false];
    }
}
