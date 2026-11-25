<?php

namespace LibreNMS\Applications;

use App\Models\Application;

class Ss extends ApplicationDefinition
{
    public const string TYPE = 'ss';
    protected const ?string DOCS = 'Socket Statistics (ss)';
    protected const ?string NAME = 'Socket Statistics';

    protected const ?array OVERVIEW = [
        'sockets',
        'dccp',
        'inet',
        'inet6',
        'link',
        'mptcp',
        'netlink',
        'raw',
        'sctp',
        'tcp',
        'tipc',
        'udp',
        'unix',
        'vsock',
        'xdp',
    ];

    protected function buildDevicePage(ApplicationPage $page, Application $application): void
    {
        require base_path('includes/ss-shared.inc.php');

        $sockets = $application->data['allowed_sockets'] ?? [];
        $families = $application->data['allowed_afs'] ?? [];
        $sections = array_intersect($ss_section_list ?? [], [...$sockets, ...$families]);
        $section = $page->option('section');
        $choices = array_combine($sections, array_map(strtoupper(...), $sections));
        $page->addFilter('Sockets', 'section', ['' => "All Socket Types' Statuses"] + $choices, $section);

        foreach ($section === null ? $sections : array_intersect($sections, [$section]) as $type) {
            $label = strtoupper($type);
            if (! isset($ss_netid_mapper[$type])) {
                $page->addGraphs([$type => "$label Sockets' Statuses"]);
                continue;
            }
            foreach (array_intersect($ss_netid_mapper[$type], $sockets) as $netid) {
                $page->addGraphs([$type => $label . ' ' . strtoupper($netid) . " Sockets' Statuses"], ['netid' => $netid]);
            }
        }
    }
}
