<?php

declare(strict_types=1);

namespace LongTermSupport\OpenApiGenerator\Component\GeneratorCore\Tools;

use Doctrine\Inflector\Inflector;
use Doctrine\Inflector\InflectorFactory;

trait InflectorTrait
{
    private ?Inflector $inflector = null;

    protected function getInflector(): Inflector
    {
        if (null === $this->inflector) {
            $this->inflector = InflectorFactory::create()->build();
        }

        return $this->inflector;
    }
}
