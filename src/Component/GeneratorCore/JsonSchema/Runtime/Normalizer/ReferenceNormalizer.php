<?php

declare(strict_types=1);

namespace LongTermSupport\OpenApiGenerator\Component\GeneratorCore\JsonSchema\Runtime\Normalizer;

use ArrayObject;
use LogicException;
use LongTermSupport\OpenApiGenerator\Component\OpenApiRuntime\Reference;
use Symfony\Component\Serializer\Normalizer\NormalizerInterface;

class ReferenceNormalizer implements NormalizerInterface
{
    /**
     * @return array<mixed>|string|int|float|bool|ArrayObject<string, mixed>|null
     */
    public function normalize(mixed $data, ?string $format = null, array $context = []): array|string|int|float|bool|ArrayObject|null
    {
        if (!$data instanceof Reference) {
            throw new LogicException('Expected Reference, got ' . get_debug_type($data));
        }

        $ref         = [];
        $ref['$ref'] = $data->getReferenceUri()->__toString();

        return $ref;
    }

    public function supportsNormalization($data, $format = null, array $context = []): bool
    {
        return $data instanceof Reference;
    }

    public function getSupportedTypes(?string $format): array
    {
        return [Reference::class => false];
    }
}
