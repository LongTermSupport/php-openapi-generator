<?php

declare(strict_types=1);

namespace LongTermSupport\OpenApiGenerator\Component\OpenApiCommon\Generator\Model;

use LongTermSupport\OpenApiGenerator\Component\GeneratorCore\Generator\Model\ClassGenerator as BaseClassGenerator;
use PhpParser\Comment\Doc;
use PhpParser\Node\Name;
use PhpParser\Node\Stmt;

trait ClassGenerator
{
    use BaseClassGenerator {
        createModel as baseCreateModel;
    }

    /**
     * Return a model class.
     *
     * @param Stmt[] $properties
     * @param Stmt[] $methods
     */
    protected function createModel(string $name, array $properties, array $methods, bool $hasExtensions = false, bool $deprecated = false, ?string $extends = null): Stmt\Class_
    {
        $classExtends = null;
        if (null !== $extends) {
            $classExtends = new Name($extends);
        } elseif ($hasExtensions) {
            $classExtends = new Name('\ArrayObject');
        }

        $attributes = [];
        $docLines   = [];
        if ($deprecated) {
            $docLines[] = ' * @deprecated';
        }

        if ($classExtends instanceof Name && null === $extends && $hasExtensions) {
            // Only add @extends for ArrayObject (not custom parent classes)
            $docLines[] = ' * @extends \ArrayObject<string, mixed>';
        }

        if ([] !== $docLines) {
            $attributes['comments'] = [new Doc("/**\n" . implode("\n", $docLines) . "\n */")];
        }

        $classFlags = 0;

        return new Stmt\Class_(
            $this->getNaming()->getClassName($name),
            [
                'flags'   => $classFlags,
                'stmts'   => array_merge($this->getInitialized(), $properties, $methods),
                'extends' => $classExtends,
            ],
            $attributes
        );
    }
}
