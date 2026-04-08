<?php

declare(strict_types=1);

namespace LongTermSupport\OpenApiGenerator\Component\OpenApiCommon\Generator;

use LongTermSupport\OpenApiGenerator\Component\GeneratorCore\Generator\ModelGenerator as BaseModelGenerator;
use LongTermSupport\OpenApiGenerator\Component\GeneratorCore\Guesser\Guess\ClassGuess as BaseClassGuess;
use LongTermSupport\OpenApiGenerator\Component\GeneratorCore\Guesser\Guess\Property;
use LongTermSupport\OpenApiGenerator\Component\OpenApiCommon\Generator\Model\ClassGenerator;
use LongTermSupport\OpenApiGenerator\Component\OpenApiCommon\Guesser\Guess\ClassGuess;
use LongTermSupport\OpenApiGenerator\Component\OpenApiCommon\Guesser\Guess\ParentClass;
use PhpParser\Node\Stmt;

class ModelGenerator extends BaseModelGenerator
{
    use ClassGenerator;

    protected function doCreateClassMethods(BaseClassGuess $classGuess, Property $property, string $namespace, bool $strict): array
    {
        $extendsArrayObject = $classGuess->willExtendArrayObject();

        return [
            $this->createGetter($property, $namespace, $strict, $extendsArrayObject),
            $this->createSetter($property, $namespace, $strict, !$classGuess instanceof ParentClass, $extendsArrayObject),
        ];
    }

    protected function doCreateModel(BaseClassGuess $class, array $properties, array $methods): Stmt\Class_
    {
        $extends = null;
        if ($class instanceof ClassGuess
            && $class->getParentClass() instanceof ParentClass) {
            $extends = $this->getNaming()->getClassName($class->getParentClass()->getName());
        }

        return $this->createModel(
            $class->getName(),
            $properties,
            $methods,
            [] !== $class->getExtensionsType(),
            $class->isDeprecated(),
            $extends
        );
    }
}
