<?php

declare(strict_types=1);

namespace LongTermSupport\OpenApiGenerator\Component\OpenApiCommon\Generator\Normalizer;

use LongTermSupport\OpenApiGenerator\Component\GeneratorCore\Generator\Context\Context;
use LongTermSupport\OpenApiGenerator\Component\GeneratorCore\Generator\Normalizer\DenormalizerGenerator as JsonSchemaDenormalizerGenerator;
use LongTermSupport\OpenApiGenerator\Component\GeneratorCore\Guesser\Guess\ClassGuess;
use LongTermSupport\OpenApiGenerator\Component\OpenApiCommon\Guesser\Guess\ParentClass;
use PhpParser\Node\Arg;
use PhpParser\Node\Expr;
use PhpParser\Node\Name;
use PhpParser\Node\Scalar;
use PhpParser\Node\Stmt;

trait DenormalizerGenerator
{
    use JsonSchemaDenormalizerGenerator {
        denormalizeMethodStatements as jsonSchemaDenormalizeMethodStatements;
    }

    protected function denormalizeMethodStatements(ClassGuess $classGuess, Context $context): array
    {
        $statements = $this->jsonSchemaDenormalizeMethodStatements($classGuess, $context);

        if ($classGuess instanceof ParentClass) {
            foreach ($classGuess->getChildEntryKeys() as $discriminatorValue) {
                $childEntryName = $classGuess->getChildEntryClassNameByKey($discriminatorValue);
                if (null === $childEntryName) {
                    throw new \LogicException(\sprintf('No child entry class name for discriminator value "%s"', $discriminatorValue));
                }

                $statements[] = new Stmt\If_(
                    new Expr\BinaryOp\BooleanAnd(
                        new Expr\FuncCall(new Name('array_key_exists'), [
                            new Arg(new Scalar\String_($classGuess->getDiscriminator())),
                            new Arg(new Expr\Variable('data')),
                        ]),
                        new Expr\BinaryOp\Identical(
                            new Scalar\String_($discriminatorValue),
                            new Expr\ArrayDimFetch(new Expr\Variable('data'), new Scalar\String_($classGuess->getDiscriminator()))
                        )
                    ),
                    [
                        'stmts' => [
                            new Stmt\Expression(
                                new Expr\Assign(
                                    new Expr\Variable('result'),
                                    new Expr\MethodCall(
                                        new Expr\PropertyFetch(
                                            new Expr\Variable('this'),
                                            'denormalizer'
                                        ),
                                        'denormalize',
                                        [
                                            new Arg(new Expr\Variable('data')),
                                            new Arg(new Scalar\String_(\sprintf('%s\Model\%s', $context->getCurrentSchema()->getNamespace(), $this->getNaming()->getClassName($childEntryName)))),
                                            new Arg(new Expr\Variable('format')),
                                            new Arg(new Expr\Variable('context')),
                                        ]
                                    )
                                ),
                            ),
                            new Stmt\Return_(new Expr\Variable('result')),
                        ],
                    ]
                );
            }
        }

        return $statements;
    }
}
