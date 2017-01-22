<?php

namespace PhpAmqpLib\Tests\Unit;

use PhpAmqpLib\Wire\AMQPReader;
use PhpAmqpLib\Wire\AMQPWriter;

class WireTest extends \PHPUnit_Framework_TestCase
{
    public function testBitWriteRead()
    {
        $this->bitWriteRead(true);
        $this->bitWriteRead(false);
    }

    protected function bitWriteRead($v)
    {
        $this->writeAndRead($v, 'write_bit', 'read_bit');
    }

    public function testOctetWriteRead()
    {
        for ($i = 0; $i < 100; $i++) {
            $this->octetWriteRead(rand(0, 255));
        }
        $this->setExpectedException('InvalidArgumentException');
        $this->octetWriteRead(-1);
        $this->octetWriteRead(256);
    }

    protected function octetWriteRead($v)
    {
        $this->writeAndRead($v, 'write_octet', 'read_octet');
    }

    public function testShortWriteRead()
    {
        for ($i = 0; $i < 100; $i++) {
            $this->shortWriteRead(rand(0, 65535));
        }

        $this->setExpectedException('InvalidArgumentException');
        $this->shortWriteRead(-1);
        $this->shortWriteRead(65536);
    }

    protected function shortWriteRead($v)
    {
        $this->writeAndRead($v, 'write_short', 'read_short');
    }

    public function testLongWriteRead()
    {
        $max = 4294967295; //2^32-1
        for ($i = 0; $i < 100; $i++) {
            $this->longWriteRead(rand(0, $max));
        }
    }

    protected function longWriteRead($v)
    {
        $this->writeAndRead($v, 'write_long', 'read_long');
    }

    public function testShortstrWriteRead()
    {
        $this->shortstrWriteRead('a');
        $this->shortstrWriteRead('üıß∑œ´®†¥¨πøˆ¨¥†®');

        $this->setExpectedException('InvalidArgumentException');
        $this->shortstrWriteRead('abcdefghijklmnopqrstuvwxyzabcdefghijklmnopqrstuvwxyzabcdefghijklmnopqrstuvwxyzabcdefghijklmnopqrstuvwxyz
        abcdefghijklmnopqrstuvwxyzabcdefghijklmnopqrstuvwxyzabcdefghijklmnopqrstuvwxyzabcdefghijklmnopqrstuvwxyz
        abcdefghijklmnopqrstuvwxyzabcdefghijklmnopqrstuvwxyzabcdefghijklmnopqrstuvwxyzabcdefghijklmnopqrstuvwxyz');
    }

    protected function shortstrWriteRead($v)
    {
        $this->writeAndRead($v, 'write_shortstr', 'read_shortstr');
    }

    public function testLongstrWriteRead()
    {
        $this->longstrWriteRead('a');
        $this->longstrWriteRead('üıß∑œ´®†¥¨πøˆ¨¥†®');
        $this->longstrWriteRead('abcdefghijklmnopqrstuvwxyzabcdefghijklmnopqrstuvwxyzabcdefghijklmnopqrstuvwxyzabcdefghijklmnopqrstuvwxyz
        abcdefghijklmnopqrstuvwxyzabcdefghijklmnopqrstuvwxyzabcdefghijklmnopqrstuvwxyzabcdefghijklmnopqrstuvwxyz
        abcdefghijklmnopqrstuvwxyzabcdefghijklmnopqrstuvwxyzabcdefghijklmnopqrstuvwxyzabcdefghijklmnopqrstuvwxyz');
    }

    protected function longstrWriteRead($v)
    {
        $this->writeAndRead($v, 'write_longstr', 'read_longstr');
    }

    public function testLongLongWriteRead()
    {
        // First test with values represented as strings
        $this->longlongWriteRead('0');
        $this->longlongWriteRead('123');
        $this->longlongWriteRead('4294967296');
        $this->longlongWriteRead('994294967296');

        // Now, with real int values
        $this->longlongWriteRead(0);
        $this->longlongWriteRead(123);
        $this->longlongWriteRead(4294967296);
    }

    protected function longlongWriteRead($v)
    {
        $this->writeAndRead($v, 'write_longlong', 'read_longlong');
    }

    protected function writeAndRead($v, $write_method, $read_method)
    {
        $w = new AMQPWriter();
        $w->{$write_method}($v);

        $r = new AMQPReader($w->getvalue());
        $this->assertEquals($v, $r->{$read_method}());
    }
}
