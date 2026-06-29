<?php

declare(strict_types=1);

namespace LongTermSupport\OpenApiGenerator\Component\GeneratorCore\Registry;

/**
 * @internal
 */
interface RegistryInterface
{
    public function getOptionsHash(): string;
}
