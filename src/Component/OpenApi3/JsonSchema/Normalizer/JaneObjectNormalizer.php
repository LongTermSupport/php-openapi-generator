<?php

declare(strict_types=1);

namespace LongTermSupport\OpenApiGenerator\Component\OpenApi3\JsonSchema\Normalizer;

use ArrayObject;
use LogicException;
use LongTermSupport\OpenApiGenerator\Component\OpenApi3\JsonSchema\Runtime\Normalizer\CheckArray;
use LongTermSupport\OpenApiGenerator\Component\OpenApi3\JsonSchema\Runtime\Normalizer\ValidatorTrait;
use Symfony\Component\Serializer\Normalizer\DenormalizerAwareInterface;
use Symfony\Component\Serializer\Normalizer\DenormalizerAwareTrait;
use Symfony\Component\Serializer\Normalizer\DenormalizerInterface;
use Symfony\Component\Serializer\Normalizer\NormalizerAwareInterface;
use Symfony\Component\Serializer\Normalizer\NormalizerAwareTrait;
use Symfony\Component\Serializer\Normalizer\NormalizerInterface;

class JaneObjectNormalizer implements DenormalizerInterface, NormalizerInterface, DenormalizerAwareInterface, NormalizerAwareInterface
{
    use DenormalizerAwareTrait;
    use NormalizerAwareTrait;
    use CheckArray;
    use ValidatorTrait;

    /** @var array<class-string, class-string> */
    protected array $normalizers = [\LongTermSupport\OpenApiGenerator\Component\OpenApi3\JsonSchema\Model\OpenApi::class => OpenApiNormalizer::class, \LongTermSupport\OpenApiGenerator\Component\OpenApi3\JsonSchema\Model\Reference::class => ReferenceNormalizer::class, \LongTermSupport\OpenApiGenerator\Component\OpenApi3\JsonSchema\Model\Info::class => InfoNormalizer::class, \LongTermSupport\OpenApiGenerator\Component\OpenApi3\JsonSchema\Model\Contact::class => ContactNormalizer::class, \LongTermSupport\OpenApiGenerator\Component\OpenApi3\JsonSchema\Model\License::class => LicenseNormalizer::class, \LongTermSupport\OpenApiGenerator\Component\OpenApi3\JsonSchema\Model\Server::class => ServerNormalizer::class, \LongTermSupport\OpenApiGenerator\Component\OpenApi3\JsonSchema\Model\ServerVariable::class => ServerVariableNormalizer::class, \LongTermSupport\OpenApiGenerator\Component\OpenApi3\JsonSchema\Model\Components::class => ComponentsNormalizer::class, \LongTermSupport\OpenApiGenerator\Component\OpenApi3\JsonSchema\Model\Schema::class => SchemaNormalizer::class, \LongTermSupport\OpenApiGenerator\Component\OpenApi3\JsonSchema\Model\Discriminator::class => DiscriminatorNormalizer::class, \LongTermSupport\OpenApiGenerator\Component\OpenApi3\JsonSchema\Model\XML::class => XMLNormalizer::class, \LongTermSupport\OpenApiGenerator\Component\OpenApi3\JsonSchema\Model\Response::class => ResponseNormalizer::class, \LongTermSupport\OpenApiGenerator\Component\OpenApi3\JsonSchema\Model\MediaType::class => MediaTypeNormalizer::class, \LongTermSupport\OpenApiGenerator\Component\OpenApi3\JsonSchema\Model\Example::class => ExampleNormalizer::class, \LongTermSupport\OpenApiGenerator\Component\OpenApi3\JsonSchema\Model\Header::class => HeaderNormalizer::class, \LongTermSupport\OpenApiGenerator\Component\OpenApi3\JsonSchema\Model\PathItem::class => PathItemNormalizer::class, \LongTermSupport\OpenApiGenerator\Component\OpenApi3\JsonSchema\Model\Operation::class => OperationNormalizer::class, \LongTermSupport\OpenApiGenerator\Component\OpenApi3\JsonSchema\Model\Responses::class => ResponsesNormalizer::class, \LongTermSupport\OpenApiGenerator\Component\OpenApi3\JsonSchema\Model\Tag::class => TagNormalizer::class, \LongTermSupport\OpenApiGenerator\Component\OpenApi3\JsonSchema\Model\ExternalDocumentation::class => ExternalDocumentationNormalizer::class, \LongTermSupport\OpenApiGenerator\Component\OpenApi3\JsonSchema\Model\Parameter::class => ParameterNormalizer::class, \LongTermSupport\OpenApiGenerator\Component\OpenApi3\JsonSchema\Model\RequestBody::class => RequestBodyNormalizer::class, \LongTermSupport\OpenApiGenerator\Component\OpenApi3\JsonSchema\Model\APIKeySecurityScheme::class => APIKeySecuritySchemeNormalizer::class, \LongTermSupport\OpenApiGenerator\Component\OpenApi3\JsonSchema\Model\HTTPSecurityScheme::class => HTTPSecuritySchemeNormalizer::class, \LongTermSupport\OpenApiGenerator\Component\OpenApi3\JsonSchema\Model\HTTPSecuritySchemeSub::class => HTTPSecuritySchemeSubNormalizer::class, \LongTermSupport\OpenApiGenerator\Component\OpenApi3\JsonSchema\Model\OAuth2SecurityScheme::class => OAuth2SecuritySchemeNormalizer::class, \LongTermSupport\OpenApiGenerator\Component\OpenApi3\JsonSchema\Model\OpenIdConnectSecurityScheme::class => OpenIdConnectSecuritySchemeNormalizer::class, \LongTermSupport\OpenApiGenerator\Component\OpenApi3\JsonSchema\Model\OAuthFlows::class => OAuthFlowsNormalizer::class, \LongTermSupport\OpenApiGenerator\Component\OpenApi3\JsonSchema\Model\ImplicitOAuthFlow::class => ImplicitOAuthFlowNormalizer::class, \LongTermSupport\OpenApiGenerator\Component\OpenApi3\JsonSchema\Model\PasswordOAuthFlow::class => PasswordOAuthFlowNormalizer::class, \LongTermSupport\OpenApiGenerator\Component\OpenApi3\JsonSchema\Model\ClientCredentialsFlow::class => ClientCredentialsFlowNormalizer::class, \LongTermSupport\OpenApiGenerator\Component\OpenApi3\JsonSchema\Model\AuthorizationCodeOAuthFlow::class => AuthorizationCodeOAuthFlowNormalizer::class, \LongTermSupport\OpenApiGenerator\Component\OpenApi3\JsonSchema\Model\Link::class => LinkNormalizer::class, \LongTermSupport\OpenApiGenerator\Component\OpenApi3\JsonSchema\Model\Encoding::class => EncodingNormalizer::class, \LongTermSupport\OpenApiGenerator\Component\OpenApiRuntime\Reference::class => \LongTermSupport\OpenApiGenerator\Component\OpenApi3\JsonSchema\Runtime\Normalizer\ReferenceNormalizer::class];

    /** @var array<class-string, object> */
    protected array $normalizersCache = [];

    public function supportsDenormalization(mixed $data, string $type, ?string $format = null, array $context = []): bool
    {
        return \array_key_exists($type, $this->normalizers);
    }

    public function supportsNormalization(mixed $data, ?string $format = null, array $context = []): bool
    {
        return \is_object($data) && \array_key_exists($data::class, $this->normalizers);
    }

    /**
     * @return array<mixed>|string|int|float|bool|ArrayObject<string, mixed>|null
     */
    public function normalize(mixed $data, ?string $format = null, array $context = []): array|string|int|float|bool|ArrayObject|null
    {
        if (!\is_object($data)) {
            throw new LogicException('Expected object, got ' . get_debug_type($data));
        }

        $normalizerClass = $this->normalizers[$data::class];
        $normalizer      = $this->getNormalizer($normalizerClass);
        if (!$normalizer instanceof NormalizerInterface) {
            throw new LogicException('Expected NormalizerInterface, got ' . get_debug_type($normalizer));
        }

        return $normalizer->normalize($data, $format, $context);
    }

    /**
     * @return ($type is class-string<object> ? object : mixed)
     */
    public function denormalize(mixed $data, string $type, ?string $format = null, array $context = []): mixed
    {
        $denormalizerClass = $this->normalizers[$type];
        $denormalizer      = $this->getNormalizer($denormalizerClass);
        if (!$denormalizer instanceof DenormalizerInterface) {
            throw new LogicException('Expected DenormalizerInterface, got ' . get_debug_type($denormalizer));
        }

        return $denormalizer->denormalize($data, $type, $format, $context);
    }

    public function getSupportedTypes(?string $format = null): array
    {
        return [\LongTermSupport\OpenApiGenerator\Component\OpenApi3\JsonSchema\Model\OpenApi::class => false, \LongTermSupport\OpenApiGenerator\Component\OpenApi3\JsonSchema\Model\Reference::class => false, \LongTermSupport\OpenApiGenerator\Component\OpenApi3\JsonSchema\Model\Info::class => false, \LongTermSupport\OpenApiGenerator\Component\OpenApi3\JsonSchema\Model\Contact::class => false, \LongTermSupport\OpenApiGenerator\Component\OpenApi3\JsonSchema\Model\License::class => false, \LongTermSupport\OpenApiGenerator\Component\OpenApi3\JsonSchema\Model\Server::class => false, \LongTermSupport\OpenApiGenerator\Component\OpenApi3\JsonSchema\Model\ServerVariable::class => false, \LongTermSupport\OpenApiGenerator\Component\OpenApi3\JsonSchema\Model\Components::class => false, \LongTermSupport\OpenApiGenerator\Component\OpenApi3\JsonSchema\Model\Schema::class => false, \LongTermSupport\OpenApiGenerator\Component\OpenApi3\JsonSchema\Model\Discriminator::class => false, \LongTermSupport\OpenApiGenerator\Component\OpenApi3\JsonSchema\Model\XML::class => false, \LongTermSupport\OpenApiGenerator\Component\OpenApi3\JsonSchema\Model\Response::class => false, \LongTermSupport\OpenApiGenerator\Component\OpenApi3\JsonSchema\Model\MediaType::class => false, \LongTermSupport\OpenApiGenerator\Component\OpenApi3\JsonSchema\Model\Example::class => false, \LongTermSupport\OpenApiGenerator\Component\OpenApi3\JsonSchema\Model\Header::class => false, \LongTermSupport\OpenApiGenerator\Component\OpenApi3\JsonSchema\Model\PathItem::class => false, \LongTermSupport\OpenApiGenerator\Component\OpenApi3\JsonSchema\Model\Operation::class => false, \LongTermSupport\OpenApiGenerator\Component\OpenApi3\JsonSchema\Model\Responses::class => false, \LongTermSupport\OpenApiGenerator\Component\OpenApi3\JsonSchema\Model\Tag::class => false, \LongTermSupport\OpenApiGenerator\Component\OpenApi3\JsonSchema\Model\ExternalDocumentation::class => false, \LongTermSupport\OpenApiGenerator\Component\OpenApi3\JsonSchema\Model\Parameter::class => false, \LongTermSupport\OpenApiGenerator\Component\OpenApi3\JsonSchema\Model\RequestBody::class => false, \LongTermSupport\OpenApiGenerator\Component\OpenApi3\JsonSchema\Model\APIKeySecurityScheme::class => false, \LongTermSupport\OpenApiGenerator\Component\OpenApi3\JsonSchema\Model\HTTPSecurityScheme::class => false, \LongTermSupport\OpenApiGenerator\Component\OpenApi3\JsonSchema\Model\HTTPSecuritySchemeSub::class => false, \LongTermSupport\OpenApiGenerator\Component\OpenApi3\JsonSchema\Model\OAuth2SecurityScheme::class => false, \LongTermSupport\OpenApiGenerator\Component\OpenApi3\JsonSchema\Model\OpenIdConnectSecurityScheme::class => false, \LongTermSupport\OpenApiGenerator\Component\OpenApi3\JsonSchema\Model\OAuthFlows::class => false, \LongTermSupport\OpenApiGenerator\Component\OpenApi3\JsonSchema\Model\ImplicitOAuthFlow::class => false, \LongTermSupport\OpenApiGenerator\Component\OpenApi3\JsonSchema\Model\PasswordOAuthFlow::class => false, \LongTermSupport\OpenApiGenerator\Component\OpenApi3\JsonSchema\Model\ClientCredentialsFlow::class => false, \LongTermSupport\OpenApiGenerator\Component\OpenApi3\JsonSchema\Model\AuthorizationCodeOAuthFlow::class => false, \LongTermSupport\OpenApiGenerator\Component\OpenApi3\JsonSchema\Model\Link::class => false, \LongTermSupport\OpenApiGenerator\Component\OpenApi3\JsonSchema\Model\Encoding::class => false, \LongTermSupport\OpenApiGenerator\Component\OpenApiRuntime\Reference::class => false];
    }

    private function getNormalizer(string $normalizerClass): object
    {
        return $this->normalizersCache[$normalizerClass] ?? $this->initNormalizer($normalizerClass);
    }

    private function initNormalizer(string $normalizerClass): object
    {
        $normalizer = new $normalizerClass();
        if (!$normalizer instanceof NormalizerAwareInterface) {
            throw new LogicException('Expected NormalizerAwareInterface, got ' . get_debug_type($normalizer));
        }

        if (!$normalizer instanceof DenormalizerAwareInterface) {
            throw new LogicException('Expected DenormalizerAwareInterface, got ' . get_debug_type($normalizer));
        }

        $normalizer->setNormalizer($this->normalizer);
        $normalizer->setDenormalizer($this->denormalizer);
        $this->normalizersCache[$normalizerClass] = $normalizer;

        return $normalizer;
    }
}
