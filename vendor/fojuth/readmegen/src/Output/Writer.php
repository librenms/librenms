<?php namespace ReadmeGen\Output;

use ReadmeGen\Output\Format\FormatInterface;

/**
 * Output writer.
 *
 * Class Writer
 * @package ReadmeGen\Output
 */
class Writer
{
    /**
     * Format specific writer.
     *
     * @var FormatInterface
     */
    protected $formatter;

    /**
     * Output breakpoint.
     *
     * @var string
     */
    protected $break;

    public function __construct(FormatInterface $formatter)
    {
        $this->formatter = $formatter;
    }

    /**
     * Writes the output to a file.
     *
     * @return bool
     */
    public function write()
    {
        // Crete the file if it does not exist
        $this->makeFile($this->formatter->getFileName());

        // Contents of the original file
        $fileContent = file_get_contents($this->formatter->getFileName());

        // Final log
        $log = join("\n", (array) $this->formatter->generate())."\n";

        // Include the breakpoint
        if (false === empty($this->break) && 1 === preg_match("/^{$this->break}/m", $fileContent)) {
            $splitFileContent = preg_split("/^{$this->break}/m", $fileContent);

            file_put_contents($this->formatter->getFileName(), $splitFileContent[0].$this->break."\n".$log.$splitFileContent[1]);

            return true;
        }

        file_put_contents($this->formatter->getFileName(), $log.$fileContent);

        return true;
    }

    /**
     * Create the file if it does not exist.
     *
     * @param string $fileName
     */
    protected function makeFile($fileName){
        if (file_exists($fileName)) {
            return;
        }

        touch($fileName);
    }

    /**
     * Breakpoint setter.
     *
     * @param null|string $break
     * @return $this
     */
    public function setBreak($break = null)
    {
        if (false === empty($break)) {
            $this->break = $break;
        }

        return $this;
    }
}
