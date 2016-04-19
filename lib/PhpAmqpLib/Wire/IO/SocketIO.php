<?php
namespace PhpAmqpLib\Wire\IO;

use PhpAmqpLib\Exception\AMQPIOException;
use PhpAmqpLib\Exception\AMQPRuntimeException;

class SocketIO extends AbstractIO
{
    /** @var string */
    protected $host;

    /** @var int */
    protected $port;

    /** @var int */
    protected $timeout;

    /** @var resource */
    private $sock;

    /** @var bool */
    private $keepalive;

    /**
     * @param string $host
     * @param int $port
     * @param int $timeout
     * @param bool $keepalive
     */
    public function __construct($host, $port, $timeout, $keepalive = false)
    {
        $this->host = $host;
        $this->port = $port;
        $this->timeout = $timeout;
        $this->keepalive = $keepalive;
    }

    /**
     * Sets up the socket connection
     *
     * @throws \Exception
     */
    public function connect()
    {
        $this->sock = socket_create(AF_INET, SOCK_STREAM, SOL_TCP);

        socket_set_option($this->sock, SOL_SOCKET, SO_RCVTIMEO, array('sec' => $this->timeout, 'usec' => 0));
        socket_set_option($this->sock, SOL_SOCKET, SO_SNDTIMEO, array('sec' => $this->timeout, 'usec' => 0));

        if (!socket_connect($this->sock, $this->host, $this->port)) {
            $errno = socket_last_error($this->sock);
            $errstr = socket_strerror($errno);
            throw new AMQPIOException(sprintf(
                'Error Connecting to server (%s): %s',
                $errno,
                $errstr
            ), $errno);
        }

        socket_set_block($this->sock);
        socket_set_option($this->sock, SOL_TCP, TCP_NODELAY, 1);

        if ($this->keepalive) {
            $this->enable_keepalive();
        }
    }

    /**
     * @return resource
     */
    public function getSocket()
    {
        return $this->sock;
    }

    /**
     * Reconnects the socket
     */
    public function reconnect()
    {
        $this->close();
        $this->connect();
    }

    /**
     * @param $n
     * @return mixed|string
     * @throws \PhpAmqpLib\Exception\AMQPIOException
     * @throws \PhpAmqpLib\Exception\AMQPRuntimeException
     */
    public function read($n)
    {
        $res = '';
        $read = 0;

        $buf = socket_read($this->sock, $n);
        while ($read < $n && $buf !== '' && $buf !== false) {
            // Null sockets are invalid, throw exception
            if (is_null($this->sock)) {
                throw new AMQPRuntimeException(sprintf(
                    'Socket was null! Last SocketError was: %s',
                    socket_strerror(socket_last_error())
                ));
            }

            $read += mb_strlen($buf, 'ASCII');
            $res .= $buf;
            $buf = socket_read($this->sock, $n - $read);
        }

        if (mb_strlen($res, 'ASCII') != $n) {
            throw new AMQPIOException(sprintf(
                'Error reading data. Received %s instead of expected %s bytes',
                mb_strlen($res, 'ASCII'),
                $n
            ));
        }

        return $res;
    }

    /**
     * @param $data
     * @return mixed|void
     * @throws \PhpAmqpLib\Exception\AMQPIOException
     * @throws \PhpAmqpLib\Exception\AMQPRuntimeException
     */
    public function write($data)
    {
        $len = mb_strlen($data, 'ASCII');

        while (true) {
            // Null sockets are invalid, throw exception
            if (is_null($this->sock)) {
                throw new AMQPRuntimeException(sprintf(
                    'Socket was null! Last SocketError was: %s',
                    socket_strerror(socket_last_error())
                ));
            }

            $sent = socket_write($this->sock, $data, $len);
            if ($sent === false) {
                throw new AMQPIOException(sprintf(
                    'Error sending data. Last SocketError: %s',
                    socket_strerror(socket_last_error())
                ));
            }

            // Check if the entire message has been sent
            if ($sent < $len) {
                // If not sent the entire message.
                // Get the part of the message that has not yet been sent as message
                $data = mb_substr($data, $sent, mb_strlen($data, 'ASCII') - $sent, 'ASCII');
                // Get the length of the not sent part
                $len -= $sent;
            } else {
                break;
            }
        }
    }

    public function close()
    {
        if (is_resource($this->sock)) {
            socket_close($this->sock);
        }
        $this->sock = null;
    }

    /**
     * @param $sec
     * @param $usec
     * @return int|mixed
     */
    public function select($sec, $usec)
    {
        $read = array($this->sock);
        $write = null;
        $except = null;

        return socket_select($read, $write, $except, $sec, $usec);
    }

    /**
     * @throws \PhpAmqpLib\Exception\AMQPIOException
     */
    protected function enable_keepalive()
    {
        if (!defined('SOL_SOCKET') || !defined('SO_KEEPALIVE')) {
            throw new AMQPIOException('Can not enable keepalive: SOL_SOCKET or SO_KEEPALIVE is not defined');
        }

        socket_set_option($this->sock, SOL_SOCKET, SO_KEEPALIVE, 1);
    }
}
