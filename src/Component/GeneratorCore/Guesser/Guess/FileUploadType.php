<?php

declare(strict_types=1);

namespace LongTermSupport\OpenApiGenerator\Component\GeneratorCore\Guesser\Guess;

use LongTermSupport\OpenApiGenerator\Component\GeneratorCore\Generator\Context\Context;
use Override;
use PhpParser\Node\Arg;
use PhpParser\Node\Expr;
use PhpParser\Node\Identifier;
use PhpParser\Node\Name;
use PhpParser\Node\Name\FullyQualified;
use PhpParser\Node\Scalar;

/**
 * Type representing a multipart/form-data binary file upload property.
 *
 * Renders as the runtime FileUpload value object (`<rootNamespace>\Runtime\Client\FileUpload`)
 * in property/getter/setter signatures.
 *
 * Normalization extracts `$input->contents` so the JSON path still yields the
 * raw bytes when the model is serialised. Denormalization wraps a string into
 * a FileUpload with empty filename/contentType, sufficient for the rare cases
 * where the request body model is deserialised from JSON.
 *
 * Multipart endpoints bypass the normalize path entirely for binary fields —
 * they read the FileUpload directly off the body DTO and set the multipart
 * part's filename + Content-Type from the value object. See
 * `FormBodyContentGenerator::getSerializeStatements()`.
 *
 * @internal
 */
class FileUploadType extends Type
{
    /**
     * Fully qualified class name of the runtime FileUpload class, with leading
     * backslash, e.g. `\Foo\Bar\Runtime\Client\FileUpload`.
     */
    public function __construct(
        object $object,
        private readonly string $runtimeFqcn,
    ) {
        parent::__construct($object, 'object');
    }

    #[Override]
    public function __toString(): string
    {
        return $this->runtimeFqcn;
    }

    #[Override]
    public function getTypeHint(string $namespace): Name
    {
        return new Name($this->runtimeFqcn);
    }

    #[Override]
    public function getDocTypeHint(string $namespace): Name
    {
        return $this->getTypeHint($namespace);
    }

    #[Override]
    public function createConditionStatement(Expr $input): Expr
    {
        return new Expr\Instanceof_($input, new FullyQualified($this->stripLeadingBackslash($this->runtimeFqcn)));
    }

    #[Override]
    public function createNormalizationConditionStatement(Expr $input): Expr
    {
        return new Expr\Instanceof_($input, new FullyQualified($this->stripLeadingBackslash($this->runtimeFqcn)));
    }

    #[Override]
    protected function createNormalizationValueStatement(Context $context, Expr $input, bool $normalizerFromObject = true): Expr
    {
        // FileUpload->contents — the raw bytes.
        return new Expr\PropertyFetch($input, new Identifier('contents'));
    }

    #[Override]
    protected function createDenormalizationValueStatement(Context $context, Expr $input, bool $normalizerFromObject = true): Expr
    {
        // new \Foo\Bar\Runtime\Client\FileUpload(TypeValidator::assertString($input, 'value'), '', '')
        return new Expr\New_(
            new FullyQualified($this->stripLeadingBackslash($this->runtimeFqcn)),
            [
                new Arg(new Expr\StaticCall(
                    new Name('TypeValidator'),
                    'assertString',
                    [
                        new Arg($input),
                        new Arg(new Scalar\String_('value')),
                    ]
                )),
                new Arg(new Scalar\String_('')),
                new Arg(new Scalar\String_('')),
            ]
        );
    }

    private function stripLeadingBackslash(string $fqcn): string
    {
        return ltrim($fqcn, '\\');
    }
}
