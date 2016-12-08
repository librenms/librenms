<?php
namespace PhpAmqpLib\Wire;

use PhpAmqpLib\Exception\AMQPInvalidArgumentException;
use PhpAmqpLib\Exception\AMQPOutOfBoundsException;

class AMQPWriter extends AbstractClient
{
    /** @var string */
    protected $out;

    /** @var array */
    protected $bits;

    /** @var int */
    protected $bitcount;

    public function __construct()
    {
        parent::__construct();

        $this->out = '';
        $this->bits = array();
        $this->bitcount = 0;
    }

    /**
     * Packs integer into raw byte string in big-endian order
     * Supports positive and negative ints represented as PHP int or string (except scientific notation)
     *
     * Floats has some precision issues and so intentionally not supported.
     * Beware that floats out of PHP_INT_MAX range will be represented in scientific (exponential) notation when casted to string
     *
     * @param int|string $x Value to pack
     * @param int $bytes Must be multiply of 2
     * @return string
     */
    private static function packBigEndian($x, $bytes)
    {
        if (($bytes <= 0) || ($bytes % 2)) {
            throw new AMQPInvalidArgumentException(sprintf('Expected bytes count must be multiply of 2, %s given', $bytes));
        }

        $ox = $x; //purely for dbg purposes (overflow exception)
        $isNeg = false;
        if (is_int($x)) {
            if ($x < 0) {
                $isNeg = true;
                $x = abs($x);
            }
        } elseif (is_string($x)) {
            if (!is_numeric($x)) {
                throw new AMQPInvalidArgumentException(sprintf('Unknown numeric string format: %s', $x));
            }
            $x = preg_replace('/^-/', '', $x, 1, $isNeg);
        } else {
            throw new AMQPInvalidArgumentException('Only integer and numeric string values are supported');
        }
        if ($isNeg) {
            $x = bcadd($x, -1, 0);
        } //in negative domain starting point is -1, not 0

        $res = array();
        for ($b = 0; $b < $bytes; $b += 2) {
            $chnk = (int) bcmod($x, 65536);
            $x = bcdiv($x, 65536, 0);
            $res[] = pack('n', $isNeg ? ~$chnk : $chnk);
        }
        if ($x || ($isNeg && ($chnk & 0x8000))) {
            throw new AMQPOutOfBoundsException(sprintf('Overflow detected while attempting to pack %s into %s bytes', $ox, $bytes));
        }

        return implode(array_reverse($res));
    }

    private function flushbits()
    {
        if (!empty($this->bits)) {
            $this->out .= implode('', array_map('chr', $this->bits));
            $this->bits = array();
            $this->bitcount = 0;
        }
    }

    /**
     * Get what's been encoded so far.
     */
    public function getvalue()
    {
        /* temporarily needed for compatibility with write_bit unit tests */
        if ($this->bitcount) {
            $this->flushbits();
        }

        return $this->out;
    }

    /**
     * Write a plain PHP string, with no special encoding.
     */
    public function write($s)
    {
        $this->out .= $s;

        return $this;
    }

    /**
     * Write a boolean value.
     * (deprecated, use write_bits instead)
     *
     * @deprecated
     * @param $b
     * @return $this
     */
    public function write_bit($b)
    {
        $b = (int) (bool) $b;
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
     * Write multiple bits as an octet
     *
     * @param $bits
     * @return $this
     */
    public function write_bits($bits)
    {
        $value = 0;

        foreach ($bits as $n => $bit) {
            $bit = $bit ? 1 : 0;
            $value |= ($bit << $n);
        }

        $this->out .= chr($value);

        return $this;
    }

    /**
     * Write an integer as an unsigned 8-bit value
     *
     * @param $n
     * @return $this
     * @throws \PhpAmqpLib\Exception\AMQPInvalidArgumentException
     */
    public function write_octet($n)
    {
        if ($n < 0 || $n > 255) {
            throw new AMQPInvalidArgumentException('Octet out of range: ' . $n);
        }

        $this->out .= chr($n);

        return $this;
    }

    public function write_signed_octet($n)
    {
        if (($n < -128) || ($n > 127)) {
            throw new AMQPInvalidArgumentException('Signed octet out of range: ' . $n);
        }

        $this->out .= pack('c', $n);

        return $this;
    }

    /**
     * Write an integer as an unsigned 16-bit value
     *
     * @param $n
     * @return $this
     * @throws \PhpAmqpLib\Exception\AMQPInvalidArgumentException
     */
    public function write_short($n)
    {
        if ($n < 0 || $n > 65535) {
            throw new AMQPInvalidArgumentException('Short out of range: ' . $n);
        }

        $this->out .= pack('n', $n);

        return $this;
    }

    public function write_signed_short($n)
    {
        if (($n < -32768) || ($n > 32767)) {
            throw new AMQPInvalidArgumentException('Signed short out of range: ' . $n);
        }

        $this->out .= $this->correctEndianness(pack('s', $n));

        return $this;
    }

    /**
     * Write an integer as an unsigned 32-bit value
     *
     * @param $n
     * @return $this
     */
    public function write_long($n)
    {
        if (($n < 0) || ($n > 4294967295)) {
            throw new AMQPInvalidArgumentException('Long out of range: ' . $n);
        }

        //Numeric strings >PHP_INT_MAX on 32bit are casted to PHP_INT_MAX, damn PHP
        if (!$this->is64bits && is_string($n)) {
            $n = (float) $n;
        }
        $this->out .= pack('N', $n);

        return $this;
    }

    /**
     * @param $n
     * @return $this
     */
    private function write_signed_long($n)
    {
        if (($n < -2147483648) || ($n > 2147483647)) {
            throw new AMQPInvalidArgumentException('Signed long out of range: ' . $n);
        }

        //on my 64bit debian this approach is slightly faster than splitIntoQuads()
        $this->out .= $this->correctEndianness(pack('l', $n));

        return $this;
    }

    /**
     * Write an integer as an unsigned 64-bit value
     *
     * @param $n
     * @return $this
     */
    public function write_longlong($n)
    {
        if ($n < 0) {
            throw new AMQPInvalidArgumentException('Longlong out of range: ' . $n);
        }

        // if PHP_INT_MAX is big enough for that
        // direct $n<=PHP_INT_MAX check is unreliable on 64bit (values close to max) due to limited float precision
        if (bcadd($n, -PHP_INT_MAX, 0) <= 0) {
            // trick explained in http://www.php.net/manual/fr/function.pack.php#109328
            if ($this->is64bits) {
                list($hi, $lo) = $this->splitIntoQuads($n);
            } else {
                $hi = 0;
                $lo = $n;
            } //on 32bits hi quad is 0 a priori
            $this->out .= pack('NN', $hi, $lo);
        } else {
            try {
                $this->out .= self::packBigEndian($n, 8);
            } catch (AMQPOutOfBoundsException $ex) {
                throw new AMQPInvalidArgumentException('Longlong out of range: ' . $n, 0, $ex);
            }
        }

        return $this;
    }

    public function write_signed_longlong($n)
    {
        if ((bcadd($n, PHP_INT_MAX, 0) >= -1) && (bcadd($n, -PHP_INT_MAX, 0) <= 0)) {
            if ($this->is64bits) {
                list($hi, $lo) = $this->splitIntoQuads($n);
            } else {
                $hi = $n < 0 ? -1 : 0;
                $lo = $n;
            } //0xffffffff for negatives
            $this->out .= pack('NN', $hi, $lo);
        } elseif ($this->is64bits) {
            throw new AMQPInvalidArgumentException('Signed longlong out of range: ' . $n);
        } else {
            if (bcadd($n, '-9223372036854775807', 0) > 0) {
                throw new AMQPInvalidArgumentException('Signed longlong out of range: ' . $n);
            }
            try {
                //will catch only negative overflow, as values >9223372036854775807 are valid for 8bytes range (unsigned)
                $this->out .= self::packBigEndian($n, 8);
            } catch (AMQPOutOfBoundsException $ex) {
                throw new AMQPInvalidArgumentException('Signed longlong out of range: ' . $n, 0, $ex);
            }
        }

        return $this;
    }

    /**
     * @param int $n
     * @return array
     */
    private function splitIntoQuads($n)
    {
        $n = (int) $n;

        return array($n >> 32, $n & 0x00000000ffffffff);
    }

    /**
     * Write a string up to 255 bytes long after encoding.
     * Assume UTF-8 encoding
     *
     * @param $s
     * @return $this
     * @throws \PhpAmqpLib\Exception\AMQPInvalidArgumentException
     */
    public function write_shortstr($s)
    {
        $len = mb_strlen($s, 'ASCII');
        if ($len > 255) {
            throw new AMQPInvalidArgumentException('String too long');
        }

        $this->write_octet($len);
        $this->out .= $s;

        return $this;
    }

    /**
     * Write a string up to 2**32 bytes long.  Assume UTF-8 encoding
     *
     * @param $s
     * @return $this
     */
    public function write_longstr($s)
    {
        $this->write_long(mb_strlen($s, 'ASCII'));
        $this->out .= $s;

        return $this;
    }

    /**
     * Supports the writing of Array types, so that you can implement
     * array methods, like Rabbitmq's HA parameters
     *
     * @param AMQPArray|array $a Instance of AMQPArray or PHP array WITHOUT format hints (unlike write_table())
     * @return self
     */
    public function write_array($a)
    {
        if (!($a instanceof AMQPArray)) {
            $a = new AMQPArray($a);
        }
        $data = new AMQPWriter();

        foreach ($a as $v) {
            $data->write_value($v[0], $v[1]);
        }

        $data = $data->getvalue();
        $this->write_long(mb_strlen($data, 'ASCII'));
        $this->write($data);

        return $this;
    }

    /**
     * Write unix time_t value as 64 bit timestamp
     *
     * @param $v
     * @return $this
     */
    public function write_timestamp($v)
    {
        $this->write_longlong($v);

        return $this;
    }

    /**
     * Write PHP array, as table. Input array format: keys are strings,
     * values are (type,value) tuples.
     *
     * @param AMQPTable|array $d Instance of AMQPTable or PHP array WITH format hints (unlike write_array())
     * @return self
     * @throws \PhpAmqpLib\Exception\AMQPInvalidArgumentException
     */
    public function write_table($d)
    {
        $typeIsSym = !($d instanceof AMQPTable); //purely for back-compat purposes

        $table_data = new AMQPWriter();
        foreach ($d as $k => $va) {
            list($ftype, $v) = $va;
            $table_data->write_shortstr($k);
            $table_data->write_value($typeIsSym ? AMQPAbstractCollection::getDataTypeForSymbol($ftype) : $ftype, $v);
        }

        $table_data = $table_data->getvalue();
        $this->write_long(mb_strlen($table_data, 'ASCII'));
        $this->write($table_data);

        return $this;
    }

    /**
     * for compat with method mapping used by AMQPMessage
     */
    public function write_table_object($d)
    {
        return $this->write_table($d);
    }

    /**
     * @param int $type One of AMQPAbstractCollection::T_* constants
     * @param mixed $val
     */
    private function write_value($type, $val)
    {
        //This will find appropriate symbol for given data type for currently selected protocol
        //Also will raise an exception on unknown type
        $this->write(AMQPAbstractCollection::getSymbolForDataType($type));

        switch ($type) {
            case AMQPAbstractCollection::T_INT_SHORTSHORT:
                $this->write_signed_octet($val);
                break;
            case AMQPAbstractCollection::T_INT_SHORTSHORT_U:
                $this->write_octet($val);
                break;
            case AMQPAbstractCollection::T_INT_SHORT:
                $this->write_signed_short($val);
                break;
            case AMQPAbstractCollection::T_INT_SHORT_U:
                $this->write_short($val);
                break;
            case AMQPAbstractCollection::T_INT_LONG:
                $this->write_signed_long($val);
                break;
            case AMQPAbstractCollection::T_INT_LONG_U:
                $this->write_long($val);
                break;
            case AMQPAbstractCollection::T_INT_LONGLONG:
                $this->write_signed_longlong($val);
                break;
            case AMQPAbstractCollection::T_INT_LONGLONG_U:
                $this->write_longlong($val);
                break;
            case AMQPAbstractCollection::T_DECIMAL:
                $this->write_octet($val->e);
                $this->write_signed_long($val->n);
                break;
            case AMQPAbstractCollection::T_TIMESTAMP:
                $this->write_timestamp($val);
                break;
            case AMQPAbstractCollection::T_BOOL:
                $this->write_octet($val ? 1 : 0);
                break;
            case AMQPAbstractCollection::T_STRING_SHORT:
                $this->write_shortstr($val);
                break;
            case AMQPAbstractCollection::T_STRING_LONG:
                $this->write_longstr($val);
                break;
            case AMQPAbstractCollection::T_ARRAY:
                $this->write_array($val);
                break;
            case AMQPAbstractCollection::T_TABLE:
                $this->write_table($val);
                break;
            case AMQPAbstractCollection::T_VOID:
                break;
            default:
                throw new AMQPInvalidArgumentException(sprintf(
                    'Unsupported type "%s"',
                    $type
                ));
        }
    }
}
