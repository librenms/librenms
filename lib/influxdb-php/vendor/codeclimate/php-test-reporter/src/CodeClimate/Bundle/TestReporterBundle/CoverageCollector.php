<?php
namespace CodeClimate\Bundle\TestReporterBundle;

use CodeClimate\Component\System\Git\GitCommand;
use CodeClimate\Bundle\TestReporterBundle\Entity\JsonFile;
use Contrib\Bundle\CoverallsV1Bundle\Api\Jobs;
use Contrib\Bundle\CoverallsV1Bundle\Config\Configuration;

class CoverageCollector
{
    protected $api;

    /**
     * Array that holds list of relative paths to Clover XML files
     * @var array
     */
    protected $cloverPaths = array();

    public function __construct($paths)
    {
        $rootDir = getcwd();
        $config = new Configuration();
        $config->setSrcDir($rootDir);
        $this->setCloverPaths($paths);
        foreach ($this->getCloverPaths() as $path) {
            if (file_exists($path)) {
                $config->addCloverXmlPath($path);
            } else {
                $config->addCloverXmlPath($rootDir . DIRECTORY_SEPARATOR . $path);
            }
        }

        $this->api = new Jobs($config);
    }

    /**
     * Set a list of Clover XML paths
     * @param array $paths Array of relative paths to Clovers XML files
     */
    public function setCloverPaths($paths)
    {
        $this->cloverPaths = $paths;
    }

    /**
     * Get a list of Clover XML paths
     * @return array Array of relative Clover XML file locations
     */
    public function getCloverPaths()
    {
        return $this->cloverPaths;
    }
    public function collectAsJson()
    {
        $cloverJsonFile = $this->api->collectCloverXml()->getJsonFile();

        $jsonFile = new JsonFile();
        $jsonFile->setRunAt($cloverJsonFile->getRunAt());

        foreach ($cloverJsonFile->getSourceFiles() as $sourceFile) {
            $jsonFile->addSourceFile($sourceFile);
        }

        return $jsonFile;
    }
}
