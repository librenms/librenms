<?php
namespace PhpAmqpLib\Wire\IO;

abstract class AbstractIO
{
    /**
     * @param int $n
     * @return mixed
     */
    abstract public function read($n);

    /**
     * @param string $data
     * @return mixed
     */
    abstract public function write($data);

    /**
     * @return mixed
     */
    abstract public function close();

    /**
     * @param int $sec
     * @param int $usec
     * @return mixed
     */
    abstract public function select($sec, $usec);

    /**
     * @return mixed
     */
    abstract public function connect();

    /**
     * @return mixed
     */
    abstract public function reconnect();

    /**
     * @return mixed
     */
    abstract public function getSocket();
}
