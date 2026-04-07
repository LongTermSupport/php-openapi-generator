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

class HeaderNormalizer implements DenormalizerInterface, NormalizerInterface, DenormalizerAwareInterface, NormalizerAwareInterface
{
    use DenormalizerAwareTrait;
    use NormalizerAwareTrait;
    use CheckArray;
    use ValidatorTrait;

    public function supportsDenormalization(mixed $data, string $type, ?string $format = null, array $context = []): bool
    {
        return \LongTermSupport\OpenApiGenerator\Component\OpenApi3\JsonSchema\Model\Header::class === $type;
    }

    public function supportsNormalization(mixed $data, ?string $format = null, array $context = []): bool
    {
        return $data instanceof \LongTermSupport\OpenApiGenerator\Component\OpenApi3\JsonSchema\Model\Header;
    }

    /**
     * @return ($type is class-string<object> ? object : mixed)
     */
    public function denormalize(mixed $data, string $type, ?string $format = null, array $context = []): mixed
    {
        $object = new \LongTermSupport\OpenApiGenerator\Component\OpenApi3\JsonSchema\Model\Header();
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

        if (\array_key_exists('description', $data) && null !== $data['description']) {
            /** @var scalar $descriptionVal */
            $descriptionVal = $data['description'];
            $object->setDescription((string)$descriptionVal);
            unset($data['description']);
        } elseif (\array_key_exists('description', $data) && null === $data['description']) {
            $object->setDescription(null);
        }

        if (\array_key_exists('required', $data) && null !== $data['required']) {
            /** @var bool $requiredVal */
            $requiredVal = $data['required'];
            $object->setRequired($requiredVal);
            unset($data['required']);
        } elseif (\array_key_exists('required', $data) && null === $data['required']) {
            $object->setRequired(null);
        }

        if (\array_key_exists('deprecated', $data) && null !== $data['deprecated']) {
            /** @var bool $deprecatedVal */
            $deprecatedVal = $data['deprecated'];
            $object->setDeprecated($deprecatedVal);
            unset($data['deprecated']);
        } elseif (\array_key_exists('deprecated', $data) && null === $data['deprecated']) {
            $object->setDeprecated(null);
        }

        if (\array_key_exists('allowEmptyValue', $data) && null !== $data['allowEmptyValue']) {
            /** @var bool $allowEmptyValueVal */
            $allowEmptyValueVal = $data['allowEmptyValue'];
            $object->setAllowEmptyValue($allowEmptyValueVal);
            unset($data['allowEmptyValue']);
        } elseif (\array_key_exists('allowEmptyValue', $data) && null === $data['allowEmptyValue']) {
            $object->setAllowEmptyValue(null);
        }

        if (\array_key_exists('style', $data) && null !== $data['style']) {
            /** @var scalar $styleVal */
            $styleVal = $data['style'];
            $object->setStyle((string)$styleVal);
            unset($data['style']);
        } elseif (\array_key_exists('style', $data) && null === $data['style']) {
            $object->setStyle(null);
        }

        if (\array_key_exists('explode', $data) && null !== $data['explode']) {
            /** @var bool $explodeVal */
            $explodeVal = $data['explode'];
            $object->setExplode($explodeVal);
            unset($data['explode']);
        } elseif (\array_key_exists('explode', $data) && null === $data['explode']) {
            $object->setExplode(null);
        }

        if (\array_key_exists('allowReserved', $data) && null !== $data['allowReserved']) {
            /** @var bool $allowReservedVal */
            $allowReservedVal = $data['allowReserved'];
            $object->setAllowReserved($allowReservedVal);
            unset($data['allowReserved']);
        } elseif (\array_key_exists('allowReserved', $data) && null === $data['allowReserved']) {
            $object->setAllowReserved(null);
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

        if (\array_key_exists('content', $data) && null !== $data['content']) {
            /** @var ArrayObject<string, \LongTermSupport\OpenApiGenerator\Component\OpenApi3\JsonSchema\Model\MediaType> $values */
            $values = new ArrayObject([], ArrayObject::ARRAY_AS_PROPS);
            /** @var array<mixed> $contentArr */
            $contentArr = $data['content'];
            foreach ($contentArr as $key => $value_1) {
                if (!\is_string($key)) {
                    continue;
                }

                /** @var \LongTermSupport\OpenApiGenerator\Component\OpenApi3\JsonSchema\Model\MediaType $denormMediaType */
                $denormMediaType = $this->denormalizer->denormalize($value_1, \LongTermSupport\OpenApiGenerator\Component\OpenApi3\JsonSchema\Model\MediaType::class, 'json', $context);
                $values[$key]    = $denormMediaType;
            }

            $object->setContent($values);
            unset($data['content']);
        } elseif (\array_key_exists('content', $data) && null === $data['content']) {
            $object->setContent(null);
        }

        if (\array_key_exists('example', $data) && null !== $data['example']) {
            $object->setExample($data['example']);
            unset($data['example']);
        } elseif (\array_key_exists('example', $data) && null === $data['example']) {
            $object->setExample(null);
        }

        if (\array_key_exists('examples', $data) && null !== $data['examples']) {
            /** @var ArrayObject<string, \LongTermSupport\OpenApiGenerator\Component\OpenApi3\JsonSchema\Model\Example|\LongTermSupport\OpenApiGenerator\Component\OpenApi3\JsonSchema\Model\Reference> $values_1 */
            $values_1 = new ArrayObject([], ArrayObject::ARRAY_AS_PROPS);
            /** @var array<mixed> $examplesArr */
            $examplesArr = $data['examples'];
            foreach ($examplesArr as $key_1 => $value_2) {
                if (!\is_string($key_1)) {
                    continue;
                }

                $value_3 = $value_2;
                if (\is_array($value_2) && isset($value_2['$ref'])) {
                    $value_3 = $this->denormalizer->denormalize($value_2, \LongTermSupport\OpenApiGenerator\Component\OpenApi3\JsonSchema\Model\Reference::class, 'json', $context);
                } elseif (\is_array($value_2)) {
                    $value_3 = $this->denormalizer->denormalize($value_2, \LongTermSupport\OpenApiGenerator\Component\OpenApi3\JsonSchema\Model\Example::class, 'json', $context);
                }

                /** @var \LongTermSupport\OpenApiGenerator\Component\OpenApi3\JsonSchema\Model\Example|\LongTermSupport\OpenApiGenerator\Component\OpenApi3\JsonSchema\Model\Reference $value_3 */
                $values_1[$key_1] = $value_3;
            }

            $object->setExamples($values_1);
            unset($data['examples']);
        } elseif (\array_key_exists('examples', $data) && null === $data['examples']) {
            $object->setExamples(null);
        }

        foreach ($data as $key_2 => $value_4) {
            if (!\is_string($key_2)) {
                continue;
            }

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
        if (!$object instanceof \LongTermSupport\OpenApiGenerator\Component\OpenApi3\JsonSchema\Model\Header) {
            throw new LogicException('Expected Header, got ' . get_debug_type($object));
        }

        $data = [];
        if ($object->isInitialized('description') && null !== $object->getDescription()) {
            $data['description'] = $object->getDescription();
        }

        if ($object->isInitialized('required') && null !== $object->getRequired()) {
            $data['required'] = $object->getRequired();
        }

        if ($object->isInitialized('deprecated') && null !== $object->getDeprecated()) {
            $data['deprecated'] = $object->getDeprecated();
        }

        if ($object->isInitialized('allowEmptyValue') && null !== $object->getAllowEmptyValue()) {
            $data['allowEmptyValue'] = $object->getAllowEmptyValue();
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

        if ($object->isInitialized('schema') && null !== $object->getSchema()) {
            $value = $object->getSchema();
            if (\is_object($object->getSchema())) {
                $value = $this->normalizer->normalize($object->getSchema(), 'json', $context);
            }

            $data['schema'] = $value;
        }

        if ($object->isInitialized('content') && null !== $object->getContent()) {
            $values = [];
            foreach ($object->getContent() as $key => $value_1) {
                $values[$key] = $this->normalizer->normalize($value_1, 'json', $context);
            }

            $data['content'] = $values;
        }

        if ($object->isInitialized('example') && null !== $object->getExample()) {
            $data['example'] = $object->getExample();
        }

        if ($object->isInitialized('examples') && null !== $object->getExamples()) {
            $values_1 = [];
            foreach ($object->getExamples() as $key_1 => $value_2) {
                $value_3 = $value_2;
                if (\is_object($value_2)) {
                    $value_3 = $this->normalizer->normalize($value_2, 'json', $context);
                }

                $values_1[$key_1] = $value_3;
            }

            $data['examples'] = $values_1;
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
        return [\LongTermSupport\OpenApiGenerator\Component\OpenApi3\JsonSchema\Model\Header::class => false];
    }
}
