<?php

declare(strict_types=1);

namespace LongTermSupport\OpenApiGenerator\Component\OpenApi3;

use Generator;
use LogicException;
use LongTermSupport\OpenApiGenerator\Component\GeneratorCore\Generator\Naming;
use LongTermSupport\OpenApiGenerator\Component\GeneratorCore\Generator\ValidatorGenerator;
use LongTermSupport\OpenApiGenerator\Component\OpenApi3\Generator\EndpointGenerator;
use LongTermSupport\OpenApiGenerator\Component\OpenApi3\Generator\GeneratorFactory;
use LongTermSupport\OpenApiGenerator\Component\OpenApi3\Guesser\OpenApiSchema\GuesserFactory;
use LongTermSupport\OpenApiGenerator\Component\OpenApi3\SchemaParser\SchemaParser;
use LongTermSupport\OpenApiGenerator\Component\OpenApiCommon\Generator\AuthenticationGenerator;
use LongTermSupport\OpenApiGenerator\Component\OpenApiCommon\Generator\ModelGenerator;
use LongTermSupport\OpenApiGenerator\Component\OpenApiCommon\Generator\NormalizerGenerator;
use LongTermSupport\OpenApiGenerator\Component\OpenApiCommon\Generator\RuntimeGenerator;
use LongTermSupport\OpenApiGenerator\Component\OpenApiCommon\JaneOpenApi as CommonJaneOpenApi;
use PhpParser\ParserFactory;
use Symfony\Component\Serializer\Normalizer\DenormalizerInterface;

final class JaneOpenApi extends CommonJaneOpenApi
{
    protected const OBJECT_NORMALIZER_CLASS = JsonSchema\Normalizer\JaneObjectNormalizer::class;

    protected const WHITELIST_FETCH_CLASS = WhitelistedSchema::class;

    /**
     * @param array<string, mixed> $options
     */
    protected static function create(array $options = []): static
    {
        $serializer = self::buildSerializer();
        if (!$serializer instanceof DenormalizerInterface) {
            throw new LogicException('Expected DenormalizerInterface, got ' . get_debug_type($serializer));
        }

        return new self(
            SchemaParser::class,
            GuesserFactory::create($serializer, $options),
            isset($options['strict']) ? (bool)$options['strict'] : true
        );
    }

    /**
     * @param array<string, mixed> $options
     */
    protected static function generators(DenormalizerInterface $denormalizer, array $options = []): Generator
    {
        $naming = new Naming();
        $parser = new ParserFactory()->createForHostVersion();

        yield new ModelGenerator($naming, $parser);
        yield new NormalizerGenerator(
            $naming,
            $parser,
            isset($options['reference'])                     && (bool)$options['reference'],
            isset($options['use-cacheable-supports-method']) && (bool)$options['use-cacheable-supports-method'],
            isset($options['skip-null-values']) ? (bool)$options['skip-null-values'] : true,
            isset($options['skip-required-fields']) && (bool)$options['skip-required-fields'],
            isset($options['validation'])           && (bool)$options['validation'],
            isset($options['include-null-value']) ? (bool)$options['include-null-value'] : true,
        );
        yield new AuthenticationGenerator();
        $endpointGeneratorClass = (isset($options['endpoint-generator']) && \is_string($options['endpoint-generator'])) ? $options['endpoint-generator'] : EndpointGenerator::class;
        yield GeneratorFactory::build($denormalizer, $endpointGeneratorClass);
        yield new RuntimeGenerator($naming, $parser);
        if (isset($options['validation']) && (bool)$options['validation']) {
            yield new ValidatorGenerator($naming);
        }
    }
}
