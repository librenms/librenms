<?php

namespace LibreNMS\Applications;

use App\Models\Application;

class MojoCapeSubmit extends ApplicationDefinition
{
    public const string TYPE = 'mojo_cape_submit';
    protected const ?string DOCS = 'Mojo CAPE Submit';
    protected const ?string NAME = 'Mojo CAPE Submit';

    protected const ?array OVERVIEW = [
        'subs',
        'subs_top12',
        'hash_changed',
        'app_protos',
        'size_sum',
        'size_stats',
        'size_max',
        'size_mean',
        'size_median',
        'size_mode',
        'size_min',
        'size_stddev',
    ];

    protected const array GRAPHS = [
        'subs' => 'Submissions',
        'subs_top12' => 'Submissions, top 12 slugs',
        'hash_changed' => 'Submissions where the hashes changed',
        'app_protos' => 'App protocols seen',
        'size_sum' => 'Size in bytes of submissions',
        'size_stats' => 'Size stats of submissions',
        'size_max' => 'Size max of any submissions',
        'size_mean' => 'Size mean of submissions',
        'size_mode' => 'Size mode of submissions',
        'size_median' => 'Size median of submissions',
        'size_min' => 'Size median of submissions',
        'size_stddev' => 'Size standard deviation of submissions',
    ];

    protected function buildDevicePage(ApplicationPage $page, Application $application): void
    {
        $slugs = array_keys($application->data['slugs'] ?? []);
        $slug = $page->addResourceFilter('Slugs', 'slug', $slugs, 'General');
        $graphs = $slug === null ? self::GRAPHS : array_diff_key(self::GRAPHS, ['subs_top12' => true]);
        $page->addGraphs($graphs, ['slug' => $slug]);
    }
}
