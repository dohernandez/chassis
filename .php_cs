<?php

$finder = PhpCsFixer\Finder::create()
    ->exclude('vendor')
    ->notPath('tests/sandbox/app/storage/cache/TestApplicationContainer.php')
    ->in(__DIR__);

return PhpCsFixer\Config::create()
    ->setRules([
        '@PSR2' => true,
        'declare_strict_types' => true,
        'no_unused_imports' => true,
        'cast_spaces' => true,
        'no_extra_consecutive_blank_lines' => true,
        'function_typehint_space' => true,
        'include' => true,
        'lowercase_cast' => true,
        'trailing_comma_in_multiline_array' => true,
        'new_with_braces' => true,
        'phpdoc_scalar' => true,
        'phpdoc_types' => true,
        'no_leading_import_slash' => true,
        'blank_line_before_return' => true,
        'short_scalar_cast' => true,
        'single_blank_line_before_namespace' => true,
        'single_quote' => true,
        'ordered_imports' => true,
        'phpdoc_order' => true,
        'array_syntax' => ['syntax' => 'short'],
        'no_useless_return' => true,
        'no_useless_else' => true,
        'full_opening_tag' => true,
        'method_separation' => true,
        'no_extra_consecutive_blank_lines' => true,
        'no_singleline_whitespace_before_semicolons' => true,
        'no_spaces_around_offset' => true,
        'php_unit_strict' => true, # PHPUnit methods like assertSame should be used instead of assertEquals.
        'phpdoc_no_empty_return' => true,
        'phpdoc_trim' => true,
        'psr4' => true,
        'ternary_operator_spaces' => true,
        'whitespace_after_comma_in_array' => true,
    ])
    ->setRiskyAllowed(true)
    ->setFinder($finder);
