<?php

declare(strict_types=1);

namespace LongTermSupport\OpenApiGenerator\Component\GeneratorCore\Generator;

use LongTermSupport\OpenApiGenerator\Component\GeneratorCore\Generator\Context\Context;
use LongTermSupport\OpenApiGenerator\Component\GeneratorCore\Registry\Registry;

abstract class ChainGenerator
{
    /** @var GeneratorInterface[] */
    private array $generators = [];

    public function addGenerator(GeneratorInterface $generator): void
    {
        $this->generators[] = $generator;
    }

    public function generate(Registry $registry): void
    {
        $context = $this->createContext($registry);

        foreach ($registry->getSchemas() as $schema) {
            $context->setCurrentSchema($schema);

            foreach ($this->generators as $generator) {
                $generator->generate($schema, $schema->getRootName(), $context);
            }
        }
    }

    abstract protected function createContext(Registry $registry): Context;
}
