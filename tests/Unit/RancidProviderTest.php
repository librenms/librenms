<?php

/**
 * RancidProviderTest.php
 *
 * This program is free software: you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation, either version 3 of the License, or
 * (at your option) any later version.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with this program.  If not, see <https://www.gnu.org/licenses/>.
 *
 * @link       https://www.librenms.org
 *
 * @copyright  2026 LibreNMS
 */

namespace LibreNMS\Tests\Unit;

use App\ConfigBackup\Providers\RancidProvider;
use App\Facades\LibrenmsConfig;
use App\Models\Device;
use LibreNMS\Interfaces\ConfigBackupProvider;
use LibreNMS\Tests\TestCase;
use Symfony\Component\Process\Process;

final class RancidProviderTest extends TestCase
{
    private string $repo = '';

    protected function setUp(): void
    {
        parent::setUp();
        $this->repo = sys_get_temp_dir() . '/rancid-test-' . uniqid();
        mkdir($this->repo);
        LibrenmsConfig::set('rancid_configs', [$this->repo]);
    }

    protected function tearDown(): void
    {
        if ($this->repo && is_dir($this->repo)) {
            (new Process(['rm', '-rf', $this->repo]))->run();
        }
        parent::tearDown();
    }

    /**
     * @param  list<string>  $args
     */
    private function git(array $args): void
    {
        (new Process(array_merge(['git'], $args), $this->repo))->mustRun();
    }

    private function makeDevice(string $hostname = 'router.example.com', int $id = 42): Device
    {
        $device = new Device(['hostname' => $hostname]);
        $device->device_id = $id;

        return $device;
    }

    /**
     * Build a git repo with two committed revisions of the device config.
     */
    private function initGitRepo(string $hostname = 'router.example.com'): void
    {
        LibrenmsConfig::set('rancid_repo_type', 'git');
        $this->git(['init', '-q']);
        $this->git(['config', 'user.email', 'test@example.com']);
        $this->git(['config', 'user.name', 'Test']);

        file_put_contents($this->repo . '/' . $hostname, "hostname router\nntp server 1.1.1.1\nend\n");
        $this->git(['add', '.']);
        $this->git(['commit', '-q', '-m', 'v1']);

        file_put_contents($this->repo . '/' . $hostname, "hostname router\nntp server 2.2.2.2\nend\n");
        $this->git(['commit', '-qam', 'v2']);
    }

    public function testIsConfigured(): void
    {
        $this->assertTrue(RancidProvider::isConfigured());

        LibrenmsConfig::set('rancid_configs', []);
        $this->assertFalse(RancidProvider::isConfigured());
    }

    public function testSupportsDeviceOnlyWhenConfigFileExists(): void
    {
        $this->initGitRepo();
        $provider = new RancidProvider();

        $this->assertTrue($provider->supportsDevice($this->makeDevice()));
        $this->assertFalse($provider->supportsDevice($this->makeDevice('unknown.example.com', 43)));
    }

    public function testBackupsListsRevisionsNewestFirst(): void
    {
        $this->initGitRepo();
        $list = (new RancidProvider())->backups($this->makeDevice());

        $this->assertSame(2, $list['total']);
        $this->assertSame(1, $list['totalPages']);
        $this->assertMatchesRegularExpression('/^[0-9a-f]{40}$/', $list['backups'][0]['id']);
        $this->assertIsInt($list['backups'][0]['date']);
        $this->assertSame('TEXT', $list['backups'][0]['type']);
        $this->assertGreaterThanOrEqual($list['backups'][1]['date'], $list['backups'][0]['date']);
    }

    public function testLatestReturnsNewestContent(): void
    {
        $this->initGitRepo();
        $latest = (new RancidProvider())->latest($this->makeDevice());

        $this->assertStringContainsString('ntp server 2.2.2.2', $latest['content']);
    }

    public function testContentFetchesSpecificRevision(): void
    {
        $this->initGitRepo();
        $provider = new RancidProvider();

        $backups = $provider->backups($this->makeDevice())['backups'];
        $oldest = end($backups)['id'];

        $this->assertStringContainsString('ntp server 1.1.1.1', $provider->content($this->makeDevice(), $oldest));
    }

    public function testContentRejectsNonHexId(): void
    {
        $this->initGitRepo();
        $provider = new RancidProvider();

        $this->assertNull($provider->content($this->makeDevice(), 'not-a-hash'));
        $this->assertSame(ConfigBackupProvider::ERROR_BACKUP_NOT_FOUND, $provider->lastError());
    }

    public function testDiffProducesGroups(): void
    {
        $this->initGitRepo();
        $provider = new RancidProvider();

        $backups = $provider->backups($this->makeDevice())['backups'];
        $new = $backups[0]['id'];
        $old = end($backups)['id'];

        $groups = $provider->diff($this->makeDevice(), $old, $new);

        $deleted = array_values(array_filter($groups, fn ($g) => $g['type'] === 'DELETED'));
        $inserted = array_values(array_filter($groups, fn ($g) => $g['type'] === 'INSERTED'));

        $this->assertNotEmpty($deleted);
        $this->assertNotEmpty($inserted);
        $this->assertStringContainsString('1.1.1.1', $deleted[0]['original'][0]['text']);
        $this->assertStringContainsString('2.2.2.2', $inserted[0]['revised'][0]['text']);
    }

    public function testDiffRejectsNonHexIds(): void
    {
        $this->initGitRepo();
        $provider = new RancidProvider();

        $this->assertNull($provider->diff($this->makeDevice(), 'zzz', 'abc'));
        $this->assertSame(ConfigBackupProvider::ERROR_BACKUP_NOT_FOUND, $provider->lastError());
    }

    public function testDeviceNotFound(): void
    {
        $this->initGitRepo();
        $provider = new RancidProvider();

        $this->assertNull($provider->backups($this->makeDevice('unknown.example.com', 99)));
        $this->assertSame(ConfigBackupProvider::ERROR_DEVICE_NOT_FOUND, $provider->lastError());
    }

    public function testUnsupportedRepoTypeReturnsError(): void
    {
        $this->initGitRepo();
        LibrenmsConfig::set('rancid_repo_type', 'cvs'); // not git/git-bare/svn

        $provider = new RancidProvider();

        $this->assertNull($provider->backups($this->makeDevice()));
        $this->assertSame(ConfigBackupProvider::ERROR_UNREACHABLE, $provider->lastError());
    }

    public function testIgnoreCommentsStripsHashLines(): void
    {
        LibrenmsConfig::set('rancid_repo_type', 'git');
        LibrenmsConfig::set('rancid_ignorecomments', true);
        $this->git(['init', '-q']);
        $this->git(['config', 'user.email', 'test@example.com']);
        $this->git(['config', 'user.name', 'Test']);
        file_put_contents($this->repo . '/router.example.com', "keep me\n# drop me\nkeep me too\n");
        $this->git(['add', '.']);
        $this->git(['commit', '-q', '-m', 'v1']);

        $content = (new RancidProvider())->latest($this->makeDevice())['content'];

        $this->assertStringNotContainsString('drop me', $content);
        $this->assertStringContainsString('keep me', $content);
    }
}
