<?php namespace ReadmeGen;

/**
 * Shell command runner.
 */
class Shell
{
    
    /**
     * Returns the result of the executed command.
     * 
     * @param string $command
     * @return string
     */
    public function run($command)
    {
        return shell_exec($command);
    }
    
}
