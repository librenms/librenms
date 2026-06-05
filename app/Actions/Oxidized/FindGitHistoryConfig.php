<?php

namespace App\Actions\Oxidized;

use App\Facades\LibrenmsConfig;
use App\Models\Device;
use Symfony\Component\Process\Process;

class FindGitHistoryConfig
{
    public function __construct(private readonly BuildDeviceOutput $buildDeviceOutput)
    {
    }

    /**
     * @return array{repo: string, file: string, source: string}|null
     */
    public function execute(Device $device): ?array
    {
        if (LibrenmsConfig::get('oxidized.history.enabled') !== true) {
            return null;
        }

        $candidates = $this->candidateFilenames($device);
        if (empty($candidates)) {
            return null;
        }

        foreach ($this->candidateRepos($device) as $repo => $source) {
            foreach ($candidates as $candidate) {
                if ($this->gitFileExists($repo, $candidate)) {
                    return [
                        'repo' => $repo,
                        'file' => $candidate,
                        'source' => $source,
                    ];
                }
            }
        }

        return null;
    }

    /**
     * @return string[]
     */
    public function candidateFilenames(Device $device): array
    {
        $candidates = [];

        $this->addCandidate($candidates, $device->hostname);
        $this->addShortHostnameCandidate($candidates, $device->hostname);

        $this->addCandidate($candidates, $device->sysName);
        $this->addShortHostnameCandidate($candidates, $device->sysName);

        $this->addCandidate($candidates, $device->ip);

        return array_values(array_unique(array_filter($candidates)));
    }

    /**
     * @return iterable<string, string>
     */
    public function candidateRepos(Device $device): iterable
    {
        $seen = [];
        $basePath = $this->configuredBasePath();

        if ($basePath !== null) {
            $output = $this->buildDeviceOutput->execute($device);
            $group = $output['group'] ?? null;

            if (is_string($group) && $this->isSafePathName($group)) {
                yield from $this->yieldRepo($seen, $basePath . DIRECTORY_SEPARATOR . $group . '.git', 'group');
            }
        }

        foreach (LibrenmsConfig::get('oxidized.history.git_repo_paths', []) as $repo) {
            if (is_string($repo)) {
                yield from $this->yieldRepo($seen, $repo, 'configured');
            }
        }

        if ($basePath !== null) {
            foreach (glob($basePath . DIRECTORY_SEPARATOR . '*.git') ?: [] as $repo) {
                yield from $this->yieldRepo($seen, $repo, 'base_path');
            }
        }
    }

    private function configuredBasePath(): ?string
    {
        $basePath = LibrenmsConfig::get('oxidized.history.git_repo_base_path');

        if (! is_string($basePath) || $basePath === '' || ! is_dir($basePath)) {
            return null;
        }

        return rtrim($basePath, DIRECTORY_SEPARATOR);
    }

    private function addCandidate(array &$candidates, mixed $value): void
    {
        if (is_string($value) && $value !== '') {
            $candidates[] = $value;
        }
    }

    private function addShortHostnameCandidate(array &$candidates, mixed $value): void
    {
        if (! is_string($value) || $value === '' || filter_var($value, FILTER_VALIDATE_IP)) {
            return;
        }

        if (str_contains($value, '.')) {
            $candidates[] = strtok($value, '.');
        }
    }

    private function isSafePathName(string $name): bool
    {
        return $name !== '' && basename($name) === $name && ! str_contains($name, DIRECTORY_SEPARATOR);
    }

    /**
     * @param array<string, bool> $seen
     * @return iterable<string, string>
     */
    private function yieldRepo(array &$seen, string $repo, string $source): iterable
    {
        $repo = rtrim($repo, DIRECTORY_SEPARATOR);

        if (isset($seen[$repo])) {
            return;
        }

        $seen[$repo] = true;

        if ($this->isBareGitRepo($repo)) {
            yield $repo => $source;
        }
    }

    private function isBareGitRepo(string $repo): bool
    {
        if (! is_dir($repo)) {
            return false;
        }

        $process = new Process(['git', '--git-dir=' . $repo, 'rev-parse', '--is-bare-repository']);
        $process->run();

        return $process->isSuccessful() && trim($process->getOutput()) === 'true';
    }

    private function gitFileExists(string $repo, string $file): bool
    {
        $process = new Process(['git', '--git-dir=' . $repo, 'cat-file', '-e', 'HEAD:' . $file]);
        $process->run();

        return $process->isSuccessful();
    }
}
