<?php
namespace CodeClimate\Bundle\TestReporterBundle\Command;

use CodeClimate\Bundle\TestReporterBundle\CoverageCollector;
use CodeClimate\Bundle\TestReporterBundle\ApiClient;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * Test reporter command
 */
class TestReporterCommand extends Command
{
    /**
     * Path to project root directory.
     *
     * @var string
     */
    protected $rootDir;

    /**
     * {@inheritdoc}
     *
     * @see \Symfony\Component\Console\Command\Command::configure()
     */
    protected function configure()
    {
        $this
        ->setName('test-reporter')
        ->setDescription('Code Climate PHP Test Reporter')
        ->addOption(
            'stdout',
            null,
            InputOption::VALUE_NONE,
            'Do not upload, print JSON payload to stdout'
        )
        ->addOption(
            'coverage-report',
            null,
            InputOption::VALUE_REQUIRED | InputOption::VALUE_IS_ARRAY,
            'Location of clover style CodeCoverage report, as produced by PHPUnit\'s --coverage-clover option.',
            array('build/logs/clover.xml')
        );
    }

    /**
     * {@inheritdoc}
     *
     * @see \Symfony\Component\Console\Command\Command::execute()
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $ret = 0;
        $collector = new CoverageCollector($input->getOption('coverage-report'));
        $json = $collector->collectAsJson();

        if ($input->getOption('stdout')) {
            $output->writeln((string)$json);
        } else {
            $client = new ApiClient();
            $response = $client->send($json);
            switch ($response->code) {
                case 200:
                    $output->writeln("Test coverage data sent.");
                    break;

                case 401:
                    $output->writeln("Invalid CODECLIMATE_REPO_TOKEN.");
                    $ret = 1;
                    break;

                default:
                    $output->writeln("Unexpected response: ".$response->code." ".$response->message);
                    $output->writeln($response->body);
                    $ret = 1;
                    break;
            }
        }

        return $ret;
    }

    // accessor

    /**
     * Set root directory.
     *
     * @param string $rootDir Path to project root directory.
     *
     * @return void
     */
    public function setRootDir($rootDir)
    {
        $this->rootDir = $rootDir;
    }
}
