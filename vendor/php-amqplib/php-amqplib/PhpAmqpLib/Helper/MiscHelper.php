<?php

namespace PhpAmqpLib\Helper;

class MiscHelper
{
    public static function debug_msg($s)
    {
        echo $s, "\n";
    }

    public static function methodSig($a)
    {
        if (is_string($a)) {
            return $a;
        } else {
            return sprintf("%d,%d",$a[0] ,$a[1]);
        }
    }

    public static function saveBytes($bytes)
    {
        $fh = fopen('/tmp/bytes', 'wb');
        fwrite($fh, $bytes);
        fclose($fh);
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
     * @param string $data       The string to be dumped
     * @param bool   $htmloutput Set to false for non-HTML output
     * @param bool   $uppercase  Set to true for uppercase hex
     * @param bool   $return     Set to true to return the dump
     */
    public static function hexdump($data, $htmloutput = true, $uppercase = false, $return = false)
    {
        // Init
        $hexi   = '';
        $ascii  = '';
        $dump   = ($htmloutput === true) ? '<pre>' : '';
        $offset = 0;
        $len    = strlen($data);

        // Upper or lower case hexidecimal
        $x = ($uppercase === false) ? 'x' : 'X';

        // Iterate string
        for ($i = $j = 0; $i < $len; $i++) {
            // Convert to hexidecimal
            $hexi .= sprintf("%02$x ", ord($data[$i]));

            // Replace non-viewable bytes with '.'
            if (ord($data[$i]) >= 32) {
                $ascii .= ($htmloutput === true) ?
                                htmlentities($data[$i]) :
                                $data[$i];
            } else {
                $ascii .= '.';
            }

            // Add extra column spacing
            if ($j === 7) {
                $hexi  .= ' ';
                $ascii .= ' ';
            }

            // Add row
            if (++$j === 16 || $i === $len - 1) {
                // Join the hexi / ascii output
                $dump .= sprintf("%04$x  %-49s  %s", $offset, $hexi, $ascii);

                // Reset vars
                $hexi   = $ascii = '';
                $offset += 16;
                $j      = 0;

                // Add newline
                if ($i !== $len - 1) {
                    $dump .= "\n";
                }
            }
        }

        // Finish dump
        $dump .= $htmloutput === true ?
                    '</pre>' :
                    '';
        $dump .= "\n";

        // Output method
        if ($return === false) {
            echo $dump;
        } else {
            return $dump;
        }
    }
}
