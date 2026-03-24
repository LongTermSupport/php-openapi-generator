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

class PathItemNormalizer implements DenormalizerInterface, NormalizerInterface, DenormalizerAwareInterface, NormalizerAwareInterface
{
    use DenormalizerAwareTrait;
    use NormalizerAwareTrait;
    use CheckArray;
    use ValidatorTrait;

    public function supportsDenormalization(mixed $data, string $type, ?string $format = null, array $context = []): bool
    {
        return \LongTermSupport\OpenApiGenerator\Component\OpenApi3\JsonSchema\Model\PathItem::class === $type;
    }

    public function supportsNormalization(mixed $data, ?string $format = null, array $context = []): bool
    {
        return $data instanceof \LongTermSupport\OpenApiGenerator\Component\OpenApi3\JsonSchema\Model\PathItem;
    }

    /**
     * @return object
     */
    public function denormalize(mixed $data, string $type, ?string $format = null, array $context = []): mixed
    {
        $object = new \LongTermSupport\OpenApiGenerator\Component\OpenApi3\JsonSchema\Model\PathItem();
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

        if (\array_key_exists('$ref', $data) && null !== $data['$ref']) {
            /** @var scalar $dollarRefVal */
            $dollarRefVal = $data['$ref'];
            $object->setDollarRef((string)$dollarRefVal);
            unset($data['$ref']);
        } elseif (\array_key_exists('$ref', $data) && null === $data['$ref']) {
            $object->setDollarRef(null);
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

        if (\array_key_exists('get', $data) && null !== $data['get']) {
            /** @var \LongTermSupport\OpenApiGenerator\Component\OpenApi3\JsonSchema\Model\Operation $denormGet */
            $denormGet = $this->denormalizer->denormalize($data['get'], \LongTermSupport\OpenApiGenerator\Component\OpenApi3\JsonSchema\Model\Operation::class, 'json', $context);
            $object->setGet($denormGet);
            unset($data['get']);
        } elseif (\array_key_exists('get', $data) && null === $data['get']) {
            $object->setGet(null);
        }

        if (\array_key_exists('put', $data) && null !== $data['put']) {
            /** @var \LongTermSupport\OpenApiGenerator\Component\OpenApi3\JsonSchema\Model\Operation $denormPut */
            $denormPut = $this->denormalizer->denormalize($data['put'], \LongTermSupport\OpenApiGenerator\Component\OpenApi3\JsonSchema\Model\Operation::class, 'json', $context);
            $object->setPut($denormPut);
            unset($data['put']);
        } elseif (\array_key_exists('put', $data) && null === $data['put']) {
            $object->setPut(null);
        }

        if (\array_key_exists('post', $data) && null !== $data['post']) {
            /** @var \LongTermSupport\OpenApiGenerator\Component\OpenApi3\JsonSchema\Model\Operation $denormPost */
            $denormPost = $this->denormalizer->denormalize($data['post'], \LongTermSupport\OpenApiGenerator\Component\OpenApi3\JsonSchema\Model\Operation::class, 'json', $context);
            $object->setPost($denormPost);
            unset($data['post']);
        } elseif (\array_key_exists('post', $data) && null === $data['post']) {
            $object->setPost(null);
        }

        if (\array_key_exists('delete', $data) && null !== $data['delete']) {
            /** @var \LongTermSupport\OpenApiGenerator\Component\OpenApi3\JsonSchema\Model\Operation $denormDelete */
            $denormDelete = $this->denormalizer->denormalize($data['delete'], \LongTermSupport\OpenApiGenerator\Component\OpenApi3\JsonSchema\Model\Operation::class, 'json', $context);
            $object->setDelete($denormDelete);
            unset($data['delete']);
        } elseif (\array_key_exists('delete', $data) && null === $data['delete']) {
            $object->setDelete(null);
        }

        if (\array_key_exists('options', $data) && null !== $data['options']) {
            /** @var \LongTermSupport\OpenApiGenerator\Component\OpenApi3\JsonSchema\Model\Operation $denormOptions */
            $denormOptions = $this->denormalizer->denormalize($data['options'], \LongTermSupport\OpenApiGenerator\Component\OpenApi3\JsonSchema\Model\Operation::class, 'json', $context);
            $object->setOptions($denormOptions);
            unset($data['options']);
        } elseif (\array_key_exists('options', $data) && null === $data['options']) {
            $object->setOptions(null);
        }

        if (\array_key_exists('head', $data) && null !== $data['head']) {
            /** @var \LongTermSupport\OpenApiGenerator\Component\OpenApi3\JsonSchema\Model\Operation $denormHead */
            $denormHead = $this->denormalizer->denormalize($data['head'], \LongTermSupport\OpenApiGenerator\Component\OpenApi3\JsonSchema\Model\Operation::class, 'json', $context);
            $object->setHead($denormHead);
            unset($data['head']);
        } elseif (\array_key_exists('head', $data) && null === $data['head']) {
            $object->setHead(null);
        }

        if (\array_key_exists('patch', $data) && null !== $data['patch']) {
            /** @var \LongTermSupport\OpenApiGenerator\Component\OpenApi3\JsonSchema\Model\Operation $denormPatch */
            $denormPatch = $this->denormalizer->denormalize($data['patch'], \LongTermSupport\OpenApiGenerator\Component\OpenApi3\JsonSchema\Model\Operation::class, 'json', $context);
            $object->setPatch($denormPatch);
            unset($data['patch']);
        } elseif (\array_key_exists('patch', $data) && null === $data['patch']) {
            $object->setPatch(null);
        }

        if (\array_key_exists('trace', $data) && null !== $data['trace']) {
            /** @var \LongTermSupport\OpenApiGenerator\Component\OpenApi3\JsonSchema\Model\Operation $denormTrace */
            $denormTrace = $this->denormalizer->denormalize($data['trace'], \LongTermSupport\OpenApiGenerator\Component\OpenApi3\JsonSchema\Model\Operation::class, 'json', $context);
            $object->setTrace($denormTrace);
            unset($data['trace']);
        } elseif (\array_key_exists('trace', $data) && null === $data['trace']) {
            $object->setTrace(null);
        }

        if (\array_key_exists('servers', $data) && null !== $data['servers']) {
            $values = [];
            /** @var array<mixed> $serversArr */
            $serversArr = $data['servers'];
            foreach ($serversArr as $value) {
                /** @var \LongTermSupport\OpenApiGenerator\Component\OpenApi3\JsonSchema\Model\Server $denormServer */
                $denormServer = $this->denormalizer->denormalize($value, \LongTermSupport\OpenApiGenerator\Component\OpenApi3\JsonSchema\Model\Server::class, 'json', $context);
                $values[]     = $denormServer;
            }

            $object->setServers($values);
            unset($data['servers']);
        } elseif (\array_key_exists('servers', $data) && null === $data['servers']) {
            $object->setServers(null);
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

        foreach ($data as $key => $value_3) {
            if (1 === \Safe\preg_match('/^(get|put|post|delete|options|head|patch|trace)$/', $key)) {
                /** @var \LongTermSupport\OpenApiGenerator\Component\OpenApi3\JsonSchema\Model\Operation $denormOp */
                $denormOp     = $this->denormalizer->denormalize($value_3, \LongTermSupport\OpenApiGenerator\Component\OpenApi3\JsonSchema\Model\Operation::class, 'json', $context);
                $object[$key] = $denormOp;
            }

            if (1 === \Safe\preg_match('/^x-/', $key)) {
                $object[$key] = $value_3;
            }
        }

        return $object;
    }

    /**
     * @return array<string, mixed>|string|int|float|bool|ArrayObject<string, mixed>|null
     */
    public function normalize(mixed $object, ?string $format = null, array $context = []): array|string|int|float|bool|ArrayObject|null
    {
        if (!$object instanceof \LongTermSupport\OpenApiGenerator\Component\OpenApi3\JsonSchema\Model\PathItem) {
            throw new LogicException('Expected PathItem, got ' . get_debug_type($object));
        }

        $data = [];
        if ($object->isInitialized('dollarRef') && null !== $object->getDollarRef()) {
            $data['$ref'] = $object->getDollarRef();
        }

        if ($object->isInitialized('summary') && null !== $object->getSummary()) {
            $data['summary'] = $object->getSummary();
        }

        if ($object->isInitialized('description') && null !== $object->getDescription()) {
            $data['description'] = $object->getDescription();
        }

        if ($object->isInitialized('get') && $object->getGet() instanceof \LongTermSupport\OpenApiGenerator\Component\OpenApi3\JsonSchema\Model\Operation) {
            $data['get'] = $this->normalizer->normalize($object->getGet(), 'json', $context);
        }

        if ($object->isInitialized('put') && $object->getPut() instanceof \LongTermSupport\OpenApiGenerator\Component\OpenApi3\JsonSchema\Model\Operation) {
            $data['put'] = $this->normalizer->normalize($object->getPut(), 'json', $context);
        }

        if ($object->isInitialized('post') && $object->getPost() instanceof \LongTermSupport\OpenApiGenerator\Component\OpenApi3\JsonSchema\Model\Operation) {
            $data['post'] = $this->normalizer->normalize($object->getPost(), 'json', $context);
        }

        if ($object->isInitialized('delete') && $object->getDelete() instanceof \LongTermSupport\OpenApiGenerator\Component\OpenApi3\JsonSchema\Model\Operation) {
            $data['delete'] = $this->normalizer->normalize($object->getDelete(), 'json', $context);
        }

        if ($object->isInitialized('options') && $object->getOptions() instanceof \LongTermSupport\OpenApiGenerator\Component\OpenApi3\JsonSchema\Model\Operation) {
            $data['options'] = $this->normalizer->normalize($object->getOptions(), 'json', $context);
        }

        if ($object->isInitialized('head') && $object->getHead() instanceof \LongTermSupport\OpenApiGenerator\Component\OpenApi3\JsonSchema\Model\Operation) {
            $data['head'] = $this->normalizer->normalize($object->getHead(), 'json', $context);
        }

        if ($object->isInitialized('patch') && $object->getPatch() instanceof \LongTermSupport\OpenApiGenerator\Component\OpenApi3\JsonSchema\Model\Operation) {
            $data['patch'] = $this->normalizer->normalize($object->getPatch(), 'json', $context);
        }

        if ($object->isInitialized('trace') && $object->getTrace() instanceof \LongTermSupport\OpenApiGenerator\Component\OpenApi3\JsonSchema\Model\Operation) {
            $data['trace'] = $this->normalizer->normalize($object->getTrace(), 'json', $context);
        }

        if ($object->isInitialized('servers') && null !== $object->getServers()) {
            $values = [];
            foreach ($object->getServers() as $value) {
                $values[] = $this->normalizer->normalize($value, 'json', $context);
            }

            $data['servers'] = $values;
        }

        if ($object->isInitialized('parameters') && null !== $object->getParameters()) {
            $values_1 = [];
            foreach ($object->getParameters() as $value_1) {
                $value_2 = $value_1;
                if (\is_object($value_1)) {
                    $value_2 = $this->normalizer->normalize($value_1, 'json', $context);
                } elseif (\is_object($value_1)) {
                    $value_2 = $this->normalizer->normalize($value_1, 'json', $context);
                }

                $values_1[] = $value_2;
            }

            $data['parameters'] = $values_1;
        }

        foreach ($object as $key => $value_3) {
            if (1 === \Safe\preg_match('/^(get|put|post|delete|options|head|patch|trace)$/', $key)) {
                $data[$key] = $this->normalizer->normalize($value_3, 'json', $context);
            }

            if (1 === \Safe\preg_match('/^x-/', $key)) {
                $data[$key] = $value_3;
            }
        }

        return $data;
    }

    public function getSupportedTypes(?string $format = null): array
    {
        return [\LongTermSupport\OpenApiGenerator\Component\OpenApi3\JsonSchema\Model\PathItem::class => false];
    }
}
