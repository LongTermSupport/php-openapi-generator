<?php

declare(strict_types=1);

namespace LongTermSupport\OpenApiGenerator\Component\GeneratorCore\Generator;

use LongTermSupport\OpenApiGenerator\Component\GeneratorCore\Generator\Context\Context;
use LongTermSupport\OpenApiGenerator\Component\GeneratorCore\Generator\Normalizer\DenormalizerGenerator;
use LongTermSupport\OpenApiGenerator\Component\GeneratorCore\Generator\Normalizer\JaneObjectNormalizerGenerator;
use LongTermSupport\OpenApiGenerator\Component\GeneratorCore\Generator\Normalizer\NormalizerGenerator as NormalizerGeneratorTrait;
use LongTermSupport\OpenApiGenerator\Component\GeneratorCore\Registry\Schema;
use PhpParser\Comment;
use PhpParser\Comment\Doc;
use PhpParser\Modifiers;
use PhpParser\Node\Expr;
use PhpParser\Node\Identifier;
use PhpParser\Node\Name;
use PhpParser\Node\NullableType;
use PhpParser\Node\Param;
use PhpParser\Node\Stmt;
use PhpParser\Parser;

class NormalizerGenerator implements GeneratorInterface
{
    use DenormalizerGenerator;
    use JaneObjectNormalizerGenerator;
    use NormalizerGeneratorTrait;

    public const FILE_TYPE_NORMALIZER = 'normalizer';

    /**
     * @param Naming $naming                     The naming service
     * @param Parser $parser                     PHP Parser
     * @param bool   $useReference               Whether to generate the JSON Reference system
     * @param bool   $useCacheableSupportsMethod Whether to use the CacheableSupportsMethodInterface interface, for >sf 4.1
     * @param bool   $skipNullValues             Skip null values or not
     * @param bool   $skipRequiredFields         if we handle required fields or not during Normalizer generation
     * @param bool   $validation                 if we run validation or not during normalization/denormalization
     * @param bool   $includeNullValue           include null value in conditions
     */
    public function __construct(
        protected Naming $naming,
        protected Parser $parser,
        protected bool $useReference = true,
        protected ?bool $useCacheableSupportsMethod = null,
        protected bool $skipNullValues = true,
        protected bool $skipRequiredFields = false,
        protected bool $validation = false,
        protected bool $includeNullValue = true,
    ) {
    }

    /**
     * {@inheritdoc}
     */
    public function generate(Schema $schema, string $className, Context $context): void
    {
        $normalizers = [];

        foreach ($schema->getClasses() as $class) {
            $modelFqdn = $schema->getNamespace() . '\Model\\' . $class->getName();

            $methods   = [];
            $methods[] = $this->createSupportsDenormalizationMethod($modelFqdn);
            $methods[] = $this->createSupportsNormalizationMethod($modelFqdn);
            $methods[] = $this->createDenormalizeMethod($modelFqdn, $context, $class);
            $methods[] = $this->createNormalizeMethod($modelFqdn, $context, $class, $this->skipNullValues, $this->skipRequiredFields, $this->includeNullValue);
            $methods[] = $this->createGetSupportedTypesMethod($modelFqdn, true === $this->useCacheableSupportsMethod);

            if (true === $this->useCacheableSupportsMethod) {
                $methods[] = $this->createHasCacheableSupportsMethod();
            }

            $symfony7NormalizerClass = $this->createNormalizerClass(
                $class->getName() . 'Normalizer',
                $methods
            );

            $useStmts = [
                new Stmt\Use_([new Stmt\UseUse(new Name($this->naming->getRuntimeClassFQCN($schema->getNamespace(), ['Normalizer'], 'Reference')))]),
                new Stmt\Use_([new Stmt\UseUse(new Name($this->naming->getRuntimeClassFQCN($schema->getNamespace(), ['Normalizer'], 'CheckArray')))]),
                new Stmt\Use_([new Stmt\UseUse(new Name($this->naming->getRuntimeClassFQCN($schema->getNamespace(), ['Normalizer'], 'ValidatorTrait')))]),
                new Stmt\Use_([new Stmt\UseUse(new Name($this->naming->getRuntimeClassFQCN($schema->getNamespace(), ['Normalizer'], 'TypeValidator')))]),
                new Stmt\Use_([new Stmt\UseUse(new Name(\Symfony\Component\Serializer\Normalizer\DenormalizerAwareInterface::class))]),
                new Stmt\Use_([new Stmt\UseUse(new Name(\Symfony\Component\Serializer\Normalizer\DenormalizerAwareTrait::class))]),
                new Stmt\Use_([new Stmt\UseUse(new Name(\Symfony\Component\Serializer\Normalizer\DenormalizerInterface::class))]),
                new Stmt\Use_([new Stmt\UseUse(new Name(\Symfony\Component\Serializer\Normalizer\NormalizerAwareInterface::class))]),
                new Stmt\Use_([new Stmt\UseUse(new Name(\Symfony\Component\Serializer\Normalizer\NormalizerAwareTrait::class))]),
                new Stmt\Use_([new Stmt\UseUse(new Name(\Symfony\Component\Serializer\Normalizer\NormalizerInterface::class))]),
            ];

            if (true === $this->useCacheableSupportsMethod) {
                $useStmts[] = new Stmt\Use_([new Stmt\UseUse(new Name('Symfony\Component\Serializer\Normalizer\CacheableSupportsMethodInterface'))]);
            }

            $useStmts = array_merge($useStmts, [$symfony7NormalizerClass]);

            $namespace               = new Stmt\Namespace_(new Name($schema->getNamespace() . '\Normalizer'), $useStmts);
            $normalizers[$modelFqdn] = $schema->getNamespace() . '\Normalizer\\' . $symfony7NormalizerClass->name;
            $schema->addFile(new File($schema->getDirectory() . '/Normalizer/' . $symfony7NormalizerClass->name . '.php', $namespace, self::FILE_TYPE_NORMALIZER));
        }

        $schema->addFile(new File(
            $schema->getDirectory() . '/Normalizer/JaneObjectNormalizer.php',
            new Stmt\Namespace_(new Name($schema->getNamespace() . '\Normalizer'), $this->createJaneObjectNormalizerClass($schema, $normalizers)),
            self::FILE_TYPE_NORMALIZER
        ));
    }

    /**
     * The naming service.
     */
    protected function getNaming(): Naming
    {
        return $this->naming;
    }

    /**
     * @param array<string, string> $normalizers
     *
     * @return array<int, Stmt>
     */
    protected function createJaneObjectNormalizerClass(Schema $schema, array $normalizers): array
    {
        if ($this->useReference) {
            $normalizers[$this->naming->getRuntimeClassFQCN($schema->getNamespace(), ['Normalizer'], 'Reference')] = $this->naming->getRuntimeClassFQCN($schema->getNamespace(), ['Normalizer'], 'ReferenceNormalizer');
        }

        $properties   = [];
        $propertyName = $this->getNaming()->getPropertyName('normalizers');
        $properties[] = new Stmt\PropertyProperty(
            $propertyName,
            new Expr\Array_(
                array_map(
                    static fn (string $key, string $value): Expr\ArrayItem => new Expr\ArrayItem(
                        new Expr\ClassConstFetch(
                            new Name\FullyQualified($value),
                            new Identifier('class')
                        ),
                        new Expr\ClassConstFetch(
                            new Name\FullyQualified($key),
                            new Identifier('class')
                        ),
                        attributes: ['comments' => [new Comment('')]]
                    ),
                    array_keys($normalizers),
                    $normalizers,
                ),
            )
        );
        $properties[] = new Stmt\PropertyProperty('normalizersCache', new Expr\Array_());

        $methods         = [];
        $normalizersProp = new Stmt\Property(Modifiers::PROTECTED, [$properties[0]], [
            'comments' => [new Doc("/**\n * @var array<class-string, class-string>\n */")],
        ], new Identifier('array'));
        $normalizersCacheProp = new Stmt\Property(Modifiers::PROTECTED, [$properties[1]], [
            'comments' => [new Doc("/**\n * @var array<string, NormalizerInterface&DenormalizerInterface&NormalizerAwareInterface&DenormalizerAwareInterface>\n */")],
        ], new Identifier('array'));
        $methods[] = $normalizersProp;
        $methods[] = $normalizersCacheProp;
        $methods[] = $this->createBaseNormalizerSupportsDenormalizationMethod();
        $methods[] = $this->createBaseNormalizerSupportsNormalizationMethod();
        $methods[] = $this->createBaseNormalizerNormalizeMethod();
        $methods[] = $this->createBaseNormalizerDenormalizeMethod();
        $methods[] = $this->createBaseNormalizerGetNormalizer();
        $methods[] = $this->createBaseNormalizerInitNormalizerMethod();
        $methods[] = $this->createProxyGetSupportedTypesMethod(array_keys($normalizers));

        if (true === $this->useCacheableSupportsMethod) {
            $methods[] = $this->createHasCacheableSupportsMethod();
        }

        $symfony7NormalizerClass = $this->createNormalizerClass(
            'JaneObjectNormalizer',
            $methods
        );

        $useStmts = [
            new Stmt\Use_([new Stmt\UseUse(new Name($this->naming->getRuntimeClassFQCN($schema->getNamespace(), ['Normalizer'], 'CheckArray')))]),
            new Stmt\Use_([new Stmt\UseUse(new Name($this->naming->getRuntimeClassFQCN($schema->getNamespace(), ['Normalizer'], 'ValidatorTrait')))]),
            new Stmt\Use_([new Stmt\UseUse(new Name($this->naming->getRuntimeClassFQCN($schema->getNamespace(), ['Normalizer'], 'TypeValidator')))]),
            new Stmt\Use_([new Stmt\UseUse(new Name(\Symfony\Component\Serializer\Normalizer\DenormalizerAwareInterface::class))]),
            new Stmt\Use_([new Stmt\UseUse(new Name(\Symfony\Component\Serializer\Normalizer\DenormalizerAwareTrait::class))]),
            new Stmt\Use_([new Stmt\UseUse(new Name(\Symfony\Component\Serializer\Normalizer\DenormalizerInterface::class))]),
            new Stmt\Use_([new Stmt\UseUse(new Name(\Symfony\Component\Serializer\Normalizer\NormalizerAwareInterface::class))]),
            new Stmt\Use_([new Stmt\UseUse(new Name(\Symfony\Component\Serializer\Normalizer\NormalizerAwareTrait::class))]),
            new Stmt\Use_([new Stmt\UseUse(new Name(\Symfony\Component\Serializer\Normalizer\NormalizerInterface::class))]),
        ];

        if (true === $this->useCacheableSupportsMethod) {
            $useStmts[] = new Stmt\Use_([new Stmt\UseUse(new Name('Symfony\Component\Serializer\Normalizer\CacheableSupportsMethodInterface'))]);
        }

        return array_merge($useStmts, [$symfony7NormalizerClass]);
    }

    /**
     * Create a method to return the supported type.
     *
     * @param string $modelFqdn Fully Qualified name of the model class denormalized
     */
    protected function createGetSupportedTypesMethod(string $modelFqdn, bool $useCacheableSupportsMethod = false): Stmt\ClassMethod
    {
        return new Stmt\ClassMethod('getSupportedTypes', [
            'flags'      => Modifiers::PUBLIC,
            'returnType' => new Identifier('array'),
            'params'     => [
                new Param(new Expr\Variable('format'), new Expr\ConstFetch(new Name('null')), new NullableType(new Identifier('string'))),
            ],
            'stmts'      => [new Stmt\Return_(new Expr\Array_([
                new Expr\ArrayItem(
                    new Expr\ConstFetch(new Name($useCacheableSupportsMethod ? 'true' : 'false')),
                    new Expr\ClassConstFetch(
                        new Name\FullyQualified($modelFqdn),
                        new Identifier('class')
                    )
                ),
            ]))],
        ], [
            'comments' => [new Doc("/**\n * @return array<class-string, bool>\n */")],
        ]);
    }

    /**
     * Create a method to return the supported type.
     *
     * @param string[] $modelsFqdn Fully Qualified name of the models class denormalized
     */
    protected function createProxyGetSupportedTypesMethod(array $modelsFqdn): Stmt\ClassMethod
    {
        $arrayItems = [];
        foreach ($modelsFqdn as $modelFqdn) {
            $arrayItems[] = new Expr\ArrayItem(
                new Expr\ConstFetch(new Name('false')), // we don't want proxy Normalizer to be cached, never
                new Expr\ClassConstFetch(
                    new Name\FullyQualified($modelFqdn),
                    new Identifier('class')
                ),
            );
        }

        if (isset($arrayItems[0])) {
            // force the array to be dumped multiline by adding a comment
            $arrayItems[0]->setAttribute('comments', [new Comment('')]);
        }

        return new Stmt\ClassMethod('getSupportedTypes', [
            'flags'      => Modifiers::PUBLIC,
            'returnType' => new Identifier('array'),
            'params'     => [
                new Param(new Expr\Variable('format'), new Expr\ConstFetch(new Name('null')), new NullableType(new Identifier('string'))),
            ],
            'stmts'      => [new Stmt\Return_(new Expr\Array_($arrayItems))],
        ], [
            'comments' => [new Doc("/**\n * @return array<class-string, bool>\n */")],
        ]);
    }
}
