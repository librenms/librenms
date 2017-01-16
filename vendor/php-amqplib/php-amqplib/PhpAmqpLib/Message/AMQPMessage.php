<?php

namespace PhpAmqpLib\Message;

use PhpAmqpLib\Wire\GenericContent;

/**
 * A Message for use with the Channnel.basic_* methods.
 */
class AMQPMessage extends GenericContent
{
    public $body;

    protected static $PROPERTIES = array(
        "content_type" => "shortstr",
        "content_encoding" => "shortstr",
        "application_headers" => "table",
        "delivery_mode" => "octet",
        "priority" => "octet",
        "correlation_id" => "shortstr",
        "reply_to" => "shortstr",
        "expiration" => "shortstr",
        "message_id" => "shortstr",
        "timestamp" => "timestamp",
        "type" => "shortstr",
        "user_id" => "shortstr",
        "app_id" => "shortstr",
        "cluster_id" => "shortstr"
    );

    public function __construct($body = '', $properties = null)
    {
        $this->body = $body;

        parent::__construct($properties, static::$PROPERTIES);
    }
}
