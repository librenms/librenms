<?php namespace ReadmeGen;

use ReadmeGen\Input\Parser;
use ReadmeGen\Config\Loader as ConfigLoader;
use ReadmeGen\Log\Extractor;
use ReadmeGen\Log\Decorator;
use ReadmeGen\Output\Writer;

class Bootstrap
{
    protected $generator;

    public function __construct(array $input)
    {
        // Set up the input parser
        $inputParser = new Parser();
        $inputParser->setInput(join(' ', $input));

        // Parse the input
        try {
            $input = $inputParser->parse();
        } catch (\BadMethodCallException $e) {
            die($e->getMessage());
        }

        // Run the whole process
        $this->run($input->getOptions());
    }

    /**
     * Generates the output file.
     *
     * @param array $options
     */
    public function run(array $options)
    {
        $this->generator = new ReadmeGen(new ConfigLoader());

        $this->setupParser($options);

        // Extract useful log entries
        $logGrouped = $this->generator->setExtractor(new Extractor())
            ->extractMessages($this->getLog());

        $config = $this->generator->getConfig();

        $formatterClassName = '\ReadmeGen\Output\Format\\' . ucfirst($config['format']);

        // Create the output formatter
        $formatter = new $formatterClassName;

        $formatter
            ->setRelease($options['release'])
            ->setFileName($config['output_file_name'])
            ->setDate($this->getToDate());

        // Pass decorated log entries to the generator
        $this->generator->setDecorator(new Decorator($formatter))
            ->getDecoratedMessages($logGrouped);

        $writer = new Writer($formatter);

        // If present, respect the breakpoint in the existing output file
        $break = $this->getBreak($options, $config);

        if (false === empty($break)) {
            $writer->setBreak($break);
        }

        // Write the output
        $this->generator->setOutputWriter($writer)
            ->writeOutput();
    }

    /**
     * Returns the parsed log.
     *
     * @return mixed
     */
    public function getLog()
    {
        return $this->generator->getParser()
            ->parse();
    }

    /**
     * Returns the date of the latter commit (--to).
     *
     * @return string
     */
    protected function getToDate()
    {
        $date = $this->generator->getParser()
            ->getToDate();

        return new \DateTime($date);
    }

    /**
     * Sets the parser.
     *
     * @param array $options
     */
    protected function setupParser(array $options)
    {
        $this->generator->getParser()
            ->setArguments($options)
            ->setShellRunner(new Shell);
    }

    /**
     * Returns the breakpoint if set, null otherwise.
     *
     * @param array $options
     * @param array $config
     * @return null|string
     */
    protected function getBreak(array $options, array $config){
        if (true === isset($options['break'])) {
            return $options['break'];
        }

        if (true === isset($config['break'])) {
            return $config['break'];
        }

        return null;
    }

}
