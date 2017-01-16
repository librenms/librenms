<?php

namespace PhpAmqpLib\Wire\IO;

abstract class AbstractIO
{
    abstract public function read($n);

    abstract public function write($data);

    abstract public function close();

    abstract public function select($sec, $usec);
}