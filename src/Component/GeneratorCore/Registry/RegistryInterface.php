<?php

declare(strict_types=1);

namespace LongTermSupport\OpenApiGenerator\Component\GeneratorCore\Registry;

interface RegistryInterface
{
    public function getOptionsHash(): string;
}
