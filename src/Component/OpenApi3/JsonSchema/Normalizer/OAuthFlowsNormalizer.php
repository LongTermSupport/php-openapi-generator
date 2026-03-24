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

class OAuthFlowsNormalizer implements DenormalizerInterface, NormalizerInterface, DenormalizerAwareInterface, NormalizerAwareInterface
{
    use DenormalizerAwareTrait;
    use NormalizerAwareTrait;
    use CheckArray;
    use ValidatorTrait;

    public function supportsDenormalization(mixed $data, string $type, ?string $format = null, array $context = []): bool
    {
        return \LongTermSupport\OpenApiGenerator\Component\OpenApi3\JsonSchema\Model\OAuthFlows::class === $type;
    }

    public function supportsNormalization(mixed $data, ?string $format = null, array $context = []): bool
    {
        return $data instanceof \LongTermSupport\OpenApiGenerator\Component\OpenApi3\JsonSchema\Model\OAuthFlows;
    }

    /**
     * @return object
     */
    public function denormalize(mixed $data, string $type, ?string $format = null, array $context = []): mixed
    {
        $object = new \LongTermSupport\OpenApiGenerator\Component\OpenApi3\JsonSchema\Model\OAuthFlows();
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

        if (\array_key_exists('implicit', $data) && null !== $data['implicit']) {
            $object->setImplicit($this->denormalizer->denormalize($data['implicit'], \LongTermSupport\OpenApiGenerator\Component\OpenApi3\JsonSchema\Model\ImplicitOAuthFlow::class, 'json', $context));
            unset($data['implicit']);
        } elseif (\array_key_exists('implicit', $data) && null === $data['implicit']) {
            $object->setImplicit(null);
        }

        if (\array_key_exists('password', $data) && null !== $data['password']) {
            $object->setPassword($this->denormalizer->denormalize($data['password'], \LongTermSupport\OpenApiGenerator\Component\OpenApi3\JsonSchema\Model\PasswordOAuthFlow::class, 'json', $context));
            unset($data['password']);
        } elseif (\array_key_exists('password', $data) && null === $data['password']) {
            $object->setPassword(null);
        }

        if (\array_key_exists('clientCredentials', $data) && null !== $data['clientCredentials']) {
            $object->setClientCredentials($this->denormalizer->denormalize($data['clientCredentials'], \LongTermSupport\OpenApiGenerator\Component\OpenApi3\JsonSchema\Model\ClientCredentialsFlow::class, 'json', $context));
            unset($data['clientCredentials']);
        } elseif (\array_key_exists('clientCredentials', $data) && null === $data['clientCredentials']) {
            $object->setClientCredentials(null);
        }

        if (\array_key_exists('authorizationCode', $data) && null !== $data['authorizationCode']) {
            $object->setAuthorizationCode($this->denormalizer->denormalize($data['authorizationCode'], \LongTermSupport\OpenApiGenerator\Component\OpenApi3\JsonSchema\Model\AuthorizationCodeOAuthFlow::class, 'json', $context));
            unset($data['authorizationCode']);
        } elseif (\array_key_exists('authorizationCode', $data) && null === $data['authorizationCode']) {
            $object->setAuthorizationCode(null);
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
        if (!$object instanceof \LongTermSupport\OpenApiGenerator\Component\OpenApi3\JsonSchema\Model\OAuthFlows) {
            throw new LogicException('Expected OAuthFlows, got ' . get_debug_type($object));
        }

        $data = [];
        if ($object->isInitialized('implicit') && $object->getImplicit() instanceof \LongTermSupport\OpenApiGenerator\Component\OpenApi3\JsonSchema\Model\ImplicitOAuthFlow) {
            $data['implicit'] = $this->normalizer->normalize($object->getImplicit(), 'json', $context);
        }

        if ($object->isInitialized('password') && $object->getPassword() instanceof \LongTermSupport\OpenApiGenerator\Component\OpenApi3\JsonSchema\Model\PasswordOAuthFlow) {
            $data['password'] = $this->normalizer->normalize($object->getPassword(), 'json', $context);
        }

        if ($object->isInitialized('clientCredentials') && $object->getClientCredentials() instanceof \LongTermSupport\OpenApiGenerator\Component\OpenApi3\JsonSchema\Model\ClientCredentialsFlow) {
            $data['clientCredentials'] = $this->normalizer->normalize($object->getClientCredentials(), 'json', $context);
        }

        if ($object->isInitialized('authorizationCode') && $object->getAuthorizationCode() instanceof \LongTermSupport\OpenApiGenerator\Component\OpenApi3\JsonSchema\Model\AuthorizationCodeOAuthFlow) {
            $data['authorizationCode'] = $this->normalizer->normalize($object->getAuthorizationCode(), 'json', $context);
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
        return [\LongTermSupport\OpenApiGenerator\Component\OpenApi3\JsonSchema\Model\OAuthFlows::class => false];
    }
}
