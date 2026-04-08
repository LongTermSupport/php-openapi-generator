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

class MediaTypeNormalizer implements DenormalizerInterface, NormalizerInterface, DenormalizerAwareInterface, NormalizerAwareInterface
{
    use DenormalizerAwareTrait;
    use NormalizerAwareTrait;
    use CheckArray;
    use ValidatorTrait;

    public function supportsDenormalization(mixed $data, string $type, ?string $format = null, array $context = []): bool
    {
        return \LongTermSupport\OpenApiGenerator\Component\OpenApi3\JsonSchema\Model\MediaType::class === $type;
    }

    public function supportsNormalization(mixed $data, ?string $format = null, array $context = []): bool
    {
        return $data instanceof \LongTermSupport\OpenApiGenerator\Component\OpenApi3\JsonSchema\Model\MediaType;
    }

    /**
     * @return ($type is class-string<object> ? object : mixed)
     */
    public function denormalize(mixed $data, string $type, ?string $format = null, array $context = []): mixed
    {
        $object = new \LongTermSupport\OpenApiGenerator\Component\OpenApi3\JsonSchema\Model\MediaType();
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

        if (\array_key_exists('schema', $data) && null !== $data['schema']) {
            $value = $data['schema'];
            if (\is_array($data['schema']) && isset($data['schema']['$ref'])) {
                $value = $this->denormalizer->denormalize($data['schema'], \LongTermSupport\OpenApiGenerator\Component\OpenApi3\JsonSchema\Model\Reference::class, 'json', $context);
            } elseif (\is_array($data['schema'])) {
                $value = $this->denormalizer->denormalize($data['schema'], \LongTermSupport\OpenApiGenerator\Component\OpenApi3\JsonSchema\Model\Schema::class, 'json', $context);
            }

            /** @var \LongTermSupport\OpenApiGenerator\Component\OpenApi3\JsonSchema\Model\Reference|\LongTermSupport\OpenApiGenerator\Component\OpenApi3\JsonSchema\Model\Schema $value */
            $object->setSchema($value);
            unset($data['schema']);
        } elseif (\array_key_exists('schema', $data) && null === $data['schema']) {
            $object->setSchema(null);
        }

        if (\array_key_exists('example', $data) && null !== $data['example']) {
            $object->setExample($data['example']);
            unset($data['example']);
        } elseif (\array_key_exists('example', $data) && null === $data['example']) {
            $object->setExample(null);
        }

        if (\array_key_exists('examples', $data) && null !== $data['examples']) {
            /** @var array<string, \LongTermSupport\OpenApiGenerator\Component\OpenApi3\JsonSchema\Model\Example|\LongTermSupport\OpenApiGenerator\Component\OpenApi3\JsonSchema\Model\Reference> $values */
            $values = [];
            if (\is_array($data['examples'])) {
                foreach ($data['examples'] as $key => $value_1) {
                    $key     = TypeValidator::assertStringKey($key, 'examples');
                    $value_2 = $value_1;
                    if (\is_array($value_1) && isset($value_1['$ref'])) {
                        $value_2 = $this->denormalizer->denormalize($value_1, \LongTermSupport\OpenApiGenerator\Component\OpenApi3\JsonSchema\Model\Reference::class, 'json', $context);
                    } elseif (\is_array($value_1)) {
                        $value_2 = $this->denormalizer->denormalize($value_1, \LongTermSupport\OpenApiGenerator\Component\OpenApi3\JsonSchema\Model\Example::class, 'json', $context);
                    }

                    /** @var \LongTermSupport\OpenApiGenerator\Component\OpenApi3\JsonSchema\Model\Example|\LongTermSupport\OpenApiGenerator\Component\OpenApi3\JsonSchema\Model\Reference $value_2 */
                    $values[$key] = $value_2;
                }
            }

            $object->setExamples($values);
            unset($data['examples']);
        } elseif (\array_key_exists('examples', $data) && null === $data['examples']) {
            $object->setExamples(null);
        }

        if (\array_key_exists('encoding', $data) && null !== $data['encoding']) {
            /** @var array<string, \LongTermSupport\OpenApiGenerator\Component\OpenApi3\JsonSchema\Model\Encoding> $values_1 */
            $values_1 = [];
            if (\is_array($data['encoding'])) {
                foreach ($data['encoding'] as $key_1 => $value_3) {
                    $key_1 = TypeValidator::assertStringKey($key_1, 'encoding');
                    /** @var \LongTermSupport\OpenApiGenerator\Component\OpenApi3\JsonSchema\Model\Encoding $denormEncoding */
                    $denormEncoding   = $this->denormalizer->denormalize($value_3, \LongTermSupport\OpenApiGenerator\Component\OpenApi3\JsonSchema\Model\Encoding::class, 'json', $context);
                    $values_1[$key_1] = $denormEncoding;
                }
            }

            $object->setEncoding($values_1);
            unset($data['encoding']);
        } elseif (\array_key_exists('encoding', $data) && null === $data['encoding']) {
            $object->setEncoding(null);
        }

        foreach ($data as $key_2 => $value_4) {
            $key_2 = TypeValidator::assertStringKey($key_2, 'MediaType');

            if (1 === \Safe\preg_match('/^x-/', $key_2)) {
                $object[$key_2] = $value_4;
            }
        }

        return $object;
    }

    /**
     * @return array<string, mixed>|string|int|float|bool|ArrayObject<string, mixed>|null
     */
    public function normalize(mixed $object, ?string $format = null, array $context = []): array|string|int|float|bool|ArrayObject|null
    {
        if (!$object instanceof \LongTermSupport\OpenApiGenerator\Component\OpenApi3\JsonSchema\Model\MediaType) {
            throw new LogicException('Expected MediaType, got ' . get_debug_type($object));
        }

        $data = [];
        if ($object->isInitialized('schema') && null !== $object->getSchema()) {
            $value = $object->getSchema();
            if (\is_object($object->getSchema())) {
                $value = $this->normalizer->normalize($object->getSchema(), 'json', $context);
            }

            $data['schema'] = $value;
        }

        if ($object->isInitialized('example') && null !== $object->getExample()) {
            $data['example'] = $object->getExample();
        }

        if ($object->isInitialized('examples') && null !== $object->getExamples()) {
            $values = [];
            foreach ($object->getExamples() as $key => $value_1) {
                $value_2 = $value_1;
                if (\is_object($value_1)) {
                    $value_2 = $this->normalizer->normalize($value_1, 'json', $context);
                }

                $values[$key] = $value_2;
            }

            $data['examples'] = $values;
        }

        if ($object->isInitialized('encoding') && null !== $object->getEncoding()) {
            $values_1 = [];
            foreach ($object->getEncoding() as $key_1 => $value_3) {
                $values_1[$key_1] = $this->normalizer->normalize($value_3, 'json', $context);
            }

            $data['encoding'] = $values_1;
        }

        foreach ($object as $key_2 => $value_4) {
            if (1 === \Safe\preg_match('/^x-/', $key_2)) {
                $data[$key_2] = $value_4;
            }
        }

        return $data;
    }

    public function getSupportedTypes(?string $format = null): array
    {
        return [\LongTermSupport\OpenApiGenerator\Component\OpenApi3\JsonSchema\Model\MediaType::class => false];
    }
}
