<?php
namespace PhpAmqpLib\Message;

use PhpAmqpLib\Wire\GenericContent;

/**
 * A Message for use with the Channnel.basic_* methods.
 */
class AMQPMessage extends GenericContent
{
    const DELIVERY_MODE_NON_PERSISTENT = 1;
    const DELIVERY_MODE_PERSISTENT = 2;

    /** @var string */
    public $body;

    /** @var int */
    public $body_size;

    /** @var bool */
    public $is_truncated = false;

    /** @var string */
    public $content_encoding;

    /** @var array */
    protected static $propertyDefinitions = array(
        'content_type' => 'shortstr',
        'content_encoding' => 'shortstr',
        'application_headers' => 'table_object',
        'delivery_mode' => 'octet',
        'priority' => 'octet',
        'correlation_id' => 'shortstr',
        'reply_to' => 'shortstr',
        'expiration' => 'shortstr',
        'message_id' => 'shortstr',
        'timestamp' => 'timestamp',
        'type' => 'shortstr',
        'user_id' => 'shortstr',
        'app_id' => 'shortstr',
        'cluster_id' => 'shortstr',
    );

    /**
     * @param string $body
     * @param array $properties
     */
    public function __construct($body = '', $properties = array())
    {
        $this->setBody($body);
        parent::__construct($properties, static::$propertyDefinitions);
    }

    /**
     * @return string
     */
    public function getBody()
    {
        return $this->body;
    }

    /**
     * Sets the message payload
     *
     * @param string $body
     * @return $this
     */
    public function setBody($body)
    {
        $this->body = $body;

        return $this;
    }

    /**
     * @return string
     */
    public function getContentEncoding()
    {
        return $this->content_encoding;
    }

    /**
     * @return int
     */
    public function getBodySize()
    {
        return $this->body_size;
    }

    /**
     * @param int $body_size Message body size in byte(s)
     * @return AMQPMessage
     */
    public function setBodySize($body_size)
    {
        $this->body_size = (int) $body_size;

        return $this;
    }

    /**
     * @return boolean
     */
    public function isTruncated()
    {
        return $this->is_truncated;
    }

    /**
     * @param bool $is_truncated
     * @return AMQPMessage
     */
    public function setIsTruncated($is_truncated)
    {
        $this->is_truncated = (bool) $is_truncated;

        return $this;
    }
}
