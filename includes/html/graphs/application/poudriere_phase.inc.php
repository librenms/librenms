<?php

$unit_text = 'count';

$colours = 'rainbow';

$stats_list = [
    'check-sanity' => [
        'stat' => 'check-sanity',
        'descr' => 'check-sanity',
    ],
    'pkg-depends' => [
        'stat' => 'pkg-depends',
        'descr' => 'pkg-depends',
    ],
    'fetch-depends' => [
        'stat' => 'fetch-depends',
        'descr' => 'fetch-depends',
    ],
    'fetch checksum' => [
        'stat' => 'fetch checksum',
        'descr' => 'fetch checksum',
    ],
    'extract-depends' => [
        'stat' => 'extract-depends',
        'descr' => 'extract-depends',
    ],
    'extract patch-depends' => [
        'stat' => 'extract patch-depends',
        'descr' => 'extract patch-depends',
    ],
    'patch' => [
        'stat' => 'patch',
        'descr' => 'patch',
    ],
    'build-depends' => [
        'stat' => 'build-depends',
        'descr' => 'build-depends',
    ],
    'lib-depends' => [
        'stat' => 'lib-depends',
        'descr' => 'lib-depends',
    ],
    'configure' => [
        'stat' => 'configure',
        'descr' => 'configure',
    ],
    'build' => [
        'stat' => 'build',
        'descr' => 'build',
    ],
    'run-depends' => [
        'stat' => 'run-depends',
        'descr' => 'run-depends',
    ],
    'stage' => [
        'stat' => 'stage',
        'descr' => 'stage',
    ],
    'package' => [
        'stat' => 'package',
        'descr' => 'package',
    ],
];

require 'poudriere-common.inc.php';
