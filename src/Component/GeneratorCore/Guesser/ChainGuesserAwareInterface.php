<?php

declare(strict_types=1);

namespace LongTermSupport\OpenApiGenerator\Component\GeneratorCore\Guesser;

interface ChainGuesserAwareInterface
{
    /**
     * Set the chain guesser.
     *
     * @internal
     */
    public function setChainGuesser(ChainGuesser $chainGuesser): void;
}
