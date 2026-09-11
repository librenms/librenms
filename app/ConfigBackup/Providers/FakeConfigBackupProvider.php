<?php

/**
 * FakeConfigBackupProvider.php
 *
 * Dummy ConfigBackupProvider for local UI testing and development.
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
 * @copyright  2026 LibreNMS
 */

namespace App\ConfigBackup\Providers;

use App\Models\Device;
use LibreNMS\Interfaces\ConfigBackupProvider;
use LibreNMS\Interfaces\RefreshableConfigBackupProvider;

class FakeConfigBackupProvider implements ConfigBackupProvider, RefreshableConfigBackupProvider
{
    private ?string $lastError = null;

    public static function isConfigured(): bool
    {
        return true;
    }

    public function supportsDevice(Device $device): bool
    {
        return true;
    }

    public function name(): string
    {
        return 'Fake Backup Provider';
    }

    /**
     * @return array{backups: list<array{id: string, date: ?int, until: ?int, type: string, content: ?string}>, total: int, totalPages: int, page: int}|null
     */
    public function backups(Device $device, int $page = 0): ?array
    {
        $this->lastError = null;

        $all = $this->sampleBackupMetadata();
        $perPage = 10;
        $total = count($all);
        $totalPages = (int) max(1, ceil($total / $perPage));
        $page = max(0, min($page, $totalPages - 1));
        $slice = array_slice($all, $page * $perPage, $perPage);

        return [
            'backups' => $slice,
            'total' => $total,
            'totalPages' => $totalPages,
            'page' => $page,
        ];
    }

    /**
     * @return array{id: string, date: ?int, until: ?int, type: string, content: ?string}|null
     */
    public function latest(Device $device): ?array
    {
        $this->lastError = null;
        $all = $this->sampleBackupMetadata();

        $latest = $all[0] ?? null;
        if ($latest !== null) {
            $latest['content'] = $this->content($device, $latest['id']);
        }

        return $latest;
    }

    public function content(Device $device, string $backupId, int $pageHint = 0): ?string
    {
        $this->lastError = null;

        $validIds = array_column($this->sampleBackupMetadata(), 'id');
        if (! in_array($backupId, $validIds, true)) {
            $this->lastError = self::ERROR_BACKUP_NOT_FOUND;

            return null;
        }

        return match ($backupId) {
            'fake-001' => $this->generateFakeConfig($device, $backupId, withChange: true, interfaceCount: 8),
            'fake-002' => $this->generateHugeConfig($device, $backupId, lineCount: 15000),
            'fake-003' => $this->generateFakeConfig($device, $backupId, olderStyle: true, interfaceCount: 8),
            'fake-004' => $this->generateFakeConfig($device, $backupId, olderStyle: true, interfaceCount: 4),
            'fake-005' => $this->generateFakeConfig($device, $backupId, olderStyle: true, interfaceCount: 2),
            default => $this->generateFakeConfig($device, $backupId),
        };
    }

    /**
     * Line-oriented diff outputting the COMMON/INSERTED/DELETED/CHANGED groups the UI expects.
     *
     * @return list<array{type: string, original: list<array{line: ?int, text: string}>, revised: list<array{line: ?int, text: string}>}>|null
     */
    public function diff(Device $device, string $origId, string $revId): ?array
    {
        $this->lastError = null;

        $origText = $this->content($device, $origId);
        $revText = $this->content($device, $revId);

        if ($origText === null || $revText === null) {
            $this->lastError = self::ERROR_BACKUP_NOT_FOUND;

            return null;
        }

        $origLines = explode("\n", rtrim($origText, "\n"));
        $revLines = explode("\n", rtrim($revText, "\n"));

        // If either file is very large, compute a slice or simple diff to avoid O(N*M) table blowup
        if (count($origLines) > 1000 || count($revLines) > 1000) {
            return [
                [
                    'type' => 'COMMON',
                    'original' => [['line' => 1, 'text' => '! Diff between ' . $origId . ' (' . count($origLines) . ' lines) and ' . $revId . ' (' . count($revLines) . ' lines)']],
                    'revised' => [['line' => 1, 'text' => '! Diff between ' . $origId . ' (' . count($origLines) . ' lines) and ' . $revId . ' (' . count($revLines) . ' lines)']],
                ],
                [
                    'type' => 'DELETED',
                    'original' => [['line' => 2, 'text' => '! [Large config file diff truncated for testing]']],
                    'revised' => [],
                ],
                [
                    'type' => 'INSERTED',
                    'original' => [],
                    'revised' => [['line' => 2, 'text' => '! [Revised large config file]']],
                ],
            ];
        }

        // LCS-based group generation
        $lcs = $this->computeLcs($origLines, $revLines);

        $groups = [];
        $oi = 0;
        $ri = 0;
        $li = 0;

        while ($oi < count($origLines) || $ri < count($revLines)) {
            // Common run
            if ($li < count($lcs) && $oi === $lcs[$li][0] && $ri === $lcs[$li][1]) {
                $groups[] = [
                    'type' => 'COMMON',
                    'original' => [['line' => $oi + 1, 'text' => $origLines[$oi]]],
                    'revised' => [['line' => $ri + 1, 'text' => $revLines[$ri]]],
                ];
                $oi++;
                $ri++;
                $li++;
                continue;
            }

            // Deleted lines
            $deleted = [];
            while ($oi < count($origLines) && ($li >= count($lcs) || $oi < $lcs[$li][0])) {
                $deleted[] = ['line' => $oi + 1, 'text' => $origLines[$oi]];
                $oi++;
            }

            // Inserted lines
            $inserted = [];
            while ($ri < count($revLines) && ($li >= count($lcs) || $ri < $lcs[$li][1])) {
                $inserted[] = ['line' => $ri + 1, 'text' => $revLines[$ri]];
                $ri++;
            }

            if ($deleted && $inserted) {
                $groups[] = [
                    'type' => 'CHANGED',
                    'original' => $deleted,
                    'revised' => $inserted,
                ];
            } elseif ($deleted) {
                $groups[] = [
                    'type' => 'DELETED',
                    'original' => $deleted,
                    'revised' => [],
                ];
            } elseif ($inserted) {
                $groups[] = [
                    'type' => 'INSERTED',
                    'original' => [],
                    'revised' => $inserted,
                ];
            }
        }

        if (empty($groups)) {
            $groups[] = [
                'type' => 'COMMON',
                'original' => [['line' => 1, 'text' => '! (no differences)']],
                'revised' => [['line' => 1, 'text' => '! (no differences)']],
            ];
        }

        return $groups;
    }

    public function lastError(): ?string
    {
        return $this->lastError;
    }

    public function refresh(Device $device, string $requestedBy): bool
    {
        return true;
    }

    // -------------------------------------------------------------------------

    /**
     * Lightweight backup metadata list.
     *
     * @return list<array{id: string, date: ?int, until: ?int, type: string, content: ?string}>
     */
    private function sampleBackupMetadata(): array
    {
        $now = time();

        return [
            [
                'id' => 'fake-001',
                'date' => $now - 3600,
                'until' => null,
                'type' => 'TEXT',
                'content' => null,
            ],
            [
                'id' => 'fake-002',
                'date' => $now - 86400,
                'until' => $now - 3600,
                'type' => 'TEXT',
                'content' => null,
            ],
            [
                'id' => 'fake-003',
                'date' => $now - 3 * 86400,
                'until' => $now - 86400,
                'type' => 'TEXT',
                'content' => null,
            ],
            [
                'id' => 'fake-004',
                'date' => $now - 7 * 86400,
                'until' => $now - 3 * 86400,
                'type' => 'TEXT',
                'content' => null,
            ],
            [
                'id' => 'fake-005',
                'date' => $now - 14 * 86400,
                'until' => $now - 7 * 86400,
                'type' => 'TEXT',
                'content' => null,
            ],
        ];
    }

    /**
     * Fast generator for a massive configuration (~15,000+ lines / ~400KB) for testing.
     */
    private function generateHugeConfig(Device $device, string $id, int $lineCount = 15000): string
    {
        $hostname = $device->hostname ?? 'testdevice';
        $sysName = $device->sysName ?? $hostname;

        $header = <<<CFG
!
! Huge fake configuration backup for scale and performance testing (~{$lineCount} lines)
! Device : {$hostname}
! SysName: {$sysName}
! Backup : {$id}
! Generated by FakeConfigBackupProvider
!
version 15.2
service timestamps debug datetime msec
service timestamps log datetime msec
!
hostname {$hostname}
!
banner motd ^C
Welcome to {$hostname} (Large Config Stress Test)
^C
!
CFG;

        $lines = [$header];
        $totalInterfaces = (int) ceil($lineCount / 7);

        for ($i = 0; $i < $totalInterfaces; $i++) {
            $slot = intdiv($i, 48);
            $port = $i % 48;
            $vlan = ($i % 10) + 1;
            $lines[] = "interface GigabitEthernet{$slot}/0/{$port}\n description Port {$slot}/0/{$port} - Server Node {$i}\n switchport mode access\n switchport access vlan {$vlan}\n spanning-tree portfast\n no shutdown\n!";
        }

        $lines[] = "ip route 0.0.0.0 0.0.0.0 10.0.0.254\n!\nend\n";

        return implode("\n", $lines);
    }

    private function generateFakeConfig(Device $device, string $id, bool $withChange = false, bool $olderStyle = false, int $interfaceCount = 4): string
    {
        $hostname = $device->hostname ?? 'testdevice';
        $sysName = $device->sysName ?? $hostname;

        $extraIface = $withChange
            ? "interface GigabitEthernet0/0/3\n description ADDED-BY-FAKE-PROVIDER\n no shutdown\n!\n"
            : '';

        $vlanBlock = $olderStyle
            ? "vlan 10\n name USERS\n!\nvlan 99\n name LEGACY\n!\n"
            : "vlan 10\n name USERS\n!\nvlan 20\n name SERVERS\n!\n";

        $banner = $olderStyle
            ? "banner motd ^C\nOld banner - please update\n^C\n"
            : "banner motd ^C\nWelcome to {$hostname}\n^C\n";

        $interfaces = '';
        for ($i = 0; $i < $interfaceCount; $i++) {
            $vlan = ($i % 2 === 0) ? 10 : 20;
            $interfaces .= <<<IFACE
interface GigabitEthernet1/0/{$i}
 description Port {$i}
 switchport mode access
 switchport access vlan {$vlan}
 no shutdown
!

IFACE;
        }

        return <<<CFG
!
! Fake configuration backup for UI testing
! Device : {$hostname}
! SysName: {$sysName}
! Backup : {$id}
! Generated by FakeConfigBackupProvider
!
version 15.2
service timestamps debug datetime msec
service timestamps log datetime msec
!
hostname {$hostname}
!
{$banner}!
interface GigabitEthernet0/0/0
 description Uplink
 ip address 10.0.0.1 255.255.255.0
 no shutdown
!
interface GigabitEthernet0/0/1
 description Access
 switchport mode access
 switchport access vlan 10
 no shutdown
!
interface GigabitEthernet0/0/2
 description Unused
 shutdown
!
{$extraIface}{$interfaces}{$vlanBlock}!
ip route 0.0.0.0 0.0.0.0 10.0.0.254
!
end

CFG;
    }

    /**
     * Classic LCS (longest common subsequence) indices.
     * Returns list of [origIndex, revIndex] pairs that match.
     *
     * @param  list<string>  $a
     * @param  list<string>  $b
     * @return list<array{0: int, 1: int}>
     */
    private function computeLcs(array $a, array $b): array
    {
        $m = count($a);
        $n = count($b);

        // DP table of lengths
        $dp = array_fill(0, $m + 1, array_fill(0, $n + 1, 0));
        for ($i = 1; $i <= $m; $i++) {
            for ($j = 1; $j <= $n; $j++) {
                if ($a[$i - 1] === $b[$j - 1]) {
                    $dp[$i][$j] = $dp[$i - 1][$j - 1] + 1;
                } else {
                    $dp[$i][$j] = max($dp[$i - 1][$j], $dp[$i][$j - 1]);
                }
            }
        }

        // Backtrack to recover the matching pairs
        $pairs = [];
        $i = $m;
        $j = $n;
        while ($i > 0 && $j > 0) {
            if ($a[$i - 1] === $b[$j - 1]) {
                $pairs[] = [$i - 1, $j - 1];
                $i--;
                $j--;
            } elseif ($dp[$i - 1][$j] >= $dp[$i][$j - 1]) {
                $i--;
            } else {
                $j--;
            }
        }

        return array_reverse($pairs);
    }
}
