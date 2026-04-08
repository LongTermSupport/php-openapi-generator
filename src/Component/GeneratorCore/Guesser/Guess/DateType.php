<?php

declare(strict_types=1);

namespace LongTermSupport\OpenApiGenerator\Component\GeneratorCore\Guesser\Guess;

use LongTermSupport\OpenApiGenerator\Component\GeneratorCore\Generator\Context\Context;
use Override;
use PhpParser\Node\Arg;
use PhpParser\Node\Expr;
use PhpParser\Node\Name;
use PhpParser\Node\Scalar;

/**
 * Represent a Date type.
 */
class DateType extends ObjectType
{
    use CheckNullableTrait;

    /**
     * Indicator whether to use DateTime or DateTimeInterface as type hint.
     */
    private bool $preferInterface;

    /**
     * @param string $format Format of the date to use
     */
    public function __construct(
        object $object,
        private string $format = 'Y-m-d',
        ?bool $preferInterface = null,
    ) {
        parent::__construct($object, '\DateTime', '');

        $this->preferInterface = $preferInterface ?? false;
    }

    #[Override]
    public function __toString(): string
    {
        return '\DateTime';
    }

    #[Override]
    public function createDenormalizationStatement(Context $context, Expr $input, bool $normalizerFromObject = true): array
    {
        // createDenormalizationValueStatement returns \DateTime (typed by TypeValidator), no assert needed
        return [[], $this->createDenormalizationValueStatement($context, $input, $normalizerFromObject)];
    }

    #[Override]
    public function createConditionStatement(Expr $input): Expr
    {
        return new Expr\BinaryOp\BooleanAnd(
            new Expr\FuncCall(
                new Name('is_string'),
                [
                    new Arg($input),
                ]
            ),
            new Expr\BinaryOp\NotIdentical(
                new Expr\ConstFetch(new Name('false')),
                new Expr\MethodCall(
                    new Expr\StaticCall(
                        new Name('\DateTime'),
                        'createFromFormat',
                        [
                            new Arg(new Scalar\String_($this->format)),
                            new Arg($input),
                        ]
                    ),
                    'setTime',
                    [
                        new Arg(new Scalar\LNumber(0)),
                        new Arg(new Scalar\LNumber(0)),
                        new Arg(new Scalar\LNumber(0)),
                    ]
                )
            )
        );
    }

    #[Override]
    public function getTypeHint(string $namespace): Name
    {
        return new Name($this->preferInterface ? '\DateTimeInterface' : '\DateTime');
    }

    #[Override]
    protected function createDenormalizationValueStatement(Context $context, Expr $input, bool $normalizerFromObject = true): Expr
    {
        // TypeValidator::assertDateTime($input, $format, $field)->setTime(0, 0, 0)
        return new Expr\MethodCall(
            new Expr\StaticCall(new Name('TypeValidator'), 'assertDateTime', [
                new Arg($input),
                new Arg(new Scalar\String_($this->format)),
                new Arg(new Scalar\String_('date')),
            ]),
            'setTime',
            [
                new Arg(new Scalar\LNumber(0)),
                new Arg(new Scalar\LNumber(0)),
                new Arg(new Scalar\LNumber(0)),
            ]
        );
    }

    #[Override]
    protected function createNormalizationValueStatement(Context $context, Expr $input, bool $normalizerFromObject = true): Expr
    {
        // Always use regular method call — the normalizer wraps nullable properties
        // in a null guard, so nullsafe ?-> is redundant and triggers nullsafe.neverNull
        return new Expr\MethodCall($input, 'format', [
            new Arg(new Scalar\String_($this->format)),
        ]);
    }
}
