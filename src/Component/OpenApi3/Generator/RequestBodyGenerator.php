<?php

declare(strict_types=1);

namespace LongTermSupport\OpenApiGenerator\Component\OpenApi3\Generator;

use LogicException;
use LongTermSupport\OpenApiGenerator\Component\GeneratorCore\Generator\Context\Context;
use LongTermSupport\OpenApiGenerator\Component\OpenApi3\Generator\RequestBodyContent\AbstractBodyContentGenerator;
use LongTermSupport\OpenApiGenerator\Component\OpenApi3\JsonSchema\Model\Reference;
use LongTermSupport\OpenApiGenerator\Component\OpenApi3\JsonSchema\Model\RequestBody;
use PhpParser\Node\Expr;
use PhpParser\Node\Name;
use PhpParser\Node\Param;
use PhpParser\Node\Stmt;

class RequestBodyGenerator
{
    /** @var RequestBodyContentGeneratorInterface[] */
    private array $generators = [];

    public function __construct(
        private readonly RequestBodyContentGeneratorInterface $defaultRequestBodyGenerator,
    ) {
    }

    /**
     * @param array<string> $contentTypes
     */
    public function addRequestBodyGenerator(array $contentTypes, RequestBodyContentGeneratorInterface $requestBodyGenerator): void
    {
        foreach ($contentTypes as $contentType) {
            $this->generators[$contentType] = $requestBodyGenerator;
        }
    }

    public function generateMethodParameter(RequestBody|Reference $requestBody, string $reference, Context $context): ?Param
    {
        $requestBodyObj = $requestBody instanceof RequestBody ? $requestBody : null;
        if (!$requestBodyObj instanceof RequestBody || null === $requestBodyObj->getContent()) {
            return null;
        }

        $name                = 'requestBody';
        [$types, $onlyArray] = $this->getTypes($requestBodyObj, $reference, $context);
        if (!\is_array($types)) {
            throw new LogicException('Expected array, got ' . get_debug_type($types));
        }

        $paramType = null;
        if (1 === \count($types) && AbstractBodyContentGenerator::PHP_TYPE_MIXED !== $types[0]) {
            $paramType = $types[0];
        }

        if (true === $onlyArray) {
            $paramType = 'array';
        }

        $paramTypeStr = \is_string($paramType) ? $paramType : null;

        $default = null;
        if (true !== $requestBodyObj->getRequired() || !$context->isStrict()) {
            $default      = new Expr\ConstFetch(new Name('null'));
            $paramTypeStr = null !== $paramTypeStr ? '?' . $paramTypeStr : null;
        }

        return new Param(new Expr\Variable($name), $default, null !== $paramTypeStr ? new Name($paramTypeStr) : null);
    }

    public function generateMethodDocParameter(RequestBody|Reference $requestBody, string $reference, Context $context): string
    {
        $requestBodyObj = $requestBody instanceof RequestBody ? $requestBody : null;
        [$types]        = $this->getTypes($requestBodyObj, $reference, $context);
        if (!\is_array($types)) {
            throw new LogicException('Expected array, got ' . get_debug_type($types));
        }

        if (!$requestBodyObj instanceof RequestBody || true !== $requestBodyObj->getRequired() || !$context->isStrict()) {
            array_unshift($types, 'null');
        }

        // Qualify bare 'array' to 'array<mixed>' so PHPStan doesn't report missingType.iterableValue
        // Handles: 'array' → 'array<mixed>', 'array[]' → 'array<mixed>[]'
        $qualifiedTypes = [];
        foreach ($types as $t) {
            if (!\is_string($t)) {
                throw new LogicException('Expected string type element, got ' . get_debug_type($t));
            }

            $replaced = \Safe\preg_replace('/\barray\b(?!<)/', 'array<mixed>', $t);
            if (!\is_string($replaced)) {
                throw new LogicException('Expected string from preg_replace, got ' . get_debug_type($replaced));
            }

            $qualifiedTypes[] = $replaced;
        }

        return \sprintf(' * @param %s $%s', implode('|', $qualifiedTypes), 'requestBody');
    }

    /**
     * @return array<mixed>
     */
    public function getSerializeStatements(?RequestBody $requestBody, string $reference, Context $context): array
    {
        if (!$requestBody instanceof RequestBody || null === $requestBody->getContent()) {
            return [
                new Stmt\Return_(new Expr\Array_([
                    new Expr\ArrayItem(new Expr\Array_()),
                    new Expr\ArrayItem(new Expr\ConstFetch(new Name('null'))),
                ])),
            ];
        }

        $statements     = [];
        $seenConditions = [];
        $printer        = new \PhpParser\PrettyPrinter\Standard();

        foreach ($requestBody->getContent() as $contentType => $content) {
            $generator = $this->defaultRequestBodyGenerator;

            if (\array_key_exists($contentType, $this->generators)) {
                $generator = $this->generators[$contentType];
            } elseif (str_starts_with($contentType, 'application/json') || str_ends_with($contentType, '+json')) {
                $generator = $this->generators['application/json'];
            }

            $typeCondition = $generator->getTypeCondition($content, $reference . '/content/' . $contentType, $context);
            if (!$typeCondition instanceof Expr) {
                throw new LogicException('Expected Expr, got ' . get_debug_type($typeCondition));
            }

            // Skip duplicate type conditions (e.g. same model class used by multiple content types)
            $conditionKey = $printer->prettyPrintExpr($typeCondition);
            if (isset($seenConditions[$conditionKey])) {
                continue;
            }

            $seenConditions[$conditionKey] = true;

            /** @var array<Stmt> $serializeStmts */
            $serializeStmts = $generator->getSerializeStatements($content, $contentType, $reference . '/content/' . $contentType, $context);

            $statements[] = new Stmt\If_(
                $typeCondition,
                [
                    'stmts' => $serializeStmts,
                ]
            );
        }

        $statements[] = new Stmt\Return_(new Expr\Array_([
            new Expr\ArrayItem(new Expr\Array_()),
            new Expr\ArrayItem(new Expr\ConstFetch(new Name('null'))),
        ]));

        return $statements;
    }

    /**
     * @return array<mixed>
     */
    private function getTypes(?RequestBody $requestBody, string $reference, Context $context): array
    {
        /** @var array<string> $types */
        $types = [];

        if (!$requestBody instanceof RequestBody || null === $requestBody->getContent()) {
            return [$types, null];
        }

        $onlyArray = null;

        foreach ($requestBody->getContent() as $contentType => $content) {
            $generator = $this->defaultRequestBodyGenerator;

            if (isset($this->generators[$contentType])) {
                $generator = $this->generators[$contentType];
            }

            [$newTypes, $isArray] = $generator->getTypes($content, $reference . '/content/' . $contentType, $context);
            if (!\is_array($newTypes)) {
                throw new LogicException('Expected array, got ' . get_debug_type($newTypes));
            }

            $isArrayBool = (bool)$isArray;
            $onlyArray   = null === $onlyArray ? $isArrayBool : $onlyArray && $isArrayBool;

            $types = array_merge($types, $newTypes);
        }

        return [array_unique($types), $onlyArray];
    }
}
