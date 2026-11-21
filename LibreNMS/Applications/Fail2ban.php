<?php

namespace LibreNMS\Applications;

use App\Models\Application;

class Fail2ban extends ApplicationDefinition
{
    public const string TYPE = 'fail2ban';
    protected const ?string DOCS = 'Fail2ban';

    protected const array GRAPHS = [
        'banned' => 'Total Banned',
    ];

    protected function buildDevicePage(ApplicationPage $page, Application $application): void
    {
        parent::buildDevicePage($page, $application);
        $jails = $application->data['jails'] ?? [];
        sort($jails);
        foreach ($jails as $jail) {
            $page->addGraphs(['jail' => "Jail: $jail"], ['jail' => $jail]);
        }
    }
}
