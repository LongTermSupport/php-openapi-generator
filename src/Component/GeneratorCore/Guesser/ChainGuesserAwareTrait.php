<?php

declare(strict_types=1);

namespace LongTermSupport\OpenApiGenerator\Component\GeneratorCore\Guesser;

trait ChainGuesserAwareTrait
{
    protected ChainGuesser $chainGuesser;

    /**
     * Set the chain guesser.
     */
    public function setChainGuesser(ChainGuesser $chainGuesser): void
    {
        $this->chainGuesser = $chainGuesser;
    }
}
