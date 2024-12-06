<?php

$unit_text = 'bytes';

$stats_list = [
    'anon' => [
        'stat' => 'anon',
        'descr' => 'anon',
    ],
    'file' => [
        'stat' => 'file',
        'descr' => 'file',
    ],
    'kernel' => [
        'stat' => 'kernel',
        'descr' => 'kernel',
    ],
    'kernel_stack' => [
        'stat' => 'kernel_stack',
        'descr' => 'kernel_stack',
    ],
    'pagetables' => [
        'stat' => 'pagetables',
        'descr' => 'pagetables',
    ],
    'sec_pagetables' => [
        'stat' => 'sec_pagetables',
        'descr' => 'sec_pagetables',
    ],
    'percpu' => [
        'stat' => 'percpu',
        'descr' => 'percpu',
    ],
    'vmalloc' => [
        'stat' => 'vmalloc',
        'descr' => 'vmalloc',
    ],
    'shmem' => [
        'stat' => 'shmem',
        'descr' => 'shmem',
    ],
    'file_mapped' => [
        'stat' => 'file_mapped',
        'descr' => 'file_mapped',
    ],
    'file_dirty' => [
        'stat' => 'file_dirty',
        'descr' => 'file_dirty',
    ],
    'file_writeback' => [
        'stat' => 'file_writeback',
        'descr' => 'file_writeback',
    ],
    'swapcached' => [
        'stat' => 'swapcached',
        'descr' => 'swapcached',
    ],
    'anon_thp' => [
        'stat' => 'anon_thp',
        'descr' => 'anon_thp',
    ],
    'file_thp' => [
        'stat' => 'file_thp',
        'descr' => 'file_thp',
    ],
    'shmem_thp' => [
        'stat' => 'shmem_thp',
        'descr' => 'shmem_thp',
    ],
    'inactive_anon' => [
        'stat' => 'inactive_anon',
        'descr' => 'inactive_anon',
    ],
    'active_anon' => [
        'stat' => 'active_anon',
        'descr' => 'active_anon',
    ],
    'slab_reclaimable' => [
        'stat' => 'slab_reclaimable',
        'descr' => 'slab_reclaimable',
    ],
    'slab_unreclaimable' => [
        'stat' => 'slab_unreclaimable',
        'descr' => 'slab_unreclaimable',
    ],
    'slab' => [
        'stat' => 'slab',
        'descr' => 'slab',
    ],
];

require 'oslv_monitor-common.inc.php';
