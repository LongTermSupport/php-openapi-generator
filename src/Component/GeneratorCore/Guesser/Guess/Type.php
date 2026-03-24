<?php

declare(strict_types=1);

namespace LongTermSupport\OpenApiGenerator\Component\GeneratorCore\Guesser\Guess;

use LongTermSupport\OpenApiGenerator\Component\GeneratorCore\Generator\Context\Context;
use PhpParser\Node;
use PhpParser\Node\Arg;
use PhpParser\Node\Expr;
use PhpParser\Node\Name;
use PhpParser\Node\Scalar;
use Stringable;

class Type implements Stringable
{
    public const TYPE_BOOLEAN = 'bool';

    public const TYPE_INTEGER = 'int';

    public const TYPE_FLOAT = 'float';

    public const TYPE_STRING = 'string';

    public const TYPE_NULL = 'null';

    public const TYPE_MIXED = 'mixed';

    public const TYPE_ARRAY = 'array';

    public const TYPE_OBJECT = 'object';

    /**
     * @var array<string, string|null>
     */
    protected array $phpMapping = [
        self::TYPE_BOOLEAN => 'bool',
        self::TYPE_INTEGER => 'int',
        self::TYPE_FLOAT   => 'float',
        self::TYPE_STRING  => 'string',
        self::TYPE_NULL    => null,
        self::TYPE_MIXED   => null,
        self::TYPE_ARRAY   => 'array',
        self::TYPE_OBJECT  => null,
    ];

    /**
     * @var array<string, string>
     */
    protected array $conditionMapping = [
        self::TYPE_BOOLEAN => 'is_bool',
        self::TYPE_INTEGER => 'is_int',
        self::TYPE_FLOAT   => 'is_float',
        self::TYPE_STRING  => 'is_string',
        self::TYPE_NULL    => 'is_null',
        self::TYPE_MIXED   => 'isset',
        self::TYPE_ARRAY   => 'is_array',
        self::TYPE_OBJECT  => 'is_array',
    ];

    /**
     * @var array<string, string>
     */
    protected array $normalizationConditionMapping = [
        self::TYPE_BOOLEAN => 'is_bool',
        self::TYPE_INTEGER => 'is_int',
        self::TYPE_FLOAT   => 'is_float',
        self::TYPE_STRING  => 'is_string',
        self::TYPE_NULL    => 'is_null',
        self::TYPE_MIXED   => '!is_null',
        self::TYPE_ARRAY   => 'is_array',
        self::TYPE_OBJECT  => 'is_object',
    ];

    public function __construct(
        protected ?object $object,
        protected string $name,
    ) {
    }

    public function __toString(): string
    {
        return $this->name;
    }

    public function getName(): string
    {
        return $this->name;
    }

    public function getObject(): ?object
    {
        return $this->object;
    }

    /**
     * @return array{array<int, Node\Stmt>, Expr}
     */
    public function createDenormalizationStatement(Context $context, Expr $input, bool $normalizerFromObject = true): array
    {
        return [[], $this->createDenormalizationValueStatement($context, $input, $normalizerFromObject)];
    }

    /**
     * @return array{array<int, Node\Stmt>, Expr}
     */
    public function createNormalizationStatement(Context $context, Expr $input, bool $normalizerFromObject = true): array
    {
        return [[], $this->createNormalizationValueStatement($context, $input, $normalizerFromObject)];
    }

    public function createConditionStatement(Expr $input): Expr
    {
        return new Expr\FuncCall(
            new Name($this->conditionMapping[$this->name]),
            [
                new Arg($input),
            ]
        );
    }

    public function createNormalizationConditionStatement(Expr $input): Expr
    {
        return new Expr\FuncCall(
            new Name($this->normalizationConditionMapping[$this->name]),
            [
                new Arg($input),
            ]
        );
    }

    public function getTypeHint(string $namespace): Node\Identifier|Name|Node\UnionType|null
    {
        return \is_string($this->phpMapping[$this->name])
            ? new Node\Identifier($this->phpMapping[$this->name])
            : $this->phpMapping[$this->name];
    }

    /**
     * Make a type hint nullable: wraps simple types in NullableType, adds null member for UnionType.
     */
    public static function makeNullable(Node\Identifier|Name|Node\UnionType $type): Node\NullableType|Node\UnionType
    {
        if ($type instanceof Node\UnionType) {
            foreach ($type->types as $member) {
                if ($member instanceof Node\Identifier && 'null' === $member->toString()) {
                    return $type;
                }
            }

            return new Node\UnionType([...$type->types, new Node\Identifier('null')]);
        }

        return new Node\NullableType($type);
    }

    /**
     * Convert any type hint node to its string representation.
     */
    public static function typeHintToString(Node\Identifier|Name|Node\UnionType|Node\NullableType|null $type): string
    {
        if (null === $type) {
            return '';
        }

        if ($type instanceof Node\UnionType) {
            return implode('|', array_map(
                static fn (Node\Identifier|Name|Node\IntersectionType $member): string => $member instanceof Node\IntersectionType
                    ? '(' . implode('&', array_map(static fn (Node\Identifier|Name $m): string => $m->toString(), $member->types)) . ')'
                    : $member->toString(),
                $type->types
            ));
        }

        if ($type instanceof Node\NullableType) {
            return '?' . $type->type->toString();
        }

        return $type->toString();
    }

    public function getDocTypeHint(string $namespace): string|Name|null
    {
        // Types without a native PHP mapping use `mixed` as the native type.
        // Emitting a narrower doc type (e.g. @return object) would contradict
        // the `mixed` native type and cause PHPStan return.type errors.
        if (null === $this->phpMapping[$this->name]) {
            return '';
        }

        return (string)$this;
    }

    protected function createDenormalizationValueStatement(Context $context, Expr $input, bool $normalizerFromObject = true): Expr
    {
        // Use TypeValidator assertions for scalar types so PHPStan can narrow from mixed
        $methodMapping = [
            self::TYPE_STRING  => 'assertString',
            self::TYPE_INTEGER => 'assertInt',
            self::TYPE_FLOAT   => 'assertFloat',
            self::TYPE_BOOLEAN => 'assertBool',
        ];

        if (isset($methodMapping[$this->name])) {
            return new Expr\StaticCall(
                new Name('TypeValidator'),
                $methodMapping[$this->name],
                [new Arg($input), new Arg(new Scalar\String_('value'))]
            );
        }

        return $input;
    }

    protected function createNormalizationValueStatement(Context $context, Expr $input, bool $normalizerFromObject = true): Expr
    {
        return $input;
    }
}
