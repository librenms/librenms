<?php

namespace PhpAmqpLib\Tests\Unit\Helper\Writer;

use PhpAmqpLib\Helper\Protocol\Protocol091;

class Protocol091Test extends \PHPUnit_Framework_TestCase
{
    public function setUp()
    {
        $this->protocol091 = new Protocol091();
    }

    public function testChannelClose()
    {
        $expected = "\x00\x00\x00\x00\x00\x00\x00";
        list($class_id, $method_id, $args) = $this->protocol091->channelClose(0, "", 0, 0);
        $this->assertEquals($expected, $args->getvalue());

        $expected = "\x00\x00\x05error\x00\x00\x00\x00";
        list($class_id, $method_id, $args) = $this->protocol091->channelClose(0, "error", 0, 0);
        $this->assertEquals($expected, $args->getvalue());

        $expected = "\x00\x00\x05error\x00\x14\x00\x28";
        list($class_id, $method_id, $args) = $this->protocol091->channelClose(0, "error", 20, 40);
        $this->assertEquals($expected, $args->getvalue());
    }

    public function testFlow()
    {
        $expected = "\x01";
        list($class_id, $method_id, $args) = $this->protocol091->channelFlow(true);
        $this->assertEquals($expected, $args->getvalue());

        $expected = "\x00";
        list($class_id, $method_id, $args) = $this->protocol091->channelFlow(false);
        $this->assertEquals($expected, $args->getvalue());
    }

    public function testXOpen()
    {
        $expected = "\x03foo";
        list($class_id, $method_id, $args) = $this->protocol091->channelOpen("foo");
        $this->assertEquals($expected, $args->getvalue());

        $expected = "\x00";
        list($class_id, $method_id, $args) = $this->protocol091->channelOpen("");
        $this->assertEquals($expected, $args->getvalue());
    }

    public function testAccessRequest()
    {
        $expected = "\x01/\x00";
        list($class_id, $method_id, $args) = $this->protocol091->accessRequest("/", false, false, false, false, false);
        $this->assertEquals($expected, $args->getvalue());

        $expected = "\x04/foo\x00";
        list($class_id, $method_id, $args) = $this->protocol091->accessRequest("/foo", false, false, false, false, false);
        $this->assertEquals($expected, $args->getvalue());
    }

    public function testExchangeDeclare()
    {
        $expected = "\x00\x00\x03foo\x06direct\x00\x00\x00\x00\x00";
        list($class_id, $method_id, $args) = $this->protocol091->exchangeDeclare(
            0, 'foo', 'direct', false,
            false, false,
            false, false,
            array()
        );
       $this->assertEquals($expected, $args->getvalue());
    }

    public function testExchangeDelete()
    {
        $expected = "\x00\x00\x03foo\x00";
        list($class_id, $method_id, $args) = $this->protocol091->exchangeDelete(0, 'foo', false, false);
        $this->assertEquals($expected, $args->getvalue());
    }

    public function testExchangeBind()
    {
        $expected = "\x00\x00\x03foo\x03bar\x03baz\x00\x00\x00\x00\x00";
        list($class_id, $method_id, $args) = $this->protocol091->exchangeBind(0, 'foo', 'bar', 'baz', false, array());
        $this->assertEquals($expected, $args->getvalue());
    }

    public function testExchangeUnbind()
    {
        $expected = "\x00\x00\x03foo\x03bar\x03baz\x00\x00\x00\x00\x00";
        list($class_id, $method_id, $args) = $this->protocol091->exchangeUnbind(0, 'foo', 'bar', 'baz', false, array());
        $this->assertEquals($expected, $args->getvalue());
    }

    public function testQueueBind()
    {
        $expected = "\x00\x00\x03foo\x03bar\x03baz\x00\x00\x00\x00\x00";
        list($class_id, $method_id, $args) = $this->protocol091->queueBind(0, 'foo', 'bar', 'baz', false, array());
        $this->assertEquals($expected, $args->getvalue());
    }

    public function testQueueUnbind()
    {
        $expected = "\x00\x00\x03foo\x03bar\x03baz\x00\x00\x00\x00";
        list($class_id, $method_id, $args) = $this->protocol091->queueUnbind(0, 'foo', 'bar', 'baz', array());
        $this->assertEquals($expected, $args->getvalue());
    }

    public function testQueueDeclare()
    {
        $expected = "\x00\x00\x03foo\x00\x00\x00\x00\x00";
        list($class_id, $method_id, $args) = $this->protocol091->queueDeclare(
            0, 'foo', false,
            false, false,
            false, false,
            array()
        );
       $this->assertEquals($expected, $args->getvalue());
    }

    public function testQueueDelete()
    {
        $expected = "\x00\x00\x03foo\x00";
        list($class_id, $method_id, $args) = $this->protocol091->queueDelete(0, 'foo', false, false, false);
        $this->assertEquals($expected, $args->getvalue());
    }

    public function testQueuePurge()
    {
        $expected = "\x00\x00\x03foo\x00";
        list($class_id, $method_id, $args) = $this->protocol091->queuePurge(0, 'foo', false);
        $this->assertEquals($expected, $args->getvalue());
    }

    public function testBasicAck()
    {
        $expected = "\x00\x00\x00\x00\x00\x00\x00\x01\x00";
        list($class_id, $method_id, $args) = $this->protocol091->basicAck(1, false);
        $this->assertEquals($expected, $args->getvalue());
    }

    public function testBasicCancel()
    {
        $expected = "\x03foo\x00";
        list($class_id, $method_id, $args) = $this->protocol091->basicCancel('foo', false);
        $this->assertEquals($expected, $args->getvalue());
    }

    public function testBasicConsume()
    {
        $expected = "\x00\x00\x03foo\x03bar\x00\x00\x00\x00\x00";
        list($class_id, $method_id, $args) = $this->protocol091->basicConsume(0, 'foo', 'bar', false, false, false, false);
        $this->assertEquals($expected, $args->getvalue());
    }

    public function testBasicGet()
    {
        $expected = "\x00\x00\x03foo\x00";
        list($class_id, $method_id, $args) = $this->protocol091->basicGet(0, 'foo', false);
        $this->assertEquals($expected, $args->getvalue());
    }

    public function testBasicPublish()
    {
        $expected = "\x00\x00\x03foo\x03bar\x00";
        list($class_id, $method_id, $args) = $this->protocol091->basicPublish(0, 'foo', 'bar', false, false);
        $this->assertEquals($expected, $args->getvalue());
    }

    public function testBasicQos()
    {
        $expected = "\x00\x00\x00\xA\x00\x01\x00";
        list($class_id, $method_id, $args) = $this->protocol091->basicQos(10, 1, false);
        $this->assertEquals($expected, $args->getvalue());
    }

    public function testBasicRecover()
    {
        $expected = "\x01";
        list($class_id, $method_id, $args) = $this->protocol091->basicRecover(true);
        $this->assertEquals($expected, $args->getvalue());

        $expected = "\x00";
        list($class_id, $method_id, $args) = $this->protocol091->basicRecover(false);
        $this->assertEquals($expected, $args->getvalue());
    }

    public function testBasicReject()
    {
        $expected = "\x00\x00\x00\x00\x00\x00\x00\x01\x01";
        list($class_id, $method_id, $args) = $this->protocol091->basicReject(1, true);
        $this->assertEquals($expected, $args->getvalue());

        $expected = "\x00\x00\x00\x00\x00\x00\x00\x01\x00";
        list($class_id, $method_id, $args) = $this->protocol091->basicReject(1, false);
        $this->assertEquals($expected, $args->getvalue());
    }
}
