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
use App\Models\Eventlog;
use LibreNMS\Enum\Severity;
use LibreNMS\Interfaces\ConfigBackupProvider;
use LibreNMS\Tests\TestCase;
use Mockery;
use PHPUnit\Framework\Attributes\Group;
use Symfony\Component\Process\Process;

final class RancidProviderTest extends TestCase
{
    private string $repo = '';

    /** RANCID keeps configs in a subdirectory of the group's repository. */
    private string $configs = '';

    protected function setUp(): void
    {
        parent::setUp();
        $this->repo = sys_get_temp_dir() . '/rancid-test-' . uniqid();
        $this->configs = $this->repo . '/configs';
        mkdir($this->configs, 0777, true);
        LibrenmsConfig::set('rancid_configs', [$this->configs]);
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

    /**
     * Commit a new revision of the device config to the test git repo.
     */
    private function commitConfig(string $content, string $hostname = 'router.example.com'): void
    {
        file_put_contents($this->configs . '/' . $hostname, $content);
        $this->git(['add', '.']);
        $this->git(['commit', '-q', '-m', 'update']);
    }

    /**
     * Bare clone of the test git repo, as RANCID pushes to.
     */
    private function bareClone(string $path): void
    {
        mkdir(dirname($path), 0777, true);
        (new Process(['git', 'clone', '-q', '--bare', $this->repo, $path]))->mustRun();
        LibrenmsConfig::set('rancid_repo_type', 'git-bare');
    }

    /**
     * Build an svn repo and checkout laid out like RANCID ($repo/SVN, $repo/core/configs)
     * with the given revisions of the device config committed in order.
     *
     * @param  list<string>  $revisions
     */
    private function initSvnRepo(array $revisions): void
    {
        $svn = LibrenmsConfig::locateBinary('svn');
        if (! is_executable($svn) || ! is_executable(dirname($svn) . '/svnadmin')) {
            $this->markTestSkipped('svn is not installed');
        }

        LibrenmsConfig::set('rancid_repo_type', 'svn');
        $url = 'file://' . $this->repo . '/SVN';
        (new Process([dirname($svn) . '/svnadmin', 'create', $this->repo . '/SVN']))->mustRun();
        (new Process([$svn, 'mkdir', '-q', '-m', 'init', "$url/core", "$url/core/configs"]))->mustRun();
        (new Process([$svn, 'checkout', '-q', "$url/core", $this->repo . '/core']))->mustRun();

        $this->configs = $this->repo . '/core/configs';
        LibrenmsConfig::set('rancid_configs', [$this->configs]);
        foreach ($revisions as $index => $content) {
            file_put_contents($this->configs . '/router.example.com', $content);
            if ($index === 0) {
                (new Process([$svn, 'add', '-q', 'router.example.com'], $this->configs))->mustRun();
            }
            (new Process([$svn, 'commit', '-q', '-m', "v$index"], $this->configs))->mustRun();
        }
        (new Process([$svn, 'update', '-q'], $this->configs))->mustRun();
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

        file_put_contents($this->configs . '/' . $hostname, "hostname router\nntp server 1.1.1.1\nend\n");
        $this->git(['add', '.']);
        $this->git(['commit', '-q', '-m', 'v1']);

        file_put_contents($this->configs . '/' . $hostname, "hostname router\nntp server 2.2.2.2\nend\n");
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

    public function testUnsupportedRepoTypeFallsBackToCurrentFile(): void
    {
        LibrenmsConfig::set('rancid_repo_type', 'cvs'); // not git/git-bare/svn
        file_put_contents($this->configs . '/router.example.com', "hostname router\n");
        $provider = new RancidProvider();
        $device = $this->makeDevice();

        $list = $provider->backups($device);
        $this->assertSame(1, $list['total']);
        $this->assertSame('current', $list['backups'][0]['id']);
        $this->assertIsInt($list['backups'][0]['date']);

        $latest = $provider->latest($device);
        $this->assertSame('current', $latest['id']);
        $this->assertSame("hostname router\n", $latest['content']);
        $this->assertSame("hostname router\n", $provider->content($device, 'current'));

        $this->assertNull($provider->diff($device, 'current', 'current'));
        $this->assertSame(ConfigBackupProvider::ERROR_BACKUP_NOT_FOUND, $provider->lastError());
    }

    public function testUnreadableRepoFallsBackToCurrentFile(): void
    {
        LibrenmsConfig::set('rancid_repo_type', 'git'); // but the directory is not a git repo
        file_put_contents($this->configs . '/router.example.com', "hostname router\n");
        $provider = new RancidProvider();

        $this->assertSame('current', $provider->backups($this->makeDevice())['backups'][0]['id']);
        $this->assertSame("hostname router\n", $provider->latest($this->makeDevice())['content']);

        $this->assertNull($provider->diff($this->makeDevice(), 'abc123', 'def456'));
        $this->assertSame(ConfigBackupProvider::ERROR_UNREACHABLE, $provider->lastError());
    }

    public function testUncommittedFileFallsBackToCurrentFile(): void
    {
        $this->initGitRepo();
        file_put_contents($this->configs . '/new.example.com', "hostname new\n");
        $provider = new RancidProvider();

        $list = $provider->backups($this->makeDevice('new.example.com', 50));
        $this->assertSame(1, $list['total']);
        $this->assertSame('current', $list['backups'][0]['id']);
    }

    public function testCurrentIdOnlyReadsDeviceFile(): void
    {
        $this->initGitRepo();
        $provider = new RancidProvider();

        // with history available, "current" still maps to this device's own file
        $this->assertStringContainsString('ntp server 2.2.2.2', $provider->content($this->makeDevice(), 'current'));
    }

    public function testDiffWithUnknownRevisionIsBackupNotFound(): void
    {
        $this->initGitRepo();
        $provider = new RancidProvider();
        $newest = $provider->backups($this->makeDevice())['backups'][0]['id'];

        $this->assertNull($provider->diff($this->makeDevice(), 'deadbeef', $newest));
        $this->assertSame(ConfigBackupProvider::ERROR_BACKUP_NOT_FOUND, $provider->lastError());
    }

    public function testGitBareHasNoCurrentFileFallback(): void
    {
        $this->initGitRepo();
        $bare = $this->repo . '/rancid.git';
        (new Process(['git', 'clone', '-q', '--bare', $this->repo, $bare]))->mustRun();
        LibrenmsConfig::set('rancid_repo_type', 'git-bare');
        LibrenmsConfig::set('rancid_configs', [$bare . '/configs']);
        $provider = new RancidProvider();

        $this->assertStringContainsString('ntp server 2.2.2.2', $provider->latest($this->makeDevice())['content']);
        $this->assertNull($provider->content($this->makeDevice(), 'current'));
        $this->assertSame(ConfigBackupProvider::ERROR_BACKUP_NOT_FOUND, $provider->lastError());
    }

    public function testIgnoreCommentsStripsHashLines(): void
    {
        LibrenmsConfig::set('rancid_repo_type', 'git');
        LibrenmsConfig::set('rancid_ignorecomments', true);
        $this->git(['init', '-q']);
        $this->git(['config', 'user.email', 'test@example.com']);
        $this->git(['config', 'user.name', 'Test']);
        file_put_contents($this->configs . '/router.example.com', "keep me\n# drop me\nkeep me too\n");
        $this->git(['add', '.']);
        $this->git(['commit', '-q', '-m', 'v1']);

        $content = (new RancidProvider())->latest($this->makeDevice())['content'];

        $this->assertStringNotContainsString('drop me', $content);
        $this->assertStringContainsString('keep me', $content);
    }

    /**
     * Replace the eventlog with a mock expecting $times warnings matching $text.
     */
    private function expectEventlogWarnings(int $times, string $text = ''): void
    {
        $eventlog = Mockery::mock(Eventlog::class);
        $eventlog->shouldReceive('_log')
            ->times($times)
            ->withArgs(fn ($message, $device, $type, $severity) => str_contains($message, $text) && $type === 'config' && $severity === Severity::Warning);
        $this->app->instance(Eventlog::class, $eventlog);
    }

    public function testCvsRepoTypeShowsCurrentFileWithoutWarning(): void
    {
        $this->expectEventlogWarnings(0);
        LibrenmsConfig::set('rancid_repo_type', 'cvs');
        file_put_contents($this->configs . '/router.example.com', "hostname router\n");

        $this->assertSame('current', (new RancidProvider())->backups($this->makeDevice())['backups'][0]['id']);
    }

    public function testUnreadableRepoLogsEventOncePerInterval(): void
    {
        $this->expectEventlogWarnings(1, 'RANCID: could not read git history of router.example.com');
        LibrenmsConfig::set('rancid_repo_type', 'git'); // but the directory is not a git repo
        file_put_contents($this->configs . '/router.example.com', "hostname router\n");

        (new RancidProvider())->backups($this->makeDevice());
        (new RancidProvider())->backups($this->makeDevice()); // repeated page views do not flood the eventlog
    }

    public function testGitBareRepoWithoutDotGitSuffix(): void
    {
        // rancid-cvs with RCSSYS=git creates bare repos at $BASEDIR/CVS/<group>
        $this->initGitRepo();
        $this->bareClone($this->repo . '/CVS/core');
        LibrenmsConfig::set('rancid_configs', [$this->repo . '/CVS/core/configs/']);

        $this->assertSame(2, (new RancidProvider())->backups($this->makeDevice())['total']);
    }

    public function testGitBarePathWithDotGitInParentDirectory(): void
    {
        $this->initGitRepo();
        $this->bareClone($this->repo . '/.github-mirror/rancid.git');
        LibrenmsConfig::set('rancid_configs', [$this->repo . '/.github-mirror/rancid.git/configs']);

        $this->assertSame(2, (new RancidProvider())->backups($this->makeDevice())['total']);
    }

    public function testGitBareSiblingPath(): void
    {
        // configs at the root of the bare repo, configured without the .git suffix
        LibrenmsConfig::set('rancid_repo_type', 'git');
        $this->git(['init', '-q']);
        $this->git(['config', 'user.email', 'test@example.com']);
        $this->git(['config', 'user.name', 'Test']);
        file_put_contents($this->repo . '/router.example.com', "hostname router\n");
        $this->git(['add', '.']);
        $this->git(['commit', '-q', '-m', 'v1']);
        $this->bareClone($this->repo . '/bare/rancid.git');
        LibrenmsConfig::set('rancid_configs', [$this->repo . '/bare/rancid']);

        $this->assertSame("hostname router\n", (new RancidProvider())->latest($this->makeDevice())['content']);
    }

    public function testGitBareOnlyMatchesConfigsPrefix(): void
    {
        $this->initGitRepo();
        file_put_contents($this->repo . '/other.example.com', "outside configs\n");
        $this->git(['add', '.']);
        $this->git(['commit', '-q', '-m', 'outside']);
        $this->bareClone($this->repo . '/bare/rancid.git');
        LibrenmsConfig::set('rancid_configs', [$this->repo . '/bare/rancid.git/configs']);

        $this->assertFalse((new RancidProvider())->supportsDevice($this->makeDevice('other.example.com', 51)));
    }

    public function testGitHistoryIsPaged(): void
    {
        LibrenmsConfig::set('rancid_repo_type', 'git');
        $this->git(['init', '-q']);

        // fast-import builds 105 commits in one process
        $stream = '';
        for ($i = 1; $i <= 105; $i++) {
            $data = "hostname router\nrevision $i\n";
            $stream .= "commit refs/heads/master\ncommitter Test <test@example.com> " . (1700000000 + $i) . " +0000\ndata 1\n\n"
                . 'M 644 inline configs/router.example.com' . "\ndata " . strlen($data) . "\n$data\n";
        }
        (new Process(['git', 'fast-import', '--quiet'], $this->repo, null, $stream))->mustRun();
        $this->git(['symbolic-ref', 'HEAD', 'refs/heads/master']);
        $this->git(['reset', '-q', '--hard']);

        $provider = new RancidProvider();
        $first = $provider->backups($this->makeDevice());
        $second = $provider->backups($this->makeDevice(), 1);

        $this->assertSame(105, $first['total']);
        $this->assertSame(2, $first['totalPages']);
        $this->assertCount(100, $first['backups']);
        $this->assertSame(1700000105, $first['backups'][0]['date']);
        $this->assertCount(5, $second['backups']);
        $this->assertSame(1, $second['page']);
        $this->assertSame(1700000001, $second['backups'][4]['date']);
        $this->assertSame(1, $provider->backups($this->makeDevice(), 9)['page']);
    }

    public function testIgnoreCommentsAppliesToDiff(): void
    {
        LibrenmsConfig::set('rancid_repo_type', 'git');
        LibrenmsConfig::set('rancid_ignorecomments', true);
        $this->git(['init', '-q']);
        $this->git(['config', 'user.email', 'test@example.com']);
        $this->git(['config', 'user.name', 'Test']);
        $this->commitConfig("# uptime 1\nhostname router\n# c\na\nb\nc\nd\ne\nf\ng\nntp server 1.1.1.1\nend\n");
        $this->commitConfig("# uptime 2\nhostname router\n# c\na\nb\nc\nd\ne\nf\ng\nntp server 2.2.2.2\nend\n");
        $this->commitConfig("# uptime 3\nhostname router\n# c\na\nb\nc\nd\ne\nf\ng\nntp server 2.2.2.2\nend\n");

        $provider = new RancidProvider();
        $ids = array_column($provider->backups($this->makeDevice())['backups'], 'id');

        // comment-only change shows as no changes
        $this->assertSame([], $provider->diff($this->makeDevice(), $ids[1], $ids[0]));

        $groups = $provider->diff($this->makeDevice(), $ids[2], $ids[1]);
        $texts = array_map(fn ($g) => $g['type'] . ' ' . ($g['original'][0]['line'] ?? $g['revised'][0]['line']) . ' ' . ($g['original'][0]['text'] ?? $g['revised'][0]['text']), $groups);
        $this->assertSame([
            'COMMON 6 e',
            'COMMON 7 f',
            'COMMON 8 g',
            'DELETED 9 ntp server 1.1.1.1',
            'INSERTED 9 ntp server 2.2.2.2',
            'COMMON 10 end',
        ], $texts);

        // line numbers match the filtered content
        $lines = explode("\n", $provider->content($this->makeDevice(), $ids[1]));
        $this->assertSame('ntp server 2.2.2.2', $lines[8]);
    }

    #[Group('external-dependencies')] // needs svn
    public function testSvnRepo(): void
    {
        $this->initSvnRepo(["hostname router\nntp server 1.1.1.1\nend\n", "hostname router\nntp server 2.2.2.2\nend\n"]);
        $provider = new RancidProvider();
        $device = $this->makeDevice();

        $list = $provider->backups($device);
        $this->assertSame(2, $list['total']);
        $this->assertSame(['3', '2'], array_column($list['backups'], 'id'));
        $this->assertIsInt($list['backups'][0]['date']);

        $latest = $provider->latest($device);
        $this->assertSame('3', $latest['id']);
        $this->assertStringContainsString('2.2.2.2', $latest['content']);
        $this->assertStringContainsString('1.1.1.1', $provider->content($device, '2'));

        $types = array_column($provider->diff($device, '2', '3'), 'type');
        $this->assertSame(['COMMON', 'DELETED', 'INSERTED', 'COMMON'], $types);
    }

    #[Group('external-dependencies')] // needs svn
    public function testSvnBadRevisions(): void
    {
        $this->initSvnRepo(["hostname router\n", "hostname router2\n"]);
        $provider = new RancidProvider();
        $device = $this->makeDevice();

        $this->assertNull($provider->content($device, 'abc'));
        $this->assertSame(ConfigBackupProvider::ERROR_BACKUP_NOT_FOUND, $provider->lastError());
        $this->assertNull($provider->content($device, '999'));
        $this->assertSame(ConfigBackupProvider::ERROR_BACKUP_NOT_FOUND, $provider->lastError());
        $this->assertNull($provider->diff($device, '2', '999'));
        $this->assertSame(ConfigBackupProvider::ERROR_BACKUP_NOT_FOUND, $provider->lastError());
    }

    #[Group('external-dependencies')] // needs svn
    public function testSvnIgnoreCommentsDiff(): void
    {
        LibrenmsConfig::set('rancid_ignorecomments', true);
        $this->initSvnRepo(["# v1\nhostname router\nntp server 1.1.1.1\n", "# v2\nhostname router\nntp server 2.2.2.2\n"]);
        $provider = new RancidProvider();

        $groups = $provider->diff($this->makeDevice(), '2', '3');
        $this->assertSame(['COMMON', 'DELETED', 'INSERTED'], array_column($groups, 'type'));
        $this->assertSame(2, $groups[1]['original'][0]['line']);
    }
}
