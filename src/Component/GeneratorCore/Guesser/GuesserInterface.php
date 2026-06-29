<?php

declare(strict_types=1);

namespace LongTermSupport\OpenApiGenerator\Component\GeneratorCore\Guesser;

/**
 * @internal
 */
interface GuesserInterface
{
    /**
     * Is this object supported for the guesser.
     *
     * @internal
     */
    public function supportObject(mixed $object): bool;
}
