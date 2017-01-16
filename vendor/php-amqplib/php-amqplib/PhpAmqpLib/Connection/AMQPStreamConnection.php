<?php

namespace PhpAmqpLib\Connection;

use PhpAmqpLib\Wire\IO\StreamIO;

class AMQPStreamConnection extends AbstractConnection
{
    protected $sock = null;

    public function __construct($host, $port,
                                $user, $password,
                                $vhost="/",$insist=false,
                                $login_method="AMQPLAIN",
                                $login_response=null,
                                $locale="en_US",
                                $connection_timeout = 3,
                                $read_write_timeout = 3,
                                $context = null)
    {
        $io = new StreamIO($host, $port, $connection_timeout, $read_write_timeout, $context);
        $this->sock = $io->get_socket();

        parent::__construct($user, $password, $vhost, $insist, $login_method, $login_response, $locale, $io);
    }

    /**
     * get socket from current connection
     *
     * @deprecated
     */
    public function getSocket()
    {
        return $this->sock;
    }

}
