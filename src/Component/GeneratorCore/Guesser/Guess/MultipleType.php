<?php

declare(strict_types=1);

namespace LongTermSupport\OpenApiGenerator\Component\GeneratorCore\Guesser\Guess;

use InvalidArgumentException;
use LogicException;
use LongTermSupport\OpenApiGenerator\Component\GeneratorCore\Generator\Context\Context;
use Override;
use PhpParser\Node;
use PhpParser\Node\Arg;
use PhpParser\Node\Expr;
use PhpParser\Node\Identifier;
use PhpParser\Node\Name;
use PhpParser\Node\Scalar;
use PhpParser\Node\Stmt;

class MultipleType extends Type
{
    /**
     * @param array<int|string, Type> $types
     */
    public function __construct(
        object $object,
        protected array $types = [],
        protected ?string $discriminatorProperty = null,
    ) {
        parent::__construct($object, 'mixed');
    }

    /**
     * Sets discriminator property.
     */
    public function setDiscriminatorProperty(string $property): self
    {
        $this->discriminatorProperty = $property;

        return $this;
    }

    /**
     * Add a type.
     */
    public function addType(Type $type, mixed $discriminant = null): self
    {
        if ($type instanceof self) {
            foreach ($type->getTypes() as $subType) {
                $this->types[] = $subType;
            }

            return $this;
        }

        if (null !== $discriminant) {
            $key               = \is_scalar($discriminant) ? (string)$discriminant : throw new InvalidArgumentException('Discriminant must be scalar');
            $this->types[$key] = $type;
        } else {
            $this->types[] = $type;
        }

        return $this;
    }

    /**
     * Return a list of types.
     *
     * @return array<int|string, Type>
     */
    public function getTypes(): array
    {
        return $this->types;
    }

    #[Override]
    public function getDocTypeHint(string $namespace): string|Name|null
    {
        $stringTypes = array_map(static fn (Type $type): string|\PhpParser\Node\Name|null => $type->getDocTypeHint($namespace), $this->types);

        return implode('|', $stringTypes);
    }

    #[Override]
    public function getTypeHint(string $namespace): Identifier|Name|Node\UnionType|null
    {
        if (1 === \count($this->types)) {
            $type = current($this->types);

            return $type->getTypeHint($namespace);
        }

        // Separate null from non-null types
        $nonNullTypes = [];
        foreach ($this->types as $type) {
            if ('null' === $type->getName()) {
                continue;
            }

            $nonNullTypes[] = $type;
        }

        // Single non-null type + null → return the non-null hint (caller handles nullable wrapping)
        if (1 === \count($nonNullTypes)) {
            return $nonNullTypes[0]->getTypeHint($namespace);
        }

        // Collect native type hints for all non-null types.
        // Only Identifier and Name are valid union members for our purposes.
        /** @var list<Identifier|Name> $hints */
        $hints = [];
        foreach ($nonNullTypes as $type) {
            $hint = $type->getTypeHint($namespace);
            if (null === $hint) {
                // If any type can't be expressed natively, fall back to mixed
                return null;
            }

            // Flatten nested union types
            if ($hint instanceof Node\UnionType) {
                foreach ($hint->types as $member) {
                    if (!$member instanceof Identifier && !$member instanceof Name) {
                        return null; // IntersectionType not supported, fall back to mixed
                    }

                    $hints[] = $member;
                }
            } elseif ($hint instanceof Identifier || $hint instanceof Name) {
                $hints[] = $hint;
            } else {
                return null;
            }
        }

        if ([] === $hints) {
            return null;
        }

        // Deduplicate by string representation
        $seen = [];
        /** @var list<Identifier|Name> $uniqueHints */
        $uniqueHints = [];
        foreach ($hints as $hint) {
            $str = $hint->toString();
            if (!isset($seen[$str])) {
                $seen[$str]    = true;
                $uniqueHints[] = $hint;
            }
        }

        if (1 === \count($uniqueHints)) {
            return $uniqueHints[0];
        }

        return new Node\UnionType($uniqueHints);
    }

    #[Override]
    public function createDenormalizationStatement(Context $context, Expr $input, bool $normalizerFromObject = true): array
    {
        $output     = new Expr\Variable($context->getUniqueVariableName('value'));
        $statements = [
            new Stmt\Expression(new Expr\Assign($output, $input)),
        ];

        $ifStmt = null;
        foreach ($this->getTypesSorted() as $discriminant => $type) {
            [$typeStatements, $typeOutput] = $type->createDenormalizationStatement($context, $input, $normalizerFromObject);

            $condition = $type->createConditionStatement($input);
            if (null !== $this->discriminatorProperty) {
                $condition = new Expr\BinaryOp\LogicalAnd($condition, $this->createDiscriminatorCondition($input, $discriminant));
            }

            $statement = array_merge($typeStatements, [new Stmt\Expression(new Expr\Assign($output, $typeOutput))]);

            if (!$ifStmt instanceof Stmt\If_) {
                $ifStmt = new Stmt\If_($condition, ['stmts' => $statement]);
            } else {
                $ifStmt->elseifs[] = new Stmt\ElseIf_($condition, $statement);
            }
        }

        if ($ifStmt instanceof Stmt\If_) {
            // Add an else clause that throws, so PHPStan can narrow $output
            // to the precise union of types from the if/elseif branches rather
            // than falling back to mixed from the initial assignment.
            // A separate type guard (e.g. !instanceof X || is_array()) would lose
            // specific array element types (list<Country> → array<mixed, mixed>).
            $hasMixed      = false;
            $typeDescParts = [];

            foreach ($this->getTypes() as $guardType) {
                if ('mixed' === $guardType->getName()) {
                    $hasMixed = true;
                    break;
                }

                if ($guardType instanceof ObjectType || $guardType instanceof CustomObjectType) {
                    $typeDescParts[] = $guardType->getClassName();
                } elseif ($guardType instanceof ArrayType) {
                    $typeDescParts[] = 'array';
                } else {
                    $typeDescParts[] = $guardType->getName();
                }
            }

            if (!$hasMixed && [] !== $typeDescParts) {
                $typeDesc     = implode('|', $typeDescParts);
                $ifStmt->else = new Stmt\Else_([
                    new Stmt\Expression(new Expr\Throw_(new Expr\New_(
                        new Name('\LogicException'),
                        [new Arg(new Expr\BinaryOp\Concat(
                            new Scalar\String_('Unexpected type for ' . $typeDesc . ': '),
                            new Expr\FuncCall(new Name('get_debug_type'), [new Arg($output)])
                        ))]
                    ))),
                ]);
            }

            $statements[] = $ifStmt;
        }

        return [$statements, $output];
    }

    #[Override]
    public function createNormalizationStatement(Context $context, Expr $input, bool $normalizerFromObject = true): array
    {
        $sortedTypes = $this->getTypesSorted();

        // When there's only one type, skip the conditional — directly normalize
        // (avoids function.alreadyNarrowedType when PHPStan knows the type from PHPDoc)
        if (1 === \count($sortedTypes)) {
            $type = reset($sortedTypes);

            return $type->createNormalizationStatement($context, $input, $normalizerFromObject);
        }

        $output     = new Expr\Variable($context->getUniqueVariableName('value'));
        $statements = [
            new Stmt\Expression(new Expr\Assign($output, $input)),
        ];

        $ifStmt = null;
        foreach ($sortedTypes as $type) {
            [$typeStatements, $typeOutput] = $type->createNormalizationStatement($context, $input, $normalizerFromObject);

            $condition = $type->createNormalizationConditionStatement($input);
            $statement = array_merge($typeStatements, [new Stmt\Expression(new Expr\Assign($output, $typeOutput))]);

            if (!$ifStmt instanceof Stmt\If_) {
                $ifStmt = new Stmt\If_($condition, ['stmts' => $statement]);
            } else {
                $ifStmt->elseifs[] = new Stmt\ElseIf_($condition, $statement);
            }
        }

        if ($ifStmt instanceof Stmt\If_) {
            $statements[] = $ifStmt;
        }

        return [$statements, $output];
    }

    /**
     * We have to place mixed normalization path at the last.
     *
     * @return array<int|string, Type>
     */
    protected function getTypesSorted(): array
    {
        $types = $this->getTypes();
        uasort($types, static function (Type $first, Type $second): int {
            if (($second instanceof ObjectType && 'Reference' === $second->getClassName()) || 'mixed' === $first->getName()) {
                return 1;
            }

            return 0;
        });

        return $types;
    }

    private function createDiscriminatorCondition(Expr $input, mixed $discriminant): Expr
    {
        if (!\is_string($this->discriminatorProperty)) {
            throw new LogicException('Expected string, got ' . get_debug_type($this->discriminatorProperty));
        }

        if (!\is_string($discriminant)) {
            throw new LogicException('Expected string, got ' . get_debug_type($discriminant));
        }

        $issetCondition = new Expr\FuncCall(
            new Name('isset'),
            [
                new Arg(new Expr\ArrayDimFetch($input, new Scalar\String_($this->discriminatorProperty))),
            ]
        );

        $valueCondition = new Expr\BinaryOp\Identical(
            new Expr\ArrayDimFetch($input, new Scalar\String_($this->discriminatorProperty)),
            new Scalar\String_($discriminant)
        );

        return new Expr\BinaryOp\LogicalAnd($issetCondition, $valueCondition);
    }
}
