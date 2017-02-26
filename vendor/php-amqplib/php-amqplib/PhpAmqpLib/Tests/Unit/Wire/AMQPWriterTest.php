<?php

namespace PhpAmqpLib\Tests\Unit;

use PhpAmqpLib\Wire\AMQPWriter;

class AMQPWriterTest extends \PHPUnit_Framework_TestCase
{
    protected $_writer;

    public function setUp()
    {
        $this->_writer = new AMQPWriter();
    }

    public function tearDown()
    {
        $this->_writer = null;
    }

    public function testWriteArray()
    {
        $this->_writer->write_array(array(
            'rabbit@localhost',
            'hare@localhost'
        ));

        $out = $this->_writer->getvalue();

        $this->assertEquals(44, strlen($out));

        $expected = "\x00\x00\x00(S\x00\x00\x00\x10rabbit@localhostS\x00\x00\x00\x0Ehare@localhost";

        $this->assertEquals($expected, $out);
    }

    public function testWriteTable()
    {
        $this->_writer->write_table(array(
                'x-foo' => array('S', 'bar'),
                'x-bar' => array('A', array('baz', 'qux')),
        ));

        $out = $this->_writer->getvalue();


        $expected = "\x00\x00\x00)\x05x-fooS\x00\x00\x00\x03bar\x05x-barA\x00\x00\x00\x10S\x00\x00\x00\x03bazS\x00\x00\x00\x03qux";

        $this->assertEquals($expected, $out);
    }

    public function testWriteTableThrowsExceptionOnInvalidType()
    {
        $this->setExpectedException('InvalidArgumentException', "Invalid type '_'");

        $this->_writer->write_table(array(
                'x-foo' => array('_', 'bar'),
        ));
    }
}
