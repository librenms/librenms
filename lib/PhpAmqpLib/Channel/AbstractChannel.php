<?php
namespace PhpAmqpLib\Channel;

use PhpAmqpLib\Connection\AbstractConnection;
use PhpAmqpLib\Exception\AMQPOutOfBoundsException;
use PhpAmqpLib\Exception\AMQPOutOfRangeException;
use PhpAmqpLib\Exception\AMQPRuntimeException;
use PhpAmqpLib\Helper\MiscHelper;
use PhpAmqpLib\Helper\Protocol\MethodMap080;
use PhpAmqpLib\Helper\Protocol\MethodMap091;
use PhpAmqpLib\Helper\Protocol\Protocol080;
use PhpAmqpLib\Helper\Protocol\Protocol091;
use PhpAmqpLib\Helper\Protocol\Wait080;
use PhpAmqpLib\Helper\Protocol\Wait091;
use PhpAmqpLib\Message\AMQPMessage;
use PhpAmqpLib\Wire\AMQPReader;

class AbstractChannel
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

    /** @var bool */
    protected $debug;

    /** @var \PhpAmqpLib\Connection\AbstractConnection */
    protected $connection;

    /** @var string */
    protected $protocolVersion;
    
    /**
     * @var int
     */
    protected $body_size_max = null;

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
     * @param $channel_id
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
        $this->debug = defined('AMQP_DEBUG') ? AMQP_DEBUG : false;

        $this->msg_property_reader = new AMQPReader(null);
        $this->wait_content_reader = new AMQPReader(null);
        $this->dispatch_reader = new AMQPReader(null);

        $this->protocolVersion = self::getProtocolVersion();
        switch ($this->protocolVersion) {
            case self::PROTOCOL_091:
                self::$PROTOCOL_CONSTANTS_CLASS = 'PhpAmqpLib\Wire\Constants091';
                $c = self::$PROTOCOL_CONSTANTS_CLASS;
                $this->amqp_protocol_header = $c::$AMQP_PROTOCOL_HEADER;
                $this->protocolWriter = new Protocol091();
                $this->waitHelper = new Wait091();
                $this->methodMap = new MethodMap091();
                break;
            case self::PROTOCOL_080:
                self::$PROTOCOL_CONSTANTS_CLASS = 'PhpAmqpLib\Wire\Constants080';
                $c = self::$PROTOCOL_CONSTANTS_CLASS;
                $this->amqp_protocol_header = $c::$AMQP_PROTOCOL_HEADER;
                $this->protocolWriter = new Protocol080();
                $this->waitHelper = new Wait080();
                $this->methodMap = new MethodMap080();
                break;
            default:
                //this is logic exception (requires code changes to fix), so OutOfRange, not OutOfBounds or Runtime
                throw new AMQPOutOfRangeException(sprintf('Protocol version %s not implemented.', $this->protocolVersion));
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
    
    public function setBodySizeLimit($max_bytes)
    {
        $max_bytes = intval($max_bytes);
        
        if ( $max_bytes > 0 ) {
            $this->body_size_max = $max_bytes;
        } else {
            $this->body_size_max = null;
        }
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
     * @param $content
     * @return mixed
     * @throws \PhpAmqpLib\Exception\AMQPRuntimeException
     */
    public function dispatch($method_sig, $args, $content)
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

        if ($content == null) {
            return call_user_func(array($this, $amqp_method), $this->dispatch_reader);
        }

        return call_user_func(array($this, $amqp_method), $this->dispatch_reader, $content);
    }

    /**
     * @param int $timeout
     * @return array|mixed
     */
    public function next_frame($timeout = 0)
    {
        if ($this->debug) {
            MiscHelper::debug_msg('waiting for a new frame');
        }

        if (!empty($this->frame_queue)) {
            return array_shift($this->frame_queue);
        }

        return $this->connection->wait_channel($this->channel_id, $timeout);
    }

    /**
     * @param $method_sig
     * @param string $args
     */
    protected function send_method_frame($method_sig, $args = '')
    {
        $this->connection->send_channel_method_frame($this->channel_id, $method_sig, $args);
    }

    /**
     * This is here for performance reasons to batch calls to fwrite from basic.publish
     *
     * @param $method_sig
     * @param string $args
     * @param null $pkt
     * @return null|\PhpAmqpLib\Wire\AMQPWriter
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
        $frm = $this->next_frame();
        $frame_type = $frm[0];
        $payload = $frm[1];

        if ($frame_type != 2) {
            throw new AMQPRuntimeException('Expecting Content header');
        }

        $this->wait_content_reader->reuse(mb_substr($payload, 0, 12, 'ASCII'));

        // $payload_reader = new AMQPReader(substr($payload,0,12));
        $class_id = $this->wait_content_reader->read_short();
        $weight = $this->wait_content_reader->read_short();

        $body_size = $this->wait_content_reader->read_longlong();

        //hack to avoid creating new instances of AMQPReader;
        $this->msg_property_reader->reuse(mb_substr($payload, 12, mb_strlen($payload, 'ASCII') - 12, 'ASCII'));

        $msg = new AMQPMessage();
        $msg->load_properties($this->msg_property_reader);
        $msg->body_size = $body_size;
        
        $body_parts = array();
        $body_received = 0;
        while (bccomp($body_size, $body_received, 0) == 1) {
            $frm = $this->next_frame();
            $frame_type = $frm[0];
            $payload = $frm[1];

            if ($frame_type != 3) {
                $PROTOCOL_CONSTANTS_CLASS = self::$PROTOCOL_CONSTANTS_CLASS;
                throw new AMQPRuntimeException(sprintf(
                    'Expecting Content body, received frame type %s (%s)',
                    $frame_type,
                    $PROTOCOL_CONSTANTS_CLASS::$FRAME_TYPES[$frame_type]
                ));
            }

            $body_received = bcadd($body_received, mb_strlen($payload, 'ASCII'), 0);

            if ( ! is_null($this->body_size_max) && $body_received > $this->body_size_max ) {
                $msg->is_truncated = true;
                continue;
            }

            $body_parts[] = $payload;
        }

        $msg->body = implode('', $body_parts);

        if ($this->auto_decode && isset($msg->content_encoding)) {
            try {
                $msg->body = $msg->body->decode($msg->content_encoding);
            } catch (\Exception $e) {
                if ($this->debug) {
                    MiscHelper::debug_msg('Ignoring body decoding exception: ' . $e->getMessage());
                }
            }
        }

        return $msg;
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
        $PROTOCOL_CONSTANTS_CLASS = self::$PROTOCOL_CONSTANTS_CLASS;

        if ($allowed_methods && $this->debug) {
            MiscHelper::debug_msg('waiting for ' . implode(', ', $allowed_methods));
        } elseif ($this->debug) {
            MiscHelper::debug_msg('waiting for any method');
        }

        //Process deferred methods
        foreach ($this->method_queue as $qk => $queued_method) {
            if ($this->debug) {
                MiscHelper::debug_msg('checking queue method ' . $qk);
            }

            $method_sig = $queued_method[0];
            if ($allowed_methods == null || in_array($method_sig, $allowed_methods)) {
                unset($this->method_queue[$qk]);

                if ($this->debug) {
                    MiscHelper::debug_msg(sprintf(
                        'Executing queued method: %s: %s',
                        $method_sig,
                        $PROTOCOL_CONSTANTS_CLASS::$GLOBAL_METHOD_NAMES[MiscHelper::methodSig($method_sig)]
                    ));
                }

                return $this->dispatch($queued_method[0], $queued_method[1], $queued_method[2]);
            }
        }

        // No deferred methods?  wait for new ones
        while (true) {
            $frm = $this->next_frame($timeout);
            $frame_type = $frm[0];
            $payload = $frm[1];

            if ($frame_type != 1) {
                throw new AMQPRuntimeException(sprintf(
                    'Expecting AMQP method, received frame type: %s (%s)',
                    $frame_type,
                    $PROTOCOL_CONSTANTS_CLASS::$FRAME_TYPES[$frame_type]
                ));
            }

            if (mb_strlen($payload, 'ASCII') < 4) {
                throw new AMQPOutOfBoundsException('Method frame too short');
            }

            $method_sig_array = unpack('n2', mb_substr($payload, 0, 4, 'ASCII'));
            $method_sig = '' . $method_sig_array[1] . ',' . $method_sig_array[2];
            $args = mb_substr($payload, 4, mb_strlen($payload, 'ASCII') - 4, 'ASCII');

            if ($this->debug) {
                MiscHelper::debug_msg(sprintf(
                    '> %s: %s',
                    $method_sig,
                    $PROTOCOL_CONSTANTS_CLASS::$GLOBAL_METHOD_NAMES[MiscHelper::methodSig($method_sig)]
                ));
            }

            if (in_array($method_sig, $PROTOCOL_CONSTANTS_CLASS::$CONTENT_METHODS)) {
                $content = $this->wait_content();
            } else {
                $content = null;
            }

            if ($allowed_methods == null ||
                in_array($method_sig, $allowed_methods) ||
                in_array($method_sig, $PROTOCOL_CONSTANTS_CLASS::$CLOSE_METHODS)
            ) {
                return $this->dispatch($method_sig, $args, $content);
            }

            // Wasn't what we were looking for? save it for later
            if ($this->debug) {
                MiscHelper::debug_msg('Queueing for later: $method_sig: '
                    . $PROTOCOL_CONSTANTS_CLASS::$GLOBAL_METHOD_NAMES[MiscHelper::methodSig($method_sig)]);
            }
            $this->method_queue[] = array($method_sig, $args, $content);

            if ($non_blocking) {
                break;
            }
        }
    }

    /**
     * @param $handler
     * @param array $arguments
     */
    protected function dispatch_to_handler($handler, array $arguments)
    {
        if (is_callable($handler)) {
            call_user_func_array($handler, $arguments);
        }
    }
}
