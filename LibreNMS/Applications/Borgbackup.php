<?php

namespace LibreNMS\Applications;

use App\Models\Application;

class Borgbackup extends ApplicationDefinition
{
    public const string TYPE = 'borgbackup';
    protected const ?string DOCS = 'BorgBackup';

    protected const array GRAPHS = [
        'unique_csize' => 'Deduplicated Size',
        'total_csize' => 'Compressed Size',
        'total_size' => 'Original Size',
        'total_chunks' => 'Total Chunks',
        'total_unique_chunks' => 'Unique Chunks',
        'unique_size' => 'Unique Chunk Size',
        'time_since_last_modified' => 'Seconds since last repo update',
        'errored' => 'Errored Repos',
        'locked' => 'Locked',
        'locked_for' => 'Locked For',
    ];

    protected function buildDevicePage(ApplicationPage $page, Application $application): void
    {
        $data = $application->data ?? [];
        $mode = $data['mode'] ?? 'single';
        $errors = $data['errored'] ?? [];
        $repo = $page->option('borgrepo') ?? $page->option('borgbackup');

        if ($mode === 'multi') {
            $repos = $data['repos'] ?? [];
            sort($repos);
            $page->addFilter('Repositories', 'borgrepo', ['' => 'Totals'] + array_combine($repos, $repos), $repo);
            if ($errors) {
                $page->details['Errored Repositories'] = implode(', ', array_keys($errors));
            }
        } elseif (isset($errors['single'])) {
            $page->details['Error'] = (string) $errors['single'];
        }

        $page->addGraphs(self::GRAPHS, ['borgrepo' => $repo]);
    }
}
