<?php

use LibreNMS\Util\GitHub;

Artisan::command('release:tag
                            {tag : The new tag / version}
                            {from : The previous tag / version}
                            {--file= : The filename to update}
                            {--pr= : The last PR to include in this release if not master branch}', function () {
    $tag = $this->argument('tag');
    $this->info("Creating release $tag.....");
    try {
        $gh = new GitHub(
            $tag,
            $this->argument('from'),
            $this->option('file') ?: 'doc/General/Changelog.md',
            getenv('GH_TOKEN') ?: $this->secret('Enter a GitHub Token?'),
            $this->option('pr')
        );
        $gh->createChangelog();
        $this->info("Changelog generated for $tag");

        if ($this->confirm('Do you want to view the generated Changelog?')) {
            echo $gh->getMarkdown();
        }

        if ($this->confirm("Do you want to create the release $tag on GitHub?")) {
            if ($gh->createRelease()) {
                $this->info('Release created.');
            } else {
                $this->error('Failed to create release, check github to see what was completed.');
            }
        }
    } catch (\Exception $e) {
        $this->error($e->getMessage());
    }
})->purpose('Create a new LibreNMS release including changelog');
