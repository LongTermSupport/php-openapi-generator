<?php

declare(strict_types=1);

namespace LongTermSupport\OpenApiGenerator\Component\GeneratorCore\Generator;

use LogicException;
use LongTermSupport\OpenApiGenerator\Component\GeneratorCore\Generator\Context\Context;
use LongTermSupport\OpenApiGenerator\Component\GeneratorCore\Guesser\Validator\ValidatorGuess;
use LongTermSupport\OpenApiGenerator\Component\GeneratorCore\Registry\Schema;
use PhpParser\Modifiers;
use PhpParser\Node;
use PhpParser\Node\Expr;
use PhpParser\Node\Scalar;
use Symfony\Component\Validator\Constraints\Collection;
use Symfony\Component\Validator\Constraints\Optional;
use Symfony\Component\Validator\Constraints\Required;

class ValidatorGenerator implements GeneratorInterface
{
    public const FILE_TYPE_VALIDATOR = 'validator';

    public const VALIDATOR_INTERFACE_NAME = 'ValidatorInterface';

    public const VALIDATOR_EXCEPTION_NAME = 'ValidationException';

    public function __construct(
        private readonly Naming $naming,
    ) {
    }

    public function generate(Schema $schema, string $className, Context $context): void
    {
        $registry  = $context->getRegistry();
        $namespace = $schema->getNamespace() . '\Validator';

        foreach ($schema->getClasses() as $class) {
            $className                  = $this->naming->getConstraintName($class->getName());
            $collectionItemsConstraints = [];
            $collectionItems            = [];

            foreach ($class->getPropertyValidatorGuesses() as $name => $propertyGuesses) {
                $constraints = [];
                foreach ($propertyGuesses as $propertyGuess) {
                    $constraints[] = new Expr\ArrayItem($this->generateConstraint($propertyGuess));
                }

                $collectionItemsConstraints[$name] = $constraints;
            }

            $optionsVariable = new Expr\Variable('options');

            $constraintsItems = [];
            foreach ($class->getValidatorGuesses() as $classGuess) {
                if (!$classGuess instanceof ValidatorGuess) {
                    throw new LogicException('Expected ValidatorGuess, got ' . get_debug_type($classGuess));
                }

                if (null === $classGuess->getSubProperty()) {
                    $constraintsItems[] = new Expr\ArrayItem($this->generateConstraint($classGuess));
                } else {
                    $localNamespace = $namespace;
                    if (null !== $classGuess->getClassReference()) {
                        foreach ($registry->getSchemas() as $localSchema) {
                            if (null !== $localSchema->getClass($classGuess->getClassReference())) {
                                $localNamespace = $localSchema->getNamespace() . '\Validator';
                            }
                        }
                    }

                    $classGuess->setConstraintClass(\sprintf('%s\%s', $localNamespace, $classGuess->getConstraintClass()));

                    $subProperty = $classGuess->getSubProperty();
                    if (!\is_string($subProperty)) {
                        throw new LogicException('Expected subProperty to be string, got ' . get_debug_type($subProperty));
                    }

                    if (!\array_key_exists($subProperty, $collectionItemsConstraints)) {
                        $collectionItemsConstraints[$subProperty] = [$this->generateConstraint($classGuess)];
                    } else {
                        $collectionItemsConstraints[$subProperty] = array_merge($collectionItemsConstraints[$subProperty], [$this->generateConstraint($classGuess)]);
                    }
                }
            }

            foreach ($collectionItemsConstraints as $name => $constraints) {
                // Skip field names that PHP would convert to integer array keys
                // (e.g., '-1', '0'). Symfony's Collection expects array<string, Constraint>
                // and integer keys would violate this type. The allowExtraFields option
                // still permits these fields through validation.
                $nameStr = (string)$name;
                if ($nameStr === (string)(int)$nameStr) {
                    continue;
                }

                $collectionClass   = $class->isRequired($nameStr) ? Required::class : Optional::class;
                $arrayItems        = array_filter($constraints, static fn (\PhpParser\Node\Expr\ArrayItem|Expr $c): bool => $c instanceof Expr\ArrayItem);
                $collectionItems[] = new Expr\ArrayItem(new Expr\New_(new Node\Name\FullyQualified($collectionClass), [
                    new Node\Arg(new Expr\Array_(array_values($arrayItems))),
                ]), new Scalar\String_((string)$name));
            }

            if ([] !== $collectionItems) {
                $classObject          = $class->getObject();
                $additionalProperties = $classObject instanceof \LongTermSupport\OpenApiGenerator\Component\GeneratorCore\JsonSchema\Model\JsonSchema ? $classObject->getAdditionalProperties() : null;
                $allowExtraFields     = null === $additionalProperties || (bool)$additionalProperties ? 'true' : 'false';

                $constraintsItems[] = new Expr\ArrayItem(new Expr\New_(new Node\Name\FullyQualified(Collection::class), [
                    new Node\Arg(
                        new Expr\Array_($collectionItems),
                        name: new Node\Identifier('fields'),
                    ),
                    new Node\Arg(
                        new Expr\ConstFetch(new Node\Name($allowExtraFields)),
                        name: new Node\Identifier('allowExtraFields'),
                    ),
                ]));
            }

            $classStmt = new Node\Stmt\Class_(
                $className,
                [
                    'stmts'   => [
                        new Node\Stmt\ClassMethod(
                            'getConstraints',
                            [
                                'flags'      => Modifiers::PROTECTED,
                                'params'     => [new Node\Param($optionsVariable)],
                                'stmts'      => [
                                    new Node\Stmt\Return_(new Expr\Array_($constraintsItems)),
                                ],
                                'returnType' => new Node\Identifier('array'),
                            ]
                        ),
                    ],
                    'extends' => new Node\Name\FullyQualified(\Symfony\Component\Validator\Constraints\Compound::class),
                ]
            );

            $namespaceStmt = new Node\Stmt\Namespace_(new Node\Name($namespace), [$classStmt]);
            $schema->addFile(new File($schema->getDirectory() . '/Validator/' . $className . '.php', $namespaceStmt, self::FILE_TYPE_VALIDATOR));
        }
    }

    private function generateConstraint(ValidatorGuess $guess): Expr
    {
        $args = [];
        foreach ($guess->getArguments() as $argName => $argument) {
            $value = null;
            if (\is_array($argument)) {
                $values = [];
                foreach ($argument as $item) {
                    if (!\is_string($item)) {
                        throw new LogicException('Expected string argument item, got ' . get_debug_type($item));
                    }

                    $values[] = new Expr\ArrayItem(new Scalar\String_($item));
                }

                $value = new Expr\Array_($values);
            } elseif (\is_string($argument)) {
                $value = new Scalar\String_($argument);
            } elseif (\is_int($argument)) {
                $value = new Scalar\LNumber($argument);
            } elseif (\is_float($argument)) {
                $value = new Scalar\DNumber($argument);
            }

            if (null !== $value) {
                $args[] = new Node\Arg($value, name: new Node\Identifier($argName));
            }
        }

        return new Expr\New_(new Node\Name\FullyQualified($guess->getConstraintClass()), $args);
    }
}
