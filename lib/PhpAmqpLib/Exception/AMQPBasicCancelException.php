<?php
namespace PhpAmqpLib\Exception;

class AMQPBasicCancelException extends \Exception implements AMQPExceptionInterface
{
    /** @var string */
    public $consumerTag;

    /**
     * @param string $consumerTag
     */
    public function __construct($consumerTag)
    {
        $this->consumerTag = $consumerTag;
    }
}
