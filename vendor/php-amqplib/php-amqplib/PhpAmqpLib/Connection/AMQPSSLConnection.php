<?php
namespace PhpAmqpLib\Connection;

class AMQPSSLConnection extends AMQPStreamConnection
{
    /**
     * @param string $host
     * @param int $port
     * @param string $user
     * @param string $password
     * @param string $vhost
     * @param array $ssl_options
     * @param array $options
     */
    public function __construct(
        $host,
        $port,
        $user,
        $password,
        $vhost = '/',
        $ssl_options = array(),
        $options = array()
    ) {
        $ssl_context = empty($ssl_options) ? null : $this->create_ssl_context($ssl_options);
        parent::__construct(
            $host,
            $port,
            $user,
            $password,
            $vhost,
            isset($options['insist']) ? $options['insist'] : false,
            isset($options['login_method']) ? $options['login_method'] : 'AMQPLAIN',
            isset($options['login_response']) ? $options['login_response'] : null,
            isset($options['locale']) ? $options['locale'] : 'en_US',
            isset($options['connection_timeout']) ? $options['connection_timeout'] : 3,
            isset($options['read_write_timeout']) ? $options['read_write_timeout'] : 3,
            $ssl_context,
            isset($options['keepalive']) ? $options['keepalive'] : false,
            isset($options['heartbeat']) ? $options['heartbeat'] : 0
        );
    }

    /**
     * @param array $options
     * @return resource
     */
    private function create_ssl_context($options)
    {
        $ssl_context = stream_context_create();
        foreach ($options as $k => $v) {
            stream_context_set_option($ssl_context, 'ssl', $k, $v);
        }

        return $ssl_context;
    }
}
