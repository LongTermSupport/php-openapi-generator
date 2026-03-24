<?php

declare(strict_types=1);

/**
 * Project-level PHP CS Fixer configuration for PHP OpenAPI Generator.
 *
 * Overrides the default php-qa-ci config to disable `final_class`.
 * This project has cross-component inheritance (GeneratorCore → OpenApiCommon → OpenApi3)
 * where base classes MUST remain non-final so child components can extend them.
 *
 * All other rules are inherited from the default php-qa-ci config.
 */

use Composer\Autoload\ClassLoader;
use PhpCsFixer\Runner\Parallel\ParallelConfigFactory;

$rules = [
    '@PhpCsFixer'                         => true,
    '@Symfony'                            => true,
    '@DoctrineAnnotation'                 => true,
    '@PHP8x4Migration'                    => true,
    'align_multiline_comment'             => true,
    'array_indentation'                   => true,
    'array_syntax'                        => ['syntax' => 'short'],
    'blank_line_after_opening_tag'        => true,
    'binary_operator_spaces'              => [
        'default' => 'align',
        'operators' => [
            '=>' => 'align_single_space_by_scope',
        ],
    ],
    'cast_spaces'                         => ['space' => 'none'],
    'concat_space'                        => ['spacing' => 'one'],
    'declare_strict_types'                => true,
    // DISABLED: This project has cross-component inheritance
    'final_class'                         => false,
    'ordered_class_elements'              => [
        'order' => [
            'use_trait',
            'constant_public',
            'constant_protected',
            'constant_private',
            'property_public',
            'property_protected',
            'property_private',
            'construct',
            'destruct',
            'magic',
            'phpunit',
            'method_public',
            'method_protected',
            'method_private',
        ],
    ],
    // fights with PSR-12 in phpcs/phpcbf
    'ordered_imports'                     => [
        'sort_algorithm' => 'alpha',
        // this is the PSR12 order, do not change
        'imports_order'  => [
            'class',
            'function',
            'const',
        ],
    ],
    'modernize_types_casting'             => true,
    // this one is not compatible with attributes
    // 'php_unit_size_class'          => ['group' => 'small'],
    // this one is not compatible with attributes
    'php_unit_test_class_requires_covers' => false,
    'psr_autoloading'                     => true,
    'return_assignment'                   => true,
    'self_accessor'                       => true,
    'static_lambda'                       => true,
    'strict_comparison'                   => true,
    'strict_param'                        => true,
    'ternary_to_null_coalescing'          => true,
    'void_return'                         => true,
    'yoda_style'                          => [
        'equal'     => true,
        'identical' => true,
    ],
    'fully_qualified_strict_types'        => true,
    'native_function_invocation'          => true,
    'method_argument_space'               => [
        'after_heredoc'                    => true,
        'keep_multiple_spaces_after_comma' => true,
        'on_multiline'                     => 'ensure_fully_multiline',
    ],
    'single_line_throw'                   => false,
    'global_namespace_import'             => true,
    'phpdoc_to_return_type'               => false,
    'no_superfluous_phpdoc_tags'          => true,
    'phpdoc_to_comment'                   => false,    // otherwise we cant use @var comments to help stan understand things
    // PHP 8.4 specific rules
    'nullable_type_declaration_for_default_null_value' => true, // Critical for PHP 8.4 compatibility
    'nullable_type_declaration'           => ['syntax' => 'question_mark'], // Use ? syntax for nullable types
];

$projectRoot = (static function () {
    $reflection = new ReflectionClass(ClassLoader::class);

    return \dirname($reflection->getFileName(), 3);
})();
if (str_starts_with($projectRoot, 'phar')) {
    // When CS Fixer runs as a PHAR, the ClassLoader reflection gives a phar:// path.
    // Use getcwd() which is always the actual project root (set by the QA pipeline).
    $projectRoot = getcwd();
}

// Use the default php-qa-ci finder, or a project override if it exists
$finderPath   = __DIR__ . '/../vendor/lts/php-qa-ci/configDefaults/generic/php_cs_finder.php';
$overridePath = __DIR__ . '/php_cs_finder.php';
if (file_exists($overridePath)) {
    $finderPath = $overridePath;
}

$finder = require $finderPath;

return (new PhpCsFixer\Config())
    ->setRules($rules)
    ->setFinder($finder)
    ->setParallelConfig(ParallelConfigFactory::detect())
;
