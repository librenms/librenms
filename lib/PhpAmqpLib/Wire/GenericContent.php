<?php
namespace PhpAmqpLib\Wire;

use PhpAmqpLib\Channel\AMQPChannel;

/**
 * Abstract base class for AMQP content.  Subclasses should override
 * the PROPERTIES attribute.
 */
abstract class GenericContent
{
    /** @var AMQPChannel[] */
    public $delivery_info = array();

    /** @var array Final property definitions */
    protected $prop_types;

    /** @var array Properties content */
    private $properties = array();

    /** @var string Compiled properties */
    private $serialized_properties;

    /**
     * @var array
     */
    protected static $PROPERTIES = array(
        'dummy' => 'shortstr'
    );

    /**
     * @param $props
     * @param null $prop_types
     */
    public function __construct($props, $prop_types = null)
    {
        if ($prop_types) {
            $this->prop_types = $prop_types;
        } else {
            $this->prop_types = self::$PROPERTIES;
        }

        if ($props) {
            $this->properties = array_intersect_key($props, $this->prop_types);
        }
    }

    /**
     * Check whether a property exists in the 'properties' dictionary
     * or if present - in the 'delivery_info' dictionary.
     *
     * @param string $name
     * @return bool
     */
    public function has($name)
    {
        return isset($this->properties[$name]) || isset($this->delivery_info[$name]);
    }

    /**
     * Look for additional properties in the 'properties' dictionary,
     * and if present - the 'delivery_info' dictionary.
     *
     * @param string $name
     * @throws \OutOfBoundsException
     * @return mixed|AMQPChannel
     */
    public function get($name)
    {
        if (isset($this->properties[$name])) {
            return $this->properties[$name];
        }

        if (isset($this->delivery_info[$name])) {
            return $this->delivery_info[$name];
        }

        throw new \OutOfBoundsException(sprintf(
            'No "%s" property',
            $name
        ));
    }

    /**
     * Returns the properties content
     *
     * @return array
     */
    public function get_properties()
    {
        return $this->properties;
    }

    /**
     * Sets a property value
     *
     * @param string $name The property name (one of the property definition)
     * @param mixed $value The property value
     * @throws \OutOfBoundsException
     */
    public function set($name, $value)
    {
        if (!array_key_exists($name, $this->prop_types)) {
            throw new \OutOfBoundsException(sprintf(
                'No "%s" property',
                $name
            ));
        }

        $this->properties[$name] = $value;
    }

    /**
     * Given the raw bytes containing the property-flags and
     * property-list from a content-frame-header, parse and insert
     * into a dictionary stored in this object as an attribute named
     * 'properties'.
     *
     * @param AMQPReader $r
     * NOTE: do not mutate $reader
     */
    public function load_properties($r)
    {
        // Read 16-bit shorts until we get one with a low bit set to zero
        $flags = array();
        while (true) {
            $flag_bits = $r->read_short();
            $flags[] = $flag_bits;
            if (($flag_bits & 1) == 0) {
                break;
            }
        }

        $shift = 0;
        $d = array();
        foreach ($this->prop_types as $key => $proptype) {
            if ($shift == 0) {
                if (!$flags) {
                    break;
                }
                $flag_bits = array_shift($flags);
                $shift = 15;
            }
            if ($flag_bits & (1 << $shift)) {
                $d[$key] = $r->{'read_' . $proptype}();
            }

            $shift -= 1;
        }
        $this->properties = $d;
    }


    /**
     * Serializes the 'properties' attribute (a dictionary) into the
     * raw bytes making up a set of property flags and a property
     * list, suitable for putting into a content frame header.
     *
     * @return string
     * @todo Inject the AMQPWriter to make the method easier to test
     */
    public function serialize_properties()
    {
        if (!empty($this->serialized_properties)) {
            return $this->serialized_properties;
        }

        $shift = 15;
        $flag_bits = 0;
        $flags = array();
        $raw_bytes = new AMQPWriter();

        foreach ($this->prop_types as $key => $prototype) {
            $val = isset($this->properties[$key]) ? $this->properties[$key] : null;

            // Very important: PHP type eval is weak, use the === to test the
            // value content. Zero or false value should not be removed
            if ($val === null) {
                $shift -= 1;
                continue;
            }

            if ($shift === 0) {
                $flags[] = $flag_bits;
                $flag_bits = 0;
                $shift = 15;
            }

            $flag_bits |= (1 << $shift);
            if ($prototype != 'bit') {
                $raw_bytes->{'write_' . $prototype}($val);
            }

            $shift -= 1;
        }

        $flags[] = $flag_bits;
        $result = new AMQPWriter();
        foreach ($flags as $flag_bits) {
            $result->write_short($flag_bits);
        }

        $result->write($raw_bytes->getvalue());

        $this->serialized_properties = $result->getvalue();

        return $this->serialized_properties;
    }
}
