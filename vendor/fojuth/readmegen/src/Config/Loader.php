<?php namespace ReadmeGen\Config;

use Symfony\Component\Yaml\Yaml;

/**
 * YAML config loader.
 */
class Loader
{

    /**
     * Returns the config as an array.
     * 
     * @param string $path Path to the file.
     * @param array $sourceConfig Config array the result should be merged with.
     * @return array
     * @throws \Symfony\Component\Yaml\Exception\ParseException When a parse error occurs.
     */
    public function get($path, array $sourceConfig = null)
    {
        $config = Yaml::parse($this->getFileContent($path));
        
        if (false === empty($sourceConfig)) {
            return array_replace_recursive($sourceConfig, $config);
        }
        
        return $config;
    }
    
    /**
     * Returns the file's contents.
     * 
     * @param string $path Path to file.
     * @return string
     * @throws \InvalidArgumentException When the file does not exist.
     */
    protected function getFileContent($path)
    {
        if (false === file_exists($path)) {
            throw new \InvalidArgumentException(sprintf('File "%s" does not exist.', $path));
        }
        
        return file_get_contents($path);
    }

}
