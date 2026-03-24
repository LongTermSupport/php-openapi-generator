<?php

declare(strict_types=1);

namespace LongTermSupport\OpenApiGenerator\Component\OpenApi3\JsonSchema\Normalizer;

use ArrayObject;
use LogicException;
use LongTermSupport\OpenApiGenerator\Component\OpenApi3\JsonSchema\Runtime\Normalizer\CheckArray;
use LongTermSupport\OpenApiGenerator\Component\OpenApi3\JsonSchema\Runtime\Normalizer\TypeValidator;
use LongTermSupport\OpenApiGenerator\Component\OpenApi3\JsonSchema\Runtime\Normalizer\ValidatorTrait;
use LongTermSupport\OpenApiGenerator\Component\OpenApiRuntime\Reference;
use Symfony\Component\Serializer\Normalizer\DenormalizerAwareInterface;
use Symfony\Component\Serializer\Normalizer\DenormalizerAwareTrait;
use Symfony\Component\Serializer\Normalizer\DenormalizerInterface;
use Symfony\Component\Serializer\Normalizer\NormalizerAwareInterface;
use Symfony\Component\Serializer\Normalizer\NormalizerAwareTrait;
use Symfony\Component\Serializer\Normalizer\NormalizerInterface;

class OperationNormalizer implements DenormalizerInterface, NormalizerInterface, DenormalizerAwareInterface, NormalizerAwareInterface
{
    use DenormalizerAwareTrait;
    use NormalizerAwareTrait;
    use CheckArray;
    use ValidatorTrait;

    public function supportsDenormalization(mixed $data, string $type, ?string $format = null, array $context = []): bool
    {
        return \LongTermSupport\OpenApiGenerator\Component\OpenApi3\JsonSchema\Model\Operation::class === $type;
    }

    public function supportsNormalization(mixed $data, ?string $format = null, array $context = []): bool
    {
        return $data instanceof \LongTermSupport\OpenApiGenerator\Component\OpenApi3\JsonSchema\Model\Operation;
    }

    /**
     * @return object
     */
    public function denormalize(mixed $data, string $type, ?string $format = null, array $context = []): mixed
    {
        $object = new \LongTermSupport\OpenApiGenerator\Component\OpenApi3\JsonSchema\Model\Operation();
        if (null === $data || false === \is_array($data)) {
            return $object;
        }

        /** @var array<string, mixed> $data */
        /** @var array<string, mixed> $context */
        if (isset($data['$ref'])) {
            return new Reference(TypeValidator::assertString($data['$ref'], '$ref'), TypeValidator::assertString($context['document-origin'], 'context.document-origin'));
        }

        if (isset($data['$recursiveRef'])) {
            return new Reference(TypeValidator::assertString($data['$recursiveRef'], '$recursiveRef'), TypeValidator::assertString($context['document-origin'], 'context.document-origin'));
        }

        if (\array_key_exists('tags', $data) && null !== $data['tags']) {
            $values = [];
            /** @var array<mixed> $tagsArr */
            $tagsArr = $data['tags'];
            foreach ($tagsArr as $value) {
                /** @var string $value */
                $values[] = $value;
            }

            $object->setTags($values);
            unset($data['tags']);
        } elseif (\array_key_exists('tags', $data) && null === $data['tags']) {
            $object->setTags(null);
        }

        if (\array_key_exists('summary', $data) && null !== $data['summary']) {
            /** @var scalar $summaryVal */
            $summaryVal = $data['summary'];
            $object->setSummary((string)$summaryVal);
            unset($data['summary']);
        } elseif (\array_key_exists('summary', $data) && null === $data['summary']) {
            $object->setSummary(null);
        }

        if (\array_key_exists('description', $data) && null !== $data['description']) {
            /** @var scalar $descriptionVal */
            $descriptionVal = $data['description'];
            $object->setDescription((string)$descriptionVal);
            unset($data['description']);
        } elseif (\array_key_exists('description', $data) && null === $data['description']) {
            $object->setDescription(null);
        }

        if (\array_key_exists('externalDocs', $data) && null !== $data['externalDocs']) {
            /** @var \LongTermSupport\OpenApiGenerator\Component\OpenApi3\JsonSchema\Model\ExternalDocumentation $denormExternalDocs */
            $denormExternalDocs = $this->denormalizer->denormalize($data['externalDocs'], \LongTermSupport\OpenApiGenerator\Component\OpenApi3\JsonSchema\Model\ExternalDocumentation::class, 'json', $context);
            $object->setExternalDocs($denormExternalDocs);
            unset($data['externalDocs']);
        } elseif (\array_key_exists('externalDocs', $data) && null === $data['externalDocs']) {
            $object->setExternalDocs(null);
        }

        if (\array_key_exists('operationId', $data) && null !== $data['operationId']) {
            /** @var scalar $operationIdVal */
            $operationIdVal = $data['operationId'];
            $object->setOperationId((string)$operationIdVal);
            unset($data['operationId']);
        } elseif (\array_key_exists('operationId', $data) && null === $data['operationId']) {
            $object->setOperationId(null);
        }

        if (\array_key_exists('parameters', $data) && null !== $data['parameters']) {
            $values_1 = [];
            /** @var array<mixed> $parametersArr */
            $parametersArr = $data['parameters'];
            foreach ($parametersArr as $value_1) {
                $value_2 = $value_1;
                if (\is_array($value_1) && isset($value_1['$ref'])) {
                    $value_2 = $this->denormalizer->denormalize($value_1, \LongTermSupport\OpenApiGenerator\Component\OpenApi3\JsonSchema\Model\Reference::class, 'json', $context);
                } elseif (\is_array($value_1) && isset($value_1['name'], $value_1['in'])) {
                    $value_2 = $this->denormalizer->denormalize($value_1, \LongTermSupport\OpenApiGenerator\Component\OpenApi3\JsonSchema\Model\Parameter::class, 'json', $context);
                }

                /** @var \LongTermSupport\OpenApiGenerator\Component\OpenApi3\JsonSchema\Model\Parameter|\LongTermSupport\OpenApiGenerator\Component\OpenApi3\JsonSchema\Model\Reference $value_2 */
                $values_1[] = $value_2;
            }

            $object->setParameters($values_1);
            unset($data['parameters']);
        } elseif (\array_key_exists('parameters', $data) && null === $data['parameters']) {
            $object->setParameters(null);
        }

        if (\array_key_exists('requestBody', $data) && null !== $data['requestBody']) {
            $value_3 = $data['requestBody'];
            if (\is_array($data['requestBody']) && isset($data['requestBody']['$ref'])) {
                $value_3 = $this->denormalizer->denormalize($data['requestBody'], \LongTermSupport\OpenApiGenerator\Component\OpenApi3\JsonSchema\Model\Reference::class, 'json', $context);
            } elseif (\is_array($data['requestBody']) && isset($data['requestBody']['content'])) {
                $value_3 = $this->denormalizer->denormalize($data['requestBody'], \LongTermSupport\OpenApiGenerator\Component\OpenApi3\JsonSchema\Model\RequestBody::class, 'json', $context);
            }

            /** @var \LongTermSupport\OpenApiGenerator\Component\OpenApi3\JsonSchema\Model\Reference|\LongTermSupport\OpenApiGenerator\Component\OpenApi3\JsonSchema\Model\RequestBody $value_3 */
            $object->setRequestBody($value_3);
            unset($data['requestBody']);
        } elseif (\array_key_exists('requestBody', $data) && null === $data['requestBody']) {
            $object->setRequestBody(null);
        }

        if (\array_key_exists('responses', $data) && null !== $data['responses']) {
            /** @var \LongTermSupport\OpenApiGenerator\Component\OpenApi3\JsonSchema\Model\Responses $denormResponses */
            $denormResponses = $this->denormalizer->denormalize($data['responses'], \LongTermSupport\OpenApiGenerator\Component\OpenApi3\JsonSchema\Model\Responses::class, 'json', $context);
            $object->setResponses($denormResponses);
            unset($data['responses']);
        } elseif (\array_key_exists('responses', $data) && null === $data['responses']) {
            $object->setResponses(null);
        }

        if (\array_key_exists('callbacks', $data) && null !== $data['callbacks']) {
            /** @var array<string, array<mixed>|\LongTermSupport\OpenApiGenerator\Component\OpenApi3\JsonSchema\Model\Reference> $values_2 */
            $values_2 = [];
            /** @var array<mixed> $callbacksArr */
            $callbacksArr = $data['callbacks'];
            foreach ($callbacksArr as $key => $value_4) {
                $value_5 = $value_4;
                if (\is_array($value_4) && isset($value_4['$ref'])) {
                    $value_5 = $this->denormalizer->denormalize($value_4, \LongTermSupport\OpenApiGenerator\Component\OpenApi3\JsonSchema\Model\Reference::class, 'json', $context);
                } elseif (\is_array($value_4)) {
                    $values_3 = [];
                    foreach ($value_4 as $key_1 => $value_6) {
                        if (1 === \Safe\preg_match('/^x-/', (string)$key_1) && null !== $value_6) {
                            $values_3[(string)$key_1] = $value_6;
                            continue;
                        }
                    }

                    $value_5 = $values_3;
                }

                /** @var array<mixed>|\LongTermSupport\OpenApiGenerator\Component\OpenApi3\JsonSchema\Model\Reference $value_5 */
                $values_2[(string)$key] = $value_5;
            }

            $object->setCallbacks($values_2);
            unset($data['callbacks']);
        } elseif (\array_key_exists('callbacks', $data) && null === $data['callbacks']) {
            $object->setCallbacks(null);
        }

        if (\array_key_exists('deprecated', $data) && null !== $data['deprecated']) {
            /** @var scalar $deprecatedVal */
            $deprecatedVal = $data['deprecated'];
            $object->setDeprecated((bool)$deprecatedVal);
            unset($data['deprecated']);
        } elseif (\array_key_exists('deprecated', $data) && null === $data['deprecated']) {
            $object->setDeprecated(null);
        }

        if (\array_key_exists('security', $data) && null !== $data['security']) {
            /** @var array<int, array<string, string[]>> $values_4 */
            $values_4 = [];
            /** @var array<mixed> $securityArr */
            $securityArr = $data['security'];
            foreach ($securityArr as $value_7) {
                /** @var array<string, string[]> $values_5 */
                $values_5 = [];
                /** @var array<mixed> $value_7 */
                foreach ($value_7 as $key_2 => $value_8) {
                    /** @var string[] $values_6 */
                    $values_6 = [];
                    /** @var array<mixed> $value_8 */
                    foreach ($value_8 as $value_9) {
                        /** @var string $value_9 */
                        $values_6[] = $value_9;
                    }

                    $values_5[(string)$key_2] = $values_6;
                }

                $values_4[] = $values_5;
            }

            $object->setSecurity($values_4);
            unset($data['security']);
        } elseif (\array_key_exists('security', $data) && null === $data['security']) {
            $object->setSecurity(null);
        }

        if (\array_key_exists('servers', $data) && null !== $data['servers']) {
            /** @var \LongTermSupport\OpenApiGenerator\Component\OpenApi3\JsonSchema\Model\Server[] $values_7 */
            $values_7 = [];
            /** @var array<mixed> $serversArr */
            $serversArr = $data['servers'];
            foreach ($serversArr as $value_10) {
                /** @var \LongTermSupport\OpenApiGenerator\Component\OpenApi3\JsonSchema\Model\Server $denormServer */
                $denormServer = $this->denormalizer->denormalize($value_10, \LongTermSupport\OpenApiGenerator\Component\OpenApi3\JsonSchema\Model\Server::class, 'json', $context);
                $values_7[]   = $denormServer;
            }

            $object->setServers($values_7);
            unset($data['servers']);
        } elseif (\array_key_exists('servers', $data) && null === $data['servers']) {
            $object->setServers(null);
        }

        foreach ($data as $key_3 => $value_11) {
            if (!\is_string($key_3)) {
                continue;
            }

            if (1 === \Safe\preg_match('/^x-/', $key_3)) {
                $object[$key_3] = $value_11;
            }
        }

        return $object;
    }

    /**
     * @return array<string, mixed>|string|int|float|bool|ArrayObject<string, mixed>|null
     */
    public function normalize(mixed $object, ?string $format = null, array $context = []): array|string|int|float|bool|ArrayObject|null
    {
        if (!$object instanceof \LongTermSupport\OpenApiGenerator\Component\OpenApi3\JsonSchema\Model\Operation) {
            throw new LogicException('Expected Operation, got ' . get_debug_type($object));
        }

        $data = [];
        if ($object->isInitialized('tags') && null !== $object->getTags()) {
            $values       = $object->getTags();
            $data['tags'] = $values;
        }

        if ($object->isInitialized('summary') && null !== $object->getSummary()) {
            $data['summary'] = $object->getSummary();
        }

        if ($object->isInitialized('description') && null !== $object->getDescription()) {
            $data['description'] = $object->getDescription();
        }

        if ($object->isInitialized('externalDocs') && $object->getExternalDocs() instanceof \LongTermSupport\OpenApiGenerator\Component\OpenApi3\JsonSchema\Model\ExternalDocumentation) {
            $data['externalDocs'] = $this->normalizer->normalize($object->getExternalDocs(), 'json', $context);
        }

        if ($object->isInitialized('operationId') && null !== $object->getOperationId()) {
            $data['operationId'] = $object->getOperationId();
        }

        if ($object->isInitialized('parameters') && null !== $object->getParameters()) {
            $values_1 = [];
            foreach ($object->getParameters() as $value_1) {
                $value_2    = $this->normalizer->normalize($value_1, 'json', $context);
                $values_1[] = $value_2;
            }

            $data['parameters'] = $values_1;
        }

        if ($object->isInitialized('requestBody') && null !== $object->getRequestBody()) {
            $data['requestBody'] = $this->normalizer->normalize($object->getRequestBody(), 'json', $context);
        }

        $data['responses'] = $this->normalizer->normalize($object->getResponses(), 'json', $context);
        if ($object->isInitialized('callbacks') && null !== $object->getCallbacks()) {
            $values_2 = [];
            foreach ($object->getCallbacks() as $key => $value_4) {
                if (\is_object($value_4)) {
                    $value_5 = $this->normalizer->normalize($value_4, 'json', $context);
                } else {
                    $values_3 = [];
                    foreach ($value_4 as $key_1 => $value_6) {
                        if (1 === \Safe\preg_match('/^x-/', (string)$key_1) && null !== $value_6) {
                            $values_3[$key_1] = $value_6;
                            continue;
                        }
                    }

                    $value_5 = $values_3;
                }

                $values_2[$key] = $value_5;
            }

            $data['callbacks'] = $values_2;
        }

        if ($object->isInitialized('deprecated') && null !== $object->getDeprecated()) {
            $data['deprecated'] = $object->getDeprecated();
        }

        if ($object->isInitialized('security') && null !== $object->getSecurity()) {
            $values_4 = [];
            foreach ($object->getSecurity() as $value_7) {
                $values_5 = [];
                foreach ($value_7 as $key_2 => $value_8) {
                    $values_6 = [];
                    foreach ($value_8 as $value_9) {
                        $values_6[] = $value_9;
                    }

                    $values_5[$key_2] = $values_6;
                }

                $values_4[] = $values_5;
            }

            $data['security'] = $values_4;
        }

        if ($object->isInitialized('servers') && null !== $object->getServers()) {
            $values_7 = [];
            foreach ($object->getServers() as $value_10) {
                $values_7[] = $this->normalizer->normalize($value_10, 'json', $context);
            }

            $data['servers'] = $values_7;
        }

        foreach ($object as $key_3 => $value_11) {
            if (1 === \Safe\preg_match('/^x-/', $key_3)) {
                $data[$key_3] = $value_11;
            }
        }

        return $data;
    }

    public function getSupportedTypes(?string $format = null): array
    {
        return [\LongTermSupport\OpenApiGenerator\Component\OpenApi3\JsonSchema\Model\Operation::class => false];
    }
}
