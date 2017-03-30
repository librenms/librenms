<?php namespace ReadmeGen\Vcs;

use ReadmeGen\Vcs\Type\TypeInterface;
use ReadmeGen\Shell;

/**
 * VCS log parser.
 * 
 * Used to return the VCS log as an array.
 */
class Parser
{
    /**
     * VCS-specific parser.
     * 
     * @var TypeInterface
     */
    protected $vcs;

    public function __construct(TypeInterface $vcs)
    {
        $this->vcs = $vcs;
    }

    /**
     * Returns the parsed log.
     * 
     * @return array
     */
    public function parse()
    {
        return $this->vcs->parse();
    }

    /**
     * Returns the VCS parser.
     * 
     * @return TypeInterface
     */
    public function getVcsParser()
    {
        return $this->vcs;
    }

    /**
     * Shell runner setter.
     *
     * @param Shell $shell
     * @return $this
     */
    public function setShellRunner(Shell $shell)
    {
        $this->vcs->setShellRunner($shell);

        return $this;
    }

    /**
     * Sets input options.
     *
     * @param array $options
     * @return $this
     */
    public function setOptions(array $options = null)
    {
        $this->vcs->setOptions($options);

        return $this;
    }

    /**
     * Sets input arguments.
     *
     * @param array $arguments
     * @return $this
     */
    public function setArguments(array $arguments = null)
    {
        $this->vcs->setArguments($arguments);

        return $this;
    }

    /**
     * Returns the date of the latter (--to) commit, in the format YYYY-MM-DD.
     *
     * @return string
     */
    public function getToDate(){
        return $this->vcs->getToDate();
    }
}
