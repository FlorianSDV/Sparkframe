<?php

$finder = PhpCsFixer\Finder::create()
    ->in(__DIR__ . '/src')
    ->in(__DIR__ . '/tests');

return (new PhpCsFixer\Config())
    ->setRiskyAllowed(true)
    ->setRules([
        '@PSR12' => true,
        'array_syntax' => ['syntax' => 'short'],
        'single_quote' => true,

        // Import/Use statements
        'ordered_imports' => ['sort_algorithm' => 'alpha'],
        'no_unused_imports' => true,
        'global_namespace_import' => [
            'import_classes' => true,
            'import_constants' => true,
            'import_functions' => true,
        ],

        // Code quality
        'declare_strict_types' => true,
        'void_return' => true,
        'no_superfluous_phpdoc_tags' => ['allow_mixed' => true],

        // Spacing and formatting
        'blank_line_after_opening_tag' => true,
        'blank_line_before_statement' => [
            'statements' => ['return', 'throw', 'try', 'if', 'switch', 'for', 'foreach', 'while', 'do'],
        ],
        'method_argument_space' => ['on_multiline' => 'ensure_fully_multiline'],

        // Cleanup
        'no_empty_phpdoc' => true,
        'no_blank_lines_after_phpdoc' => true,
        'no_extra_blank_lines' => true,
        'no_trailing_whitespace' => true,
        'no_whitespace_in_blank_line' => true,

        // Opinionated
        'self_static_accessor' => true,
    ])
    ->setFinder($finder);
