# LTS PHP OpenAPI Generator

**Hard fork of [janephp/janephp](https://github.com/janephp/janephp) with OpenAPI 3.1 support.**

A set of libraries to generate PHP models and API clients from OpenAPI 3.1 specifications.

## Why this fork?

Jane PHP does not support OpenAPI 3.1 ([janephp/janephp#759](https://github.com/janephp/janephp/issues/759)). OAS 3.1 has been the current standard since February 2021, and many API providers publish 3.1 specs exclusively. The upstream maintainers have indicated they welcome PRs but have no roadmap for 3.1 support.

This fork adds:

- **Full OpenAPI 3.1 support** — handles `type` as array (e.g. `["string", "null"]`), nullable via type arrays, `const`, `$schema`, and other 3.1 features
- **PHP 8.4** — requires and targets modern PHP
- **PHPUnit 11** — test suite fully updated to PHPUnit 11 conventions
- Strict OpenAPI spec validation via `lts/strict-openapi-validator`

## Requirements

- PHP 8.4+

## Installation

```bash
composer require lts/php-openapi-generator
```

## Usage

Generate a client from an OpenAPI 3.1 spec:

```bash
vendor/bin/php-openapi generate --config-file .php-openapi
```

Example `.php-openapi` config:

```php
<?php

return [
    'openapi-file' => __DIR__ . '/openapi.yaml',
    'namespace'    => 'MyApp\Client',
    'directory'    => __DIR__ . '/generated',
];
```

## Symfony Integration

To register the generate command in a Symfony application, add it as a console command in your services config:

```php
// config/services.php
use LongTermSupport\OpenApiGenerator\Component\OpenApiCommon\Console\Command\GenerateCommand;
use LongTermSupport\OpenApiGenerator\Component\OpenApiCommon\Console\Loader\ConfigLoader;
use LongTermSupport\OpenApiGenerator\Component\OpenApiCommon\Console\Loader\OpenApiMatcher;
use LongTermSupport\OpenApiGenerator\Component\OpenApiCommon\Console\Loader\SchemaLoader;
use Symfony\Component\DependencyInjection\Loader\Configurator\ContainerConfigurator;

return function (ContainerConfigurator $container): void {
    $container->services()
        ->set('php_openapi.generate_command', GenerateCommand::class)
        ->args([
            new ConfigLoader(),
            new SchemaLoader(),
            new OpenApiMatcher(),
        ])
        ->tag('console.command');
};
```

Then run:

```bash
bin/console php-openapi:generate --config-file .php-openapi
```

## Upstream

- Original repository: [janephp/janephp](https://github.com/janephp/janephp)
- Original documentation: [jane.jolicode.com](https://jane.jolicode.com/)
- Original license: MIT

## Maintained by

[Long Term Support Ltd](https://ltscommerce.dev) — joseph@ltscommerce.dev

## Credits

- [JoliCode](https://jolicode.com/) for creating and maintaining Jane PHP
- All original [janephp contributors](https://github.com/janephp/janephp/graphs/contributors)
- [Joel Wurtz](https://github.com/joelwurtz) — original author
- [Baptiste Leduc](https://github.com/Korbeil) — major contributor

## License

MIT — see [LICENSE](LICENSE). Original work copyright © 2016–2017 Joel Wurtz.
