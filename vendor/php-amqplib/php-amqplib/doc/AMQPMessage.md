# AMQPMessage #

## Message Durability ##

When creating a new message set the `delivery_mode` to __2__:

    $msg = new AMQPMessage(
        $msg_body,
        array(
            'delivery_mode' = 2
        )
    );

## Supported message properties ##

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
    "cluster_id" => "shortst"


Getting message properties:

    $msg->get('correlation_id');
    $msg->get('delivery_mode');

## Acknowledging messages ##

You can acknowledge messages by sending a `basic_ack` on the channel:

    $msg->delivery_info['channel']->
        basic_ack($msg->delivery_info['delivery_tag']);

Keep in mind that the `delivery_tag` has to be valid so most of the time just use the one provide by the server.

If you don't want to access the `delivery_info` array directly you can also use `$msg->get('delivery_tag')` but keep in mind that's slower than just accessing the array by key.

## What's on the delivery info? ##

When RabbitMQ delivers a message the library will add the following `delivery_info` to the message:

    $delivery_info = array(
        "channel" => $this,
        "consumer_tag" => $consumer_tag,
        "delivery_tag" => $delivery_tag,
        "redelivered" => $redelivered,
        "exchange" => $exchange,
        "routing_key" => $routing_key
    );

They can also be accessed using the AMQPMessage::get function:

    $msg->get('channel');