<?php

namespace App\Actions\Oxidized;

use App\Facades\LibrenmsConfig;
use Symfony\Component\Process\Process;

class ReadGitHistoryConfig
{
    /**
     * @return array<int, array{oid: string, date: string, timestamp: int, author: array{name: string}, message: string}>
     */
    public function versions(string $repo, string $file, int $limit = 50): array
    {
        $limit = max(1, min($limit, 200));

        $process = new Process([
            'git',
            '--git-dir=' . $repo,
            'log',
            '-n',
            (string) $limit,
            '--pretty=format:%H%x1f%ct%x1f%an%x1f%s',
            '--',
            $file,
        ]);
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

            if ($oid === '' || ! ctype_digit($timestamp) || ! $this->gitFileExistsAt($repo, $file, $oid)) {
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

        return $versions;
    }

    public function config(string $repo, string $file, string $oid = 'HEAD'): string
    {
        $process = new Process([
            'git',
            '--git-dir=' . $repo,
            'show',
            $oid . ':' . $file,
        ]);
        $process->run();

        return $process->isSuccessful() ? $process->getOutput() : '';
    }

    public function diff(string $repo, string $file, string $currentOid, string $previousOid): string
    {
        $process = new Process([
            'git',
            '--git-dir=' . $repo,
            'diff',
            '--find-renames',
            $previousOid,
            $currentOid,
            '--',
            $file,
        ]);
        $process->run();

        return $process->isSuccessful() ? $process->getOutput() : '';
    }

    private function gitFileExistsAt(string $repo, string $file, string $oid): bool
    {
        $process = new Process([
            'git',
            '--git-dir=' . $repo,
            'cat-file',
            '-e',
            $oid . ':' . $file,
        ]);
        $process->run();

        return $process->isSuccessful();
    }
}
