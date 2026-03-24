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
use PhpParser\Node\Name;
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
        }
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
        return [$this->createGetter($property, $namespace, $strict), $this->createSetter($property, $namespace, $strict)];
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
