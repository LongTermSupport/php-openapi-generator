<?php

declare(strict_types=1);

namespace LongTermSupport\OpenApiGenerator\Component\OpenApi3\Guesser\OpenApiSchema;

use LogicException;
use LongTermSupport\OpenApiGenerator\Component\GeneratorCore\Guesser\ClassGuesserInterface;
use LongTermSupport\OpenApiGenerator\Component\GeneratorCore\Guesser\GuesserInterface;
use LongTermSupport\OpenApiGenerator\Component\GeneratorCore\Registry\Registry;
use LongTermSupport\OpenApiGenerator\Component\OpenApi3\JsonSchema\Model\APIKeySecurityScheme;
use LongTermSupport\OpenApiGenerator\Component\OpenApi3\JsonSchema\Model\HTTPSecurityScheme;
use LongTermSupport\OpenApiGenerator\Component\OpenApi3\JsonSchema\Model\OAuth2SecurityScheme;
use LongTermSupport\OpenApiGenerator\Component\OpenApi3\JsonSchema\Model\OpenIdConnectSecurityScheme;
use LongTermSupport\OpenApiGenerator\Component\OpenApiCommon\Guesser\Guess\SecuritySchemeGuess;
use LongTermSupport\OpenApiGenerator\Component\OpenApiCommon\Registry\Schema;

class SecurityGuesser implements GuesserInterface, ClassGuesserInterface
{
    public function supportObject(mixed $object): bool
    {
        return ($object instanceof APIKeySecurityScheme || $object instanceof HTTPSecurityScheme || $object instanceof OAuth2SecurityScheme || $object instanceof OpenIdConnectSecurityScheme) && \in_array($object->getType(), SecuritySchemeGuess::getAvailableTypes(), true);
    }

    public function guessClass(mixed $object, string $name, string $reference, Registry $registry): void
    {
        if (!$object instanceof APIKeySecurityScheme && !$object instanceof HTTPSecurityScheme && !$object instanceof OAuth2SecurityScheme && !$object instanceof OpenIdConnectSecurityScheme) {
            throw new LogicException('Expected APIKeySecurityScheme, HTTPSecurityScheme, OAuth2SecurityScheme or OpenIdConnectSecurityScheme, got ' . get_debug_type($object));
        }

        if (!\in_array($object->getType(), [SecuritySchemeGuess::TYPE_HTTP, SecuritySchemeGuess::TYPE_API_KEY], true)) {
            return;
        }

        $apiKeyName          = ($object instanceof APIKeySecurityScheme) ? $object->getName() : null;
        $apiKeyVariableName  = $object instanceof APIKeySecurityScheme ? ($apiKeyName ?? $name) : $name;
        $variableName        = $object instanceof HTTPSecurityScheme ? $name : $apiKeyVariableName;
        $securitySchemeGuess = new SecuritySchemeGuess($name, $object, $variableName, $object->getType());
        switch ($securitySchemeGuess->getType()) {
            case SecuritySchemeGuess::TYPE_HTTP:
                if (!$object instanceof HTTPSecurityScheme) {
                    throw new LogicException('Expected HTTPSecurityScheme, got ' . get_debug_type($object));
                }

                $scheme = $object->getScheme() ?? SecuritySchemeGuess::SCHEME_BEARER;
                $scheme = ucfirst(mb_strtolower($scheme));
                $securitySchemeGuess->setScheme($scheme);
                break;
            case SecuritySchemeGuess::TYPE_API_KEY:
                if (!$object instanceof APIKeySecurityScheme) {
                    throw new LogicException('Expected APIKeySecurityScheme, got ' . get_debug_type($object));
                }

                $apiKeyIn = $object->getIn();
                if (!\is_string($apiKeyIn)) {
                    throw new LogicException('Expected string API key "in" value, got ' . get_debug_type($apiKeyIn));
                }

                $securitySchemeGuess->setIn($apiKeyIn);
                break;
        }

        $schema = $registry->getSchema($reference);
        if (!$schema instanceof Schema) {
            throw new LogicException('Expected Schema for reference ' . $reference . ', got ' . get_debug_type($schema));
        }

        $schema->addSecurityScheme($reference, $securitySchemeGuess);
    }
}
