<?php
namespace PhpAmqpLib\Wire;

use PhpAmqpLib\Exception\AMQPInvalidArgumentException;
use PhpAmqpLib\Exception\AMQPOutOfBoundsException;
use PhpAmqpLib\Exception\AMQPRuntimeException;
use PhpAmqpLib\Exception\AMQPTimeoutException;
use PhpAmqpLib\Helper\MiscHelper;
use PhpAmqpLib\Wire\IO\AbstractIO;

/**
 * This class can read from a string or from a stream
 *
 * TODO : split this class: AMQPStreamReader and a AMQPBufferReader
 */
class AMQPReader extends AbstractClient
{
    const BIT = 1;
    const OCTET = 1;
    const SHORTSTR = 1;
    const SHORT = 2;
    const LONG = 4;
    const SIGNED_LONG = 4;
    const READ_PHP_INT = 4; // use READ_ to avoid possible clashes with PHP
    const LONGLONG = 8;
    const TIMESTAMP = 8;

    /** @var string */
    protected $str;

    /** @var int */
    protected $str_length;

    /** @var int */
    protected $offset;

    /** @var int */
    protected $bitcount;

    /** @var bool */
    protected $is64bits;

    /** @var int */
    protected $timeout;

    /** @var int */
    protected $bits;

    /** @var \PhpAmqpLib\Wire\IO\AbstractIO */
    protected $io;

    /**
     * @param string $str
     * @param AbstractIO $io
     * @param int $timeout
     */
    public function __construct($str, AbstractIO $io = null, $timeout = 0)
    {
        parent::__construct();

        $this->str = $str;
        $this->str_length = mb_strlen($this->str, 'ASCII');
        $this->io = $io;
        $this->offset = 0;
        $this->bitcount = $this->bits = 0;
        $this->timeout = $timeout;
    }

    /**
     * Resets the object from the injected param
     *
     * Used to not need to create a new AMQPReader instance every time.
     * when we can just pass a string and reset the object state.
     * NOTE: since we are working with strings we don't need to pass an AbstractIO
     *       or a timeout.
     *
     * @param string $str
     */
    public function reuse($str)
    {
        $this->str = $str;
        $this->str_length = mb_strlen($this->str, 'ASCII');
        $this->offset = 0;
        $this->bitcount = $this->bits = 0;
    }

    /**
     * Closes the stream
     */
    public function close()
    {
        if ($this->io) {
            $this->io->close();
        }
    }

    /**
     * @param $n
     * @return string
     */
    public function read($n)
    {
        $this->bitcount = $this->bits = 0;

        return $this->rawread($n);
    }

    /**
     * Waits until some data is retrieved from the socket.
     *
     * AMQPTimeoutException can be raised if the timeout is set
     *
     * @throws \PhpAmqpLib\Exception\AMQPTimeoutException
     */
    protected function wait()
    {
        if ($this->timeout == 0) {
            return;
        }

        // wait ..
        list($sec, $usec) = MiscHelper::splitSecondsMicroseconds($this->timeout);
        $result = $this->io->select($sec, $usec);

        if ($result === false) {
            throw new AMQPRuntimeException('A network error occured while awaiting for incoming data');
        }

        if ($result === 0) {
            throw new AMQPTimeoutException(sprintf(
                'The connection timed out after %s sec while awaiting incoming data',
                $this->getTimeout()
            ));
        }
    }

    /**
     * @param $n
     * @return string
     * @throws \RuntimeException
     * @throws \PhpAmqpLib\Exception\AMQPRuntimeException
     */
    protected function rawread($n)
    {
        if ($this->io) {
            $this->wait();
            $res = $this->io->read($n);
            $this->offset += $n;
        } else {
            if ($this->str_length < $n) {
                throw new AMQPRuntimeException(sprintf(
                    'Error reading data. Requested %s bytes while string buffer has only %s',
                    $n,
                    $this->str_length
                ));
            }

            $res = mb_substr($this->str, 0, $n, 'ASCII');
            $this->str = mb_substr($this->str, $n, mb_strlen($this->str, 'ASCII') - $n, 'ASCII');
            $this->str_length -= $n;
            $this->offset += $n;
        }

        return $res;
    }

    /**
     * @return bool
     */
    public function read_bit()
    {
        if (!$this->bitcount) {
            $this->bits = ord($this->rawread(1));
            $this->bitcount = 8;
        }

        $result = ($this->bits & 1) == 1;
        $this->bits >>= 1;
        $this->bitcount -= 1;

        return $result;
    }

    /**
     * @return mixed
     */
    public function read_octet()
    {
        $this->bitcount = $this->bits = 0;
        list(, $res) = unpack('C', $this->rawread(1));

        return $res;
    }

    /**
     * @return mixed
     */
    public function read_signed_octet()
    {
        $this->bitcount = $this->bits = 0;
        list(, $res) = unpack('c', $this->rawread(1));

        return $res;
    }

    /**
     * @return mixed
     */
    public function read_short()
    {
        $this->bitcount = $this->bits = 0;
        list(, $res) = unpack('n', $this->rawread(2));

        return $res;
    }

    /**
     * @return mixed
     */
    public function read_signed_short()
    {
        $this->bitcount = $this->bits = 0;
        list(, $res) = unpack('s', $this->correctEndianness($this->rawread(2)));

        return $res;
    }

    /**
     * Reads 32 bit integer in big-endian byte order.
     *
     * On 64 bit systems it will return always unsigned int
     * value in 0..2^32 range.
     *
     * On 32 bit systems it will return signed int value in
     * -2^31...+2^31 range.
     *
     * Use with caution!
     */
    public function read_php_int()
    {
        list(, $res) = unpack('N', $this->rawread(4));
        if ($this->is64bits) {
            $sres = sprintf('%u', $res);
            return (int) $sres;
        } else {
            return $res;
        }
    }

    /**
     * PHP does not have unsigned 32 bit int,
     * so we return it as a string
     *
     * @return string
     */
    public function read_long()
    {
        $this->bitcount = $this->bits = 0;
        list(, $res) = unpack('N', $this->rawread(4));

        return !$this->is64bits && self::getLongMSB($res) ? sprintf('%u', $res) : $res;
    }

    /**
     * @return integer
     */
    private function read_signed_long()
    {
        $this->bitcount = $this->bits = 0;
        list(, $res) = unpack('l', $this->correctEndianness($this->rawread(4)));

        return $res;
    }

    /**
     * Even on 64 bit systems PHP integers are singed.
     * Since we need an unsigned value here we return it
     * as a string.
     *
     * @return string
     */
    public function read_longlong()
    {
        $this->bitcount = $this->bits = 0;

        list(, $hi, $lo) = unpack('N2', $this->rawread(8));
        $msb = self::getLongMSB($hi);

        if (!$this->is64bits) {
            if ($msb) {
                $hi = sprintf('%u', $hi);
            }
            if (self::getLongMSB($lo)) {
                $lo = sprintf('%u', $lo);
            }
        }

        return bcadd($this->is64bits && !$msb ? $hi << 32 : bcmul($hi, '4294967296', 0), $lo, 0);
    }

    /**
     * @return string
     */
    public function read_signed_longlong()
    {
        $this->bitcount = $this->bits = 0;

        list(, $hi, $lo) = unpack('N2', $this->rawread(8));

        if ($this->is64bits) {
            return bcadd($hi << 32, $lo, 0);
        } else {
            return bcadd(bcmul($hi, '4294967296', 0), self::getLongMSB($lo) ? sprintf('%u', $lo) : $lo, 0);
        }
    }

    /**
     * @param int $longInt
     * @return bool
     */
    private static function getLongMSB($longInt)
    {
        return (bool) ($longInt & 0x80000000);
    }

    /**
     * Read a utf-8 encoded string that's stored in up to
     * 255 bytes.  Return it decoded as a PHP unicode object.
     */
    public function read_shortstr()
    {
        $this->bitcount = $this->bits = 0;
        list(, $slen) = unpack('C', $this->rawread(1));

        return $this->rawread($slen);
    }

    /**
     * Read a string that's up to 2**32 bytes, the encoding
     * isn't specified in the AMQP spec, so just return it as
     * a plain PHP string.
     */
    public function read_longstr()
    {
        $this->bitcount = $this->bits = 0;
        $slen = $this->read_php_int();

        if ($slen < 0) {
            throw new AMQPOutOfBoundsException('Strings longer than supported on this platform');
        }

        return $this->rawread($slen);
    }

    /**
     * Read and AMQP timestamp, which is a 64-bit integer representing
     * seconds since the Unix epoch in 1-second resolution.
     */
    public function read_timestamp()
    {
        return $this->read_longlong();
    }

    /**
     * Read an AMQP table, and return as a PHP array. keys are strings,
     * values are (type,value) tuples.
     *
     * @param bool $returnObject Whether to return AMQPArray instance instead of plain array
     * @return array|AMQPTable
     */
    public function read_table($returnObject = false)
    {
        $this->bitcount = $this->bits = 0;
        $tlen = $this->read_php_int();

        if ($tlen < 0) {
            throw new AMQPOutOfBoundsException('Table is longer than supported');
        }

        $table_data = new AMQPReader($this->rawread($tlen), null);
        $result = $returnObject ? new AMQPTable() : array();
        while ($table_data->tell() < $tlen) {
            $name = $table_data->read_shortstr();
            $ftype = AMQPAbstractCollection::getDataTypeForSymbol($ftypeSym = $table_data->rawread(1));
            $val = $table_data->read_value($ftype, $returnObject);
            $returnObject ? $result->set($name, $val, $ftype) : $result[$name] = array($ftypeSym, $val);
        }

        return $result;
    }

    /**
     * @return array|AMQPTable
     */
    public function read_table_object()
    {
        return $this->read_table(true);
    }

    /**
     * Reads the array in the next value.
     *
     * @param bool $returnObject Whether to return AMQPArray instance instead of plain array
     * @return array|AMQPArray
     */
    public function read_array($returnObject = false)
    {
        $this->bitcount = $this->bits = 0;

        // Determine array length and its end position
        $arrayLength = $this->read_php_int();
        $endOffset = $this->offset + $arrayLength;

        $result = $returnObject ? new AMQPArray() : array();
        // Read values until we reach the end of the array
        while ($this->offset < $endOffset) {
            $fieldType = AMQPAbstractCollection::getDataTypeForSymbol($this->rawread(1));
            $fieldValue = $this->read_value($fieldType, $returnObject);
            $returnObject ? $result->push($fieldValue, $fieldType) : $result[] = $fieldValue;
        }

        return $result;
    }

    /**
     * @return array|AMQPArray
     */
    public function read_array_object()
    {
        return $this->read_array(true);
    }

    /**
     * Reads the next value as the provided field type.
     *
     * @param int $fieldType One of AMQPAbstractCollection::T_* constants
     * @param bool $collectionsAsObjects Description
     * @return mixed
     * @throws \PhpAmqpLib\Exception\AMQPRuntimeException
     */
    public function read_value($fieldType, $collectionsAsObjects = false)
    {
        $this->bitcount = $this->bits = 0;

        $val = null;
        switch ($fieldType) {
            case AMQPAbstractCollection::T_INT_SHORTSHORT:
                //according to AMQP091 spec, 'b' is not bit, it is short-short-int, also valid for rabbit/qpid
                //$val=$this->read_bit();
                $val = $this->read_signed_octet();
                break;
            case AMQPAbstractCollection::T_INT_SHORTSHORT_U:
                $val = $this->read_octet();
                break;
            case AMQPAbstractCollection::T_INT_SHORT:
                $val = $this->read_signed_short();
                break;
            case AMQPAbstractCollection::T_INT_SHORT_U:
                $val = $this->read_short();
                break;
            case AMQPAbstractCollection::T_INT_LONG:
                $val = $this->read_signed_long();
                break;
            case AMQPAbstractCollection::T_INT_LONG_U:
                $val = $this->read_long();
                break;
            case AMQPAbstractCollection::T_INT_LONGLONG:
                $val = $this->read_signed_longlong();
                break;
            case AMQPAbstractCollection::T_INT_LONGLONG_U:
                $val = $this->read_longlong();
                break;
            case AMQPAbstractCollection::T_DECIMAL:
                $e = $this->read_octet();
                $n = $this->read_signed_long();
                $val = new AMQPDecimal($n, $e);
                break;
            case AMQPAbstractCollection::T_TIMESTAMP:
                $val = $this->read_timestamp();
                break;
            case AMQPAbstractCollection::T_BOOL:
                $val = $this->read_octet();
                break;
            case AMQPAbstractCollection::T_STRING_SHORT:
                $val = $this->read_shortstr();
                break;
            case AMQPAbstractCollection::T_STRING_LONG:
                $val = $this->read_longstr();
                break;
            case AMQPAbstractCollection::T_ARRAY:
                $val = $this->read_array($collectionsAsObjects);
                break;
            case AMQPAbstractCollection::T_TABLE:
                $val = $this->read_table($collectionsAsObjects);
                break;
            case AMQPAbstractCollection::T_VOID:
                $val = null;
                break;
            default:
                throw new AMQPInvalidArgumentException(sprintf(
                    'Unsupported type "%s"',
                    $fieldType
                ));
        }

        return $val;
    }

    /**
     * @return int
     */
    protected function tell()
    {
        return $this->offset;
    }

    /**
     * Sets the timeout (second)
     *
     * @param $timeout
     */
    public function setTimeout($timeout)
    {
        $this->timeout = $timeout;
    }

    /**
     * @return int
     */
    public function getTimeout()
    {
        return $this->timeout;
    }
}
