<?php namespace ReadmeGen\Output\Format;

use ReadmeGen\Vcs\Type\AbstractType as VCS;

class Md implements FormatInterface
{
    /**
     * VCS log.
     *
     * @var array
     */
    protected $log;

    /**
     * Issue tracker link pattern.
     *
     * @var string
     */
    protected $pattern;

    /**
     * Output filename.
     *
     * @var string
     */
    protected $fileName = 'README.md';

    /**
     * Release number (included in the output).
     *
     * @var string
     */
    protected $release;

    /**
     * Date (included in the output).
     *
     * @var \DateTime
     */
    protected $date;

    /**
     * Log setter.
     *
     * @param array $log
     * @return mixed
     */
    public function setLog(array $log = null)
    {
        $this->log = $log;

        return $this;
    }

    /**
     * Issue tracker patter setter.
     *
     * @param $pattern
     * @return mixed
     */
    public function setIssueTrackerUrlPattern($pattern)
    {
        $this->pattern = $pattern;

        return $this;
    }

    /**
     * Decorates the output (e.g. adds linkgs to the issue tracker)
     *
     * @return self
     */
    public function decorate()
    {
        foreach ($this->log as &$entries) {
            array_walk($entries, array($this, 'injectLinks'));
        }

        return $this->log;
    }

    /**
     * Injects issue tracker links into the log.
     *
     * @param string $entry Log entry.
     */
    protected function injectLinks(&$entry)
    {
        $entry = preg_replace('/#(\d+)/', "[#\\1]({$this->pattern})", $entry);
    }

    /**
     * Returns a write-ready log.
     *
     * @return array
     */
    public function generate()
    {
        if (true === empty($this->log)) {
            return array();
        }

        $log = array();

        // Iterate over grouped entries
        foreach ($this->log as $header => &$entries) {

            // Add a group header (e.g. Bugfixes)
            $log[] = sprintf("\n#### %s", $header);

            // Iterate over entries
            foreach ($entries as &$line) {
                $message = explode(VCS::MSG_SEPARATOR, $line);

                $log[] = sprintf("* %s", trim($message[0]));

                // Include multi-line entries
                if (true === isset($message[1])) {
                    $log[] = sprintf("\n  %s", trim($message[1]));
                }
            }
        }

        // Return a write-ready log
        return array_merge(array("## {$this->release}", "*({$this->date->format('Y-m-d')})*"), $log, array("\n---\n"));
    }

    /**
     * Returns the output filename.
     *
     * @return string
     */
    public function getFileName()
    {
        return $this->fileName;
    }

    /**
     * Output filename setter.
     *
     * @param $fileName
     * @return mixed
     */
    public function setFileName($fileName)
    {
        $this->fileName = $fileName;

        return $this;
    }

    /**
     * Release number setter.
     *
     * @param $release
     * @return mixed
     */
    public function setRelease($release) {
        $this->release = $release;

        return $this;
    }

    /**
     * Creation date setter.
     *
     * @param \DateTime $date
     * @return mixed
     */
    public function setDate(\DateTime $date) {
        $this->date = $date;

        return $this;
    }

}
