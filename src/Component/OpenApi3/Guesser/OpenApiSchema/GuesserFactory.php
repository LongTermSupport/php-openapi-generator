<?php

declare(strict_types=1);

namespace LongTermSupport\OpenApiGenerator\Component\OpenApi3\Guesser\OpenApiSchema;

use DateTimeInterface;
use LongTermSupport\OpenApiGenerator\Component\GeneratorCore\Generator\Naming;
use LongTermSupport\OpenApiGenerator\Component\GeneratorCore\Guesser\ChainGuesser;
use LongTermSupport\OpenApiGenerator\Component\OpenApi3\JsonSchema\Model\Schema;
use LongTermSupport\OpenApiGenerator\Component\OpenApiCommon\Guesser\OpenApiSchema\AdditionalPropertiesGuesser;
use LongTermSupport\OpenApiGenerator\Component\OpenApiCommon\Guesser\OpenApiSchema\AllOfGuesser;
use LongTermSupport\OpenApiGenerator\Component\OpenApiCommon\Guesser\OpenApiSchema\ArrayGuesser;
use LongTermSupport\OpenApiGenerator\Component\OpenApiCommon\Guesser\OpenApiSchema\CustomStringFormatGuesser;
use LongTermSupport\OpenApiGenerator\Component\OpenApiCommon\Guesser\OpenApiSchema\DateGuesser;
use LongTermSupport\OpenApiGenerator\Component\OpenApiCommon\Guesser\OpenApiSchema\DateTimeGuesser;
use LongTermSupport\OpenApiGenerator\Component\OpenApiCommon\Guesser\OpenApiSchema\ItemsGuesser;
use LongTermSupport\OpenApiGenerator\Component\OpenApiCommon\Guesser\OpenApiSchema\MultipleGuesser;
use LongTermSupport\OpenApiGenerator\Component\OpenApiCommon\Guesser\OpenApiSchema\ReferenceGuesser;
use LongTermSupport\OpenApiGenerator\Component\OpenApiCommon\Guesser\OpenApiSchema\SimpleTypeGuesser;
use Symfony\Component\Serializer\Normalizer\DenormalizerInterface;

class GuesserFactory
{
    /**
     * @param array<string, mixed> $options
     */
    public static function create(DenormalizerInterface $denormalizer, array $options = []): ChainGuesser
    {
        $naming               = new Naming();
        $dateFormat           = isset($options['full-date-format'])      && \is_string($options['full-date-format']) ? $options['full-date-format'] : 'Y-m-d';
        $outputDateTimeFormat = isset($options['date-format'])           && \is_string($options['date-format']) ? $options['date-format'] : DateTimeInterface::RFC3339;
        $inputDateTimeFormat  = isset($options['date-input-format'])     && \is_string($options['date-input-format']) ? $options['date-input-format'] : null;
        $datePreferInterface  = isset($options['date-prefer-interface']) && \is_bool($options['date-prefer-interface']) ? $options['date-prefer-interface'] : null;
        /** @var array<string, string> $customStringFormatMapping */
        $customStringFormatMapping = isset($options['custom-string-format-mapping']) && \is_array($options['custom-string-format-mapping']) ? $options['custom-string-format-mapping'] : [];

        $chainGuesser = new ChainGuesser();
        $chainGuesser->addGuesser(new SecurityGuesser());
        $chainGuesser->addGuesser(new CustomStringFormatGuesser(Schema::class, $customStringFormatMapping));
        $chainGuesser->addGuesser(new DateGuesser(Schema::class, $dateFormat, $datePreferInterface));
        $chainGuesser->addGuesser(new DateTimeGuesser(Schema::class, $outputDateTimeFormat, $inputDateTimeFormat, $datePreferInterface));
        $chainGuesser->addGuesser(new ReferenceGuesser($denormalizer, Schema::class));
        $chainGuesser->addGuesser(new OpenApiGuesser($denormalizer));
        $chainGuesser->addGuesser(new SchemaGuesser($denormalizer, $naming));
        $chainGuesser->addGuesser(new AdditionalPropertiesGuesser(Schema::class));
        $chainGuesser->addGuesser(new AllOfGuesser($denormalizer, $naming, Schema::class));
        $chainGuesser->addGuesser(new AnyOfReferencefGuesser($denormalizer, $naming, Schema::class));
        $chainGuesser->addGuesser(new ArrayGuesser(Schema::class));
        $chainGuesser->addGuesser(new ItemsGuesser(Schema::class));
        $chainGuesser->addGuesser(new SimpleTypeGuesser(Schema::class));
        $chainGuesser->addGuesser(new MultipleGuesser(Schema::class));

        return $chainGuesser;
    }
}
