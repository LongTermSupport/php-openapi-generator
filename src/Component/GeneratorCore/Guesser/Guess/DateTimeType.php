<?php

declare(strict_types=1);

namespace LongTermSupport\OpenApiGenerator\Component\GeneratorCore\Guesser\Guess;

use DateTimeInterface;
use LongTermSupport\OpenApiGenerator\Component\GeneratorCore\Generator\Context\Context;
use Override;
use PhpParser\Node\Arg;
use PhpParser\Node\Expr;
use PhpParser\Node\Name;
use PhpParser\Node\Scalar;

/**
 * Represent a DateTime type.
 */
class DateTimeType extends ObjectType
{
    use CheckNullableTrait;

    /**
     * Format of the date to use when denormalized.
     */
    private string $inputFormat;

    /**
     * @param bool|null $preferInterface indicator whether to use DateTime or DateTimeInterface as type hint
     */
    public function __construct(
        object $object,
        /**
         * Format of the date to use when normalized.
         */
        private string $outputFormat = DateTimeInterface::RFC3339,
        ?string $inputFormat = null,
        private ?bool $preferInterface = null,
    ) {
        parent::__construct($object, '\DateTime', '');
        $this->inputFormat     = $inputFormat     ?? $this->outputFormat;
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
        return new Expr\BinaryOp\LogicalAnd(
            new Expr\FuncCall(
                new Name('is_string'),
                [
                    new Arg($input),
                ]
            ),
            new Expr\BinaryOp\NotIdentical(
                new Expr\ConstFetch(new Name('false')),
                $this->generateParseExpression($input)
            )
        );
    }

    #[Override]
    public function getTypeHint(string $namespace): Name
    {
        return new Name(true === $this->preferInterface ? '\DateTimeInterface' : '\DateTime');
    }

    #[Override]
    protected function createDenormalizationValueStatement(Context $context, Expr $input, bool $normalizerFromObject = true): Expr
    {
        if ('' === $this->inputFormat) {
            // Fall back to raw parse expression for format-less parsing
            return $this->generateParseExpression($input);
        }

        // TypeValidator::assertDateTime($input, $format, $field) — returns \DateTime, never false
        return new Expr\StaticCall(new Name('TypeValidator'), 'assertDateTime', [
            new Arg($input),
            new Arg(new Scalar\String_($this->inputFormat)),
            new Arg(new Scalar\String_('datetime')),
        ]);
    }

    #[Override]
    protected function createNormalizationValueStatement(Context $context, Expr $input, bool $normalizerFromObject = true): Expr
    {
        // Always use regular method call — the normalizer wraps nullable properties
        // in a null guard, so nullsafe ?-> is redundant and triggers nullsafe.neverNull
        return new Expr\MethodCall($input, 'format', [
            new Arg(new Scalar\String_($this->outputFormat)),
        ]);
    }

    protected function generateParseExpression(Expr $input): Expr
    {
        if ('' === $this->inputFormat) {
            // new \DateTime($data)
            $new = new Expr\New_(new Name('\DateTime'), [new Arg($input)]);
            // (new \DateTime($data))->getTimezone()->getName()
            $timezoneName = new Expr\MethodCall(
                new Expr\MethodCall($new, 'getTimezone'),
                'getName'
            );
            // new \DateTimeZone('GMT')
            $gmtTimezone = new Expr\New_(new Name('\DateTimeZone'), [new Arg(new Scalar\String_('GMT'))]);

            // (new \DateTime($data))->getTimezone()->getName() === 'Z' ? (new \DateTime($data))->setTimezone(new \DateTimeZone('GMT')) : \DateTime($data)
            return new Expr\Ternary(
                new Expr\BinaryOp\Identical($timezoneName, new Scalar\String_('Z')),
                new Expr\MethodCall($new, 'setTimezone', [new Arg($gmtTimezone)]),
                $new
            );
        }

        // \DateTime::createFromFormat($format, $data)
        return new Expr\StaticCall(new Name('\DateTime'), 'createFromFormat', [
            new Arg(new Scalar\String_($this->inputFormat)),
            new Arg($input),
        ]);
    }
}
