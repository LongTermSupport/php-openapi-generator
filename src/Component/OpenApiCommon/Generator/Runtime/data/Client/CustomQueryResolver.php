<?php

declare(strict_types=1);

use Symfony\Component\OptionsResolver\Options;

interface CustomQueryResolver
{
    public function __invoke(Options $options, mixed $value): mixed;
}
