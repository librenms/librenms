<?php

/**
 * Patch.php
 *
 * A GitHub pull request applied to the local install for testing.
 *
 * This program is free software: you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation, either version 3 of the License, or
 * (at your option) any later version.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with this program.  If not, see <https://www.gnu.org/licenses/>.
 *
 * @link       https://www.librenms.org
 *
 * @copyright  2026 Tony Murray
 * @author     Tony Murray <murraytony@gmail.com>
 */

namespace App\Patches;

use Illuminate\Support\Str;

/**
 * @phpstan-type PatchFile array{status: string, path: string, old?: string}
 * @phpstan-type PatchArray array{pr: int, sha: string, title: string, author: string, state: string, applied_at: string, files: list<PatchFile>}
 */
class Patch
{
    public const APPLIED = 'applied';
    public const SUSPENDED = 'suspended';

    /**
     * @param  list<PatchFile>  $files
     */
    public function __construct(
        public readonly int $pr,
        public readonly string $sha,
        public readonly string $diff,
        public readonly array $files,
        public readonly string $title = '',
        public readonly string $author = '',
        public string $state = self::APPLIED,
        public string $appliedAt = '',
    ) {
    }

    /**
     * Pull request number from user input: 123, #123 or a GitHub URL
     */
    public static function parseNumber(string $input): ?int
    {
        if (preg_match('#^\#?(\d+)$#', $input, $matches) || preg_match('#github\.com/librenms/librenms/pull/(\d+)#i', $input, $matches)) {
            return (int) $matches[1];
        }

        return null;
    }

    public function url(): string
    {
        return "https://github.com/librenms/librenms/pull/$this->pr";
    }

    public function shortSha(): string
    {
        return $this->sha ? substr($this->sha, 0, 8) : '?';
    }

    public function label(): string
    {
        return $this->title ? "#$this->pr $this->title" : "#$this->pr";
    }

    /**
     * Migrations added by this patch
     *
     * @return list<string> relative paths
     */
    public function migrations(): array
    {
        $migrations = [];
        foreach ($this->files as $file) {
            if ($file['status'] === 'A' && Str::is('database/migrations/*.php', $file['path'])) {
                $migrations[] = $file['path'];
            }
        }

        sort($migrations);

        return $migrations;
    }

    public function changesDependencies(): bool
    {
        foreach ($this->files as $file) {
            if (in_array($file['path'], ['composer.json', 'composer.lock'])) {
                return true;
            }
        }

        return false;
    }

    /**
     * All paths touched by this patch, including the old name of renamed files
     *
     * @return list<string>
     */
    public function paths(): array
    {
        $paths = [];
        foreach ($this->files as $file) {
            $paths[] = $file['path'];
            if (isset($file['old'])) {
                $paths[] = $file['old'];
            }
        }

        return $paths;
    }

    /**
     * @return PatchArray
     */
    public function toArray(): array
    {
        return [
            'pr' => $this->pr,
            'sha' => $this->sha,
            'title' => $this->title,
            'author' => $this->author,
            'state' => $this->state,
            'applied_at' => $this->appliedAt,
            'files' => $this->files,
        ];
    }

    /**
     * @param  PatchArray  $data
     */
    public static function fromArray(array $data, string $diff): self
    {
        return new self(
            pr: (int) $data['pr'],
            sha: $data['sha'],
            diff: $diff,
            files: $data['files'],
            title: $data['title'],
            author: $data['author'],
            state: $data['state'],
            appliedAt: $data['applied_at'],
        );
    }
}
