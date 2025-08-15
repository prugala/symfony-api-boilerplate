<?php

$finder = (new PhpCsFixer\Finder())
    ->in(__DIR__ . '/src')
    ->in(__DIR__ . '/tests')
;

return (new PhpCsFixer\Config())
    ->setRules([
        '@Symfony' => true,
        '@PHP84Migration' => true,
        'no_unused_imports' => true,
        'no_useless_else' => true,
        'blank_line_before_statement' => true,
        'declare_strict_types' => true,
    ])
    ->setFinder($finder)
;
