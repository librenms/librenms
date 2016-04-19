<?php
namespace PhpAmqpLib\Wire;

use PhpAmqpLib\Channel\AbstractChannel;
use PhpAmqpLib\Exception;


/**
 * Iterator implemented for transparent integration with AMQPWriter::write_[array|table]()
 */
abstract class AMQPAbstractCollection implements \Iterator
{

    //protocol defines available field types and their corresponding symbols
    const PROTOCOL_080 = AbstractChannel::PROTOCOL_080;
    const PROTOCOL_091 = AbstractChannel::PROTOCOL_091;
    const PROTOCOL_RBT = 'rabbit'; //pseudo proto

    //Abstract data types
    const T_INT_SHORTSHORT = 1;
    const T_INT_SHORTSHORT_U = 2;
    const T_INT_SHORT = 3;
    const T_INT_SHORT_U = 4;
    const T_INT_LONG = 5;
    const T_INT_LONG_U = 6;
    const T_INT_LONGLONG = 7;
    const T_INT_LONGLONG_U = 8;

    const T_DECIMAL = 9;
    const T_TIMESTAMP = 10;
    const T_VOID = 11;

    const T_BOOL = 12;

    const T_STRING_SHORT = 13;
    const T_STRING_LONG = 14;

    const T_ARRAY = 15;
    const T_TABLE = 16;

    /**
     * @var string
     */
    private static $_protocol = null;

    /*
     * Field types messy mess http://www.rabbitmq.com/amqp-0-9-1-errata.html#section_3
     * Default behaviour is to use rabbitMQ compatible field-set
     * Define AMQP_STRICT_FLD_TYPES=true to use strict AMQP instead
     */
    private static $_types_080 = array(
        self::T_INT_LONG => 'I',
        self::T_DECIMAL => 'D',
        self::T_TIMESTAMP => 'T',
        self::T_STRING_LONG => 'S',
        self::T_TABLE => 'F'
    );

    /**
     * @var array
     */
    private static $_types_091 = array(
        self::T_INT_SHORTSHORT => 'b',
        self::T_INT_SHORTSHORT_U => 'B',
        self::T_INT_SHORT => 'U',
        self::T_INT_SHORT_U => 'u',
        self::T_INT_LONG => 'I',
        self::T_INT_LONG_U => 'i',
        self::T_INT_LONGLONG => 'L',
        self::T_INT_LONGLONG_U => 'l',
        self::T_DECIMAL => 'D',
        self::T_TIMESTAMP => 'T',
        self::T_VOID => 'V',
        self::T_BOOL => 't',
        self::T_STRING_SHORT => 's',
        self::T_STRING_LONG => 'S',
        self::T_ARRAY => 'A',
        self::T_TABLE => 'F'
    );

    /**
     * @var array
     */
    private static $_types_rabbit = array(
        self::T_INT_SHORTSHORT => 'b',
        self::T_INT_SHORT => 's',
        self::T_INT_LONG => 'I',
        self::T_INT_LONGLONG => 'l',
        self::T_DECIMAL => 'D',
        self::T_TIMESTAMP => 'T',
        self::T_VOID => 'V',
        self::T_BOOL => 't',
        self::T_STRING_LONG => 'S',
        self::T_ARRAY => 'A',
        self::T_TABLE => 'F'
    );

    /**
     * @var array
     */
    protected $data = array();

    public function __construct(array $data = null)
    {
        if (!empty($data)) {
            $this->data = $this->encodeCollection($data);
        }
    }

    /**
     * @return int
     */
    abstract public function getType();

    /**
     * @param mixed $val
     * @param int $type
     * @param string $key
     */
    final protected function setValue($val, $type = null, $key = null)
    {
        if ($val instanceof self) {
            if ($type && ($type != $val->getType())) {
                throw new Exception\AMQPInvalidArgumentException(
                    'Attempted to add instance of ' . get_class($val) . ' representing type [' . $val->getType() . '] as mismatching type [' . $type . ']'
                );
            }
            $type = $val->getType();
        } elseif ($type) { //ensuring data integrity and that all members are properly validated
            switch ($type) {
                case self::T_ARRAY:
                    throw new Exception\AMQPInvalidArgumentException('Arrays must be passed as AMQPArray instance');
                    break;
                case self::T_TABLE:
                    throw new Exception\AMQPInvalidArgumentException('Tables must be passed as AMQPTable instance');
                    break;
                case self::T_DECIMAL:
                    if (!($val instanceof AMQPDecimal)) {
                        throw new Exception\AMQPInvalidArgumentException('Decimal values must be instance of AMQPDecimal');
                    }
                    break;
            }
        }

        if ($type) {
            self::checkDataTypeIsSupported($type, false);
            $val = array($type, $val);
        } else {
            $val = $this->encodeValue($val);
        }

        if ($key === null) {
            $this->data[] = $val;
        } else {
            $this->data[$key] = $val;
        }
    }

    /**
     * @return array
     */
    final public function getNativeData()
    {
        return $this->decodeCollection($this->data);
    }

    /**
     * @param array $val
     * @return array
     */
    final protected function encodeCollection(array $val)
    {
        foreach ($val as &$v) {
            $v = $this->encodeValue($v);
        }
        unset($v);

        return $val;
    }

    /**
     * @param array $val
     * @return array
     */
    final protected function decodeCollection(array $val)
    {
        foreach ($val as &$v) {
            $v = $this->decodeValue($v[1], $v[0]);
        }
        unset($v);

        return $val;
    }

    /**
     * @param mixed $val
     * @return mixed
     * @throws Exception\AMQPOutOfBoundsException
     */
    protected function encodeValue($val)
    {
        if (is_string($val)) {
            $val = $this->encodeString($val);
        } elseif (is_float($val)) {
            $val = $this->encodeFloat($val);
        } elseif (is_int($val)) {
            $val = $this->encodeInt($val);
        } elseif (is_bool($val)) {
            $val = $this->encodeBool($val);
        } elseif (is_null($val)) {
            $val = $this->encodeVoid();
        } elseif ($val instanceof \DateTime) {
            $val = array(self::T_TIMESTAMP, $val->getTimestamp());
        } elseif ($val instanceof AMQPDecimal) {
            $val = array(self::T_DECIMAL, $val);
        } elseif ($val instanceof self) {
            //avoid silent type correction of strictly typed values
            self::checkDataTypeIsSupported($val->getType(), false);
            $val = array($val->getType(), $val);
        } elseif (is_array($val)) {
            //AMQP specs says "Field names MUST start with a letter, '$' or '#'"
            //so beware, some servers may raise an exception with 503 code in cases when indexed array is encoded as table
            if (self::isProtocol(self::PROTOCOL_080)) {
                //080 doesn't support arrays, forcing table
                $val = array(self::T_TABLE, new AMQPTable($val));
            } elseif (empty($val) || (array_keys($val) === range(0, count($val) - 1))) {
                $val = array(self::T_ARRAY, new AMQPArray($val));
            } else {
                $val = array(self::T_TABLE, new AMQPTable($val));
            }
        } else {
            throw new Exception\AMQPOutOfBoundsException(sprintf('Encountered value of unsupported type: %s', gettype($val)));
        }

        return $val;
    }

    /**
     * @param mixed $val
     * @param integer $type
     * @return array|bool|\DateTime|null
     */
    protected function decodeValue($val, $type)
    {
        if ($val instanceof self) {
            //covering arrays and tables
            $val = $val->getNativeData();
        } else {
            switch ($type) {
                case self::T_BOOL:
                    $val = (bool) $val;
                    break;
                case self::T_TIMESTAMP:
                    $val = \DateTime::createFromFormat('U', $val);
                    break;
                case self::T_VOID:
                    $val = null;
                    break;
                case self::T_ARRAY:
                case self::T_TABLE:
                    throw new Exception\AMQPLogicException(
                        'Encountered an array/table struct which is not an instance of AMQPCollection. ' .
                        'This is considered a bug and should be fixed, please report'
                    );
            }
        }

        return $val;
    }

    /**
     * @param string $val
     * @return array
     */
    protected function encodeString($val)
    {
        return array(self::T_STRING_LONG, $val);
    }

    /**
     * @param int $val
     * @return array
     */
    protected function encodeInt($val)
    {
        if (($val >= -2147483648) && ($val <= 2147483647)) {
            $ev = array(self::T_INT_LONG, $val);
        } elseif (self::isProtocol(self::PROTOCOL_080)) {
            //080 doesn't support longlong
            $ev = $this->encodeString((string) $val);
        } else {
            $ev = array(self::T_INT_LONGLONG, $val);
        }

        return $ev;
    }

    /**
     * @param float $val
     * @return array
     */
    protected function encodeFloat($val)
    {
        return static::encodeString((string) $val);
    }

    /**
     * @param bool $val
     * @return array
     */
    protected function encodeBool($val)
    {
        $val = (bool) $val;

        return self::isProtocol(self::PROTOCOL_080) ? array(self::T_INT_LONG, (int) $val) : array(self::T_BOOL, $val);
    }

    /**
     * @return array
     */
    protected function encodeVoid()
    {
        return self::isProtocol(self::PROTOCOL_080) ? $this->encodeString('') : array(self::T_VOID, null);
    }

    /**
     * @return string
     */
    final public static function getProtocol()
    {
        if (self::$_protocol === null) {
            self::$_protocol = defined('AMQP_STRICT_FLD_TYPES') && AMQP_STRICT_FLD_TYPES ?
                AbstractChannel::getProtocolVersion() :
                self::PROTOCOL_RBT;
        }

        return self::$_protocol;
    }

    /**
     * @param string $proto
     * @return bool
     */
    final public static function isProtocol($proto)
    {
        return self::getProtocol() == $proto;
    }

    /**
     * @return array  [dataTypeConstant => dataTypeSymbol]
     */
    final public static function getSupportedDataTypes()
    {
        switch ($proto = self::getProtocol()) {
            case self::PROTOCOL_080:
                $types = self::$_types_080;
                break;
            case self::PROTOCOL_091:
                $types = self::$_types_091;
                break;
            case self::PROTOCOL_RBT:
                $types = self::$_types_rabbit;
                break;
            default:
                throw new Exception\AMQPOutOfRangeException(sprintf('Unknown protocol: %s', $proto));
        }

        return $types;
    }

    /**
     * @param string $type
     * @param bool $return Whether to return or raise AMQPOutOfRangeException
     * @return boolean
     */
    final public static function checkDataTypeIsSupported($type, $return = true)
    {
        try {
            $supported = self::getSupportedDataTypes();
            if (!isset($supported[$type])) {
                throw new Exception\AMQPOutOfRangeException(sprintf('AMQP-%s doesn\'t support data of type [%s]', self::getProtocol(), $type));
            }
            return true;

        } catch (Exception\AMQPOutOfRangeException $ex) {
            if (!$return) {
                throw $ex;
            }

            return false;
        }
    }

    /**
     * @param integer $type
     * @return string
     */
    final public static function getSymbolForDataType($type)
    {
        $types = self::getSupportedDataTypes();
        if (!isset($types[$type])) {
            throw new Exception\AMQPOutOfRangeException(sprintf('AMQP-%s doesn\'t support data of type [%s]', self::getProtocol(), $type));
        }

        return $types[$type];
    }

    /**
     * @param string $symbol
     * @return integer
     */
    final public static function getDataTypeForSymbol($symbol)
    {
        $symbols = array_flip(self::getSupportedDataTypes());
        if (!isset($symbols[$symbol])) {
            throw new Exception\AMQPOutOfRangeException(sprintf('AMQP-%s doesn\'t define data of type [%s]', self::getProtocol(), $symbol));
        }

        return $symbols[$symbol];
    }

    public function current()
    {
        return current($this->data);
    }

    public function key()
    {
        return key($this->data);
    }

    public function next()
    {
        next($this->data);
    }

    public function rewind()
    {
        reset($this->data);
    }

    public function valid()
    {
        return key($this->data) !== null;
    }
}
