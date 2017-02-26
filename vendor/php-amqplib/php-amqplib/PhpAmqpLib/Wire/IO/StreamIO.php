<?php

namespace PhpAmqpLib\Wire\IO;

use PhpAmqpLib\Exception\AMQPRuntimeException;
use PhpAmqpLib\Exception\AMQPTimeoutException;

class StreamIO extends AbstractIO
{
    private $sock = null;

    public function __construct($host, $port, $connection_timeout, $read_write_timeout, $context = null)
    {
        $errstr = $errno = null;
        $this->sock = null;

        //TODO clean up
        if ($context) {
            $remote = sprintf('ssl://%s:%s', $host, $port);
            $this->sock = @stream_socket_client($remote, $errno, $errstr, $connection_timeout, STREAM_CLIENT_CONNECT, $context);
        } else {
            $remote = sprintf('tcp://%s:%s', $host, $port);
            $this->sock = @stream_socket_client($remote, $errno, $errstr, $connection_timeout, STREAM_CLIENT_CONNECT);
        }

        if (!$this->sock) {
            throw new AMQPRuntimeException("Error Connecting to server($errno): $errstr ");
        }

        if(!stream_set_timeout($this->sock, $read_write_timeout)) {
            throw new \Exception ("Timeout could not be set");
        }

        stream_set_blocking($this->sock, 1);
    }

    public function read($n)
    {
        $res = '';
        $read = 0;

        while ($read < $n && !feof($this->sock) &&
            (false !== ($buf = fread($this->sock, $n - $read)))) {

            $read += strlen($buf);
            $res .= $buf;
        }

        if (strlen($res)!=$n) {
            throw new AMQPRuntimeException("Error reading data. Received " .
                strlen($res) . " instead of expected $n bytes");
        }

        return $res;
    }

    public function write($data)
    {
        $len = strlen($data);
        while (true) {
            if (false === ($written = fwrite($this->sock, $data))) {
                throw new AMQPRuntimeException("Error sending data");
            }
            if ($written === 0) {
                throw new AMQPRuntimeException("Broken pipe or closed connection");
            }

            // get status of socket to determine whether or not it has timed out
            $info = stream_get_meta_data($this->sock);
            if($info['timed_out']) {
                throw new AMQPTimeoutException("Error sending data. Socket connection timed out");
            }

            $len = $len - $written;
            if ($len > 0) {
                $data = substr($data,0-$len);
            } else {
                break;
            }
        }
    }

    public function close()
    {
        if (is_resource($this->sock)) {
            fclose($this->sock);
        }
        $this->sock = null;
    }

    public function get_socket()
    {
        return $this->sock;
    }

    public function select($sec, $usec)
    {
        $read   = array($this->sock);
        $write  = null;
        $except = null;
        return stream_select($read, $write, $except, $sec, $usec);
    }
}