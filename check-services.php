#!/usr/bin/env php
<?php

$init_modules = [];
require __DIR__ . '/includes/init.php';

$options = getopt('drfpgh:');

Artisan::call('services:poll', ['device spec' => $options['h'] ?? null, '--verbose' => isset($options['d']) ? 3 : 1]);
