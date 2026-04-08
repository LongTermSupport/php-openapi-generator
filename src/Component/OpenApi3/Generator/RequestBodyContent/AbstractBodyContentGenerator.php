<?php

declare(strict_types=1);

namespace LongTermSupport\OpenApiGenerator\Component\OpenApi3\Generator\RequestBodyContent;

use LogicException;
use LongTermSupport\OpenApiGenerator\Component\GeneratorCore\Generator\Context\Context;
use LongTermSupport\OpenApiGenerator\Component\OpenApi3\Generator\RequestBodyContentGeneratorInterface;
use LongTermSupport\OpenApiGenerator\Component\OpenApi3\Guesser\GuessClass;
use LongTermSupport\OpenApiGenerator\Component\OpenApi3\JsonSchema\Model\MediaType;
use LongTermSupport\OpenApiGenerator\Component\OpenApi3\JsonSchema\Model\Schema;
use LongTermSupport\OpenApiGenerator\Component\OpenApiCommon\Registry\Registry as OpenApiRegistry;
use PhpParser\Node;
use PhpParser\Node\Arg;
use PhpParser\Node\Expr;
use PhpParser\Node\Name;
use Psr\Http\Message\StreamInterface;
use Symfony\Component\Serializer\Normalizer\DenormalizerInterface;

abstract class AbstractBodyContentGenerator implements RequestBodyContentGeneratorInterface
{
    public const PHP_TYPE_MIXED = 'mixed';

    protected GuessClass $guessClass;

    public function __construct(DenormalizerInterface $denormalizer)
    {
        $this->guessClass = new GuessClass(Schema::class, $denormalizer);
    }

    /**
     * @return array<mixed>
     */
    public function getTypes(MediaType $content, string $reference, Context $context): array
    {
        $schema   = $content->getSchema();
        $registry = $context->getRegistry();
        if (!$registry instanceof OpenApiRegistry) {
            throw new LogicException('Expected OpenApiRegistry, got ' . get_debug_type($registry));
        }

        $classGuess = $this->guessClass->guessClass($schema, $reference . '/schema', $registry, $array);

        if (!$classGuess instanceof \LongTermSupport\OpenApiGenerator\Component\GeneratorCore\Guesser\Guess\ClassGuess) {
            if (!$schema instanceof Schema && null !== $schema) {
                throw new LogicException('Expected Schema or null, got ' . get_debug_type($schema));
            }

            $types = $this->schemaTypeToPHP($schema instanceof Schema ? $schema->getType() : null, $schema instanceof Schema ? $schema->getFormat() : null);

            if (true === $array) {
                $types = array_map(static fn (string $type): string => $type . '[]', $types);
            }

            return [$types, $array];
        }

        $schemaObj = $registry->getSchema($classGuess->getReference());
        if (!$schemaObj instanceof \LongTermSupport\OpenApiGenerator\Component\GeneratorCore\Registry\Schema) {
            throw new LogicException('Expected Schema, got ' . get_debug_type($schemaObj));
        }

        $class = $schemaObj->getNamespace() . '\Model\\' . $classGuess->getName();

        if (true === $array) {
            $class .= '[]';
        }

        return [['\\' . $class], $array];
    }

    public function getTypeCondition(MediaType $content, string $reference, Context $context): Node
    {
        $schema   = $content->getSchema();
        $registry = $context->getRegistry();
        if (!$registry instanceof OpenApiRegistry) {
            throw new LogicException('Expected OpenApiRegistry, got ' . get_debug_type($registry));
        }

        $classGuess = $this->guessClass->guessClass($schema, $reference . '/schema', $registry, $array);

        if (!$classGuess instanceof \LongTermSupport\OpenApiGenerator\Component\GeneratorCore\Guesser\Guess\ClassGuess) {
            if (!$schema instanceof Schema && null !== $schema) {
                throw new LogicException('Expected Schema or null, got ' . get_debug_type($schema));
            }

            return $this->typeToCondition($schema instanceof Schema ? $schema->getType() : null, $schema instanceof Schema ? $schema->getFormat() : null, new Expr\PropertyFetch(new Expr\Variable('this'), 'body'));
        }

        $schemaObj = $registry->getSchema($classGuess->getReference());
        if (!$schemaObj instanceof \LongTermSupport\OpenApiGenerator\Component\GeneratorCore\Registry\Schema) {
            throw new LogicException('Expected Schema, got ' . get_debug_type($schemaObj));
        }

        $class = $schemaObj->getNamespace() . '\Model\\' . $classGuess->getName();

        if (true === $array) {
            return new Expr\BinaryOp\BooleanAnd(
                new Expr\BinaryOp\BooleanAnd(
                    new Expr\FuncCall(new Name('is_array'), [new Arg(new Expr\PropertyFetch(new Expr\Variable('this'), 'body'))]),
                    new Expr\FuncCall(new Name('isset'), [new Arg(new Expr\ArrayDimFetch(
                        new Expr\PropertyFetch(new Expr\Variable('this'), 'body'),
                        new Expr\ConstFetch(new Name('0'))
                    ))])
                ),
                new Expr\Instanceof_(
                    new Expr\ArrayDimFetch(
                        new Expr\PropertyFetch(new Expr\Variable('this'), 'body'),
                        new Expr\ConstFetch(new Name('0'))
                    ),
                    new Name('\\' . $class)
                )
            );
        }

        return new Expr\Instanceof_(
            new Expr\PropertyFetch(new Expr\Variable('this'), 'body'),
            new Name('\\' . $class)
        );
    }

    /**
     * @return list<string>
     */
    private function schemaTypeToPHP(?string $type, ?string $format = null): array
    {
        if (null === $format) {
            $format = 'default';
        }

        $convertArray = [
            'string'  => [
                'default' => ['string'],
                'binary'  => ['string', 'resource', '\\' . StreamInterface::class],
            ],
            'number'  => [
                'default' => ['float'],
            ],
            'boolean' => [
                'default' => ['bool'],
            ],
            'integer' => [
                'default' => ['int'],
            ],
            'array'   => [
                'default' => ['array'],
            ],
            'object'  => [
                'default' => ['\stdClass'],
            ],
            'file'    => [
                'default' => ['string', 'resource', '\\' . StreamInterface::class],
            ],
        ];

        if (!isset($convertArray[$type]) || !isset($convertArray[$type][$format])) {
            return [self::PHP_TYPE_MIXED];
        }

        return $convertArray[$type][$format];
    }

    private function typeToCondition(?string $type, ?string $format, Expr $fetch): Expr
    {
        if (null === $format) {
            $format = 'default';
        }

        $inputArg = new Arg($fetch);

        $convertArray = [
            'string'  => [
                'default' => new Expr\FuncCall(new Name('is_string'), [$inputArg]),
                'binary'  => new Expr\BinaryOp\BooleanOr(
                    new Expr\BinaryOp\BooleanOr(
                        new Expr\FuncCall(new Name('is_string'), [$inputArg]),
                        new Expr\FuncCall(new Name('is_resource'), [$inputArg])
                    ),
                    new Expr\Instanceof_($fetch, new Name('\\' . StreamInterface::class))
                ),
            ],
            'number'  => [
                'default' => new Expr\FuncCall(new Name('is_float'), [$inputArg]),
            ],
            'boolean' => [
                'default' => new Expr\FuncCall(new Name('is_bool'), [$inputArg]),
            ],
            'integer' => [
                'default' => new Expr\FuncCall(new Name('is_int'), [$inputArg]),
            ],
            'array'   => [
                'default' => new Expr\FuncCall(new Name('is_array'), [$inputArg]),
            ],
            'object'  => [
                'default' => new Expr\Instanceof_($fetch, new Name('\stdClass')),
            ],
            'file'    => [
                'default' => new Expr\BinaryOp\BooleanOr(
                    new Expr\BinaryOp\BooleanOr(
                        new Expr\FuncCall(new Name('is_string'), [$inputArg]),
                        new Expr\FuncCall(new Name('is_resource'), [$inputArg])
                    ),
                    new Expr\Instanceof_($fetch, new Name('\\' . StreamInterface::class))
                ),
            ],
        ];

        if (!isset($convertArray[$type]) || !isset($convertArray[$type][$format])) {
            return new Expr\FuncCall(new Name('isset'), [$inputArg]);
        }

        return $convertArray[$type][$format];
    }
}
