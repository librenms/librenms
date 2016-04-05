<?php
namespace PhpAmqpLib\Wire;


class AbstractClient
{

    /**
     * @var bool
     */
    protected $is64bits;

    /**
     * @var bool
     */
    protected $isLittleEndian;

    public function __construct()
    {
        $this->is64bits = PHP_INT_SIZE == 8;

        $tmp = unpack('S', "\x01\x00"); // to maintain 5.3 compatibility
        $this->isLittleEndian = $tmp[1] == 1;
    }

    /**
     * Converts byte-string between native and network byte order, in both directions
     *
     * @param string $byteStr
     * @return string
     */
    protected function correctEndianness($byteStr)
    {
        return $this->isLittleEndian ? strrev($byteStr) : $byteStr;
    }
}
