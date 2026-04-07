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

class AuthorizationCodeOAuthFlowNormalizer implements DenormalizerInterface, NormalizerInterface, DenormalizerAwareInterface, NormalizerAwareInterface
{
    use DenormalizerAwareTrait;
    use NormalizerAwareTrait;
    use CheckArray;
    use ValidatorTrait;

    public function supportsDenormalization(mixed $data, string $type, ?string $format = null, array $context = []): bool
    {
        return \LongTermSupport\OpenApiGenerator\Component\OpenApi3\JsonSchema\Model\AuthorizationCodeOAuthFlow::class === $type;
    }

    public function supportsNormalization(mixed $data, ?string $format = null, array $context = []): bool
    {
        return $data instanceof \LongTermSupport\OpenApiGenerator\Component\OpenApi3\JsonSchema\Model\AuthorizationCodeOAuthFlow;
    }

    /**
     * @return ($type is class-string<object> ? object : mixed)
     */
    public function denormalize(mixed $data, string $type, ?string $format = null, array $context = []): mixed
    {
        $object = new \LongTermSupport\OpenApiGenerator\Component\OpenApi3\JsonSchema\Model\AuthorizationCodeOAuthFlow();
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

        if (\array_key_exists('authorizationUrl', $data) && null !== $data['authorizationUrl']) {
            $object->setAuthorizationUrl(TypeValidator::assertString($data['authorizationUrl'], 'authorizationUrl'));
            unset($data['authorizationUrl']);
        } elseif (\array_key_exists('authorizationUrl', $data) && null === $data['authorizationUrl']) {
            $object->setAuthorizationUrl(null);
        }

        if (\array_key_exists('tokenUrl', $data) && null !== $data['tokenUrl']) {
            $object->setTokenUrl(TypeValidator::assertString($data['tokenUrl'], 'tokenUrl'));
            unset($data['tokenUrl']);
        } elseif (\array_key_exists('tokenUrl', $data) && null === $data['tokenUrl']) {
            $object->setTokenUrl(null);
        }

        if (\array_key_exists('refreshUrl', $data) && null !== $data['refreshUrl']) {
            $object->setRefreshUrl(TypeValidator::assertString($data['refreshUrl'], 'refreshUrl'));
            unset($data['refreshUrl']);
        } elseif (\array_key_exists('refreshUrl', $data) && null === $data['refreshUrl']) {
            $object->setRefreshUrl(null);
        }

        if (\array_key_exists('scopes', $data) && null !== $data['scopes']) {
            $values = new ArrayObject([], ArrayObject::ARRAY_AS_PROPS);
            if (\is_array($data['scopes'])) {
                foreach ($data['scopes'] as $key => $value) {
                    $values[$key] = $value;
                }
            }

            $object->setScopes($values);
            unset($data['scopes']);
        } elseif (\array_key_exists('scopes', $data) && null === $data['scopes']) {
            $object->setScopes(null);
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
        if (!$object instanceof \LongTermSupport\OpenApiGenerator\Component\OpenApi3\JsonSchema\Model\AuthorizationCodeOAuthFlow) {
            throw new LogicException('Expected AuthorizationCodeOAuthFlow, got ' . get_debug_type($object));
        }

        $data                     = [];
        $data['authorizationUrl'] = $object->getAuthorizationUrl();
        $data['tokenUrl']         = $object->getTokenUrl();
        if ($object->isInitialized('refreshUrl') && null !== $object->getRefreshUrl()) {
            $data['refreshUrl'] = $object->getRefreshUrl();
        }

        if ($object->isInitialized('scopes') && null !== $object->getScopes()) {
            $values = [];
            foreach ($object->getScopes() as $key => $value) {
                $values[$key] = $value;
            }

            $data['scopes'] = $values;
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
        return [\LongTermSupport\OpenApiGenerator\Component\OpenApi3\JsonSchema\Model\AuthorizationCodeOAuthFlow::class => false];
    }
}
