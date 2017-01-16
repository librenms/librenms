<?php
namespace PhpAmqpLib\Channel;

use PhpAmqpLib\Connection\AbstractConnection;
use PhpAmqpLib\Exception\AMQPOutOfBoundsException;
use PhpAmqpLib\Exception\AMQPOutOfRangeException;
use PhpAmqpLib\Exception\AMQPRuntimeException;
use PhpAmqpLib\Helper\DebugHelper;
use PhpAmqpLib\Helper\Protocol\MethodMap080;
use PhpAmqpLib\Helper\Protocol\MethodMap091;
use PhpAmqpLib\Helper\Protocol\Protocol080;
use PhpAmqpLib\Helper\Protocol\Protocol091;
use PhpAmqpLib\Helper\Protocol\Wait080;
use PhpAmqpLib\Helper\Protocol\Wait091;
use PhpAmqpLib\Message\AMQPMessage;
use PhpAmqpLib\Wire\AMQPReader;

abstract class AbstractChannel
{
    const PROTOCOL_080 = '0.8';
    const PROTOCOL_091 = '0.9.1';

    public static $PROTOCOL_CONSTANTS_CLASS;

    /** @var array */
    protected $frame_queue;

    /** @var array */
    protected $method_queue;

    /** @var bool */
    protected $auto_decode;

    /** @var string */
    protected $amqp_protocol_header;

    /** @var \PhpAmqpLib\Helper\DebugHelper */
    protected $debug;

    /** @var \PhpAmqpLib\Connection\AbstractConnection */
    protected $connection;

    /** @var string */
    protected $protocolVersion;

    /** @var int */
    protected $maxBodySize;

    /** @var \PhpAmqpLib\Helper\Protocol\Protocol080|\PhpAmqpLib\Helper\Protocol\Protocol091 */
    protected $protocolWriter;

    /** @var \PhpAmqpLib\Helper\Protocol\Wait080|\PhpAmqpLib\Helper\Protocol\Wait091 */
    protected $waitHelper;

    /** @var \PhpAmqpLib\Helper\Protocol\MethodMap080|\PhpAmqpLib\Helper\Protocol\MethodMap091 */
    protected $methodMap;

    /** @var string */
    protected $channel_id;

    /** @var \PhpAmqpLib\Wire\AMQPReader */
    protected $msg_property_reader;

    /** @var \PhpAmqpLib\Wire\AMQPReader */
    protected $wait_content_reader;

    /** @var \PhpAmqpLib\Wire\AMQPReader */
    protected $dispatch_reader;

    /**
     * @param AbstractConnection $connection
     * @param string $channel_id
     * @throws \PhpAmqpLib\Exception\AMQPRuntimeException
     */
    public function __construct(AbstractConnection $connection, $channel_id)
    {
        $this->connection = $connection;
        $this->channel_id = $channel_id;
        $connection->channels[$channel_id] = $this;
        $this->frame_queue = array(); // Lower level queue for frames
        $this->method_queue = array(); // Higher level queue for methods
        $this->auto_decode = false;

        $this->msg_property_reader = new AMQPReader(null);
        $this->wait_content_reader = new AMQPReader(null);
        $this->dispatch_reader = new AMQPReader(null);

        $this->protocolVersion = self::getProtocolVersion();
        switch ($this->protocolVersion) {
            case self::PROTOCOL_091:
                self::$PROTOCOL_CONSTANTS_CLASS = 'PhpAmqpLib\Wire\Constants091';
                $c = self::$PROTOCOL_CONSTANTS_CLASS;
                $this->debug = new DebugHelper($c);
                $this->amqp_protocol_header = $c::$AMQP_PROTOCOL_HEADER;
                $this->protocolWriter = new Protocol091();
                $this->waitHelper = new Wait091();
                $this->methodMap = new MethodMap091();
                break;
            case self::PROTOCOL_080:
                self::$PROTOCOL_CONSTANTS_CLASS = 'PhpAmqpLib\Wire\Constants080';
                $c = self::$PROTOCOL_CONSTANTS_CLASS;
                $this->debug = new DebugHelper($c);
                $this->amqp_protocol_header = $c::$AMQP_PROTOCOL_HEADER;
                $this->protocolWriter = new Protocol080();
                $this->waitHelper = new Wait080();
                $this->methodMap = new MethodMap080();
                break;
            default:
                throw new AMQPRuntimeException(sprintf(
                    'Protocol: %s not implemented.',
                    $this->protocolVersion
                ));
        }
    }

    /**
     * @return string
     * @throws AMQPOutOfRangeException
     */
    public static function getProtocolVersion()
    {
        $protocol = defined('AMQP_PROTOCOL') ? AMQP_PROTOCOL : self::PROTOCOL_091;
        //adding check here to catch unknown protocol ASAP, as this method may be called from the outside
        if (!in_array($protocol, array(self::PROTOCOL_080, self::PROTOCOL_091), TRUE)) {
            throw new AMQPOutOfRangeException(sprintf('Protocol version %s not implemented.', $protocol));
        }

        return $protocol;
    }

    /**
     * @return string
     */
    public function getChannelId()
    {
        return $this->channel_id;
    }

    /**
     * @param int $max_bytes Max message body size for this channel
     * @return $this
     */
    public function setBodySizeLimit($max_bytes)
    {
        $max_bytes = (int) $max_bytes;

        if ($max_bytes > 0) {
            $this->maxBodySize = $max_bytes;
        }

        return $this;
    }

    /**
     * @return AbstractConnection
     */
    public function getConnection()
    {
        return $this->connection;
    }

    /**
     * @return array
     */
    public function getMethodQueue()
    {
        return $this->method_queue;
    }

    /**
     * @param string $method_sig
     * @param string $args
     * @param AMQPMessage|null $amqpMessage
     * @return mixed
     * @throws \PhpAmqpLib\Exception\AMQPRuntimeException
     */
    public function dispatch($method_sig, $args, $amqpMessage)
    {
        if (!$this->methodMap->valid_method($method_sig)) {
            throw new AMQPRuntimeException(sprintf(
                'Unknown AMQP method "%s"',
                $method_sig
            ));
        }

        $amqp_method = $this->methodMap->get_method($method_sig);

        if (!method_exists($this, $amqp_method)) {
            throw new AMQPRuntimeException(sprintf(
                'Method: "%s" not implemented by class: %s',
                $amqp_method,
                get_class($this)
            ));
        }

        $this->dispatch_reader->reuse($args);

        if ($amqpMessage == null) {
            return call_user_func(array($this, $amqp_method), $this->dispatch_reader);
        }

        return call_user_func(array($this, $amqp_method), $this->dispatch_reader, $amqpMessage);
    }

    /**
     * @param int $timeout
     * @return array|mixed
     */
    public function next_frame($timeout = 0)
    {
        $this->debug->debug_msg('waiting for a new frame');

        if (!empty($this->frame_queue)) {
            return array_shift($this->frame_queue);
        }

        return $this->connection->wait_channel($this->channel_id, $timeout);
    }

    /**
     * @param array $method_sig
     * @param \PhpAmqpLib\Wire\AMQPWriter|string $args
     */
    protected function send_method_frame($method_sig, $args = '')
    {
        if ($this->connection === null) {
            throw new AMQPRuntimeException('Channel connection is closed.');
        }

        $this->connection->send_channel_method_frame($this->channel_id, $method_sig, $args);
    }

    /**
     * This is here for performance reasons to batch calls to fwrite from basic.publish
     *
     * @param array $method_sig
     * @param \PhpAmqpLib\Wire\AMQPWriter|string $args
     * @param \PhpAmqpLib\Wire\AMQPWriter $pkt
     * @return \PhpAmqpLib\Wire\AMQPWriter
     */
    protected function prepare_method_frame($method_sig, $args = '', $pkt = null)
    {
        return $this->connection->prepare_channel_method_frame($this->channel_id, $method_sig, $args, $pkt);
    }

    /**
     * @return AMQPMessage
     * @throws \PhpAmqpLib\Exception\AMQPRuntimeException
     */
    public function wait_content()
    {
        list($frame_type, $payload) = $this->next_frame();

        $this->validate_header_frame($frame_type);

        $this->wait_content_reader->reuse(mb_substr($payload, 0, 12, 'ASCII'));

        // $payload_reader = new AMQPReader(substr($payload,0,12));
        $class_id = $this->wait_content_reader->read_short();
        $weight = $this->wait_content_reader->read_short();

        //hack to avoid creating new instances of AMQPReader;
        $this->msg_property_reader->reuse(mb_substr($payload, 12, mb_strlen($payload, 'ASCII') - 12, 'ASCII'));

        return $this->createMessage(
            $this->msg_property_reader,
            $this->wait_content_reader
        );
    }

    /**
     * @param AMQPReader $propertyReader
     * @param AMQPReader $contentReader
     * @return \PhpAmqpLib\Message\AMQPMessage
     */
    protected function createMessage($propertyReader, $contentReader)
    {
        $bodyChunks = array();
        $bodyReceivedBytes = 0;

        $message = new AMQPMessage();
        $message
            ->load_properties($propertyReader)
            ->setBodySize($contentReader->read_longlong());

        while (bccomp($message->getBodySize(), $bodyReceivedBytes, 0) == 1) {
            list($frame_type, $payload) = $this->next_frame();

            $this->validate_body_frame($frame_type);
            $bodyReceivedBytes = bcadd($bodyReceivedBytes, mb_strlen($payload, 'ASCII'), 0);

            if (is_int($this->maxBodySize) && $bodyReceivedBytes > $this->maxBodySize ) {
                $message->setIsTruncated(true);
                continue;
            }

            $bodyChunks[] = $payload;
        }

        $message->setBody(implode('', $bodyChunks));

        return $message;
    }

    /**
     * Wait for some expected AMQP methods and dispatch to them.
     * Unexpected methods are queued up for later calls to this PHP
     * method.
     *
     * @param array $allowed_methods
     * @param bool $non_blocking
     * @param int $timeout
     * @throws \PhpAmqpLib\Exception\AMQPOutOfBoundsException
     * @throws \PhpAmqpLib\Exception\AMQPRuntimeException
     * @return mixed
     */
    public function wait($allowed_methods = null, $non_blocking = false, $timeout = 0)
    {
        $this->debug->debug_allowed_methods($allowed_methods);

        $deferred = $this->process_deferred_methods($allowed_methods);
        if ($deferred['dispatch'] === true) {
            return $this->dispatch_deferred_method($deferred['queued_method']);
        }

        // No deferred methods?  wait for new ones
        while (true) {
            list($frame_type, $payload) = $this->next_frame($timeout);

            $this->validate_method_frame($frame_type);
            $this->validate_frame_payload($payload);

            $method_sig = $this->build_method_signature($payload);
            $args = $this->extract_args($payload);

            $this->debug->debug_method_signature('> %s', $method_sig);

            $amqpMessage = $this->maybe_wait_for_content($method_sig);

            if ($this->should_dispatch_method($allowed_methods, $method_sig)) {
                return $this->dispatch($method_sig, $args, $amqpMessage);
            }

            // Wasn't what we were looking for? save it for later
            $this->debug->debug_method_signature('Queueing for later: %s', $method_sig);
            $this->method_queue[] = array($method_sig, $args, $amqpMessage);

            if ($non_blocking) {
                break;
            }
        }
    }

    /**
     * @param array $allowed_methods
     * @return array
     */
    protected function process_deferred_methods($allowed_methods)
    {
        $dispatch = false;
        $queued_method = array();

        foreach ($this->method_queue as $qk => $qm) {
            $this->debug->debug_msg('checking queue method ' . $qk);

            $method_sig = $qm[0];

            if ($allowed_methods == null || in_array($method_sig, $allowed_methods)) {
                unset($this->method_queue[$qk]);
                $dispatch = true;
                $queued_method = $qm;
                break;
            }
        }

        return array('dispatch' => $dispatch, 'queued_method' => $queued_method);
    }

    /**
     * @param array $queued_method
     * @return mixed
     */
    protected function dispatch_deferred_method($queued_method)
    {
        $this->debug->debug_method_signature('Executing queued method: %s', $queued_method[0]);

        return $this->dispatch($queued_method[0], $queued_method[1], $queued_method[2]);
    }

    /**
     * @param int $frame_type
     * @throws \PhpAmqpLib\Exception\AMQPRuntimeException
     */
    protected function validate_method_frame($frame_type)
    {
        $this->validate_frame($frame_type, 1, 'AMQP method');
    }

    /**
     * @param int $frame_type
     * @throws \PhpAmqpLib\Exception\AMQPRuntimeException
     */
    protected function validate_header_frame($frame_type)
    {
        $this->validate_frame($frame_type, 2, 'AMQP Content header');
    }

    /**
     * @param int $frame_type
     * @throws \PhpAmqpLib\Exception\AMQPRuntimeException
     */
    protected function validate_body_frame($frame_type)
    {
        $this->validate_frame($frame_type, 3, 'AMQP Content body');
    }

    /**
     * @param int $frameType
     * @param int $expectedType
     * @param string $expectedMessage
     */
    protected function validate_frame($frameType, $expectedType, $expectedMessage)
    {
        if ($frameType != $expectedType) {
            $protocolClass = self::$PROTOCOL_CONSTANTS_CLASS;
            throw new AMQPRuntimeException(sprintf(
                    'Expecting %s, received frame type %s (%s)',
                    $expectedMessage,
                    $frameType,
                    $protocolClass::$FRAME_TYPES[$frameType]
                ));
        }
    }

    /**
     * @param string $payload
     * @throws \PhpAmqpLib\Exception\AMQPOutOfBoundsException
     */
    protected function validate_frame_payload($payload)
    {
        if (mb_strlen($payload, 'ASCII') < 4) {
            throw new AMQPOutOfBoundsException('Method frame too short');
        }
    }

    /**
     * @param string $payload
     * @return string
     */
    protected function build_method_signature($payload)
    {
        $method_sig_array = unpack('n2', mb_substr($payload, 0, 4, 'ASCII'));

        return sprintf('%s,%s', $method_sig_array[1], $method_sig_array[2]);
    }

    /**
     * @param string $payload
     * @return string
     */
    protected function extract_args($payload)
    {
        return mb_substr($payload, 4, mb_strlen($payload, 'ASCII') - 4, 'ASCII');
    }

    /**
     * @param array|null $allowed_methods
     * @param string $method_sig
     * @return bool
     */
    protected function should_dispatch_method($allowed_methods, $method_sig)
    {
        $protocolClass = self::$PROTOCOL_CONSTANTS_CLASS;

        return $allowed_methods == null
            || in_array($method_sig, $allowed_methods)
            || in_array($method_sig, $protocolClass::$CLOSE_METHODS);
    }

    /**
     * @param string $method_sig
     * @return AMQPMessage|null
     */
    protected function maybe_wait_for_content($method_sig)
    {
        $protocolClass = self::$PROTOCOL_CONSTANTS_CLASS;
        $amqpMessage = null;

        if (in_array($method_sig, $protocolClass::$CONTENT_METHODS)) {
            $amqpMessage = $this->wait_content();
        }

        return $amqpMessage;
    }

    /**
     * @param callable $handler
     * @param array $arguments
     */
    protected function dispatch_to_handler($handler, array $arguments)
    {
        if (is_callable($handler)) {
            call_user_func_array($handler, $arguments);
        }
    }
}
