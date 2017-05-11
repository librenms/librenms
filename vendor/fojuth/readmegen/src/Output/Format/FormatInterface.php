<?php namespace ReadmeGen\Output\Format;

interface FormatInterface {

    /**
     * Log setter.
     *
     * @param array $log
     * @return mixed
     */
    public function setLog(array $log);

    /**
     * Issue tracker patter setter.
     *
     * @param $pattern
     * @return mixed
     */
    public function setIssueTrackerUrlPattern($pattern);

    /**
     * Decorates the output (e.g. adds linkgs to the issue tracker)
     *
     * @return self
     */
    public function decorate();

    /**
     * Returns a write-ready log.
     *
     * @return array
     */
    public function generate();

    /**
     * Returns the output filename.
     *
     * @return string
     */
    public function getFileName();

    /**
     * Output filename setter.
     *
     * @param $fileName
     * @return mixed
     */
    public function setFileName($fileName);

    /**
     * Release number setter.
     *
     * @param $release
     * @return mixed
     */
    public function setRelease($release);

    /**
     * Creation date setter.
     *
     * @param \DateTime $date
     * @return mixed
     */
    public function setDate(\DateTime $date);

} 