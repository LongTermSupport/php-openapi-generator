<?php

declare(strict_types=1);

namespace LongTermSupport\OpenApiGenerator\Component\GeneratorCore\Generator;

use LogicException;
use LongTermSupport\OpenApiGenerator\Component\GeneratorCore\Tools\InflectorTrait;

/**
 * Helper to generate name for property / class / ...
 */
class Naming
{
    use InflectorTrait;

    public const BAD_CLASS_NAME_REGEX = '/^
        ([0-9])|
        \b(
            (a(bstract|nd|rray|s))|
            (c(a(llable|se|tch)|l(ass|one)|on(st|tinue)))|
            (d(e(clare|fault)|ie|o))|
            (e(cho|lse(if)?|mpty|nd(declare|for(each)?|if|switch|while)|val|x(it|tends)))|
            (f(inal|or(each)?|unction))|
            (g(lobal|oto))|
            (i(f|mplements|n(clude(_once)?|st(anceof|eadof)|terface)|sset))|
            (n(amespace|ew))|
            (p(r(i(nt|vate)|otected)|ublic))|
            (re(quire(_once)?|turn))|
            (s(tatic|witch))|
            (t(hrow|r(ait|y)))|
            (u(nset|se))|
            (__halt_compiler|break|list|(x)?or|var|while)|
            (bool|enum|false|finally|float|fn|int|iterable|match|mixed|never|null|object|parent|readonly|self|string|true|void|yield)
        )\b
    /ix';

    /**
     * Method names that ArrayObject defines and that generated accessors must
     * not override with an incompatible signature. When a model extends
     * \ArrayObject and a property's accessor would land on one of these names,
     * we rename it (see {@see self::getReservedSafeMethodName()}). The
     * `#[\ReturnTypeWillChange]` attribute is NOT a fix — it only suppresses
     * PHP's runtime deprecation, it does NOT make the override covariant, so
     * PHPStan still reports `method.childReturnType`.
     *
     * @var array<string, true>
     */
    private const array ARRAY_OBJECT_RESERVED_METHODS = [
        'getFlags'         => true,
        'setFlags'         => true,
        'getIterator'      => true,
        'setIteratorClass' => true,
        'getIteratorClass' => true,
        'getArrayCopy'     => true,
    ];

    public function getPropertyName(string $name): string
    {
        $name = $this->cleaning($name);
        // php property can't start with a number
        if (is_numeric(substr($name, 0, 1))) {
            return 'n' . $name;
        }

        return $name;
    }

    /**
     * @param string             $name                Property name to be cleaned/deduplicated
     * @param array<string, int> $otherPropertiesName
     */
    public function getDeduplicatedName(string $name, array &$otherPropertiesName): string
    {
        $cleanedName = $this->cleaning($name);

        $duplicateName = strtolower($cleanedName);
        if (\array_key_exists($duplicateName, $otherPropertiesName)) {
            ++$otherPropertiesName[$duplicateName];
            $cleanedName .= '' . $otherPropertiesName[$duplicateName];
        } else {
            $otherPropertiesName[$duplicateName] = 1;
        }

        return $cleanedName;
    }

    public function getPrefixedMethodName(string $prefix, string $name): string
    {
        $name = $this->cleaning($name);
        // since it's prefixed, it doesn't require to check if it start with a number

        return \sprintf('%s%s', $prefix, $this->getInflector()->classify($name));
    }

    /**
     * Suffix the given accessor name with `Field` if (a) the model class extends
     * \ArrayObject and (b) the accessor name would override an ArrayObject method
     * with a different signature. Otherwise the original name is returned
     * unchanged. Centralised here so {@see GetterSetterGenerator}, the normalizer
     * generator, and the denormalizer generator all agree on the renamed name.
     */
    public function getReservedSafeMethodName(string $methodName, bool $extendsArrayObject): string
    {
        if (!$extendsArrayObject) {
            return $methodName;
        }

        if (!isset(self::ARRAY_OBJECT_RESERVED_METHODS[$methodName])) {
            return $methodName;
        }

        return $methodName . 'Field';
    }

    public function getClassName(string $name): string
    {
        $name = $this->cleaning($name, true);

        if (0 !== \Safe\preg_match(self::BAD_CLASS_NAME_REGEX, $name)) {
            return '_' . $name;
        }

        return $name;
    }

    public function getAuthName(string $name): string
    {
        return $this->getClassName(\sprintf('%sAuthentication', $name));
    }

    public function getConstraintName(string $name): string
    {
        return $this->getClassName(\sprintf('%sConstraint', $name));
    }

    /** @param array<string> $namespace */
    public function getRuntimeNamespace(string $schemaNamespace, array $namespace): string
    {
        $namespaceSuffix = '';
        if ([] !== $namespace) {
            $namespaceSuffix = '\\' . implode('\\', $namespace);
        }

        return $schemaNamespace . '\Runtime' . $namespaceSuffix;
    }

    /** @param array<string> $namespace */
    public function getRuntimeClassFQCN(string $schemaNamespace, array $namespace, string $class): string
    {
        return \sprintf('%s\%s', $this->getRuntimeNamespace($schemaNamespace, $namespace), $class);
    }

    protected function cleaning(string $name, bool $class = false): string
    {
        if (0 !== \Safe\preg_match('/\$/', $name)) {
            $result = \Safe\preg_replace_callback(
                '/\$([a-z])/',
                static function (array $matches): string {
                    $captured = $matches[1];
                    if (!\is_string($captured)) {
                        throw new LogicException('Expected string, got ' . get_debug_type($captured));
                    }

                    return 'dollar' . ucfirst($captured);
                },
                $name,
            );
            if (!\is_string($result)) {
                throw new LogicException('Expected string, got ' . get_debug_type($result));
            }

            $name = $result;
        }

        $result = \Safe\preg_replace_callback(
            '#[/\{\}]+(\w)#',
            static function (array $matches): string {
                $captured = $matches[1];
                if (!\is_string($captured)) {
                    throw new LogicException('Expected string, got ' . get_debug_type($captured));
                }

                return ucfirst($captured);
            },
            $name,
        );
        if (!\is_string($result)) {
            throw new LogicException('Expected string, got ' . get_debug_type($result));
        }

        $name = $result;

        // Doctrine Inflector does not seem to handle some characters (like dots, @, :) well.
        // So replace invalid char by an underscore to allow Doctrine to uppercase word correctly.
        $result = \Safe\preg_replace('/[^a-z0-9 ]+/iu', '_', $name);
        if (!\is_string($result)) {
            throw new LogicException('Expected string, got ' . get_debug_type($result));
        }

        $name = trim($result);

        if ($class) {
            return $this->getInflector()->classify($name);
        }

        return $this->getInflector()->camelize($name);
    }
}
