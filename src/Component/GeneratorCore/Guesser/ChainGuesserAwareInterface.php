<?php

declare(strict_types=1);

namespace LongTermSupport\OpenApiGenerator\Component\GeneratorCore\Guesser;

/**
 * @internal
 */
interface ChainGuesserAwareInterface
{
    /**
     * Set the chain guesser.
     */
    public function setChainGuesser(ChainGuesser $chainGuesser): void;
}
