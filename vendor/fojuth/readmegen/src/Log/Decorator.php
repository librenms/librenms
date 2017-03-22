<?php namespace ReadmeGen\Log;

use ReadmeGen\Output\Format\FormatInterface;

/**
 * Output decorator.
 *
 * Class Decorator
 * @package ReadmeGen\Log
 */
class Decorator
{
    /**
     * Formatter instance.
     *
     * @var FormatInterface
     */
    protected $formatter;

    public function __construct(FormatInterface $formatter)
    {
        $this->formatter = $formatter;
    }

    /**
     * Log setter.
     *
     * @param array $log
     * @return $this
     */
    public function setLog(array $log)
    {
        $this->formatter->setLog($log);

        return $this;
    }

    /**
     * Issue tracker pattern setter.
     *
     * @param string $pattern
     * @return $this
     */
    public function setIssueTrackerUrlPattern($pattern)
    {
        $this->formatter->setIssueTrackerUrlPattern($pattern);

        return $this;
    }

    /**
     * Returns the decorated log.
     *
     * @return FormatInterface
     */
    public function decorate()
    {
        return $this->formatter->decorate();
    }
}
