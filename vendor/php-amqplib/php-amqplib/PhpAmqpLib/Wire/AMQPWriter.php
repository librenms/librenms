<?php

namespace PhpAmqpLib\Wire;

use PhpAmqpLib\Exception\AMQPOutOfBoundsException;

class AMQPWriter
{
    public function __construct()
    {
        $this->out = "";
        $this->bits = array();
        $this->bitcount = 0;
    }

    private static function chrbytesplit($x, $bytes)
    {
        return array_map('chr', AMQPWriter::bytesplit($x,$bytes));
    }

    /**
     * Splits number (could be either int or string) into array of byte
     * values (represented as integers) in big-endian byte order.
     */
    private static function bytesplit($x, $bytes)
    {
        if (is_int($x)) {
            if ($x<0) {
                $x = sprintf("%u", $x);
            }
        }

        $res = array();

        while ($bytes > 0) {
            $b = bcmod($x,'256');
            $res[] = (int) $b;
            $x = bcdiv($x,'256', 0);
            $bytes--;
        }

        $res = array_reverse($res);

        if ($x!=0) {
            throw new AMQPOutOfBoundsException("Value too big!");
        }

        return $res;
    }

    private function flushbits()
    {
        if (!empty($this->bits)) {
            $this->out .= implode("", array_map('chr', $this->bits));
            $this->bits = array();
            $this->bitcount = 0;
        }
    }

    /**
     * Get what's been encoded so far.
     */
    public function getvalue()
    {
        $this->flushbits();

        return $this->out;
    }

    /**
     * Write a plain PHP string, with no special encoding.
     */
    public function write($s)
    {
        $this->flushbits();
        $this->out .= $s;

        return $this;
    }

    /**
     * Write a boolean value.
     */
    public function write_bit($b)
    {
        if ($b) {
            $b = 1;
        } else {
            $b = 0;
        }

        $shift = $this->bitcount % 8;

        if ($shift == 0) {
            $last = 0;
        } else {
            $last = array_pop($this->bits);
        }

        $last |= ($b << $shift);
        array_push($this->bits, $last);

        $this->bitcount += 1;

        return $this;
    }

    /**
     * Write an integer as an unsigned 8-bit value.
     */
    public function write_octet($n)
    {
        if ($n < 0 || $n > 255) {
            throw new \InvalidArgumentException('Octet out of range 0..255');
        }

        $this->flushbits();
        $this->out .= chr($n);

        return $this;
    }

    /**
     * Write an integer as an unsigned 16-bit value.
     */
    public function write_short($n)
    {
        if ($n < 0 ||  $n > 65535) {
            throw new \InvalidArgumentException('Octet out of range 0..65535');
        }

        $this->flushbits();
        $this->out .= pack('n', $n);

        return $this;
    }

    /**
     * Write an integer as an unsigned 32-bit value.
     */
    public function write_long($n)
    {
        $this->flushbits();
        $this->out .= implode("", AMQPWriter::chrbytesplit($n,4));

        return $this;
    }

    private function write_signed_long($n)
    {
        $this->flushbits();
        // although format spec for 'N' mentions unsigned
        // it will deal with sinned integers as well. tested.
        $this->out .= pack('N', $n);

        return $this;
    }

    /**
     * Write an integer as an unsigned 64-bit value.
     */
    public function write_longlong($n)
    {
        $this->flushbits();
        $this->out .= implode("", AMQPWriter::chrbytesplit($n,8));

        return $this;
    }

    /**
     * Write a string up to 255 bytes long after encoding.
     * Assume UTF-8 encoding.
     */
    public function write_shortstr($s)
    {
        $this->flushbits();
        if (strlen($s) > 255) {
            throw new \InvalidArgumentException('String too long');
        }

        $this->write_octet(strlen($s));
        $this->out .= $s;

        return $this;
    }


    /*
     * Write a string up to 2**32 bytes long.  Assume UTF-8 encoding.
     */
    public function write_longstr($s)
    {
        $this->flushbits();
        $this->write_long(strlen($s));
        $this->out .= $s;

        return $this;
    }

    /**
     * Supports the writing of Array types, so that you can implement
     * array methods, like Rabbitmq's HA parameters
     *
     * @param array $a
     *
     * @return self
     */
    public function write_array($a)
    {
        $this->flushbits();
        $data = new AMQPWriter();

        foreach ($a as $v) {
            if (is_string($v)) {
                $data->write('S');
                $data->write_longstr($v);
            } elseif (is_int($v)) {
                $data->write('I');
                $data->write_signed_long($v);
            } elseif ($v instanceof AMQPDecimal) {
                $data->write('D');
                $data->write_octet($v->e);
                $data->write_signed_long($v->n);
            } elseif (is_array($v)) {
                $data->write('A');
                $data->write_array($v);
            }
        }

        $data = $data->getvalue();
        $this->write_long(strlen($data));
        $this->write($data);

        return $this;
    }

    /**
     * Write unix time_t value as 64 bit timestamp.
     */
   public function write_timestamp($v)
   {
       $this->write_longlong($v);

       return $this;
   }

   /**
    * Write PHP array, as table. Input array format: keys are strings,
    * values are (type,value) tuples.
    */
    public function write_table($d)
    {
        $this->flushbits();
        $table_data = new AMQPWriter();
        foreach ($d as $k=>$va) {
            list($ftype,$v) = $va;
            $table_data->write_shortstr($k);
            if ($ftype=='S') {
                $table_data->write('S');
                $table_data->write_longstr($v);
            } elseif ($ftype=='I') {
                $table_data->write('I');
                $table_data->write_signed_long($v);
            } elseif ($ftype=='D') {
                // 'D' type values are passed AMQPDecimal instances.
                $table_data->write('D');
                $table_data->write_octet($v->e);
                $table_data->write_signed_long($v->n);
            } elseif ($ftype=='T') {
                $table_data->write('T');
                $table_data->write_timestamp($v);
            } elseif ($ftype=='F') {
                $table_data->write('F');
                $table_data->write_table($v);
            } elseif ($ftype=='A') {
                $table_data->write('A');
                $table_data->write_array($v);
            } else {
                throw new \InvalidArgumentException(sprintf("Invalid type '%s'", $ftype));
            }
        }

        $table_data = $table_data->getvalue();
        $this->write_long(strlen($table_data));
        $this->write($table_data);

        return $this;
    }
}
