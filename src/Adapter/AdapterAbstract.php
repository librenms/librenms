<?php
namespace InfluxDB\Adapter;

use InfluxDB\Options;

abstract class AdapterAbstract implements AdapterInterface
{
    private $options;

    /**
     * @param Options $options
     */
    public function __construct(Options $options)
    {
        $this->options = $options;
    }

    /**
     * @return Options
     */
    public function getOptions()
    {
        return $this->options;
    }

    protected function getMessageDefaults()
    {
        return [
            "database" => $this->getOptions()->getDatabase(),
            "retentionPolicy" => $this->getOptions()->getRetentionPolicy(),
            "tags" => $this->getOptions()->getTags(),
        ];
    }

    abstract public function send(array $message);
}
