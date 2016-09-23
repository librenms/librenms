<?php
function q_bridge_bits2indices($hex_data)
{
    /* convert hex string to an array of 1-based indices of the nonzero bits
     * ie. '9a00' -> '100110100000' -> array(1, 4, 5, 7)
     */
    $value = hex2bin($hex_data);
    $length = strlen($value);
    $indices = array();
    for ($i = 0; $i < $length; $i++) {
        $byte = ord($value[$i]);
        for ($j = 7; $j >= 0; $j--) {
            if ($byte & (1 << $j)) {
                $indices[] = 8*$i + 8-$j;
            }
        }
    }
    return $indices;
}
