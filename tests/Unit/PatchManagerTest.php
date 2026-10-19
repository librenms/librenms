<?php

namespace LibreNMS\Tests\Unit;

use App\Patches\Patch;
use App\Patches\PatchException;
use App\Patches\PatchManager;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Http;
use LibreNMS\Tests\TestCase;
use Symfony\Component\Process\Process;

final class PatchManagerTest extends TestCase
{
    private string $tmp;
    private string $upstream;
    private string $local;
    private PatchManager $manager;
    /** @var array<int, array<string, mixed>> */
    private array $apiResponses = [];

    protected function setUp(): void
    {
        parent::setUp();

        $this->tmp = sys_get_temp_dir() . '/lnms-patch-test-' . uniqid();
        $this->upstream = "$this->tmp/upstream";
        $this->local = "$this->tmp/local";
        mkdir($this->upstream, 0777, true);

        $this->git($this->upstream, 'init', '--quiet', '--initial-branch=master');
        $this->write($this->upstream, 'file.txt', "one\ntwo\nthree\nfour\nfive\nsix\nseven\neight\nnine\nten\n");
        $this->write($this->upstream, 'other.txt', "other\n");
        $this->commit($this->upstream, 'initial');

        $this->git($this->tmp, 'clone', '--quiet', $this->upstream, $this->local);

        // serve the upstream test repo like GitHub does
        Http::fake(function ($request) {
            $url = $request->url();

            if (preg_match('#/pulls/(\d+)$#', $url, $matches)) {
                return isset($this->apiResponses[(int) $matches[1]])
                    ? Http::response($this->apiResponses[(int) $matches[1]])
                    : Http::response(['message' => 'Not Found'], 404);
            }

            if (preg_match('#^https://github.com/librenms/librenms/pull/(\d+)\.diff$#', $url, $matches)) {
                $base = $this->apiResponses[(int) $matches[1]]['base']['ref'] ?? 'master';
                $process = new Process(['git', 'diff', '--find-renames', "$base...refs/pull/$matches[1]/head"], $this->upstream);
                $process->run();

                return $process->isSuccessful() ? Http::response($process->getOutput()) : Http::response('Not Found', 404);
            }

            if (preg_match('#^https://raw.githubusercontent.com/librenms/librenms/(refs/pull/\d+/head|[0-9a-f]{40})/(.+)$#', $url, $matches)) {
                $process = new Process(['git', 'show', "$matches[1]:" . rawurldecode($matches[2])], $this->upstream);
                $process->run();

                return $process->isSuccessful() ? Http::response($process->getOutput()) : Http::response('Not Found', 404);
            }

            return Http::response('Unexpected request', 500);
        });

        $this->manager = $this->newManager();
    }

    protected function tearDown(): void
    {
        File::deleteDirectory($this->tmp);

        parent::tearDown();
    }

    public function testApplyAndRemoveKeepsOtherChanges(): void
    {
        $this->createPullRequest(1, ['file.txt' => fn ($c) => str_replace('two', 'TWO', $c), 'new.txt' => "new file\n"]);

        // local changes made by the user
        $this->write($this->local, 'file.txt', str_replace('nine', 'NINE', $this->read('file.txt')));
        $this->write($this->local, 'other.txt', "changed by user\n");

        $patch = $this->manager->fetch(1);
        $this->assertSame([['status' => 'M', 'path' => 'file.txt'], ['status' => 'A', 'path' => 'new.txt']], $patch->files);
        $this->manager->apply($patch);

        $this->assertStringContainsString('TWO', $this->read('file.txt'));
        $this->assertFileExists("$this->local/new.txt");
        $this->assertTrue($this->newManager()->isApplied($this->newManager()->get(1)));

        $this->newManager()->remove($this->newManager()->get(1));

        $this->assertSame("one\ntwo\nthree\nfour\nfive\nsix\nseven\neight\nNINE\nten\n", $this->read('file.txt'));
        $this->assertSame("changed by user\n", $this->read('other.txt'));
        $this->assertFileDoesNotExist("$this->local/new.txt");
        $this->assertEmpty($this->newManager()->all());
        $this->assertFileDoesNotExist("$this->tmp/storage/patches.json");
    }

    public function testBinaryFiles(): void
    {
        $this->write($this->upstream, 'old.png', "\x89PNG\0old");
        $this->write($this->upstream, 'gone.png', "\x89PNG\0gone");
        $this->commit($this->upstream, 'images');
        $this->git($this->local, 'pull', '--quiet');

        $this->createPullRequest(1, ['logo.png' => "\x89PNG\r\n\x1a\n\0\0binary\0data", 'old.png' => "\x89PNG\0new", 'gone.png' => null]);

        $patch = $this->manager->fetch(1);
        $this->assertStringNotContainsString('Binary files', $patch->diff);
        $this->manager->apply($patch);
        $this->assertSame("\x89PNG\r\n\x1a\n\0\0binary\0data", $this->read('logo.png'));
        $this->assertSame("\x89PNG\0new", $this->read('old.png'));
        $this->assertFileDoesNotExist("$this->local/gone.png");

        $this->manager->remove($this->manager->get(1));
        $this->assertFileDoesNotExist("$this->local/logo.png");
        $this->assertSame("\x89PNG\0old", $this->read('old.png'));
        $this->assertSame("\x89PNG\0gone", $this->read('gone.png'));
    }

    public function testWorksWithoutGitRepository(): void
    {
        // like the docker image: no .git, and it must not use a repository in a parent directory
        $parent = "$this->tmp/parent";
        mkdir($parent);
        $this->git($parent, 'init', '--quiet');
        $this->local = "$parent/install";
        mkdir($this->local);
        $this->git($this->upstream, 'archive', '--output', "$this->tmp/export.tar", 'master');
        (new Process(['tar', '-xf', "$this->tmp/export.tar", '-C', $this->local]))->mustRun();
        $manager = $this->newManager();

        $this->createPullRequest(1, ['file.txt' => fn ($c) => str_replace('two', 'TWO', $c), 'logo.png' => "\x89PNG\0data"]);
        $manager->apply($manager->fetch(1));
        $this->assertStringContainsString('TWO', $this->read('file.txt'));
        $this->assertSame("\x89PNG\0data", $this->read('logo.png'));

        $manager->remove($manager->get(1));
        $this->assertSame("one\ntwo\nthree\nfour\nfive\nsix\nseven\neight\nnine\nten\n", $this->read('file.txt'));
        $this->assertFileDoesNotExist("$this->local/logo.png");
        $this->assertFileDoesNotExist("$parent/file.txt");
    }

    public function testApplyingAgainUpdatesToNewVersion(): void
    {
        $this->createPullRequest(1, ['file.txt' => fn ($c) => str_replace('two', 'TWO', $c)]);
        $this->manager->apply($this->manager->fetch(1));

        $this->createPullRequest(1, ['file.txt' => fn ($c) => str_replace('two', 'TWO v2', $c)]);
        $this->manager->apply($this->manager->fetch(1));

        $this->assertStringContainsString("TWO v2\n", $this->read('file.txt'));
        $this->assertStringNotContainsString("TWO\n", $this->read('file.txt'));

        $this->manager->remove($this->manager->get(1));
        $this->assertSame("one\ntwo\nthree\nfour\nfive\nsix\nseven\neight\nnine\nten\n", $this->read('file.txt'));
    }

    public function testConflictingPatchIsNotApplied(): void
    {
        $this->createPullRequest(1, ['file.txt' => fn ($c) => str_replace('two', 'TWO', $c), 'new.txt' => "new\n"]);
        $this->write($this->local, 'file.txt', str_replace('two', 'user edit', $this->read('file.txt')));

        try {
            $this->manager->apply($this->manager->fetch(1));
            $this->fail('Expected exception');
        } catch (PatchException) {
        }

        $this->assertFileDoesNotExist("$this->local/new.txt"); // nothing partially applied
        $this->assertEmpty($this->manager->all());
    }

    public function testRemoveUnwindsOverlappingPatches(): void
    {
        $this->createPullRequest(1, ['file.txt' => fn ($c) => str_replace('two', 'TWO', $c)]);
        $this->createPullRequest(2, ['other.txt' => "other changed\n"], rename: ['file.txt' => 'moved.txt']);

        $this->manager->apply($this->manager->fetch(1));
        $this->manager->apply($this->manager->fetch(2));
        $this->assertStringContainsString("TWO\n", $this->read('moved.txt'));
        $this->assertTrue($this->manager->isApplied($this->manager->get(1)));

        // #2 moved the file changed by #1, so #1 can only be removed by unwinding #2
        $this->assertSame([], $this->manager->remove($this->manager->get(1)));

        $this->assertSame("one\ntwo\nthree\nfour\nfive\nsix\nseven\neight\nnine\nten\n", $this->read('moved.txt'));
        $this->assertFileDoesNotExist("$this->local/file.txt");
        $this->assertSame([2], array_keys($this->manager->all()));
        $this->assertTrue($this->manager->isApplied($this->manager->get(2)));
    }

    public function testRemoveDependentPatch(): void
    {
        $this->createPullRequest(1, ['new.txt' => "new\n"]);
        $this->createPullRequest(2, [], rename: ['other.txt' => 'new.txt'], from: 'pr-1');
        $this->apiResponses[2]['base']['ref'] = 'pr-1';

        $this->manager->apply($this->manager->fetch(1));
        $this->manager->apply($this->manager->fetch(2));

        $warnings = $this->manager->remove($this->manager->get(1));
        $this->assertCount(1, $warnings);
        $this->assertSame(Patch::SUSPENDED, $this->manager->get(2)->state);
    }

    public function testRemoveRevertedPatch(): void
    {
        $this->createPullRequest(1, ['file.txt' => fn ($c) => str_replace('two', 'TWO', $c)]);
        $this->manager->apply($this->manager->fetch(1));

        $this->git($this->local, 'checkout', '--', 'file.txt'); // changes discarded outside of lnms

        $this->manager->remove($this->manager->get(1));
        $this->assertEmpty($this->manager->all());
    }

    public function testMergedPatchIsNeverRemoved(): void
    {
        $this->createPullRequest(1, ['file.txt' => fn ($c) => str_replace('two', 'TWO v1', $c), 'v1.txt' => "v1 only\n"]);
        $this->createPullRequest(2, ['other.txt' => "changed by 2\n"]);
        $this->manager->apply($this->manager->fetch(1));
        $this->manager->apply($this->manager->fetch(2));
        $patched = $this->read('file.txt');

        $this->mergeUpstream(1, ['file.txt' => fn ($c) => str_replace('two', 'TWO final', $c)]);
        $target = $this->fetchUpdate();

        // the merged patch blocks the update until the user removes it
        $manager = $this->newManager();
        $this->assertSame([1 => ['file.txt']], $manager->blockingPatches($target));
        $this->assertNotNull($manager->mergeCommit($manager->get(1), $target));
        $this->assertNull($manager->mergeCommit($manager->get(2), $target));

        $pull = new Process(['git', 'pull', '--quiet'], $this->local);
        $pull->run();
        $this->assertFalse($pull->isSuccessful());
        $this->assertSame($patched, $this->read('file.txt'));
        $this->assertFileExists("$this->local/v1.txt");
        $this->assertSame([1, 2], array_keys($this->newManager()->all()));

        // after removing it, the update goes through and the other patch stays
        $this->newManager()->remove($this->newManager()->get(1));
        $this->assertSame([], $this->newManager()->blockingPatches($target));
        $this->git($this->local, 'pull', '--quiet');
        $this->assertStringContainsString('TWO final', $this->read('file.txt'));
        $this->assertFileDoesNotExist("$this->local/v1.txt");
        $this->assertSame("changed by 2\n", $this->read('other.txt'));
        $this->assertTrue($this->newManager()->isApplied($this->newManager()->get(2)));
    }

    public function testPreUpdateOnlyReportsBlockingPatches(): void
    {
        $this->createPullRequest(1, ['file.txt' => fn ($c) => str_replace('two', 'TWO', $c)]);
        $this->createPullRequest(2, ['file.txt' => fn ($c) => str_replace('nine', 'NINE', $c)]);
        $this->manager->apply($this->manager->fetch(1));
        $this->manager->apply($this->manager->fetch(2));
        $patched = $this->read('file.txt');

        $this->mergeUpstream(1, ['file.txt' => fn ($c) => str_replace('two', 'TWO', $c)]);
        $target = $this->fetchUpdate();
        $this->app->instance(PatchManager::class, $this->newManager());

        $this->artisan('patch:pre-update', ['target' => $target])
            ->expectsOutputToContain(trans('commands.patch:pre-update.merged', ['patch' => '#1 Test PR 1', 'pr' => 1]))
            ->expectsOutputToContain(trans('commands.patch:pre-update.conflict', ['patch' => '#2 Test PR 2', 'pr' => 2, 'files' => 'file.txt']))
            ->assertExitCode(1);

        $this->assertSame($patched, $this->read('file.txt'));
        $this->assertSame([1, 2], array_keys($this->newManager()->all()));
    }

    public function testUnmergedPatchBlocksUpdate(): void
    {
        $this->createPullRequest(1, ['file.txt' => fn ($c) => str_replace('two', 'TWO', $c)]);
        $this->manager->apply($this->manager->fetch(1));
        $patched = $this->read('file.txt');

        $this->git($this->upstream, 'checkout', '--quiet', 'master');
        $this->write($this->upstream, 'file.txt', str_replace('nine', 'upstream nine', $this->read('file.txt', $this->upstream)));
        $this->commit($this->upstream, 'Unrelated change to the same file');
        $target = $this->fetchUpdate();

        $this->assertSame([1 => ['file.txt']], $this->newManager()->blockingPatches($target));
        $this->assertNull($this->newManager()->mergeCommit($this->newManager()->get(1), $target));

        // git keeps the patch and the code as they are
        $pull = new Process(['git', 'pull', '--quiet'], $this->local);
        $pull->run();
        $this->assertFalse($pull->isSuccessful());
        $this->assertSame($patched, $this->read('file.txt'));
    }

    public function testPatchNotBlockingUpdateStaysApplied(): void
    {
        $this->createPullRequest(1, ['other.txt' => "changed by 1\n"]);
        $this->manager->apply($this->manager->fetch(1));

        $this->git($this->upstream, 'checkout', '--quiet', 'master');
        $this->write($this->upstream, 'file.txt', str_replace('nine', 'upstream nine', $this->read('file.txt', $this->upstream)));
        $this->commit($this->upstream, 'Unrelated change');
        $target = $this->fetchUpdate();

        $this->assertSame([], $this->newManager()->blockingPatches($target));
        $this->git($this->local, 'pull', '--quiet');
        $this->assertStringContainsString('upstream nine', $this->read('file.txt'));
        $this->assertSame("changed by 1\n", $this->read('other.txt'));
        $this->assertTrue($this->newManager()->isApplied($this->newManager()->get(1)));
    }

    public function testResetPreviewMatchesWhatResetDiscards(): void
    {
        $this->write($this->upstream, '.gitignore', "*.log\n");
        File::ensureDirectoryExists("$this->upstream/app");
        $this->write($this->upstream, 'app/tracked.php', "tracked\n");
        $this->commit($this->upstream, 'app');
        $this->git($this->local, 'pull', '--quiet');

        $this->createPullRequest(1, ['app/patched.php' => "patched\n"]);
        $this->manager->apply($this->manager->fetch(1));

        $this->write($this->local, 'other.txt', "unstaged change\n");
        $this->write($this->local, 'file.txt', "staged change\n");
        $this->write($this->local, 'app/staged.php', "staged new\n");
        $this->write($this->local, 'staged.txt', "staged new outside code dirs\n");
        $this->git($this->local, 'add', 'file.txt', 'app/staged.php', 'staged.txt');
        $this->write($this->local, 'app/untracked.php', "untracked\n");
        $this->write($this->local, 'app/ignored.log', "ignored\n");
        $this->write($this->local, 'notes.txt', "untracked outside code dirs\n");

        $preview = $this->manager->resetPreview();
        sort($preview['modified']);
        sort($preview['untracked']);
        $this->assertSame(['file.txt', 'other.txt'], $preview['modified']);
        $this->assertSame(['app/patched.php', 'app/staged.php', 'app/untracked.php'], $preview['untracked']);

        $this->manager->reset();

        $this->assertSame("one\ntwo\nthree\nfour\nfive\nsix\nseven\neight\nnine\nten\n", $this->read('file.txt'));
        $this->assertSame("other\n", $this->read('other.txt'));
        $this->assertFileDoesNotExist("$this->local/app/patched.php");
        $this->assertFileDoesNotExist("$this->local/app/staged.php");
        $this->assertFileDoesNotExist("$this->local/app/untracked.php");
        $this->assertFileExists("$this->local/app/tracked.php");
        // not in the preview, so not deleted
        $this->assertFileExists("$this->local/app/ignored.log");
        $this->assertFileExists("$this->local/staged.txt");
        $this->assertFileExists("$this->local/notes.txt");
        $this->assertEmpty($this->newManager()->all());
    }

    public function testResetRequiresGitRepository(): void
    {
        $this->local = "$this->tmp/export";
        mkdir($this->local);
        $this->write($this->local, 'file.txt', "keep me\n");
        $manager = $this->newManager();

        foreach ([$manager->resetPreview(...), $manager->reset(...)] as $call) {
            try {
                $call();
                $this->fail('Expected exception');
            } catch (PatchException) {
            }
        }

        $this->assertSame("keep me\n", $this->read('file.txt'));
    }

    public function testRemoveAllAsksForConfirmation(): void
    {
        $this->createPullRequest(1, ['file.txt' => fn ($c) => str_replace('two', 'TWO', $c)]);
        $this->createPullRequest(2, ['other.txt' => "changed by 2\n"]);
        $this->manager->apply($this->manager->fetch(1));
        $this->manager->apply($this->manager->fetch(2));
        $this->app->instance(PatchManager::class, $this->newManager());

        $this->artisan('patch:remove')
            ->expectsOutputToContain('#1 Test PR 1')
            ->expectsOutputToContain('#2 Test PR 2')
            ->expectsConfirmation(trans('commands.patch:remove.remove_all_prompt'), 'no')
            ->assertExitCode(1);
        $this->assertSame([1, 2], array_keys($this->newManager()->all()));

        // naming the pull request does not ask
        $this->app->instance(PatchManager::class, $this->newManager());
        $this->artisan('patch:remove', ['pull-request' => ['2']])->assertExitCode(0);
        $this->assertSame([1], array_keys($this->newManager()->all()));

        $this->app->instance(PatchManager::class, $this->newManager());
        $this->artisan('patch:remove')
            ->expectsConfirmation(trans('commands.patch:remove.remove_all_prompt'), 'yes')
            ->assertExitCode(0);
        $this->assertEmpty($this->newManager()->all());
        $this->assertSame("other\n", $this->read('other.txt'));
    }

    public function testResetListsFilesAndCanBeDeclined(): void
    {
        $this->createPullRequest(1, ['file.txt' => fn ($c) => str_replace('two', 'TWO', $c)]);
        $this->manager->apply($this->manager->fetch(1));
        $this->write($this->local, 'other.txt', "my own work\n");
        $this->app->instance(PatchManager::class, $this->newManager());

        $this->artisan('patch:remove', ['--reset' => true])
            ->expectsOutputToContain(trans('commands.patch:remove.reset_warning'))
            ->expectsOutputToContain('other.txt')
            ->expectsConfirmation(trans('commands.patch:remove.reset_prompt'), 'no')
            ->assertExitCode(1);
        $this->assertSame("my own work\n", $this->read('other.txt'));
        $this->assertStringContainsString('TWO', $this->read('file.txt'));
        $this->assertSame([1], array_keys($this->newManager()->all()));
    }

    public function testComposerFilesRestoredUnlessPatchChangesThem(): void
    {
        $this->write($this->upstream, 'composer.json', "{}\n");
        $this->write($this->upstream, 'composer.lock', "{}\n");
        $this->commit($this->upstream, 'composer');
        $this->git($this->local, 'pull', '--quiet');

        // installing a plugin changes the composer files
        $this->write($this->local, 'composer.json', "{\"plugin\": true}\n");
        $this->assertTrue($this->manager->restoreComposerFiles());
        $this->assertSame("{}\n", $this->read('composer.json'));

        $this->createPullRequest(1, ['composer.json' => "{\"patched\": true}\n"]);
        $this->manager->apply($this->manager->fetch(1));
        $this->assertFalse($this->manager->restoreComposerFiles());
        $this->assertSame("{\"patched\": true}\n", $this->read('composer.json'));
    }

    public function testParseNumber(): void
    {
        $this->assertSame(123, Patch::parseNumber('123'));
        $this->assertSame(123, Patch::parseNumber('#123'));
        $this->assertSame(123, Patch::parseNumber('https://github.com/librenms/librenms/pull/123'));
        $this->assertSame(123, Patch::parseNumber('https://github.com/librenms/librenms/pull/123/files'));
        $this->assertNull(Patch::parseNumber('abc'));
        $this->assertNull(Patch::parseNumber('https://github.com/other/repo/pull/123'));
    }

    public function testReleaseChannel(): void
    {
        $this->createPullRequest(1, ['file.txt' => fn ($c) => str_replace('two', 'TWO', $c)]);
        $this->git($this->local, 'checkout', '--quiet', '--detach');
        $this->manager->apply($this->manager->fetch(1));

        $this->mergeUpstream(1, ['file.txt' => fn ($c) => str_replace('two', 'TWO', $c)]);
        $this->git($this->upstream, 'tag', '26.10.0');
        $this->git($this->local, 'fetch', '--quiet', '--tags');
        $release = trim($this->git($this->local, 'rev-list', '--tags', '--max-count=1'));

        $manager = $this->newManager();
        $this->assertSame([1 => ['file.txt']], $manager->blockingPatches($release));
        $this->assertNotNull($manager->mergeCommit($manager->get(1), $release));
    }

    public function testMergedDetectionWithoutGitHubApi(): void
    {
        $this->createPullRequest(1, ['file.txt' => fn ($c) => str_replace('two', 'TWO', $c)]);
        $patch = $this->manager->fetch(1);
        $this->apiResponses = []; // GitHub API not available
        $this->assertNull($this->manager->mergeCommit($patch, 'HEAD'));

        $this->git($this->upstream, 'checkout', '--quiet', 'master');
        $this->write($this->upstream, 'other.txt', "unrelated\n");
        $this->commit($this->upstream, "Squashed change (#1)\n\n* fix typo (#12)");
        $this->git($this->local, 'pull', '--quiet');

        $this->assertSame(trim($this->git($this->local, 'rev-parse', 'HEAD')), $this->manager->mergeCommit($patch, 'HEAD'));
        $this->assertNull($this->manager->mergeCommit(new Patch(12, '', '', []), 'HEAD'));
    }

    public function testFetchMissingPullRequest(): void
    {
        $this->expectException(PatchException::class);
        $this->manager->fetch(99);
    }

    /**
     * Fetch upstream into the local repo like daily.sh and return the commit to update to
     */
    private function fetchUpdate(): string
    {
        $this->git($this->local, 'fetch', '--quiet');

        return trim($this->git($this->local, 'rev-parse', '@{upstream}'));
    }

    /**
     * Squash merge a version of the pull request into upstream master
     *
     * @param  array<string, string|callable>  $changes
     */
    private function mergeUpstream(int $pr, array $changes): void
    {
        $this->git($this->upstream, 'checkout', '--quiet', 'master');
        foreach ($changes as $file => $content) {
            File::ensureDirectoryExists(dirname("$this->upstream/$file"));
            $this->write($this->upstream, $file, is_callable($content) ? $content($this->read($file, $this->upstream)) : $content);
        }
        $merge = $this->commit($this->upstream, "Merged PR (#$pr)");
        $this->apiResponses[$pr]['merged'] = true;
        $this->apiResponses[$pr]['merge_commit_sha'] = $merge;
    }

    private function newManager(): PatchManager
    {
        return new PatchManager($this->local, "$this->tmp/storage", 'http://github.test/');
    }

    /**
     * Create refs/pull/$pr/head in the upstream repo branched from master
     *
     * @param  array<string, string|callable|null>  $changes
     * @param  array<string, string>  $rename
     */
    private function createPullRequest(int $pr, array $changes, array $rename = [], string $from = 'master'): void
    {
        $this->git($this->upstream, 'checkout', '--quiet', '-B', "pr-$pr", $from);
        foreach ($changes as $file => $content) {
            if ($content === null) {
                $this->git($this->upstream, 'rm', '--quiet', $file);
            } else {
                File::ensureDirectoryExists(dirname("$this->upstream/$file"));
                $this->write($this->upstream, $file, is_callable($content) ? $content($this->read($file, $this->upstream)) : $content);
            }
        }
        foreach ($rename as $old => $new) {
            $this->git($this->upstream, 'mv', '-f', $old, $new);
        }
        $sha = $this->commit($this->upstream, "PR $pr");
        $this->git($this->upstream, 'update-ref', "refs/pull/$pr/head", $sha);
        $this->git($this->upstream, 'checkout', '--quiet', 'master');

        $this->apiResponses[$pr] = ['number' => $pr, 'title' => "Test PR $pr", 'user' => ['login' => 'tester'], 'merged' => false, 'merge_commit_sha' => null, 'head' => ['sha' => $sha]];
    }

    private function commit(string $dir, string $message): string
    {
        $this->git($dir, 'add', '--all');
        $this->git($dir, '-c', 'user.name=Test', '-c', 'user.email=test@example.com', 'commit', '--quiet', '-m', $message);

        return trim($this->git($dir, 'rev-parse', 'HEAD'));
    }

    private function write(string $dir, string $file, string $content): void
    {
        file_put_contents("$dir/$file", $content);
    }

    private function read(string $file, ?string $dir = null): string
    {
        return (string) file_get_contents(($dir ?? $this->local) . "/$file");
    }

    private function git(string $dir, string ...$args): string
    {
        // ignore the user's git config, such as commit signing
        $process = new Process(['git', ...$args], $dir, ['GIT_CONFIG_GLOBAL' => '/dev/null']);
        $process->mustRun();

        return $process->getOutput();
    }
}
