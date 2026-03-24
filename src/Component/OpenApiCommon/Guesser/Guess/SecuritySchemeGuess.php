<?php

declare(strict_types=1);

namespace LongTermSupport\OpenApiGenerator\Component\OpenApiCommon\Guesser\Guess;

class SecuritySchemeGuess
{
    public const TYPE_API_KEY = 'apiKey';

    public const TYPE_HTTP = 'http';

    public const TYPE_OAUTH2 = 'oauth2';

    public const TYPE_OPEN_ID_CONNECT = 'openIdConnect';

    public const SCHEME_BEARER = 'Bearer';

    public const SCHEME_BASIC = 'Basic';

    private ?string $scheme = null;

    private ?string $in = null;

    public function __construct(
        private readonly string $name,
        private readonly object $object,
        private readonly string $variable,
        private readonly string $type,
    ) {
    }

    /** @return array<string> */
    public static function getAvailableTypes(): array
    {
        return [
            self::TYPE_API_KEY,
            self::TYPE_HTTP,
            self::TYPE_OAUTH2,
            self::TYPE_OPEN_ID_CONNECT,
        ];
    }

    public function getName(): string
    {
        return $this->name;
    }

    public function getObject(): object
    {
        return $this->object;
    }

    public function getVariable(): string
    {
        return $this->variable;
    }

    public function getType(): string
    {
        return $this->type;
    }

    public function getScheme(): ?string
    {
        return $this->scheme;
    }

    public function setScheme(?string $scheme): void
    {
        $this->scheme = $scheme;
    }

    public function getIn(): ?string
    {
        return $this->in;
    }

    public function setIn(?string $in): void
    {
        $this->in = $in;
    }
}
