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

class HTTPSecuritySchemeNormalizer implements DenormalizerInterface, NormalizerInterface, DenormalizerAwareInterface, NormalizerAwareInterface
{
    use DenormalizerAwareTrait;
    use NormalizerAwareTrait;
    use CheckArray;
    use ValidatorTrait;

    public function supportsDenormalization(mixed $data, string $type, ?string $format = null, array $context = []): bool
    {
        return \LongTermSupport\OpenApiGenerator\Component\OpenApi3\JsonSchema\Model\HTTPSecurityScheme::class === $type;
    }

    public function supportsNormalization(mixed $data, ?string $format = null, array $context = []): bool
    {
        return $data instanceof \LongTermSupport\OpenApiGenerator\Component\OpenApi3\JsonSchema\Model\HTTPSecurityScheme;
    }

    /**
     * @return ($type is class-string<object> ? object : mixed)
     */
    public function denormalize(mixed $data, string $type, ?string $format = null, array $context = []): mixed
    {
        $object = new \LongTermSupport\OpenApiGenerator\Component\OpenApi3\JsonSchema\Model\HTTPSecurityScheme();
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

        if (\array_key_exists('scheme', $data) && null !== $data['scheme']) {
            $object->setScheme(TypeValidator::assertString($data['scheme'], 'scheme'));
            unset($data['scheme']);
        } elseif (\array_key_exists('scheme', $data) && null === $data['scheme']) {
            $object->setScheme(null);
        }

        if (\array_key_exists('bearerFormat', $data) && null !== $data['bearerFormat']) {
            $object->setBearerFormat(TypeValidator::assertString($data['bearerFormat'], 'bearerFormat'));
            unset($data['bearerFormat']);
        } elseif (\array_key_exists('bearerFormat', $data) && null === $data['bearerFormat']) {
            $object->setBearerFormat(null);
        }

        if (\array_key_exists('description', $data) && null !== $data['description']) {
            $object->setDescription(TypeValidator::assertString($data['description'], 'description'));
            unset($data['description']);
        } elseif (\array_key_exists('description', $data) && null === $data['description']) {
            $object->setDescription(null);
        }

        if (\array_key_exists('type', $data) && null !== $data['type']) {
            $object->setType(TypeValidator::assertString($data['type'], 'type'));
            unset($data['type']);
        } elseif (\array_key_exists('type', $data) && null === $data['type']) {
            $object->setType(null);
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
        if (!$object instanceof \LongTermSupport\OpenApiGenerator\Component\OpenApi3\JsonSchema\Model\HTTPSecurityScheme) {
            throw new LogicException('Expected HTTPSecurityScheme, got ' . get_debug_type($object));
        }

        $data           = [];
        $data['scheme'] = $object->getScheme();
        if ($object->isInitialized('bearerFormat') && null !== $object->getBearerFormat()) {
            $data['bearerFormat'] = $object->getBearerFormat();
        }

        if ($object->isInitialized('description') && null !== $object->getDescription()) {
            $data['description'] = $object->getDescription();
        }

        $data['type'] = $object->getType();
        foreach ($object as $key => $value) {
            if (0 !== \Safe\preg_match('/^x-/', $key)) {
                $data[$key] = $value;
            }
        }

        return $data;
    }

    public function getSupportedTypes(?string $format = null): array
    {
        return [\LongTermSupport\OpenApiGenerator\Component\OpenApi3\JsonSchema\Model\HTTPSecurityScheme::class => false];
    }
}
