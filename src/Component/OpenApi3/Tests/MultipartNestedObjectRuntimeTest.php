<?php

declare(strict_types=1);

namespace LongTermSupport\OpenApiGenerator\Component\OpenApi3\Tests;

use PHPUnit\Framework\TestCase;

/**
 * Runtime test for generated multipart/form-data endpoint code.
 *
 * This test verifies that the generated getBody() method correctly handles
 * non-scalar form parameters (nested objects, booleans, etc.) without throwing
 * InvalidArgumentException from MultipartStreamBuilder::createStream().
 *
 * Addresses issue https://github.com/janephp/janephp/issues/738
 *
 * @internal
 */
class MultipartNestedObjectRuntimeTest extends TestCase
{
    public function testMultipartNestedObject(): void
    {
        // Fixture classes are resolved by Composer's classmap autoloader (from expected/ or generated/).
        // Manual require_once is not used because generated/ and expected/ contain identical code
        // under the same FQCN, and require_once from a second path triggers "Cannot redeclare class".

        $item = new Expected\MultipartNestedObject\Model\FilePostBodyItem();
        $item->setItemId(42);
        $item->setItemType('document');

        $body = new Expected\MultipartNestedObject\Model\FilePostBody();
        $body->setFichier('file-content');
        $body->setItem($item);

        $normalizers = [
            new \Symfony\Component\Serializer\Normalizer\ArrayDenormalizer(),
            new Expected\MultipartNestedObject\Normalizer\JaneObjectNormalizer(),
        ];
        $encoders = [
            new \Symfony\Component\Serializer\Encoder\JsonEncoder(
                new \Symfony\Component\Serializer\Encoder\JsonEncode(),
                new \Symfony\Component\Serializer\Encoder\JsonDecode(['json_decode_associative' => true])
            ),
        ];
        $serializer    = new \Symfony\Component\Serializer\Serializer($normalizers, $encoders);
        $streamFactory = \Http\Discovery\Psr17FactoryDiscovery::findStreamFactory();

        $endpoint = new Expected\MultipartNestedObject\Endpoint\PostFile($body);
        $result   = $endpoint->getBody($serializer, $streamFactory);

        $this->assertIsArray($result);
        $this->assertCount(2, $result);
        $headers = $result[0];
        $stream  = $result[1];
        \assert(\is_array($headers));
        $this->assertArrayHasKey('Content-Type', $headers);
        \assert(\is_array($headers['Content-Type']));
        $contentType = $headers['Content-Type'];
        $this->assertIsString($contentType[0]);
        $this->assertStringContainsString('multipart/form-data', $contentType[0]);
        $this->assertInstanceOf(\Psr\Http\Message\StreamInterface::class, $stream);

        $streamContent = \is_object($result[1]) && method_exists($result[1], '__toString') ? (string)$result[1] : '';
        $this->assertStringContainsString('file-content', $streamContent);
        $this->assertStringContainsString('{"itemId":42,"itemType":"document"}', $streamContent);
    }
}
