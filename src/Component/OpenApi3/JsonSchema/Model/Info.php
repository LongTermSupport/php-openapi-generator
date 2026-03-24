<?php

declare(strict_types=1);

namespace LongTermSupport\OpenApiGenerator\Component\OpenApi3\JsonSchema\Model;

use ArrayObject;

/** @extends ArrayObject<string, mixed> */
class Info extends ArrayObject
{
    /**
     * @var array<string, bool>
     */
    protected $initialized = [];

    protected ?string $title = null;

    protected ?string $description = null;

    protected ?string $termsOfService = null;

    protected ?Contact $contact = null;

    protected ?License $license = null;

    protected ?string $version = null;

    public function isInitialized(string $property): bool
    {
        return \array_key_exists($property, $this->initialized);
    }

    public function getTitle(): ?string
    {
        return $this->title;
    }

    public function setTitle(?string $title): self
    {
        $this->initialized['title'] = true;
        $this->title                = $title;

        return $this;
    }

    public function getDescription(): ?string
    {
        return $this->description;
    }

    public function setDescription(?string $description): self
    {
        $this->initialized['description'] = true;
        $this->description                = $description;

        return $this;
    }

    public function getTermsOfService(): ?string
    {
        return $this->termsOfService;
    }

    public function setTermsOfService(?string $termsOfService): self
    {
        $this->initialized['termsOfService'] = true;
        $this->termsOfService                = $termsOfService;

        return $this;
    }

    public function getContact(): ?Contact
    {
        return $this->contact;
    }

    public function setContact(?Contact $contact): self
    {
        $this->initialized['contact'] = true;
        $this->contact                = $contact;

        return $this;
    }

    public function getLicense(): ?License
    {
        return $this->license;
    }

    public function setLicense(?License $license): self
    {
        $this->initialized['license'] = true;
        $this->license                = $license;

        return $this;
    }

    public function getVersion(): ?string
    {
        return $this->version;
    }

    public function setVersion(?string $version): self
    {
        $this->initialized['version'] = true;
        $this->version                = $version;

        return $this;
    }
}
