<?php
namespace InfluxDB\Adapter;

use InfluxDB\Options;
use InfluxDB\Adapter\WritableInterface;

abstract class AdapterAbstract implements WritableInterface
{
    private $options;

    public function __construct(Options $options)
    {
        $this->options = $options;
    }

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
