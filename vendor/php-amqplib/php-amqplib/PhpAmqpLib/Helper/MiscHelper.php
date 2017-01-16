<?php
namespace PhpAmqpLib\Helper;

class MiscHelper
{
    /**
     * @param string|array $a
     * @return string
     */
    public static function methodSig($a)
    {
        if (is_string($a)) {
            return $a;
        }

        return sprintf('%d,%d', $a[0], $a[1]);
    }

    /**
     * @param string $bytes
     */
    public static function saveBytes($bytes)
    {
        $fh = fopen('/tmp/bytes', 'wb');
        fwrite($fh, $bytes);
        fclose($fh);
    }

    /**
     * Gets a number (either int or float) and returns an array containing its integer part as first element and its
     * decimal part mutliplied by 10^6. Useful for some PHP stream functions that need seconds and microseconds as
     * different arguments
     *
     * @param int|float $number
     * @return array
     */
    public static function splitSecondsMicroseconds($number)
    {
        return array(floor($number), ($number - floor($number)) * 1000000);
    }

    /**
     * View any string as a hexdump.
     *
     * This is most commonly used to view binary data from streams
     * or sockets while debugging, but can be used to view any string
     * with non-viewable characters.
     *
     * @version     1.3.2
     * @author      Aidan Lister <aidan@php.net>
     * @author      Peter Waller <iridum@php.net>
     * @link        http://aidanlister.com/repos/v/function.hexdump.php
     *
     * @param string $data The string to be dumped
     * @param bool $htmloutput Set to false for non-HTML output
     * @param bool $uppercase Set to true for uppercase hex
     * @param bool $return Set to true to return the dump
     * @return string|null
     */
    public static function hexdump($data, $htmloutput = true, $uppercase = false, $return = false)
    {
        // Init
        $hexi = '';
        $ascii = '';
        $dump = $htmloutput ? '<pre>' : '';
        $offset = 0;
        $len = mb_strlen($data, 'ASCII');

        // Upper or lower case hexidecimal
        $hexFormat = $uppercase ? 'X' : 'x';

        // Iterate string
        for ($i = $j = 0; $i < $len; $i++) {
            // Convert to hexidecimal
            // We must use concatenation here because the $hexFormat value
            // is needed for sprintf() to parse the format
            $hexi .= sprintf('%02' .  $hexFormat . ' ', ord($data[$i]));

            // Replace non-viewable bytes with '.'
            if (ord($data[$i]) >= 32) {
                $ascii .= $htmloutput ? htmlentities($data[$i]) : $data[$i];
            } else {
                $ascii .= '.';
            }

            // Add extra column spacing
            if ($j === 7) {
                $hexi .= ' ';
                $ascii .= ' ';
            }

            // Add row
            if (++$j === 16 || $i === $len - 1) {
                // Join the hexi / ascii output
                // We must use concatenation here because the $hexFormat value
                // is needed for sprintf() to parse the format
                $dump .= sprintf('%04' . $hexFormat . '  %-49s  %s', $offset, $hexi, $ascii);

                // Reset vars
                $hexi = $ascii = '';
                $offset += 16;
                $j = 0;

                // Add newline
                if ($i !== $len - 1) {
                    $dump .= PHP_EOL;
                }
            }
        }

        // Finish dump
        $dump .= $htmloutput ? '</pre>' : '';
        $dump .= PHP_EOL;

        if ($return) {
            return $dump;
        }

        echo $dump;
    }

    /**
     * @param array $table
     * @return string
     */
    public static function dump_table($table)
    {
        $tokens = array();
        foreach ($table as $name => $value) {
            switch ($value[0]) {
                case 'D':
                    $val = $value[1]->n . 'E' . $value[1]->e;
                    break;
                case 'F':
                    $val = '(' . self::dump_table($value[1]) . ')';
                    break;
                case 'T':
                    $val = date('Y-m-d H:i:s', $value[1]);
                    break;
                default:
                    $val = $value[1];
            }
            $tokens[] = $name . '=' . $val;
        }

        return implode(', ', $tokens);
    }
}
