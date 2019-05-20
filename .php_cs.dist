<?php

$finder = PhpCsFixer\Finder::create()
    ->exclude(['.git', 'vendor'])
    ->in(__DIR__);

/**
 * To know the effect of any of the following rules, please execute:
 *
 *     php-cs-fixer describe <rule_name>
 *
 */
$config = PhpCsFixer\Config::create()
    ->setRiskyAllowed(false)
    ->setRules([
        'array_syntax' => ['syntax' => 'short'],
        'binary_operator_spaces' => [
            'operators' => [
                '=' => 'align_single_space_minimal',
                '=>' => 'align_single_space_minimal',
            ],
        ],
        'blank_line_after_namespace' => true,
        'blank_line_after_opening_tag' => true,
        'blank_line_before_statement' => true,
        'braces' => true,
        'cast_spaces' => true,
        'class_definition' => true,
        'class_keyword_remove' => false,
        'combine_consecutive_issets' => true,
        'combine_consecutive_unsets' => true,
        'concat_space' => true,
        'declare_equal_normalize' => true,
        'declare_strict_types' => false, // Risky rule
        'dir_constant' => false, // Risky rule
        'doctrine_annotation_array_assignment' => false,
        'doctrine_annotation_braces' => false,
        'doctrine_annotation_indentation' => false,
        'doctrine_annotation_spaces' => false,
        'elseif' => true,
        'encoding' => true,
        'ereg_to_preg' => false, // Risky rule
        'full_opening_tag' => true,
        'function_declaration' => true,
        'function_to_constant' => false, // Risky rule
        'function_typehint_space' => true,
        'general_phpdoc_annotation_remove' => false,
        'header_comment' => false,
        'heredoc_to_nowdoc' => true,
        'include' => true,
        'indentation_type' => true,
        'is_null' => false, // Risky rule
        'line_ending' => true,
        'linebreak_after_opening_tag' => false,
        'list_syntax' => ['syntax' => 'long'],
        'lowercase_cast' => true,
        'lowercase_constants' => true,
        'lowercase_keywords' => true,
        'magic_constant_casing' => true,
        'mb_str_functions' => false, // Risky rule
        'method_argument_space' => ['ensure_fully_multiline' => true],
        'method_separation' => true,
        'modernize_types_casting' => false, // Risky rule
        'native_function_casing' => true,
        'native_function_invocation' => false, // Risky rule
        'new_with_braces' => true,
        'no_alias_functions' => false, // Risky rule
        'no_blank_lines_after_class_opening' => true,
        'no_blank_lines_after_phpdoc' => true,
        'no_blank_lines_before_namespace' => false,
        'no_break_comment' => true,
        'no_closing_tag' => true,
        'no_empty_comment' => true,
        'no_empty_phpdoc' => true,
        'no_empty_statement' => true,
        'no_extra_consecutive_blank_lines' => [
            'tokens' => [
                'break',
                'case',
                'continue',
                'curly_brace_block',
                'default',
                'extra',
                'parenthesis_brace_block',
                'return',
                'square_brace_block',
                'switch',
                'throw',
                'use',
                'use_trait',
            ],
        ],
        'no_homoglyph_names' => false, // Risky rule
        'no_leading_import_slash' => true,
        'no_leading_namespace_whitespace' => true,
        'no_mixed_echo_print' => true,
        'no_multiline_whitespace_around_double_arrow' => true,
        'no_multiline_whitespace_before_semicolons' => true,
        'no_null_property_initialization' => false,
        'no_php4_constructor' => false, // Risky rule
        'no_short_bool_cast' => true,
        'no_short_echo_tag' => false,
        'no_singleline_whitespace_before_semicolons' => true,
        'no_spaces_after_function_name' => true,
        'no_spaces_around_offset' => true,
        'no_spaces_inside_parenthesis' => true,
        'no_superfluous_elseif' => true,
        'no_trailing_comma_in_list_call' => true,
        'no_trailing_comma_in_singleline_array' => true,
        'no_trailing_whitespace' => true,
        'no_trailing_whitespace_in_comment' => true,
        'no_unneeded_control_parentheses' => true,
        'no_unneeded_curly_braces' => true,
        'no_unneeded_final_method' => true,
        'no_unreachable_default_argument_value' => false, // Risky rule
        'no_unused_imports' => true,
        'no_useless_else' => true,
        'no_useless_return' => true,
        'no_whitespace_before_comma_in_array' => true,
        'no_whitespace_in_blank_line' => true,
        'non_printable_character' => false, // Risky rule
        'normalize_index_brace' => true,
        'not_operator_with_space' => false,
        'not_operator_with_successor_space' => true,
        'object_operator_without_whitespace' => true,
        'ordered_class_elements' => true,
        'ordered_imports' => true,
        'php_unit_construct' => false, // Risky rule
        'php_unit_dedicate_assert' => false, // Risky rule
        'php_unit_fqcn_annotation' => true,
        'php_unit_strict' => false, // Risky rule
        'php_unit_test_class_requires_covers' => false,
        'phpdoc_add_missing_param_annotation' => true,
        'phpdoc_align' => true,
        'phpdoc_annotation_without_dot' => true,
        'phpdoc_indent' => true,
        'phpdoc_inline_tag' => true,
        'phpdoc_no_access' => true,
        'phpdoc_no_alias_tag' => [
             'property-write' => 'property',
             'type' => 'var',
             'link' => 'see'
        ],
        'phpdoc_no_empty_return' => true,
        'phpdoc_no_package' => true,
        'phpdoc_no_useless_inheritdoc' => true,
        'phpdoc_order' => true,
        'phpdoc_return_self_reference' => true,
        'phpdoc_scalar' => true,
        'phpdoc_separation' => true,
        'phpdoc_single_line_var_spacing' => true,
        'phpdoc_summary' => true,
        'phpdoc_to_comment' => false,
        'phpdoc_trim' => true,
        'phpdoc_types' => true,
        'phpdoc_types_order' => [
            'sort_algorithm' => 'alpha',
            'null_adjustment' => 'always_last',
        ],
        'phpdoc_var_without_name' => true,
        'pow_to_exponentiation' => false, // Risky rule
        'pre_increment' => true,
        'protected_to_private' => false,
        'psr0' => false, // Risky rule
        'psr4' => false, // Risky rule
        'random_api_migration' => false, // Risky rule
        'return_type_declaration' => true,
        'self_accessor' => false, // Risky rule
        'semicolon_after_instruction' => true,
        'short_scalar_cast' => true,
        'silenced_deprecation_error' => false, // Risky rule
        'simplified_null_return' => false, // Risky rule
        'single_blank_line_at_eof' => true,
        'single_blank_line_before_namespace' => true,
        'single_class_element_per_statement' => true,
        'single_import_per_statement' => true,
        'single_line_after_imports' => true,
        'single_line_comment_style' => true,
        'single_quote' => true,
        'space_after_semicolon' => true,
        'standardize_not_equals' => true,
        'strict_comparison' => false, // Risky rule
        'strict_param' => false, // Risky rule
        'switch_case_semicolon_to_colon' => true,
        'switch_case_space' => true,
        'ternary_operator_spaces' => true,
        'ternary_to_null_coalescing' => true,
        'trailing_comma_in_multiline_array' => true,
        'trim_array_spaces' => true,
        'unary_operator_spaces' => true,
        'visibility_required' => true,
        'void_return' => false, // Risky rule
        'whitespace_after_comma_in_array' => true,
        'yoda_style' => false,
    ])
    ->setFinder($finder);

return $config;
