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

class XMLNormalizer implements DenormalizerInterface, NormalizerInterface, DenormalizerAwareInterface, NormalizerAwareInterface
{
    use DenormalizerAwareTrait;
    use NormalizerAwareTrait;
    use CheckArray;
    use ValidatorTrait;

    public function supportsDenormalization(mixed $data, string $type, ?string $format = null, array $context = []): bool
    {
        return \LongTermSupport\OpenApiGenerator\Component\OpenApi3\JsonSchema\Model\XML::class === $type;
    }

    public function supportsNormalization(mixed $data, ?string $format = null, array $context = []): bool
    {
        return $data instanceof \LongTermSupport\OpenApiGenerator\Component\OpenApi3\JsonSchema\Model\XML;
    }

    /**
     * @return ($type is class-string<object> ? object : mixed)
     */
    public function denormalize(mixed $data, string $type, ?string $format = null, array $context = []): mixed
    {
        $object = new \LongTermSupport\OpenApiGenerator\Component\OpenApi3\JsonSchema\Model\XML();
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

        if (\array_key_exists('name', $data) && null !== $data['name']) {
            $object->setName(TypeValidator::assertString($data['name'], 'name'));
            unset($data['name']);
        } elseif (\array_key_exists('name', $data) && null === $data['name']) {
            $object->setName(null);
        }

        if (\array_key_exists('namespace', $data) && null !== $data['namespace']) {
            $object->setNamespace(TypeValidator::assertString($data['namespace'], 'namespace'));
            unset($data['namespace']);
        } elseif (\array_key_exists('namespace', $data) && null === $data['namespace']) {
            $object->setNamespace(null);
        }

        if (\array_key_exists('prefix', $data) && null !== $data['prefix']) {
            $object->setPrefix(TypeValidator::assertString($data['prefix'], 'prefix'));
            unset($data['prefix']);
        } elseif (\array_key_exists('prefix', $data) && null === $data['prefix']) {
            $object->setPrefix(null);
        }

        if (\array_key_exists('attribute', $data) && null !== $data['attribute']) {
            $object->setAttribute(TypeValidator::assertBool($data['attribute'], 'attribute'));
            unset($data['attribute']);
        } elseif (\array_key_exists('attribute', $data) && null === $data['attribute']) {
            $object->setAttribute(null);
        }

        if (\array_key_exists('wrapped', $data) && null !== $data['wrapped']) {
            $object->setWrapped(TypeValidator::assertBool($data['wrapped'], 'wrapped'));
            unset($data['wrapped']);
        } elseif (\array_key_exists('wrapped', $data) && null === $data['wrapped']) {
            $object->setWrapped(null);
        }

        foreach ($data as $key => $value) {
            if (0 !== \Safe\preg_match('/^x-/', $key)) {
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
        if (!$object instanceof \LongTermSupport\OpenApiGenerator\Component\OpenApi3\JsonSchema\Model\XML) {
            throw new LogicException('Expected XML, got ' . get_debug_type($object));
        }

        $data = [];
        if ($object->isInitialized('name') && null !== $object->getName()) {
            $data['name'] = $object->getName();
        }

        if ($object->isInitialized('namespace') && null !== $object->getNamespace()) {
            $data['namespace'] = $object->getNamespace();
        }

        if ($object->isInitialized('prefix') && null !== $object->getPrefix()) {
            $data['prefix'] = $object->getPrefix();
        }

        if ($object->isInitialized('attribute') && null !== $object->getAttribute()) {
            $data['attribute'] = $object->getAttribute();
        }

        if ($object->isInitialized('wrapped') && null !== $object->getWrapped()) {
            $data['wrapped'] = $object->getWrapped();
        }

        foreach ($object as $key => $value) {
            if (0 !== \Safe\preg_match('/^x-/', $key)) {
                $data[$key] = $value;
            }
        }

        return $data;
    }

    public function getSupportedTypes(?string $format = null): array
    {
        return [\LongTermSupport\OpenApiGenerator\Component\OpenApi3\JsonSchema\Model\XML::class => false];
    }
}
