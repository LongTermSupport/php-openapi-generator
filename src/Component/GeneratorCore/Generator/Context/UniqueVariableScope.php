<?php

declare(strict_types=1);

namespace LongTermSupport\OpenApiGenerator\Component\GeneratorCore\Generator\Context;

/**
 * Allow getting a unique variable name for a scope (like a method).
 */
class UniqueVariableScope
{
    /** @var array<string, int> */
    private array $registry = [];

    /**
     * Return a unique name for a variable.
     *
     * @param string $name Name of the variable
     *
     * @return string if not found, return the $name given, if not, return the name suffixed with a number
     */
    public function getUniqueName(string $name): string
    {
        if (!isset($this->registry[$name])) {
            $this->registry[$name] = 0;

            return $name;
        }

        ++$this->registry[$name];

        return \sprintf('%s_%s', $name, $this->registry[$name]);
    }
}
