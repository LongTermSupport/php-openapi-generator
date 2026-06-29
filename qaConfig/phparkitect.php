<?php

declare(strict_types=1);

/**
 * PHPArkitect entry config for php-openapi-generator.
 *
 * php-qa-ci's on-by-default arkitect tier enforces Interface/Enum/Trait *suffix*
 * naming (it is the single source of truth for type-suffix naming since the
 * PHPStan RequireTypeSuffixRule was migrated to PHPArkitect).
 *
 * This generator intentionally defines UNSUFFIXED interfaces and traits — Jane
 * PHP runtime templates (CheckArray, ApiException, ClientException,
 * ServerException, AuthenticationPlugin, CustomQueryResolver, Endpoint) and the
 * internal `*Generator` traits — that are copied VERBATIM into every generated
 * SDK under those exact names. Renaming them is a breaking change to the public
 * surface of every SDK this tool has ever produced (and to every fixture under
 * Tests/.../Expected that pins generated output). So the Interface and Trait
 * suffix rules are NOT applied here — this preserves the project's prior,
 * deliberate PHPStan `phpqaci.traitSuffix` / `phpqaci.interfaceSuffix`
 * exemption, now that type-suffix naming lives in PHPArkitect.
 *
 * The Enum suffix rule IS kept: the generator has no intentionally-unsuffixed
 * enums, so that convention still applies.
 */

use Arkitect\ClassSet;
use Arkitect\CLI\Config;
use Arkitect\Expression\ForClasses\HaveNameMatching;
use Arkitect\Expression\ForClasses\IsEnum;
use Arkitect\Rules\Rule;

return static function (Config $config): void {
    $srcDir = getenv('PHPQACI_ARKITECT_SRC_DIR') ?: (getcwd() . '/src');

    if (!is_dir($srcDir)) {
        return;
    }

    // Generated code is regenerated and cannot be renamed — never check it.
    $classSet = ClassSet::fromDir($srcDir)->excludePath('Generated');

    $config->add(
        $classSet,
        Rule::allClasses()
            ->that(new IsEnum())
            ->should(new HaveNameMatching('*Enum'))
            ->because('an Enum suffix makes the symbol kind obvious at every use site'),
    );
};
