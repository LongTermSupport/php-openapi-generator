<?php

declare(strict_types=1);

namespace LongTermSupport\OpenApiGenerator\Component\GeneratorCore\Guesser;

use LongTermSupport\OpenApiGenerator\Component\OpenApiRuntime\Reference;
use RuntimeException;
use Symfony\Component\Serializer\Normalizer\DenormalizerInterface;

trait GuesserResolverTrait
{
    protected DenormalizerInterface $denormalizer;

    /**
     * Resolve a reference with a denormalizer.
     */
    public function resolve(Reference $reference, string $class): object
    {
        $result = $reference;

        while ($result instanceof Reference) {
            $result = $result->resolve(fn ($data) => $this->denormalizer->denormalize($data, $class, 'json', [
                'document-origin' => $result->getMergedUri()->withFragment('')->__toString(),
            ]));
        }

        if (!\is_object($result)) {
            throw new RuntimeException(\sprintf('Expected object after resolving reference, got %s', get_debug_type($result)));
        }

        return $result;
    }
}
