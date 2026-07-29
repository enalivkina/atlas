<?php

use PhpCsFixer\Config;
use PhpCsFixer\Finder;

$finder = Finder::create()
    ->in(__DIR__ . '/../')
    ->exclude([
        '.docker',
        'helm',
        'vendor',
        'var',
        'public',
        'templates',
        'bin',
        'config',
    ])
    ->name('*.php');

return (new Config())
    ->setRules([
        '@PER-CS2.0' => true,
    ])
    ->setFinder($finder);