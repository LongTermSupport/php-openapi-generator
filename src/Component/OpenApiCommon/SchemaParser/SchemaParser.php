<?php

declare(strict_types=1);

namespace LongTermSupport\OpenApiGenerator\Component\OpenApiCommon\SchemaParser;

use Exception;
use LogicException;
use LongTermSupport\OpenApiGenerator\Component\OpenApiCommon\Exception\CouldNotParseException;
use LongTermSupport\OpenApiGenerator\Component\OpenApiCommon\Exception\OpenApiVersionSupportException;
use Symfony\Component\Serializer\Normalizer\DenormalizerInterface;
use Symfony\Component\Yaml\Exception\ExceptionInterface as YamlException;
use Symfony\Component\Yaml\Yaml;

abstract class SchemaParser
{
    protected const OPEN_API_MODEL = null;

    protected const OPEN_API_VERSION_MAJOR = null;

    /** @var array<string, mixed> */
    protected static array $parsed = [];

    public function __construct(
        private readonly DenormalizerInterface $denormalizer,
    ) {
    }

    public function parseSchema(string $openApiSpecPath): mixed
    {
        if (!\array_key_exists($openApiSpecPath, static::$parsed)) {
            $openApiSpecContents = \Safe\file_get_contents($openApiSpecPath);
            $jsonException       = null;
            $yamlException       = null;

            try {
                return static::$parsed[$openApiSpecPath] = $this->deserialize($openApiSpecContents, $openApiSpecPath);
            } catch (Exception $exception) {
                $jsonException = $exception;
            }

            try {
                $content = Yaml::parse(
                    $openApiSpecContents,
                    Yaml::PARSE_OBJECT | Yaml::PARSE_DATETIME | Yaml::PARSE_EXCEPTION_ON_INVALID_TYPE
                );

                return static::$parsed[$openApiSpecPath] = $this->denormalize($content, $openApiSpecPath);
            } catch (YamlException $yamlException) {
                throw new CouldNotParseException(\sprintf("Could not parse schema in JSON nor YAML format:\n- JSON error: \"%s\"\n- YAML error: \"%s\"\n", $jsonException->getMessage(), $yamlException->getMessage()), $yamlException->getCode(), $yamlException);
            }
        }

        return static::$parsed[$openApiSpecPath];
    }

    protected function deserialize(string $openApiSpecContents, string $openApiSpecPath): mixed
    {
        $openApiData = \Safe\json_decode($openApiSpecContents, true);

        return $this->denormalize($openApiData, $openApiSpecPath);
    }

    abstract protected function validSchema(mixed $openApiSpecData): bool;

    protected function denormalize(mixed $openApiSpecData, string $openApiSpecPath): mixed
    {
        if (!$this->validSchema($openApiSpecData)) {
            $version = \is_scalar(static::OPEN_API_VERSION_MAJOR) ? (string)static::OPEN_API_VERSION_MAJOR : 'unknown';
            throw new OpenApiVersionSupportException(\sprintf('Only OpenAPI v%s specifications and up are supported, use an external tool to convert your api files', $version));
        }

        $model = \is_string(static::OPEN_API_MODEL) ? static::OPEN_API_MODEL : throw new LogicException('OPEN_API_MODEL must be a string');

        return $this->denormalizer->denormalize(
            $openApiSpecData,
            $model,
            'json',
            [
                'document-origin' => $openApiSpecPath,
            ]
        );
    }
}
