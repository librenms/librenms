<?php
namespace CodeClimate\Component\System\Git;

use Contrib\Component\System\SystemCommand;

class GitCommand extends SystemCommand
{
    protected $commandPath = 'git';

    public function getHead()
    {
        $command = $this->createCommand("log -1 --pretty=format:'%H'");

        return current($this->executeCommand($command));
    }

    public function getBranch()
    {
        $command  = $this->createCommand("branch");
        $branches = $this->executeCommand($command);

        foreach ($branches as $branch) {
            if ($branch[0] == "*") {
                return str_replace("* ", "", $branch);
            }
        }

        return null;
    }

    public function getCommittedAt()
    {
        $command = $this->createCommand("log -1 --pretty=format:'%ct'");

        return (int)current($this->executeCommand($command));
    }
}
