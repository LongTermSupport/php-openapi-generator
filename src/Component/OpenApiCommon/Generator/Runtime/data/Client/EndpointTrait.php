<?php

declare(strict_types=1);

use Psr\Http\Message\ResponseInterface;
use Symfony\Component\Serializer\SerializerInterface;

trait EndpointTrait
{
    public function parseResponse(ResponseInterface $response, SerializerInterface $serializer, string $fetchMode = Client::FETCH_OBJECT): mixed
    {
        $contentType = null;
        if ($response->hasHeader('Content-Type')) {
            $header      = current($response->getHeader('Content-Type'));
            $contentType = false !== $header ? $header : null;
        }

        return $this->transformResponseBody($response, $serializer, $contentType);
    }

    abstract protected function transformResponseBody(ResponseInterface $response, SerializerInterface $serializer, ?string $contentType = null): mixed;
}
