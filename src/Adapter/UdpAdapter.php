<?php
namespace InfluxDB\Adapter;

use DateTime;

class UdpAdapter extends AdapterAbstract
{
    public function send(array $message)
    {
        $message = array_replace_recursive($this->getMessageDefaults(), $message);

        if (array_key_exists("tags", $message)) {
            $message["tags"] = array_replace_recursive($this->getOptions()->getTags(), $message["tags"]);
        }

        $message = message_to_inline_protocol($message);

        $this->write($message);
    }

    public function write($message)
    {
        // Create a handler in order to handle the 'Host is down' message
        set_error_handler(function() {
            // Suppress the error, this is the UDP adapter and if we can't send
            // it then we shouldn't inturrupt their application.
        });

        $socket = socket_create(AF_INET, SOCK_DGRAM, SOL_UDP);
        socket_sendto($socket, $message, strlen($message), 0, $this->getOptions()->getHost(), $this->getOptions()->getPort());
        socket_close($socket);

        // Remove our error handler.
        restore_error_handler();
    }
}
