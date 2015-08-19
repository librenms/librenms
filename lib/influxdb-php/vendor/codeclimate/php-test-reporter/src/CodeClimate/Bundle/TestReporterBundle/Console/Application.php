<?php
namespace CodeClimate\Bundle\TestReporterBundle\Console;

use CodeClimate\Bundle\TestReporterBundle\Command\TestReporterCommand;
use Symfony\Component\Console\Application as BaseApplication;
use Symfony\Component\Console\Input\InputInterface;

/**
 * Coveralls API application.
 *
 * @author Kitamura Satoshi <with.no.parachute@gmail.com>
 */
class Application extends BaseApplication
{
    /**
     * Path to project root directory.
     *
     * @var string
     */
    private $rootDir;

    /**
     * Constructor.
     *
     * @param string $rootDir Path to project root directory.
     * @param string $name    The name of the application
     * @param string $version The version of the application
     */
    public function __construct($rootDir, $name = 'UNKNOWN', $version = 'UNKNOWN')
    {
        $this->rootDir = $rootDir;

        parent::__construct($name, $version);
    }

    // internal method

    /**
     * {@inheritdoc}
     *
     * @see \Symfony\Component\Console\Application::getCommandName()
     */
    protected function getCommandName(InputInterface $input)
    {
        return 'test-reporter';
    }

    /**
     * {@inheritdoc}
     *
     * @see \Symfony\Component\Console\Application::getDefaultCommands()
     */
    protected function getDefaultCommands()
    {
        // Keep the core default commands to have the HelpCommand
        // which is used when using the --help option
        $defaultCommands = parent::getDefaultCommands();
        $defaultCommands[] = $this->createTestReporterCommand();

        return $defaultCommands;
    }

    /**
     * Create TestReporterCommand.
     *
     * @return \CodeClimate\Bundle\TestReporterBundle\Command\TestReporterCommand
     */
    protected function createTestReporterCommand()
    {
        $command = new TestReporterCommand();
        $command->setRootDir($this->rootDir);

        return $command;
    }

    // accessor

    /**
     * {@inheritdoc}
     *
     * @see \Symfony\Component\Console\Application::getDefinition()
     */
    public function getDefinition()
    {
        $inputDefinition = parent::getDefinition();
        // clear out the normal first argument, which is the command name
        $inputDefinition->setArguments();

        return $inputDefinition;
    }
}
