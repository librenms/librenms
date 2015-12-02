<?php
namespace CodeClimate\Bundle\TestReporterBundle\Console;

use Symfony\Component\Console\Tester\ApplicationTester;

class ApplicationTest extends \PHPUnit_Framework_TestCase
{
    const PROJECT_DIR = "/tmp/php-test-reporter-example-project";

    protected $srcDir;

    public function setUp()
    {
        $this->srcDir = realpath(__DIR__ . '/../../../../..');
        $this->setupProject();
        $this->setupEnvironment();
    }

    /**
     * @test
     */
    public function shouldExecuteSuccessfully()
    {
        $app = new Application($this->srcDir, 'PHP Test Reporter', '1.0.0');
        $app->setAutoExit(false);
        $tester = new ApplicationTester($app);

        $status = $tester->run(array('--stdout' => true));

        $this->assertEquals(0, $status);
    }

    private function setupProject()
    {
        shell_exec("rm -rf ".static::PROJECT_DIR);
        mkdir(static::PROJECT_DIR."/build/logs", 0755, true);
        copy("tests/files/test.php", static::PROJECT_DIR."/test.php");
        copy("tests/files/test.php", static::PROJECT_DIR."/test2.php");
        copy("tests/files/clover.xml", static::PROJECT_DIR."/build/logs/clover.xml");

        chdir(static::PROJECT_DIR);

        shell_exec("git init");
        shell_exec("git add test.php test2.php");
        shell_exec("git commit -m 'Initial commit'");
        shell_exec("git remote add origin git@github.com:foo/bar.git");
    }

    private function setupEnvironment()
    {
        $_SERVER["CODECLIMATE_REPO_TOKEN"] = 'abc123';
        $_SERVER["TRAVIS"] = "1";
        $_SERVER["TRAVIS_BRANCH"] = "master";
        $_SERVER["TRAVIS_JOB_ID"] = "1";
        $_SERVER["TRAVIS_PULL_REQUEST"] = "fb-feature";
    }
}
