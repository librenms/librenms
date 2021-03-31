<?php

namespace LibreNMS\Alert\Transport;

use LibreNMS\Alert\Transport;
use PhpAmqpLib\Connection\AMQPStreamConnection;
use PhpAmqpLib\Exchange\AMQPExchangeType;
use PhpAmqpLib\Message\AMQPMessage;

class Canopsis extends Transport
{
    public function deliverAlert($obj, $opts)
    {
        if (! empty($this->config)) {
            $opts['host'] = $this->config['canopsis-host'];
            $opts['port'] = $this->config['canopsis-port'];
            $opts['user'] = $this->config['canopsis-user'];
            $opts['pass'] = $this->config['canopsis-pass'];
            $opts['vhost'] = $this->config['canopsis-vhost'];
        }

        return $this->contactCanopsis($obj, $opts);
    }

    public function contactCanopsis($obj, $opts)
    {
        // Configurations
        $host = $opts['host'];
        $port = $opts['port'];
        $user = $opts['user'];
        $pass = $opts['pass'];
        $vhost = $opts['vhost'];
        $exchange = 'canopsis.events';

        // Connection
        $conn = new AMQPStreamConnection($host, $port, $user, $pass, $vhost);
        $ch = $conn->channel();

        // Declare exchange (if not exist)
        // exchange_declare($exchange, $type, $passive=false, $durable=false, $auto_delete=true, $internal=false, $nowait=false, $arguments=null, $ticket=null)
        $ch->exchange_declare($exchange, AMQPExchangeType::TOPIC, false, true, false);

        // Create Canopsis event, see: https://github.com/capensis/canopsis/wiki/Event-specification
        switch ($obj['severity']) {
            case 'ok':
                $state = 0;
                break;
            case 'warning':
                $state = 2;
                break;
            case 'critical':
                $state = 3;
                break;
            default:
                $state = 0;
        }
        $msg_body = [
            'timestamp' => time(),
            'connector' => 'librenms',
            'connector_name' => 'LibreNMS1',
            'event_type' => 'check',
            'source_type' => 'resource',
            'component' => $obj['hostname'],
            'resource' => $obj['name'],
            'state' => $state,
            'output' => $obj['msg'],
            'display_name' => 'librenms',
        ];
        $msg_raw = json_encode($msg_body);

        // Build routing key
        $msg_rk = '';
        if ($msg_body['source_type'] == 'resource') {
            $msg_rk = $msg_rk . '.' . $msg_body['resource'];
        } else {
            $msg_rk = $msg_body['connector'] . '.' . $msg_body['connector_name'] . '.' . $msg_body['event_type'] . '.' . $msg_body['source_type'] . '.' . $msg_body['component'];
        }

        // Publish Event
        $msg = new AMQPMessage($msg_raw, ['content_type' => 'application/json', 'delivery_mode' => 2]);
        $ch->basic_publish($msg, $exchange, $msg_rk);

        // Close connection
        $ch->close();
        $conn->close();

        return true;
    }

    public static function configTemplate()
    {
        return [
            'config' => [
                [
                    'title' => 'Hostname',
                    'name' => 'canopsis-host',
                    'descr' => 'Canopsis Hostname',
                    'type' => 'text',
                ],
                [
                    'title' => 'Port Number',
                    'name' => 'canopsis-port',
                    'descr' => 'Canopsis Port Number',
                    'type' => 'text',
                ],
                [
                    'title' => 'User',
                    'name' => 'canopsis-user',
                    'descr' => 'Canopsis User',
                    'type' => 'text',
                ],
                [
                    'title' => 'Password',
                    'name' => 'canopsis-pass',
                    'descr' => 'Canopsis Password',
                    'type' => 'text',
                ],
                [
                    'title' => 'Vhost',
                    'name' => 'canopsis-vhost',
                    'descr' => 'Canopsis Vhost',
                    'type' => 'text',
                ],
            ],
            'validation' => [
                'canopsis-host' => 'required|string',
                'canopsis-port' => 'required|numeric',
                'canopsis-user' => 'required|string',
                'canopsis-pass' => 'required|string',
                'canopsis-vhost' => 'required|string',
            ],
        ];
    }
}
