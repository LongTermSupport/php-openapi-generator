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

class InfoNormalizer implements DenormalizerInterface, NormalizerInterface, DenormalizerAwareInterface, NormalizerAwareInterface
{
    use DenormalizerAwareTrait;
    use NormalizerAwareTrait;
    use CheckArray;
    use ValidatorTrait;

    public function supportsDenormalization(mixed $data, string $type, ?string $format = null, array $context = []): bool
    {
        return \LongTermSupport\OpenApiGenerator\Component\OpenApi3\JsonSchema\Model\Info::class === $type;
    }

    public function supportsNormalization(mixed $data, ?string $format = null, array $context = []): bool
    {
        return $data instanceof \LongTermSupport\OpenApiGenerator\Component\OpenApi3\JsonSchema\Model\Info;
    }

    /**
     * @return object
     */
    public function denormalize(mixed $data, string $type, ?string $format = null, array $context = []): mixed
    {
        $object = new \LongTermSupport\OpenApiGenerator\Component\OpenApi3\JsonSchema\Model\Info();
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

        if (\array_key_exists('title', $data) && null !== $data['title']) {
            $object->setTitle(TypeValidator::assertString($data['title'], 'title'));
            unset($data['title']);
        } elseif (\array_key_exists('title', $data) && null === $data['title']) {
            $object->setTitle(null);
        }

        if (\array_key_exists('description', $data) && null !== $data['description']) {
            $object->setDescription(TypeValidator::assertString($data['description'], 'description'));
            unset($data['description']);
        } elseif (\array_key_exists('description', $data) && null === $data['description']) {
            $object->setDescription(null);
        }

        if (\array_key_exists('termsOfService', $data) && null !== $data['termsOfService']) {
            $object->setTermsOfService(TypeValidator::assertString($data['termsOfService'], 'termsOfService'));
            unset($data['termsOfService']);
        } elseif (\array_key_exists('termsOfService', $data) && null === $data['termsOfService']) {
            $object->setTermsOfService(null);
        }

        if (\array_key_exists('contact', $data) && null !== $data['contact']) {
            $object->setContact($this->denormalizer->denormalize($data['contact'], \LongTermSupport\OpenApiGenerator\Component\OpenApi3\JsonSchema\Model\Contact::class, 'json', $context));
            unset($data['contact']);
        } elseif (\array_key_exists('contact', $data) && null === $data['contact']) {
            $object->setContact(null);
        }

        if (\array_key_exists('license', $data) && null !== $data['license']) {
            $object->setLicense($this->denormalizer->denormalize($data['license'], \LongTermSupport\OpenApiGenerator\Component\OpenApi3\JsonSchema\Model\License::class, 'json', $context));
            unset($data['license']);
        } elseif (\array_key_exists('license', $data) && null === $data['license']) {
            $object->setLicense(null);
        }

        if (\array_key_exists('version', $data) && null !== $data['version']) {
            $object->setVersion(TypeValidator::assertString($data['version'], 'version'));
            unset($data['version']);
        } elseif (\array_key_exists('version', $data) && null === $data['version']) {
            $object->setVersion(null);
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
        if (!$object instanceof \LongTermSupport\OpenApiGenerator\Component\OpenApi3\JsonSchema\Model\Info) {
            throw new LogicException('Expected Info, got ' . get_debug_type($object));
        }

        $data          = [];
        $data['title'] = $object->getTitle();
        if ($object->isInitialized('description') && null !== $object->getDescription()) {
            $data['description'] = $object->getDescription();
        }

        if ($object->isInitialized('termsOfService') && null !== $object->getTermsOfService()) {
            $data['termsOfService'] = $object->getTermsOfService();
        }

        if ($object->isInitialized('contact') && $object->getContact() instanceof \LongTermSupport\OpenApiGenerator\Component\OpenApi3\JsonSchema\Model\Contact) {
            $data['contact'] = $this->normalizer->normalize($object->getContact(), 'json', $context);
        }

        if ($object->isInitialized('license') && $object->getLicense() instanceof \LongTermSupport\OpenApiGenerator\Component\OpenApi3\JsonSchema\Model\License) {
            $data['license'] = $this->normalizer->normalize($object->getLicense(), 'json', $context);
        }

        $data['version'] = $object->getVersion();
        foreach ($object as $key => $value) {
            if (1 === \Safe\preg_match('/^x-/', $key)) {
                $data[$key] = $value;
            }
        }

        return $data;
    }

    public function getSupportedTypes(?string $format = null): array
    {
        return [\LongTermSupport\OpenApiGenerator\Component\OpenApi3\JsonSchema\Model\Info::class => false];
    }
}
