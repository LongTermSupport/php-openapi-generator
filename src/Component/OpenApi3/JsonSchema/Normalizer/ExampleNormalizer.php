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

class ExampleNormalizer implements DenormalizerInterface, NormalizerInterface, DenormalizerAwareInterface, NormalizerAwareInterface
{
    use DenormalizerAwareTrait;
    use NormalizerAwareTrait;
    use CheckArray;
    use ValidatorTrait;

    public function supportsDenormalization(mixed $data, string $type, ?string $format = null, array $context = []): bool
    {
        return \LongTermSupport\OpenApiGenerator\Component\OpenApi3\JsonSchema\Model\Example::class === $type;
    }

    public function supportsNormalization(mixed $data, ?string $format = null, array $context = []): bool
    {
        return $data instanceof \LongTermSupport\OpenApiGenerator\Component\OpenApi3\JsonSchema\Model\Example;
    }

    /**
     * @return ($type is class-string<object> ? object : mixed)
     */
    public function denormalize(mixed $data, string $type, ?string $format = null, array $context = []): mixed
    {
        $object = new \LongTermSupport\OpenApiGenerator\Component\OpenApi3\JsonSchema\Model\Example();
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

        if (\array_key_exists('summary', $data) && null !== $data['summary']) {
            $object->setSummary(TypeValidator::assertString($data['summary'], 'summary'));
            unset($data['summary']);
        } elseif (\array_key_exists('summary', $data) && null === $data['summary']) {
            $object->setSummary(null);
        }

        if (\array_key_exists('description', $data) && null !== $data['description']) {
            $object->setDescription(TypeValidator::assertString($data['description'], 'description'));
            unset($data['description']);
        } elseif (\array_key_exists('description', $data) && null === $data['description']) {
            $object->setDescription(null);
        }

        if (\array_key_exists('value', $data) && null !== $data['value']) {
            $object->setValue($data['value']);
            unset($data['value']);
        } elseif (\array_key_exists('value', $data) && null === $data['value']) {
            $object->setValue(null);
        }

        if (\array_key_exists('externalValue', $data) && null !== $data['externalValue']) {
            $object->setExternalValue(TypeValidator::assertString($data['externalValue'], 'externalValue'));
            unset($data['externalValue']);
        } elseif (\array_key_exists('externalValue', $data) && null === $data['externalValue']) {
            $object->setExternalValue(null);
        }

        foreach ($data as $key => $value) {
            if (1 === \Safe\preg_match('/^x-/', $key)) {
                $object[$key] = $value;
            }
        }

        return $object;
    }

    /**
     * @return array<string, mixed>|string|int|float|bool|ArrayObject<string, mixed>|null
     */
    public function normalize(mixed $object, ?string $format = null, array $context = []): array|string|int|float|bool|ArrayObject|null
    {
        if (!$object instanceof \LongTermSupport\OpenApiGenerator\Component\OpenApi3\JsonSchema\Model\Example) {
            throw new LogicException('Expected Example, got ' . get_debug_type($object));
        }

        $data = [];
        if ($object->isInitialized('summary') && null !== $object->getSummary()) {
            $data['summary'] = $object->getSummary();
        }

        if ($object->isInitialized('description') && null !== $object->getDescription()) {
            $data['description'] = $object->getDescription();
        }

        if ($object->isInitialized('value') && null !== $object->getValue()) {
            $data['value'] = $object->getValue();
        }

        if ($object->isInitialized('externalValue') && null !== $object->getExternalValue()) {
            $data['externalValue'] = $object->getExternalValue();
        }

        foreach ($object as $key => $value) {
            if (1 === \Safe\preg_match('/^x-/', $key)) {
                $data[$key] = $value;
            }
        }

        return $data;
    }

    public function getSupportedTypes(?string $format = null): array
    {
        return [\LongTermSupport\OpenApiGenerator\Component\OpenApi3\JsonSchema\Model\Example::class => false];
    }
}
