<?php

namespace LibreNMS\Applications;

use App\Models\Application;

class Systemd extends ApplicationDefinition
{
    public const string TYPE = 'systemd';
    protected const ?string DOCS = 'Systemd';

    protected const ?array OVERVIEW = [
        'all',
        'sub',
        'active',
        'load',
    ];

    protected function buildDevicePage(ApplicationPage $page, Application $application): void
    {
        require base_path('includes/systemd-shared.inc.php');

        $choices = ['' => 'All Unit States'];
        $types = $systemd_state_types ?? [];
        foreach ($types as $type) {
            $choices[$type] = ucfirst($type) . ' State';
        }
        $section = $page->option('section');
        $page->addFilter('Unit States', 'section', $choices, $section);

        foreach ($section === null ? $types : array_intersect($types, [$section]) as $type) {
            if (! in_array($type, $state_type_ternary_depth ?? [], true)) {
                $page->addGraphs([$type => $choices[$type]]);
                continue;
            }
            foreach (array_keys($systemd_mapper ?? []) as $subtype) {
                if (str_starts_with($subtype, $type . '_')) {
                    $page->addGraphs([$type => ucfirst($subtype) . ' State'], ['sub_state_type' => $subtype]);
                }
            }
        }
    }
}
