<?php

declare(strict_types=1);

namespace LongTermSupport\OpenApiGenerator\Component\GeneratorCore\Generator\Model;

use LongTermSupport\OpenApiGenerator\Component\GeneratorCore\Generator\Naming;
use PhpParser\Comment\Doc;
use PhpParser\Modifiers;
use PhpParser\Node;
use PhpParser\Node\Expr;
use PhpParser\Node\Name;
use PhpParser\Node\Stmt;

trait ClassGenerator
{
    /**
     * The naming service.
     */
    abstract protected function getNaming(): Naming;

    /**
     * Return a model class.
     *
     * @param array<int, Stmt> $properties
     * @param array<int, Stmt> $methods
     */
    protected function createModel(string $name, array $properties, array $methods, bool $hasExtensions = false, bool $deprecated = false): Stmt\Class_
    {
        $attributes = [];

        $docLines = [];
        if ($deprecated) {
            $docLines[] = ' * @deprecated';
        }

        if ($hasExtensions) {
            $docLines[] = ' * @extends \ArrayObject<string, mixed>';
        }

        if ([] !== $docLines) {
            $attributes['comments'] = [new Doc("/**\n" . implode("\n", $docLines) . "\n */")];
        }

        return new Stmt\Class_(
            $this->getNaming()->getClassName($name),
            [
                'stmts'   => array_merge($this->getInitialized(), $properties, $methods),
                'extends' => $hasExtensions ? new Name('\ArrayObject') : null,
            ],
            $attributes
        );
    }

    /** @return array<int, Stmt> */
    protected function getInitialized(): array
    {
        $initializedProperty = new Stmt\Property(Modifiers::PROTECTED, [new Stmt\PropertyProperty('initialized', new Expr\Array_())], ['comments' => [new Doc(
            <<<'EOD'
                /**
                 * @var array<string, bool>
                 */
                EOD
        )]]);
        $initializedProperty->type = new Node\Identifier('array');

        $initializedMethod = new Stmt\ClassMethod(
            'isInitialized',
            [
                // public function
                'flags'      => Modifiers::PUBLIC,
                'params'     => [new Node\Param($propertyVariable = new Expr\Variable('property'), type: new Node\Identifier('string'))],
                'stmts'      => [
                    new Stmt\Return_(
                        new Expr\FuncCall(new Name('array_key_exists'), [new Node\Arg($propertyVariable), new Node\Arg(new Expr\PropertyFetch(new Expr\Variable('this'), 'initialized'))])
                    ),
                ],
                'returnType' => new Name('bool'),
            ]
        );

        return [$initializedProperty, $initializedMethod];
    }
}
