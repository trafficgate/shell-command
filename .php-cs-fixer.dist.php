<?php

declare(strict_types=1);

$finder = PhpCsFixer\Finder::create()
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
        'declare_parentheses' => true,
        'global_namespace_import' => true,
        'heredoc_indentation' => true,
        'list_syntax' => true,
        'multiline_whitespace_before_semicolons' => ['strategy'=>'no_multi_line'],
        'not_operator_with_successor_space' => true,
        'nullable_type_declaration_for_default_null_value' => true,
        'php_unit_internal_class' => false,
        'php_unit_test_class_requires_covers' => false,
        'phpdoc_types_order' => ['null_adjustment'=>'always_last'],
        'protected_to_private' => false,
        'self_static_accessor' => true,
        'simplified_if_return' => true,
        'simplified_null_return' => true,
        'ternary_to_null_coalescing' => true,
        'yoda_style' => false,
    ])
    ->setFinder($finder);

return $config;
