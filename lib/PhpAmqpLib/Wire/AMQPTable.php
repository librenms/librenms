<?php
namespace PhpAmqpLib\Wire;

use PhpAmqpLib\Exception;


class AMQPTable extends AMQPAbstractCollection
{

    /**
     * @return int
     */
    final public function getType()
    {
        return self::T_TABLE;
    }

    /**
     * @param string $key
     * @param mixed $val
     * @param integer $type
     */
    public function set($key, $val, $type = null)
    {
        //https://www.rabbitmq.com/resources/specs/amqp0-9-1.pdf, https://www.rabbitmq.com/resources/specs/amqp0-8.pdf
        //Field names MUST start with a letter, '$' or '#' and may continue with letters, '$' or '#', digits, or underlines, to a maximum length of 128 characters
        //The server SHOULD validate field names and upon receiving an invalid field name, it SHOULD signal a connection exception with reply code 503 (syntax error)

        //validating length only and delegating other stuff to server, as rabbit seems to currently support numeric keys
        if (!($len = strlen($key)) || ($len > 128)) {
            throw new Exception\AMQPInvalidArgumentException('Table key must be non-empty string up to 128 chars in length');
        }
        $this->setValue($val, $type, $key);
    }
}
