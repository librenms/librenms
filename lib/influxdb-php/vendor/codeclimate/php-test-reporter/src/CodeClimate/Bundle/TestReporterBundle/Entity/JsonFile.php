<?php
namespace CodeClimate\Bundle\TestReporterBundle\Entity;

use CodeClimate\Component\System\Git\GitCommand;
use CodeClimate\Bundle\TestReporterBundle\Entity\CiInfo;
use CodeClimate\Bundle\TestReporterBundle\Version;
use Contrib\Bundle\CoverallsV1Bundle\Entity\JsonFile as SatooshiJsonFile;

class JsonFile extends SatooshiJsonFile
{
    public function toArray()
    {
       return array(
            "partial"      => false,
            "run_at"       => $this->getRunAt(),
            "repo_token"   => $this->getRepoToken(),
            "environment"  => $this->getEnvironment(),
            "git"          => $this->collectGitInfo(),
            "ci_service"   => $this->collectCiServiceInfo(),
            "source_files" => $this->collectSourceFiles()
        );
    }

    public function getRunAt()
    {
        return strtotime(parent::getRunAt());
    }

    public function getRepoToken()
    {
        return $_SERVER["CODECLIMATE_REPO_TOKEN"];
    }

    protected function getEnvironment()
    {
        return array(
            "pwd"             => getcwd(),
            "package_version" => Version::VERSION
        );
    }

    protected function collectGitInfo()
    {
        $command = new GitCommand();

        return array(
            "head"         => $command->getHead(),
            "branch"       => $command->getBranch(),
            "committed_at" => $command->getCommittedAt()
        );
    }

    protected function collectCiServiceInfo()
    {
        $ciInfo = new CiInfo();

        return $ciInfo->toArray();
    }

    protected function collectSourceFiles()
    {
        $sourceFiles = array();

        foreach ($this->getSourceFiles() as $sourceFile) {
            array_push($sourceFiles, array(
                "name"     => $sourceFile->getName(),
                "coverage" => json_encode($sourceFile->getCoverage()),
                "blob_id"  => $this->calculateBlobId($sourceFile)
            ));
        }

        return $sourceFiles;
    }

    protected function calculateBlobId($sourceFile)
    {
        $content = file_get_contents($sourceFile->getPath());
        $header  = "blob ".strlen($content)."\0";

        return sha1($header.$content);
    }
}
