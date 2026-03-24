<?php

declare(strict_types=1);

namespace LongTermSupport\OpenApiGenerator\Component\OpenApi3\JsonSchema\Model;

use ArrayObject;

/** @extends ArrayObject<string, mixed> */
class OAuthFlows extends ArrayObject
{
    /**
     * @var array<string, bool>
     */
    protected $initialized = [];

    protected ?ImplicitOAuthFlow $implicit = null;

    protected ?PasswordOAuthFlow $password = null;

    protected ?ClientCredentialsFlow $clientCredentials = null;

    protected ?AuthorizationCodeOAuthFlow $authorizationCode = null;

    public function isInitialized(string $property): bool
    {
        return \array_key_exists($property, $this->initialized);
    }

    public function getImplicit(): ?ImplicitOAuthFlow
    {
        return $this->implicit;
    }

    public function setImplicit(?ImplicitOAuthFlow $implicit): self
    {
        $this->initialized['implicit'] = true;
        $this->implicit                = $implicit;

        return $this;
    }

    public function getPassword(): ?PasswordOAuthFlow
    {
        return $this->password;
    }

    public function setPassword(?PasswordOAuthFlow $password): self
    {
        $this->initialized['password'] = true;
        $this->password                = $password;

        return $this;
    }

    public function getClientCredentials(): ?ClientCredentialsFlow
    {
        return $this->clientCredentials;
    }

    public function setClientCredentials(?ClientCredentialsFlow $clientCredentials): self
    {
        $this->initialized['clientCredentials'] = true;
        $this->clientCredentials                = $clientCredentials;

        return $this;
    }

    public function getAuthorizationCode(): ?AuthorizationCodeOAuthFlow
    {
        return $this->authorizationCode;
    }

    public function setAuthorizationCode(?AuthorizationCodeOAuthFlow $authorizationCode): self
    {
        $this->initialized['authorizationCode'] = true;
        $this->authorizationCode                = $authorizationCode;

        return $this;
    }
}
