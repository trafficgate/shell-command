<?php

declare(strict_types=1);

$finder = PhpCsFixer\Finder::create()
    ->exclude('bootstrap/cache')
    ->exclude('build')
    ->exclude('node_modules')
    ->exclude('storage')
    ->exclude('vendor')
    ->ignoreVCS(true)
    ->in(__DIR__);

$config = new PhpCsFixer\Config();
$config
    ->setRiskyAllowed(false)
    ->setRules([
        '@PSR12' => true,
        '@PhpCsFixer' => true,
        'binary_operator_spaces' => ['default'=>'align_single_space_minimal'],
        'class_definition' => ['space_before_parenthesis' => true],
        'concat_space' => ['spacing' => 'one'],
        'declare_parentheses' => true,
        'global_namespace_import' => true,
        'heredoc_indentation' => true,
        'list_syntax' => ['syntax' => 'short'],
        'multiline_whitespace_before_semicolons' => ['strategy'=>'no_multi_line'],
        'not_operator_with_successor_space' => true,
        'nullable_type_declaration_for_default_null_value' => true,
        'php_unit_internal_class' => false,
        'php_unit_test_class_requires_covers' => false,
        'phpdoc_line_span' => ['const' => 'single'],
        'phpdoc_to_comment' => [
            'ignored_tags' => [
                // Used for Swagger documentation
                'OA\Components',
                'OA\Delete',
                'OA\Get',
                'OA\OpenApi',
                'OA\Parameter',
                'OA\Patch',
                'OA\Post',
                'OA\Put',
                'OA\RequestBody',
                'OA\Schema',
                'OA\SecurityScheme',

                // Used for PHPStorm type hinting
                'var',
            ],
        ],
        'phpdoc_types_order' => ['null_adjustment'=>'always_last'],
        'protected_to_private' => false,
        'self_static_accessor' => true,
        'simplified_if_return' => true,
        'simplified_null_return' => true,
        'single_line_comment_style' => ['comment_types' => ['hash']],
        'ternary_to_null_coalescing' => true,
        'yoda_style' => false,
    ])
    ->setFinder($finder);

return $config;
