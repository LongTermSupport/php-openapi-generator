<?php

declare(strict_types=1);

namespace LongTermSupport\OpenApiGenerator\Component\GeneratorCore\JsonSchema\Normalizer;

use ArrayObject;
use LogicException;
use LongTermSupport\OpenApiGenerator\Component\GeneratorCore\JsonSchema\Runtime\Normalizer\CheckArray;
use LongTermSupport\OpenApiGenerator\Component\GeneratorCore\JsonSchema\Runtime\Normalizer\ValidatorTrait;
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
    protected array $normalizers = [
        \LongTermSupport\OpenApiGenerator\Component\GeneratorCore\JsonSchema\Model\JsonSchema::class => JsonSchemaNormalizer::class,

        \LongTermSupport\OpenApiGenerator\Component\OpenApiRuntime\Reference::class                  => \LongTermSupport\OpenApiGenerator\Component\GeneratorCore\JsonSchema\Runtime\Normalizer\ReferenceNormalizer::class,
    ];

    /** @var array<string, object> */
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
        return [
            \LongTermSupport\OpenApiGenerator\Component\GeneratorCore\JsonSchema\Model\JsonSchema::class => false,
            \LongTermSupport\OpenApiGenerator\Component\OpenApiRuntime\Reference::class                  => false,
        ];
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
