<?php

$finder = Symfony\CS\Finder\DefaultFinder::create()
    ->files()
    ->in(__DIR__)
    ->exclude('build')
    ->exclude('vendor')
    ->notName('*.phar')
    ->notName('CONTRIBUTING')
    ->ignoreDotFiles(true)
    ->ignoreVCS(true);

return Symfony\CS\Config\Config::create()
    // use default PSR-2_LEVEL:
    ->level(Symfony\CS\FixerInterface::PSR2_LEVEL)
    ->fixers(
        [
            'header_comment',
            'newline_after_open_tag',
            '-no_empty_lines_after_phpdocs',
            'phpdoc_order',
            '-psr0',
            'ordered_use',
            'short_array_syntax',
            'strict',
            'strict_param',
        ]
    )
    ->finder($finder)
    ->setUsingCache(true);
