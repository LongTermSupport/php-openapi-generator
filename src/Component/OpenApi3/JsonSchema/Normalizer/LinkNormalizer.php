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

class LinkNormalizer implements DenormalizerInterface, NormalizerInterface, DenormalizerAwareInterface, NormalizerAwareInterface
{
    use DenormalizerAwareTrait;
    use NormalizerAwareTrait;
    use CheckArray;
    use ValidatorTrait;

    public function supportsDenormalization(mixed $data, string $type, ?string $format = null, array $context = []): bool
    {
        return \LongTermSupport\OpenApiGenerator\Component\OpenApi3\JsonSchema\Model\Link::class === $type;
    }

    public function supportsNormalization(mixed $data, ?string $format = null, array $context = []): bool
    {
        return $data instanceof \LongTermSupport\OpenApiGenerator\Component\OpenApi3\JsonSchema\Model\Link;
    }

    /**
     * @return ($type is class-string<object> ? object : mixed)
     */
    public function denormalize(mixed $data, string $type, ?string $format = null, array $context = []): mixed
    {
        $object = new \LongTermSupport\OpenApiGenerator\Component\OpenApi3\JsonSchema\Model\Link();
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

        if (\array_key_exists('operationId', $data) && null !== $data['operationId']) {
            $object->setOperationId(TypeValidator::assertString($data['operationId'], 'operationId'));
            unset($data['operationId']);
        } elseif (\array_key_exists('operationId', $data) && null === $data['operationId']) {
            $object->setOperationId(null);
        }

        if (\array_key_exists('operationRef', $data) && null !== $data['operationRef']) {
            $object->setOperationRef(TypeValidator::assertString($data['operationRef'], 'operationRef'));
            unset($data['operationRef']);
        } elseif (\array_key_exists('operationRef', $data) && null === $data['operationRef']) {
            $object->setOperationRef(null);
        }

        if (\array_key_exists('parameters', $data) && null !== $data['parameters']) {
            $values = new ArrayObject([], ArrayObject::ARRAY_AS_PROPS);
            if (\is_array($data['parameters'])) {
                foreach ($data['parameters'] as $key => $value) {
                    $values[$key] = $value;
                }
            }

            $object->setParameters($values);
            unset($data['parameters']);
        } elseif (\array_key_exists('parameters', $data) && null === $data['parameters']) {
            $object->setParameters(null);
        }

        if (\array_key_exists('requestBody', $data) && null !== $data['requestBody']) {
            $object->setRequestBody($data['requestBody']);
            unset($data['requestBody']);
        } elseif (\array_key_exists('requestBody', $data) && null === $data['requestBody']) {
            $object->setRequestBody(null);
        }

        if (\array_key_exists('description', $data) && null !== $data['description']) {
            $object->setDescription(TypeValidator::assertString($data['description'], 'description'));
            unset($data['description']);
        } elseif (\array_key_exists('description', $data) && null === $data['description']) {
            $object->setDescription(null);
        }

        if (\array_key_exists('server', $data) && null !== $data['server']) {
            $object->setServer($this->denormalizer->denormalize($data['server'], \LongTermSupport\OpenApiGenerator\Component\OpenApi3\JsonSchema\Model\Server::class, 'json', $context));
            unset($data['server']);
        } elseif (\array_key_exists('server', $data) && null === $data['server']) {
            $object->setServer(null);
        }

        foreach ($data as $key_1 => $value_1) {
            if (!\is_string($key_1)) {
                continue;
            }

            if (1 === \Safe\preg_match('/^x-/', $key_1)) {
                $object[$key_1] = $value_1;
            }
        }

        return $object;
    }

    /**
     * @return array<string, mixed>|string|int|float|bool|ArrayObject<string, mixed>|null
     */
    public function normalize(mixed $object, ?string $format = null, array $context = []): array|string|int|float|bool|ArrayObject|null
    {
        if (!$object instanceof \LongTermSupport\OpenApiGenerator\Component\OpenApi3\JsonSchema\Model\Link) {
            throw new LogicException('Expected Link, got ' . get_debug_type($object));
        }

        $data = [];
        if ($object->isInitialized('operationId') && null !== $object->getOperationId()) {
            $data['operationId'] = $object->getOperationId();
        }

        if ($object->isInitialized('operationRef') && null !== $object->getOperationRef()) {
            $data['operationRef'] = $object->getOperationRef();
        }

        if ($object->isInitialized('parameters') && null !== $object->getParameters()) {
            $values = [];
            foreach ($object->getParameters() as $key => $value) {
                $values[$key] = $value;
            }

            $data['parameters'] = $values;
        }

        if ($object->isInitialized('requestBody') && null !== $object->getRequestBody()) {
            $data['requestBody'] = $object->getRequestBody();
        }

        if ($object->isInitialized('description') && null !== $object->getDescription()) {
            $data['description'] = $object->getDescription();
        }

        if ($object->isInitialized('server') && $object->getServer() instanceof \LongTermSupport\OpenApiGenerator\Component\OpenApi3\JsonSchema\Model\Server) {
            $data['server'] = $this->normalizer->normalize($object->getServer(), 'json', $context);
        }

        foreach ($object as $key_1 => $value_1) {
            if (1 === \Safe\preg_match('/^x-/', $key_1)) {
                $data[$key_1] = $value_1;
            }
        }

        return $data;
    }

    public function getSupportedTypes(?string $format = null): array
    {
        return [\LongTermSupport\OpenApiGenerator\Component\OpenApi3\JsonSchema\Model\Link::class => false];
    }
}
