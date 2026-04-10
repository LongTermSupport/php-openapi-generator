<?php

declare(strict_types=1);

namespace LongTermSupport\OpenApiGenerator\Component\GeneratorCore\Generator;

use LogicException;
use LongTermSupport\OpenApiGenerator\Component\GeneratorCore\Generator\Context\Context;
use LongTermSupport\OpenApiGenerator\Component\GeneratorCore\Generator\Model\ClassGenerator;
use LongTermSupport\OpenApiGenerator\Component\GeneratorCore\Generator\Model\GetterSetterGenerator;
use LongTermSupport\OpenApiGenerator\Component\GeneratorCore\Generator\Model\PropertyGenerator;
use LongTermSupport\OpenApiGenerator\Component\GeneratorCore\Guesser\Guess\ClassGuess;
use LongTermSupport\OpenApiGenerator\Component\GeneratorCore\Guesser\Guess\Property;
use LongTermSupport\OpenApiGenerator\Component\GeneratorCore\Registry\Schema;
use PhpParser\Comment;
use PhpParser\Modifiers;
use PhpParser\Node;
use PhpParser\Node\Expr;
use PhpParser\Node\Identifier;
use PhpParser\Node\Name;
use PhpParser\Node\Param;
use PhpParser\Node\Stmt;
use PhpParser\Parser;

class ModelGenerator implements GeneratorInterface
{
    use ClassGenerator;
    use GetterSetterGenerator;
    use PropertyGenerator;

    public const FILE_TYPE_MODEL = 'model';

    public function __construct(
        protected Naming $naming,
        protected Parser $parser,
    ) {
    }

    /**
     * Generate a model given a schema.
     */
    public function generate(Schema $schema, string $className, Context $context): void
    {
        $namespace = $schema->getNamespace() . '\Model';

        foreach ($schema->getClasses() as $class) {
            /** @var array<int, Stmt> $properties */
            $properties = [];
            /** @var array<int, Stmt> $methods */
            $methods = [];

            foreach ($class->getLocalProperties() as $property) {
                if (!$property instanceof Property) {
                    throw new LogicException('Expected Property, got ' . get_debug_type($property));
                }

                $properties[] = $this->createProperty($property, $namespace, null, $context->isStrict());
                $methods      = array_merge($methods, $this->doCreateClassMethods($class, $property, $namespace, $context->isStrict()));
            }

            $model = $this->doCreateModel($class, $properties, $methods);

            $namespaceStmt = new Stmt\Namespace_(new Name($namespace), [$model]);
            $schema->addFile(new File($schema->getDirectory() . '/Model/' . $class->getName() . '.php', $namespaceStmt, self::FILE_TYPE_MODEL));

            $this->generateCollectionClass($class->getName(), $namespace, $schema);
        }
    }

    /**
     * Generate a typed collection class for a model.
     *
     * Example for model `BarItem` in namespace `Foo\Model`:
     *
     *   final class BarItemCollection {
     *       /** @var list<\Foo\Model\BarItem> * /
     *       public private(set) array $items;
     *       public function __construct(\Foo\Model\BarItem ...$items) {
     *           $this->items = array_values($items);
     *       }
     *   }
     *
     * The variadic constructor enforces the element type at the PHP level so
     * PHPStan does not need generics to understand the contents.
     */
    private function generateCollectionClass(string $modelName, string $namespace, Schema $schema): void
    {
        $collectionName = $modelName . 'Collection';
        $itemFqcn       = new Name\FullyQualified($namespace . '\\' . $modelName);

        $collectionClass = new Stmt\Class_($collectionName, [
            'flags' => Modifiers::FINAL,
            'stmts' => [
                new Stmt\Property(
                    Modifiers::PUBLIC | Modifiers::PRIVATE_SET,
                    [new Node\PropertyItem('items')],
                    ['comments' => [new Comment\Doc(
                        '/** @var list<\\' . $namespace . '\\' . $modelName . '> */',
                    )]],
                    new Identifier('array'),
                ),
                new Stmt\ClassMethod('__construct', [
                    'flags'  => Modifiers::PUBLIC,
                    'params' => [
                        new Param(
                            var:      new Expr\Variable('items'),
                            default:  null,
                            type:     $itemFqcn,
                            byRef:    false,
                            variadic: true,
                        ),
                    ],
                    'stmts'  => [
                        new Stmt\Expression(
                            new Expr\Assign(
                                new Expr\PropertyFetch(new Expr\Variable('this'), 'items'),
                                new Expr\FuncCall(
                                    new Name('array_values'),
                                    [new Node\Arg(new Expr\Variable('items'))],
                                ),
                            ),
                        ),
                    ],
                ]),
            ],
        ]);

        $namespaceStmt = new Stmt\Namespace_(new Name($namespace), [$collectionClass]);
        $schema->addFile(new File(
            $schema->getDirectory() . '/Model/' . $collectionName . '.php',
            $namespaceStmt,
            self::FILE_TYPE_MODEL,
        ));
    }

    /**
     * The naming service.
     */
    protected function getNaming(): Naming
    {
        return $this->naming;
    }

    /**
     * {@inheritdoc}
     */
    protected function getParser(): Parser
    {
        return $this->parser;
    }

    /** @return array<int, Stmt\ClassMethod> */
    protected function doCreateClassMethods(ClassGuess $classGuess, Property $property, string $namespace, bool $strict): array
    {
        $extendsArrayObject = $classGuess->willExtendArrayObject();

        return [
            $this->createGetter($property, $namespace, $strict, $extendsArrayObject),
            $this->createSetter($property, $namespace, $strict, true, $extendsArrayObject),
        ];
    }

    /**
     * @param array<int, Stmt> $properties
     * @param array<int, Stmt> $methods
     */
    protected function doCreateModel(ClassGuess $class, array $properties, array $methods): Stmt\Class_
    {
        return $this->createModel(
            $class->getName(),
            $properties,
            $methods,
            [] !== $class->getExtensionsType(),
            $class->isDeprecated()
        );
    }
}
