<?php
namespace PhpAmqpLib\Connection;

use PhpAmqpLib\Channel\AMQPChannel;
use PhpAmqpLib\Channel\AbstractChannel;
use PhpAmqpLib\Exception\AMQPProtocolConnectionException;
use PhpAmqpLib\Exception\AMQPRuntimeException;
use PhpAmqpLib\Exception\AMQPTimeoutException;
use PhpAmqpLib\Wire\AMQPReader;
use PhpAmqpLib\Wire\AMQPTable;
use PhpAmqpLib\Wire\AMQPWriter;
use PhpAmqpLib\Wire\IO\AbstractIO;
use PhpAmqpLib\Wire\IO\SocketIO;
use PhpAmqpLib\Wire\IO\StreamIO;

class AbstractConnection extends AbstractChannel
{
    /** @var array */
    public static $LIBRARY_PROPERTIES = array(
        'product' => array('S', 'AMQPLib'),
        'platform' => array('S', 'PHP'),
        'version' => array('S', '2.6'),
        'information' => array('S', ''),
        'copyright' => array('S', ''),
        'capabilities' => array(
            'F',
            array(
                'authentication_failure_close' => array('t', true),
                'publisher_confirms' => array('t', true),
                'consumer_cancel_notify' => array('t', true),
                'exchange_exchange_bindings' => array('t', true),
                'basic.nack' => array('t', true),
                'connection.blocked' => array('t', true)
            )
        )
    );

    /** @var AMQPChannel[] */
    public $channels = array();

    /** @var int */
    protected $version_major;

    /** @var int */
    protected $version_minor;

    /** @var array */
    protected $server_properties;

    /** @var array */
    protected $mechanisms;

    /** @var array */
    protected $locales;

    /** @var bool */
    protected $wait_tune_ok;

    /** @var string */
    protected $known_hosts;

    /** @var AMQPReader */
    protected $input;

    /** @var string */
    protected $vhost;

    /** @var bool */
    protected $insist;

    /** @var string */
    protected $login_method;

    /** @var string */
    protected $login_response;

    /** @var string */
    protected $locale;

    /** @var int */
    protected $heartbeat;

    /** @var SocketIO */
    protected $sock;

    /** @var int */
    protected $channel_max = 65535;

    /** @var int */
    protected $frame_max = 131072;

     /** @var array Constructor parameters for clone */
    protected $construct_params;

    /** @var bool Close the connection in destructor */
    protected $close_on_destruct = true;

    /** @var bool Maintain connection status */
    protected $is_connected = false;

    /** @var \PhpAmqpLib\Wire\IO\AbstractIO */
    protected $io;

    /** @var \PhpAmqpLib\Wire\AMQPReader */
    protected $wait_frame_reader;

    /** @var callable Handles connection blocking from the server */
    private $connection_block_handler;

    /** @var callable Handles connection unblocking from the server */
    private $connection_unblock_handler;

    /**
     * Circular buffer to speed up prepare_content().
     * Max size limited by $prepare_content_cache_max_size.
     *
     * @var array
     * @see prepare_content()
     */
    private $prepare_content_cache;

    /** @var int Maximal size of $prepare_content_cache */
    private $prepare_content_cache_max_size;

    /**
     * @param string $user
     * @param string $password
     * @param string $vhost
     * @param bool $insist
     * @param string $login_method
     * @param null $login_response
     * @param string $locale
     * @param AbstractIO $io
     * @param int $heartbeat
     * @throws \Exception
     */
    public function __construct(
        $user,
        $password,
        $vhost = '/',
        $insist = false,
        $login_method = 'AMQPLAIN',
        $login_response = null,
        $locale = 'en_US',
        AbstractIO $io,
        $heartbeat = 0
    ) {
        // save the params for the use of __clone
        $this->construct_params = func_get_args();

        $this->wait_frame_reader = new AMQPReader(null);
        $this->vhost = $vhost;
        $this->insist = $insist;
        $this->login_method = $login_method;
        $this->login_response = $login_response;
        $this->locale = $locale;
        $this->io = $io;
        $this->heartbeat = $heartbeat;

        if ($user && $password) {
            $this->login_response = new AMQPWriter();
            $this->login_response->write_table(array(
                'LOGIN' => array('S', $user),
                'PASSWORD' => array('S', $password)
            ));

            // Skip the length
            $responseValue = $this->login_response->getvalue();
            $this->login_response = mb_substr($responseValue, 4, mb_strlen($responseValue, 'ASCII') - 4, 'ASCII');

        } else {
            $this->login_response = null;
        }

        $this->prepare_content_cache = array();
        $this->prepare_content_cache_max_size = 100;

        // Lazy Connection waits on connecting
        if ($this->connectOnConstruct()) {
            $this->connect();
        }
    }

    /**
     * Connects to the AMQP server
     */
    protected function connect()
    {
        try {
            // Loop until we connect
            while (!$this->isConnected()) {
                // Assume we will connect, until we dont
                $this->setIsConnected(true);

                // Connect the socket
                $this->getIO()->connect();

                $this->channels = array();
                // The connection object itself is treated as channel 0
                parent::__construct($this, 0);

                $this->input = new AMQPReader(null, $this->getIO());

                $this->write($this->amqp_protocol_header);
                $this->wait(array($this->waitHelper->get_wait('connection.start')));
                $this->x_start_ok(self::$LIBRARY_PROPERTIES, $this->login_method, $this->login_response, $this->locale);

                $this->wait_tune_ok = true;
                while ($this->wait_tune_ok) {
                    $this->wait(array(
                        $this->waitHelper->get_wait('connection.secure'),
                        $this->waitHelper->get_wait('connection.tune')
                    ));
                }

                $host = $this->x_open($this->vhost, '', $this->insist);
                if (!$host) {
                    return null; // we weren't redirected
                }

                $this->setIsConnected(false);
                $this->closeChannels();

                // we were redirected, close the socket, loop and try again
                $this->close_socket();
            }

        } catch (\Exception $e) {
            // Something went wrong, set the connection status
            $this->setIsConnected(false);
            $this->closeChannels();
            throw $e; // Rethrow exception
        }
    }

    /**
     * Reconnects using the original connection settings.
     * This will not recreate any channels that were established previously
     */
    public function reconnect()
    {
        // Try to close the AMQP connection
        $this->safeClose();
        // Reconnect the socket/stream then AMQP
        $this->getIO()->reconnect();
        $this->setIsConnected(false); // getIO can initiate the connection setting via LazyConnection, set it here to be sure
        $this->connect();
    }

    /**
     * Cloning will use the old properties to make a new connection to the same server
     */
    public function __clone()
    {
        call_user_func_array(array($this, '__construct'), $this->construct_params);
    }

    public function __destruct()
    {
        if ($this->close_on_destruct) {
            $this->safeClose();
        }
    }

    /**
     * Attempts to close the connection safely
     */
    protected function safeClose()
    {
        try {
            if (isset($this->input) && $this->input) {
                $this->close();
            }
        } catch (\Exception $e) {
            // Nothing here
        }
    }

    /**
     * @param int $sec
     * @param int $usec
     * @return mixed
     */
    public function select($sec, $usec = 0)
    {
        return $this->getIO()->select($sec, $usec);
    }

    /**
     * Allows to not close the connection
     * it's useful after the fork when you don't want to close parent process connection
     *
     * @param bool $close
     */
    public function set_close_on_destruct($close = true)
    {
        $this->close_on_destruct = (bool) $close;
    }

    protected function close_input()
    {
        $this->debug->debug_msg('closing input');

        if (!is_null($this->input)) {
            $this->input->close();
            $this->input = null;
        }
    }

    protected function close_socket()
    {
        $this->debug->debug_msg('closing socket');

        if (!is_null($this->getIO())) {
            $this->getIO()->close();
        }
    }

    /**
     * @param $data
     */
    public function write($data)
    {
        $this->debug->debug_hexdump($data);

        try {
            $this->getIO()->write($data);
        } catch (AMQPRuntimeException $e) {
            $this->setIsConnected(false);
            throw $e;
        }
    }

    protected function do_close()
    {
        $this->setIsConnected(false);
        $this->close_input();
        $this->close_socket();
    }

    /**
     * @return int
     * @throws \PhpAmqpLib\Exception\AMQPRuntimeException
     */
    public function get_free_channel_id()
    {
        for ($i = 1; $i <= $this->channel_max; $i++) {
            if (!isset($this->channels[$i])) {
                return $i;
            }
        }

        throw new AMQPRuntimeException('No free channel ids');
    }

    /**
     * @param string $channel
     * @param int $class_id
     * @param int $weight
     * @param int $body_size
     * @param string $packed_properties
     * @param string $body
     * @param AMQPWriter $pkt
     */
    public function send_content($channel, $class_id, $weight, $body_size, $packed_properties, $body, $pkt = null)
    {
        $this->prepare_content($channel, $class_id, $weight, $body_size, $packed_properties, $body, $pkt);
        $this->write($pkt->getvalue());
    }

    /**
     * Returns a new AMQPWriter or mutates the provided $pkt
     *
     * @param string $channel
     * @param int $class_id
     * @param int $weight
     * @param int $body_size
     * @param string $packed_properties
     * @param string $body
     * @param AMQPWriter $pkt
     * @return AMQPWriter
     */
    public function prepare_content($channel, $class_id, $weight, $body_size, $packed_properties, $body, $pkt = null)
    {
        $pkt = $pkt ?: new AMQPWriter();

        // Content already prepared ?
        $key_cache = sprintf(
            '%s|%s|%s|%s',
            $channel,
            $packed_properties,
            $class_id,
            $weight
        );

        if (!isset($this->prepare_content_cache[$key_cache])) {
            $w = new AMQPWriter();
            $w->write_octet(2);
            $w->write_short($channel);
            $w->write_long(mb_strlen($packed_properties, 'ASCII') + 12);
            $w->write_short($class_id);
            $w->write_short($weight);
            $this->prepare_content_cache[$key_cache] = $w->getvalue();
            if (count($this->prepare_content_cache) > $this->prepare_content_cache_max_size) {
                reset($this->prepare_content_cache);
                $old_key = key($this->prepare_content_cache);
                unset($this->prepare_content_cache[$old_key]);
            }
        }
        $pkt->write($this->prepare_content_cache[$key_cache]);

        $pkt->write_longlong($body_size);
        $pkt->write($packed_properties);

        $pkt->write_octet(0xCE);


        // memory efficiency: walk the string instead of biting
        // it. good for very large packets (close in size to
        // memory_limit setting)
        $position = 0;
        $bodyLength = mb_strlen($body,'ASCII');
        while ($position < $bodyLength) {
            $payload = mb_substr($body, $position, $this->frame_max - 8, 'ASCII');
            $position += $this->frame_max - 8;

            $pkt->write_octet(3);
            $pkt->write_short($channel);
            $pkt->write_long(mb_strlen($payload, 'ASCII'));

            $pkt->write($payload);

            $pkt->write_octet(0xCE);
        }

        return $pkt;
    }

    /**
     * @param string $channel
     * @param array $method_sig
     * @param AMQPWriter|string $args
     * @param null $pkt
     */
    protected function send_channel_method_frame($channel, $method_sig, $args = '', $pkt = null)
    {
        $pkt = $this->prepare_channel_method_frame($channel, $method_sig, $args, $pkt);
        $this->write($pkt->getvalue());
        $this->debug->debug_method_signature1($method_sig);
    }

    /**
     * Returns a new AMQPWriter or mutates the provided $pkt
     *
     * @param string $channel
     * @param array $method_sig
     * @param AMQPWriter|string $args
     * @param AMQPWriter $pkt
     * @return AMQPWriter
     */
    protected function prepare_channel_method_frame($channel, $method_sig, $args = '', $pkt = null)
    {
        if ($args instanceof AMQPWriter) {
            $args = $args->getvalue();
        }

        $pkt = $pkt ?: new AMQPWriter();

        $pkt->write_octet(1);
        $pkt->write_short($channel);
        $pkt->write_long(mb_strlen($args, 'ASCII') + 4); // 4 = length of class_id and method_id
        // in payload

        $pkt->write_short($method_sig[0]); // class_id
        $pkt->write_short($method_sig[1]); // method_id
        $pkt->write($args);

        $pkt->write_octet(0xCE);

        $this->debug->debug_method_signature1($method_sig);

        return $pkt;
    }

    /**
     * Waits for a frame from the server
     *
     * @param int $timeout
     * @return array
     * @throws \Exception
     * @throws \PhpAmqpLib\Exception\AMQPTimeoutException
     * @throws \PhpAmqpLib\Exception\AMQPRuntimeException
     */
    protected function wait_frame($timeout = 0)
    {
        if (is_null($this->input))
        {
            $this->setIsConnected(false);
            throw new AMQPRuntimeException('Broken pipe or closed connection');
        }

        $currentTimeout = $this->input->getTimeout();
        $this->input->setTimeout($timeout);

        try {
            // frame_type + channel_id + size
            $this->wait_frame_reader->reuse(
                $this->input->read(AMQPReader::OCTET + AMQPReader::SHORT + AMQPReader::LONG)
            );

            $frame_type = $this->wait_frame_reader->read_octet();
            $class = self::$PROTOCOL_CONSTANTS_CLASS;
            if (!array_key_exists($frame_type, $class::$FRAME_TYPES)) {
                throw new AMQPRuntimeException('Invalid frame type ' . $frame_type);
            }
            $channel = $this->wait_frame_reader->read_short();
            $size = $this->wait_frame_reader->read_long();

            // payload + ch
            $this->wait_frame_reader->reuse($this->input->read(AMQPReader::OCTET + (int) $size));

            $payload = $this->wait_frame_reader->read($size);
            $ch = $this->wait_frame_reader->read_octet();

        } catch (AMQPTimeoutException $e) {
            $this->input->setTimeout($currentTimeout);
            throw $e;
        }

        $this->input->setTimeout($currentTimeout);

        if ($ch != 0xCE) {
            throw new AMQPRuntimeException(sprintf(
                'Framing error, unexpected byte: %x',
                $ch
            ));
        }

        return array($frame_type, $channel, $payload);
    }

    /**
     * Waits for a frame from the server destined for a particular channel.
     *
     * @param string $channel_id
     * @param int $timeout
     * @return array
     */
    protected function wait_channel($channel_id, $timeout = 0)
    {
        // Keeping the original timeout unchanged.
        $_timeout = $timeout;
        while (true) {
            $now = time();
            list($frame_type, $frame_channel, $payload) = $this->wait_frame($_timeout);

            if ($frame_channel === 0 && $frame_type === 8) {
                // skip heartbeat frames and reduce the timeout by the time passed
                if($_timeout > 0) {
                    $_timeout -= time() - $now;
                    if($_timeout <= 0) {
                        // If timeout has been reached, throw the exception without calling wait_frame
                        throw new AMQPTimeoutException("Timeout waiting on channel");
                    }
                }
                continue;

            } else {

                if ($frame_channel == $channel_id) {
                    return array($frame_type, $payload);
                }

                // Not the channel we were looking for.  Queue this frame
                //for later, when the other channel is looking for frames.
                // Make sure the channel still exists, it could have been
                // closed by a previous Exception.
                if (isset($this->channels[$frame_channel])) {
                    array_push($this->channels[$frame_channel]->frame_queue, array($frame_type, $payload));
                }

                // If we just queued up a method for channel 0 (the Connection
                // itself) it's probably a close method in reaction to some
                // error, so deal with it right away.
                if (($frame_type == 1) && ($frame_channel == 0)) {
                    $this->wait();
                }
            }
        }
    }

    /**
     * Fetches a channel object identified by the numeric channel_id, or
     * create that object if it doesn't already exist.
     *
     * @param string $channel_id
     * @return AMQPChannel
     */
    public function channel($channel_id = null)
    {
        if (isset($this->channels[$channel_id])) {
            return $this->channels[$channel_id];
        }

        $channel_id = $channel_id ? $channel_id : $this->get_free_channel_id();
        $ch = new AMQPChannel($this->connection, $channel_id);
        $this->channels[$channel_id] = $ch;

        return $ch;
    }

    /**
     * Requests a connection close
     *
     * @param int $reply_code
     * @param string $reply_text
     * @param array $method_sig
     * @return mixed|null
     */
    public function close($reply_code = 0, $reply_text = '', $method_sig = array(0, 0))
    {
        if ($this->io instanceof StreamIO)
        {
            $this->io->disableHeartbeat();
        }

        if (!$this->protocolWriter || !$this->isConnected()) {
            return null;
        }

        $this->closeChannels();

        list($class_id, $method_id, $args) = $this->protocolWriter->connectionClose(
            $reply_code,
            $reply_text,
            $method_sig[0],
            $method_sig[1]
        );
        $this->send_method_frame(array($class_id, $method_id), $args);

        $this->setIsConnected(false);

        return $this->wait(array(
            $this->waitHelper->get_wait('connection.close_ok')
        ));
    }

    /**
     * @param AMQPReader $reader
     * @throws \PhpAmqpLib\Exception\AMQPProtocolConnectionException
     */
    protected function connection_close(AMQPReader $reader)
    {
        $reply_code = $reader->read_short();
        $reply_text = $reader->read_shortstr();
        $class_id = $reader->read_short();
        $method_id = $reader->read_short();

        $this->x_close_ok();

        throw new AMQPProtocolConnectionException($reply_code, $reply_text, array($class_id, $method_id));
    }

    /**
     * Confirms a connection close
     */
    protected function x_close_ok()
    {
        $this->send_method_frame(
            explode(',', $this->waitHelper->get_wait('connection.close_ok'))
        );
        $this->do_close();
    }

    /**
     * Confirm a connection close
     *
     * @param AMQPReader $args
     */
    protected function connection_close_ok($args)
    {
        $this->do_close();
    }

    /**
     * @param string $virtual_host
     * @param string $capabilities
     * @param bool $insist
     * @return mixed
     */
    protected function x_open($virtual_host, $capabilities = '', $insist = false)
    {
        $args = new AMQPWriter();
        $args->write_shortstr($virtual_host);
        $args->write_shortstr($capabilities);
        $args->write_bits(array($insist));
        $this->send_method_frame(array(10, 40), $args);

        $wait = array(
            $this->waitHelper->get_wait('connection.open_ok')
        );

        if ($this->protocolVersion == '0.8') {
            $wait[] = $this->waitHelper->get_wait('connection.redirect');
        }

        return $this->wait($wait);
    }

    /**
     * Signals that the connection is ready
     *
     * @param AMQPReader $args
     */
    protected function connection_open_ok($args)
    {
        $this->known_hosts = $args->read_shortstr();
        $this->debug->debug_msg('Open OK! known_hosts: ' . $this->known_hosts);
    }

    /**
     * Asks the client to use a different server
     *
     * @param AMQPReader $args
     * @return string
     */
    protected function connection_redirect($args)
    {
        $host = $args->read_shortstr();
        $this->known_hosts = $args->read_shortstr();
        $this->debug->debug_msg(sprintf(
                'Redirected to [%s], known_hosts [%s]',
                $host,
                $this->known_hosts
            ));

        return $host;
    }

    /**
     * Security mechanism challenge
     *
     * @param AMQPReader $args
     */
    protected function connection_secure($args)
    {
        $challenge = $args->read_longstr();
    }

    /**
     * Security mechanism response
     *
     * @param string $response
     */
    protected function x_secure_ok($response)
    {
        $args = new AMQPWriter();
        $args->write_longstr($response);
        $this->send_method_frame(array(10, 21), $args);
    }

    /**
     * Starts connection negotiation
     *
     * @param AMQPReader $args
     */
    protected function connection_start($args)
    {
        $this->version_major = $args->read_octet();
        $this->version_minor = $args->read_octet();
        $this->server_properties = $args->read_table();
        $this->mechanisms = explode(' ', $args->read_longstr());
        $this->locales = explode(' ', $args->read_longstr());

        $this->debug->debug_connection_start(
            $this->version_major,
            $this->version_minor,
            $this->server_properties,
            $this->mechanisms,
            $this->locales
        );
    }

    /**
     * @param AMQPTable|array $clientProperties
     * @param string $mechanism
     * @param string $response
     * @param string $locale
     */
    protected function x_start_ok($clientProperties, $mechanism, $response, $locale)
    {
        $args = new AMQPWriter();
        $args->write_table($clientProperties);
        $args->write_shortstr($mechanism);
        $args->write_longstr($response);
        $args->write_shortstr($locale);
        $this->send_method_frame(array(10, 11), $args);
    }

    /**
     * Proposes connection tuning parameters
     *
     * @param AMQPReader $args
     */
    protected function connection_tune($args)
    {
        $v = $args->read_short();
        if ($v) {
            $this->channel_max = $v;
        }

        $v = $args->read_long();
        if ($v) {
            $this->frame_max = $v;
        }

        // use server proposed value if not set
        if ($this->heartbeat === null) {
            $this->heartbeat = $args->read_short();
        }

        $this->x_tune_ok($this->channel_max, $this->frame_max, $this->heartbeat);
    }

    /**
     * Negotiates connection tuning parameters
     *
     * @param int $channel_max
     * @param int $frame_max
     * @param int $heartbeat
     */
    protected function x_tune_ok($channel_max, $frame_max, $heartbeat)
    {
        $args = new AMQPWriter();
        $args->write_short($channel_max);
        $args->write_long($frame_max);
        $args->write_short($heartbeat);
        $this->send_method_frame(array(10, 31), $args);
        $this->wait_tune_ok = false;
    }

    /**
     * @return SocketIO
     */
    public function getSocket()
    {
        return $this->io->getSocket();
    }

    /**
     * @return \PhpAmqpLib\Wire\IO\AbstractIO
     */
    protected function getIO()
    {
        return $this->io;
    }

    /**
     * Handles connection blocked notifications
     *
     * @param AMQPReader $args
     */
    protected function connection_blocked(AMQPReader $args)
    {
        // Call the block handler and pass in the reason
        $this->dispatch_to_handler($this->connection_block_handler, array($args->read_shortstr()));
    }

    /**
     * Handles connection unblocked notifications
     *
     * @param AMQPReader $args
     */
    protected function connection_unblocked(AMQPReader $args)
    {
        // No args to an unblock event
        $this->dispatch_to_handler($this->connection_unblock_handler, array());
    }

    /**
     * Sets a handler which is called whenever a connection.block is sent from the server
     *
     * @param callable $callback
     */
    public function set_connection_block_handler($callback)
    {
        $this->connection_block_handler = $callback;
    }

    /**
     * Sets a handler which is called whenever a connection.block is sent from the server
     *
     * @param callable $callback
     */
    public function set_connection_unblock_handler($callback)
    {
        $this->connection_unblock_handler = $callback;
    }

    /**
     * Gets the connection status
     *
     * @return bool
     */
    public function isConnected()
    {
        return (bool) $this->is_connected;
    }

    /**
     * Set the connection status
     *
     * @param bool $is_connected
     */
    protected function setIsConnected($is_connected)
    {
        $this->is_connected = (bool) $is_connected;
    }

    /**
     * Closes all available channels
     */
    protected function closeChannels()
    {
        foreach ($this->channels as $key => $channel) {
            // channels[0] is this connection object, so don't close it yet
            if ($key === 0) {
                continue;
            }
            try {
                $channel->close();
            } catch (\Exception $e) {
                /* Ignore closing errors */
            }
        }
    }

    /**
     * Should the connection be attempted during construction?
     *
     * @return bool
     */
    public function connectOnConstruct()
    {
        return true;
    }

    /**
     * @return array
     */
    public function getServerProperties()
    {
        return $this->server_properties;
    }
}
