<?php

namespace App\Actions\Oxidized;

use App\Facades\LibrenmsConfig;
use Symfony\Component\Process\Process;

class ReadGitHistoryConfig
{
    /**
     * @return array<int, array{
     *     oid: string,
     *     date: string,
     *     timestamp: int,
     *     author: array{name: string},
     *     message: string
     * }>
     */
    public function versions(string $repo, string $file, int $limit = 200): array
    {
        $limit = max(1, min($limit, 200));

        $process = new Process([
            'git',
            '--git-dir=' . $repo,
            'log',
            '--diff-filter=ACMRTUXB',
            '-n',
            (string) $limit,
            '--pretty=format:%H%x1f%ct%x1f%an%x1f%s',
            '--',
            $file,
        ]);
        $process->setTimeout(10);
        $process->run();

        if (! $process->isSuccessful()) {
            return [];
        }

        $versions = [];

        foreach (explode(PHP_EOL, trim($process->getOutput())) as $line) {
            if ($line === '') {
                continue;
            }

            [$oid, $timestamp, $author, $message] = array_pad(explode("\x1f", $line, 4), 4, '');

            if ($oid === '' || ! ctype_digit($timestamp)) {
                continue;
            }

            $versions[] = [
                'oid' => $oid,
                'date' => date(LibrenmsConfig::get('dateformat.long'), (int) $timestamp),
                'timestamp' => (int) $timestamp,
                'author' => ['name' => $author],
                'message' => $message,
            ];
        }

        $existingFiles = $this->existingFilesAt($repo, $file, array_column($versions, 'oid'));

        return array_values(array_filter(
            $versions,
            static fn (array $version): bool => $existingFiles[$version['oid']] ?? false
        ));
    }

    /**
     * @param string[] $oids
     * @return array<string, bool>
     */
    private function existingFilesAt(string $repo, string $file, array $oids): array
    {
        if (empty($oids)) {
            return [];
        }

        $process = new Process([
            'git',
            '--git-dir=' . $repo,
            'cat-file',
            '--batch-check',
        ]);
        $process->setTimeout(10);

        $process->setInput(implode(PHP_EOL, array_map(
            static fn (string $oid): string => $oid . ':' . $file,
            $oids
        )) . PHP_EOL);

        $process->run();

        if (! $process->isSuccessful()) {
            return [];
        }

        $existing = [];
        $lines = explode(PHP_EOL, trim($process->getOutput()));

        foreach ($oids as $index => $oid) {
            $line = $lines[$index] ?? '';
            $existing[$oid] = preg_match('/^[0-9a-f]{40,64} blob [0-9]+$/', $line) === 1;
        }

        return $existing;
    }

    public function config(string $repo, string $file, string $oid = 'HEAD'): string
    {
        $process = new Process([
            'git',
            '--git-dir=' . $repo,
            'show',
            $oid . ':' . $file,
        ]);
        $process->setTimeout(10);
        $process->run();

        return $process->isSuccessful() ? $process->getOutput() : '';
    }

    public function diff(string $repo, string $file, string $currentOid, string $previousOid): string
    {
        $process = new Process([
            'git',
            '--git-dir=' . $repo,
            'diff',
            '--no-ext-diff',
            '--no-textconv',
            '--find-renames',
            $previousOid,
            $currentOid,
            '--',
            $file,
        ]);
        $process->setTimeout(10);
        $process->run();

        return $process->isSuccessful() ? $process->getOutput() : '';
    }
}
