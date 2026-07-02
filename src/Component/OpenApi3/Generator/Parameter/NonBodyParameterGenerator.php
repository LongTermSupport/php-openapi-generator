<?php

declare(strict_types=1);

namespace LongTermSupport\OpenApiGenerator\Component\OpenApi3\Generator\Parameter;

use LogicException;
use LongTermSupport\OpenApiGenerator\Component\GeneratorCore\Generator\Context\Context;
use LongTermSupport\OpenApiGenerator\Component\OpenApi3\Guesser\GuessClass;
use LongTermSupport\OpenApiGenerator\Component\OpenApi3\JsonSchema\Model\Parameter;
use LongTermSupport\OpenApiGenerator\Component\OpenApi3\JsonSchema\Model\Schema;
use LongTermSupport\OpenApiGenerator\Component\OpenApiCommon\Generator\Parameter\ParameterGenerator;
use LongTermSupport\OpenApiGenerator\Component\OpenApiCommon\Generator\Traits\OptionResolverNormalizationTrait;
use LongTermSupport\OpenApiGenerator\Component\OpenApiRuntime\Reference;
use Override;
use PhpParser\Node;
use PhpParser\Node\Expr;
use PhpParser\Node\Scalar;
use PhpParser\Node\Stmt;
use PhpParser\Parser;
use Psr\Http\Message\StreamInterface;
use Symfony\Component\Serializer\Normalizer\DenormalizerInterface;

/**
 * @internal
 */
class NonBodyParameterGenerator extends ParameterGenerator
{
    use OptionResolverNormalizationTrait;

    private GuessClass $guessClass;

    public function __construct(DenormalizerInterface $denormalizer, Parser $parser)
    {
        parent::__construct($parser);
        $this->guessClass = new GuessClass(Schema::class, $denormalizer);
    }

    public function generateMethodParameter(mixed $parameter, Context $context, string $reference): ?Node\Param
    {
        if (!$parameter instanceof Parameter) {
            throw new LogicException('Expected Parameter, got ' . get_debug_type($parameter));
        }

        $parameterName = $parameter->getName();
        if (!\is_string($parameterName)) {
            throw new LogicException('Expected string parameter name, got ' . get_debug_type($parameterName));
        }

        $name            = $this->getInflector()->camelize($parameterName);
        $methodParameter = new Node\Param(new Expr\Variable($name));

        $schema = $parameter->getSchema();
        if (!$schema instanceof Schema) {
            // Swagger 2.0 parameters may not have a nested Schema object.
            // Default to string since path/query parameters are always string-representable.
            $methodParameter->type = new Node\Name('string');

            return $methodParameter;
        }

        if (true !== $parameter->getRequired() || null !== $schema->getDefault()) {
            $methodParameter->default = $this->getDefaultAsExpr($parameter);
        }

        $branches = $schema->getAnyOf();
        if (null === $branches || [] === $branches) {
            // oneOf is semantically "exactly one" rather than anyOf's "at least one", but for
            // native-PHP-type-resolution purposes both are a set of candidate branch schemas —
            // resolved identically here.
            $branches = $schema->getOneOf();
        }

        if (null !== $branches && [] !== $branches) {
            return $this->resolveBranchTypedParameter($branches, $methodParameter);
        }

        $types = $this->convertParameterType($schema);

        if (1 === \count($types)) {
            $methodParameter->type = new Node\Name($types[0]);
        } elseif (\count($types) > 1) {
            $methodParameter->type = new Node\UnionType(array_map(
                static fn (string $t): Node\Identifier|Node\Name => \in_array($t, ['int', 'float', 'bool', 'string', 'array', 'null'], true)
                    ? new Node\Identifier($t)
                    : new Node\Name($t),
                $types
            ));
        }

        return $methodParameter;
    }

    /**
     * Resolves a set of anyOf/oneOf branch schemas to a native PHP type on the given
     * parameter — a union type if the branches resolve to more than one distinct type
     * (e.g. int|string), or that single type if every branch resolves to the same one
     * (e.g. a oneOf of two differently-formatted strings collapses to plain `string`).
     * Leaves the parameter untyped if any branch is unresolvable ('mixed').
     *
     * @param array<Reference|Schema> $branches
     */
    private function resolveBranchTypedParameter(array $branches, Node\Param $methodParameter): Node\Param
    {
        $branchTypes = [];
        foreach ($branches as $branchSchema) {
            if ($branchSchema instanceof Reference) {
                [, $branchSchema] = $this->guessClass->resolve($branchSchema, Schema::class);
            }

            if ($branchSchema instanceof Schema) {
                foreach ($this->convertParameterType($branchSchema) as $t) {
                    $branchTypes[$t] = true;
                }
            }
        }

        $uniqueTypes = array_keys($branchTypes);

        // If any type is 'mixed' or no types resolved, leave parameter untyped
        if ([] === $uniqueTypes || isset($branchTypes['mixed'])) {
            return $methodParameter;
        }

        $methodParameter->type = 1 === \count($uniqueTypes)
            ? new Node\Name($uniqueTypes[0])
            : new Node\UnionType(array_map(
                static fn (string $t): Node\Identifier|Node\Name => \in_array($t, ['int', 'float', 'bool', 'string', 'array', 'null'], true)
                    ? new Node\Identifier($t)
                    : new Node\Name($t),
                $uniqueTypes
            ));

        return $methodParameter;
    }

    /**
     * @param Parameter[]          $parameters
     * @param array<string, mixed> $genericResolver
     *
     * @return array<mixed>
     */
    public function generateOptionsResolverStatements(Expr\Variable $optionsResolverVariable, array $parameters, array $genericResolver = []): array
    {
        $required            = [];
        $allowedTypes        = [];
        $defined             = [];
        $defaults            = [];
        $genericResolverKeys = array_keys($genericResolver);

        foreach ($parameters as $parameter) {
            $parameterNameRaw = $parameter->getName();
            if (!\is_string($parameterNameRaw)) {
                throw new LogicException('Expected string parameter name, got ' . get_debug_type($parameterNameRaw));
            }

            $parameterName = $parameterNameRaw;
            if (str_contains($parameterName, '[]')) {
                $parameterName = substr($parameterName, 0, -2);
            }

            if (!\array_key_exists($parameterName, $defined)) {
                $defined[$parameterName] = new Expr\ArrayItem(new Scalar\String_($parameterName));
            }

            $schema = $parameter->getSchema();

            if ($schema instanceof Reference) {
                [, $schema] = $this->guessClass->resolve($schema, Schema::class);
            }

            if ($schema instanceof Schema && true === $parameter->getRequired() && null === $schema->getDefault()) {
                $required[] = new Expr\ArrayItem(new Scalar\String_($parameterName));
            }

            $matchGenericResolver = null;
            if ($schema instanceof Schema && null !== $schema->getType()) {
                $types = [];

                foreach ($this->convertParameterType($schema) as $typeString) {
                    if (\in_array($typeString, $genericResolverKeys, true)) {
                        $matchGenericResolver = $typeString;
                    }

                    $types[] = new Expr\ArrayItem(new Scalar\String_($typeString));
                }

                if (true === $schema->getNullable()) {
                    $types[] = new Expr\ArrayItem(new Scalar\String_('null'));
                }

                $allowedTypes[] = new Stmt\Expression(new Expr\MethodCall($optionsResolverVariable, 'addAllowedTypes', [
                    new Node\Arg(new Scalar\String_($parameterName)),
                    new Node\Arg(new Expr\Array_($types)),
                ]));
            }

            if (true !== $parameter->getRequired() && $schema instanceof Schema && null !== $schema->getDefault()) {
                $defaults[] = new Expr\ArrayItem($this->getDefaultAsExpr($parameter), new Scalar\String_($parameterName));
            }

            if (null !== $matchGenericResolver) {
                if (!\is_string($genericResolver[$matchGenericResolver])) {
                    throw new LogicException('Expected string, got ' . get_debug_type($genericResolver[$matchGenericResolver]));
                }

                $allowedTypes[] = $this->generateOptionResolverNormalizationStatement($parameterName, $genericResolver[$matchGenericResolver]);
            }
        }

        return array_merge([
            new Stmt\Expression(new Expr\MethodCall($optionsResolverVariable, 'setDefined', [
                new Node\Arg(new Expr\Array_(array_values($defined))),
            ])),
            new Stmt\Expression(new Expr\MethodCall($optionsResolverVariable, 'setRequired', [
                new Node\Arg(new Expr\Array_($required)),
            ])),
            new Stmt\Expression(new Expr\MethodCall($optionsResolverVariable, 'setDefaults', [
                new Node\Arg(new Expr\Array_($defaults)),
            ])),
        ], $allowedTypes);
    }

    #[Override]
    public function generateMethodDocParameter(mixed $parameter, Context $context, string $reference): string
    {
        if (!$parameter instanceof Parameter) {
            throw new LogicException('Expected Parameter, got ' . get_debug_type($parameter));
        }

        $type = 'string';

        $schema = $parameter->getSchema();
        if ($schema instanceof Schema) {
            $branches = $schema->getAnyOf();
            if (null === $branches || [] === $branches) {
                $branches = $schema->getOneOf();
            }

            if (null === $branches || [] === $branches) {
                $type = implode('|', $this->convertParameterTypeForDoc($schema));
            } else {
                // Resolve anyOf/oneOf types for PHPDoc (must match native union type
                // resolved by resolveBranchTypedParameter())
                $branchTypes = [];
                foreach ($branches as $branchSchema) {
                    if ($branchSchema instanceof Reference) {
                        [, $branchSchema] = $this->guessClass->resolve($branchSchema, Schema::class);
                    }

                    if ($branchSchema instanceof Schema) {
                        foreach ($this->convertParameterType($branchSchema) as $t) {
                            $branchTypes[$t] = true;
                        }
                    }
                }

                $uniqueTypes = array_keys($branchTypes);
                if ([] !== $uniqueTypes && !isset($branchTypes['mixed'])) {
                    $type = implode('|', $uniqueTypes);
                }
            }
        }

        $description = (string)$parameter->getDescription();

        $docParamName = $parameter->getName();
        if (!\is_string($docParamName)) {
            throw new LogicException('Expected string parameter name, got ' . get_debug_type($docParamName));
        }

        return rtrim(\sprintf(' * @param %s $%s %s', $type, $this->getInflector()->camelize($docParamName), $description));
    }

    public function generateOptionDocParameter(Parameter $parameter): string
    {
        $type = 'mixed';

        if ($parameter->getSchema() instanceof Schema) {
            $type = implode('|', $this->convertParameterTypeForDoc($parameter->getSchema()));
        }

        $optionParamName = $parameter->getName();
        if (!\is_string($optionParamName)) {
            throw new LogicException('Expected string parameter name, got ' . get_debug_type($optionParamName));
        }

        return \sprintf(
            ' *    "%s"?: %s,',
            $optionParamName,
            $type,
        );
    }

    /**
     * Generate a default value as an Expr.
     */
    private function getDefaultAsExpr(Parameter $parameter): Expr
    {
        $schema  = $parameter->getSchema();
        $default = $schema instanceof Schema ? $schema->getDefault() : null;
        $parsed  = $this->parser->parse('<?php ' . var_export($default, true) . ';');
        if (null === $parsed || [] === $parsed) {
            throw new LogicException('Expected non-empty parsed result');
        }

        $firstStmt = $parsed[0];
        if ($firstStmt instanceof Stmt\Expression) {
            return $firstStmt->expr;
        }

        if (!$firstStmt instanceof Expr) {
            throw new LogicException('Expected Expr, got ' . get_debug_type($firstStmt));
        }

        return $firstStmt;
    }

    /**
     * Like convertParameterType but returns PHPStan-compatible types
     * (with value types for iterables to avoid missingType.iterableValue).
     *
     * @return list<string>
     */
    private function convertParameterTypeForDoc(Schema $schema): array
    {
        $types = $this->convertParameterType($schema);

        $itemsType = $this->resolveItemsType($schema);

        return array_map(static fn (string $type): string => match ($type) {
            'array' => 'list<' . $itemsType . '>',
            default => $type,
        }, $types);
    }

    private function resolveItemsType(Schema $schema): string
    {
        $items = $schema->getItems();
        if (!$items instanceof Schema) {
            return 'mixed';
        }

        $convertMap = [
            'string'  => 'string',
            'integer' => 'int',
            'number'  => 'float',
            'boolean' => 'bool',
        ];

        $itemType = $items->getType();
        if (\is_string($itemType) && isset($convertMap[$itemType])) {
            return $convertMap[$itemType];
        }

        return 'mixed';
    }

    /**
     * @return list<string>
     */
    private function convertParameterType(Schema $schema): array
    {
        $type                 = $schema->getType();
        $additionalProperties = $schema->getAdditionalProperties();

        if (null === $type && null !== $schema->getEnum() && [] !== $schema->getEnum()) {
            $type = 'string';
        }

        if ($additionalProperties instanceof Schema
            && 'object' === $type
            && 'string' === $additionalProperties->getType()) {
            return ['string'];
        }

        $convertArray = [
            'string'  => ['string'],
            'number'  => ['float'],
            'boolean' => ['bool'],
            'integer' => ['int'],
            'array'   => ['array'],
            'object'  => ['array'],
            'file'    => ['string', 'resource', '\\' . StreamInterface::class],
        ];

        // OAS 3.1 allows type to be an array of scalar types (e.g. ["string", "null", "integer"]).
        // SchemaNormalizer strips "null" from the type array and sets nullable=true instead,
        // so we must check getNullable() to restore null into the union.
        if (\is_array($type)) {
            $result = [];
            foreach ($type as $t) {
                if (!\is_string($t)) {
                    continue;
                }

                if ('null' === $t) {
                    $result['null'] = true;
                    continue;
                }

                if (\array_key_exists($t, $convertArray)) {
                    foreach ($convertArray[$t] as $mapped) {
                        $result[$mapped] = true;
                    }
                } else {
                    $result['mixed'] = true;
                }
            }

            if (true === $schema->getNullable()) {
                $result['null'] = true;
            }

            $keys = array_keys($result);

            return [] === $keys ? ['mixed'] : $keys;
        }

        if (!\is_string($type) || !\array_key_exists($type, $convertArray)) {
            return ['mixed'];
        }

        return $convertArray[$type];
    }
}
