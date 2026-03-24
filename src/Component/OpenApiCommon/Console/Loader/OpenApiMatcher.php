<?php

declare(strict_types=1);

namespace LongTermSupport\OpenApiGenerator\Component\OpenApiCommon\Console\Loader;

use LogicException;
use LongTermSupport\OpenApiGenerator\Component\OpenApi3\JaneOpenApi as OpenApi3Base;
use LongTermSupport\OpenApiGenerator\Component\OpenApiCommon\Exception\CouldNotParseException;
use LongTermSupport\OpenApiGenerator\Component\OpenApiCommon\Exception\OpenApiVersionSupportException;
use LongTermSupport\OpenApiGenerator\Component\OpenApiCommon\SchemaParser\SchemaParser;

class OpenApiMatcher
{
    /** @var array<class-string, SchemaParser> */
    private array $schemaParsers = [];

    public function match(string $origin): string
    {
        [$openApiClass, $openApi3Message] = $this->isOpenApi3($origin);

        if (null === $openApiClass) {
            if (null !== $openApi3Message) {
                throw new CouldNotParseException(\sprintf("Could not parse schema in OpenApi 3.x format:\n- OpenApi v3 error: \"%s\"\n", $openApi3Message));
            }

            throw new OpenApiVersionSupportException('Only OpenApi 3.x specifications are supported.');
        }

        return $openApiClass;
    }

    /**
     * @return array{0: class-string|null, 1: string|null}
     */
    private function isOpenApi3(string $origin): array
    {
        $class   = null;
        $message = null;

        if (class_exists(OpenApi3Base::class)) {
            if (!\array_key_exists(OpenApi3Base::class, $this->schemaParsers)) {
                $openApi3Serializer = OpenApi3Base::buildSerializer();
                if (!$openApi3Serializer instanceof \Symfony\Component\Serializer\Normalizer\DenormalizerInterface) {
                    throw new LogicException('Expected DenormalizerInterface, got ' . get_debug_type($openApi3Serializer));
                }

                $this->schemaParsers[OpenApi3Base::class] = new \LongTermSupport\OpenApiGenerator\Component\OpenApi3\SchemaParser\SchemaParser($openApi3Serializer);
            }

            try {
                $this->schemaParsers[OpenApi3Base::class]->parseSchema($origin);
                $class = OpenApi3Base::class;
            } catch (CouldNotParseException $e) {
                $message = $e->getMessage();
            } catch (OpenApiVersionSupportException $e) {
                // Intentionally ignored: version mismatch is expected when probing multiple parsers.
                // A different exception will be thrown downstream if no parser matches.
                unset($e);
            }
        }

        return [$class, $message];
    }
}
