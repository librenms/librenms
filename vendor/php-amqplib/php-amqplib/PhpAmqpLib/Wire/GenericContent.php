<?php

namespace PhpAmqpLib\Wire;

use PhpAmqpLib\Wire\AMQPReader;
use PhpAmqpLib\Wire\AMQPWriter;

/**
 * Abstract base class for AMQP content.  Subclasses should override
 * the PROPERTIES attribute.
 */
abstract class GenericContent
{
    public $delivery_info = array();
    private $properties = array();

    protected static $PROPERTIES = array(
        "dummy" => "shortstr"
    );

    public function __construct($props, $prop_types=null)
    {
        if($prop_types)
            $this->prop_types = $prop_types;
        else
            $this->prop_types = self::$PROPERTIES;
        $d = array();
        if ($props)
            $d = array_intersect_key($props, $this->prop_types);
        else
            $d = array();
        $this->properties = $d;
    }

    /**
     * Check wheter a property exists in the 'properties' dictionary
     * or if present - in the 'delivery_info' dictionary.
     */
    public function has($name)
    {
        return isset($this->properties[$name]) || isset($this->delivery_info[$name]);
    }

    /**
     * Look for additional properties in the 'properties' dictionary,
     * and if present - the 'delivery_info' dictionary.
     */
    public function get($name)
    {
        if(isset($this->properties[$name])) {
            return $this->properties[$name];
        }
        if(isset($this->delivery_info[$name])) {
            return $this->delivery_info[$name];
        }

        throw new \OutOfBoundsException("No '$name' property");
    }

    /**
     * allows to set the property after creation of the object
     */
    public function set($name, $value)
    {
        if(array_key_exists($name, $this->prop_types))
            $this->properties[$name] = $value;
        else
            throw new \OutOfBoundsException("No '$name' property");
    }
    /**
     * Given the raw bytes containing the property-flags and
     * property-list from a content-frame-header, parse and insert
     * into a dictionary stored in this object as an attribute named
     * 'properties'.
     */
    public function load_properties($raw_bytes)
    {
        $r = new AMQPReader($raw_bytes);

        // Read 16-bit shorts until we get one with a low bit set to zero
        $flags = array();
        while (true) {
            $flag_bits = $r->read_short();
            $flags[] = $flag_bits;
            if(($flag_bits & 1) == 0)
                break;
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
                $d[$key] = $r->{'read_'.$proptype}();
            }

            $shift -= 1;
        }
        $this->properties = $d;
    }

    /**
     * serialize the 'properties' attribute (a dictionary) into the
     * raw bytes making up a set of property flags and a property
     * list, suitable for putting into a content frame header.
     */
    public function serialize_properties()
    {
        $shift = 15;
        $flag_bits = 0;
        $flags = array();
        $raw_bytes = new AMQPWriter();

        foreach ($this->prop_types as $key => $proptype) {
            if (isset($this->properties[$key])) {
                $val = $this->properties[$key];
            } else {
                $val = null;
            }

            if ($val != null) {
                if ($shift == 0) {
                    $flags[] = $flag_bits;
                    $flag_bits = 0;
                    $shift = 15;
                }

                $flag_bits |= (1 << $shift);
                if ($proptype != "bit") {
                    $raw_bytes->{'write_'.$proptype}($val);
                }

            }
            $shift -= 1;
        }

        $flags[] = $flag_bits;
        $result = new AMQPWriter();
        foreach ($flags as $flag_bits) {
            $result->write_short($flag_bits);
        }

        $result->write($raw_bytes->getvalue());

        return $result->getvalue();
    }
}
