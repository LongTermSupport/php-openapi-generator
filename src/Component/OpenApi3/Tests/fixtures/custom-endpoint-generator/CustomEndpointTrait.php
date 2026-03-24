<?php

declare(strict_types=1);

namespace LongTermSupport\OpenApiGenerator\Component\OpenApi3\Tests\Fixtures\CustomEndpointGenerator;

use Psr\Http\Message\ResponseInterface;
use Symfony\Component\Serializer\SerializerInterface;

trait CustomEndpointTrait
{
    public function parsePSR7Response(ResponseInterface $response, SerializerInterface $serializer, string $fetchMode = Client::FETCH_OBJECT): void
    {
        // do something awesome
    }

    abstract protected function transformResponseBody(string $body, int $status, SerializerInterface $serializer, ?string $contentType = null);
}
