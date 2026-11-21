<?php

namespace LibreNMS\Applications;

use App\Models\Application;

class Nextcloud extends ApplicationDefinition
{
    public const string TYPE = 'nextcloud';
    protected const ?string DOCS = 'Nextcloud';

    protected const ?array OVERVIEW = [
        'used',
        'calendars',
        'disabled_apps',
        'enabled_apps',
        'encryption_enabled',
        'user_count',
    ];

    protected const array USER_GRAPHS = [
        'used' => 'Used Storage Space, bytes',
        'user_storage' => 'User Storage Space, bytes',
        'last_seen' => 'Last Seen Ago, seconds',
        'calendars' => 'Calendars',
        'quota' => 'Storage Quota, bytes',
        'relative' => 'Storage Relative, percent',
    ];

    protected const array GRAPHS = [
        'calendars' => 'Calendars',
        'disabled_apps' => 'Disabled Apps',
        'enabled_apps' => 'Enabled Apps',
        'encryption_enabled' => 'Encryption Enabled',
        'user_count' => 'User Count',
    ];

    protected function buildDevicePage(ApplicationPage $page, Application $application): void
    {
        $data = $application->data ?? [];
        $users = $data['users'] ?? [];
        sort($users);
        $user = $page->addResourceFilter('Users', 'nextcloud_user', $users, 'Basics');

        if ($user !== null) {
            $lastSeen = $data['user_last_seen'][$user] ?? null;
            if ($lastSeen !== null) {
                $page->details['Last Seen'] = $lastSeen === '1970-01-01T00:00:00+00:00' ? 'never' : $lastSeen;
            }
            $page->addGraphs(self::USER_GRAPHS, ['nextcloud_user' => $user]);

            return;
        }

        $page->addGraphs(['used' => 'Used Storage Space, bytes']);
        if (empty($data['multimount'])) {
            $page->addGraphs(['storage' => 'Storage Space, bytes', 'storage_with_quota' => 'Storage Space And Quota, bytes']);
        }
        $page->addGraphs(self::GRAPHS);
    }
}
