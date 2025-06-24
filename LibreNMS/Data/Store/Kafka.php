<?php

namespace LibreNMS\Data\Store;

use App\Facades\LibrenmsConfig;
use App\Polling\Measure\Measurement;
use Carbon\Carbon;
use Illuminate\Support\Facades\Log;
use RdKafka\Conf;
use RdKafka\FFI\Library;
use RdKafka\Message;
use RdKafka\Producer;

class Kafka extends BaseDatastore
{
    private $client = null;
    private $topicName = null;
    private $kafkaFlushTimeout = 100;
    private $excluded_groups = [];
    private $excluded_measurement = [];

    public function __construct(Producer $client)
    {
        parent::__construct();

        $this->client = $client;

        // Load the topic name from config
        $this->topicName = LibrenmsConfig::get('kafka.topic', 'librenms');

        // Cache the flush timeout value early to avoid Config during shutdown
        if ($this->kafkaFlushTimeout == null) {
            $this->kafkaFlushTimeout = LibrenmsConfig::get('kafka.flush.timeout', 100);
        }

        // Load excluded values from config
        foreach (LibrenmsConfig::get('kafka.groups-exclude', []) as $exclude_group) {
            // Ensure its a valid number and parse it as an integer
            if (! is_numeric($exclude_group)) {
                Log::warning('KAFKA: Excluded group is not a valid number', [
                    'exclude_group' => $exclude_group,
                ]);
                continue;
            }
            $this->excluded_groups[] = (int) $exclude_group;
        }
        foreach (LibrenmsConfig::get('kafka.measurement-exclude', []) as $exclude_measurement) {
            $this->excluded_measurement[] = trim(strtolower($exclude_measurement));
        }
    }

    public function __destruct()
    {
        $this->terminate();
    }

    public function terminate(): void
    {
        // Safely flush the producer to ensure all messages are sent before shutdown
        $this->safeFlush();
    }

    public static function getClient(): Producer
    {
        $conf = new Conf();
        // Set the log level
        $conf->set('log_level', (string) LOG_DEBUG);
        // Set the log callback for exceptions
        $conf->setDrMsgCb(
            function (Producer $producer, Message $message): void {
                if ($message->err !== RD_KAFKA_RESP_ERR_NO_ERROR) {
                    Log::error(
                        'KAFKA: Delivery failed',
                        [
                            'error' => $message->errstr(),
                        ]
                    );
                }
            }
        );
        // Set the log callback for logs
        $conf->setLogCb(
            function (Producer $producer, int $level, string $facility, string $message): void {
                error_log('KAFKA: ' . $message);
            }
        );

        // Set the kafka broker servers
        $conf->set('bootstrap.servers', LibrenmsConfig::get('kafka.broker.list', '127.0.2.2:9092'));
        // Set the idempotence
        $conf->set('enable.idempotence', LibrenmsConfig::get('kafka.idempotence', false) ? 'true' : 'false');
        // Max queue allowed messages in poller memory
        $conf->set('queue.buffering.max.messages', LibrenmsConfig::get('kafka.buffer.max.message', 1_000));
        // Num of messages each call to kafka
        $conf->set('batch.num.messages', LibrenmsConfig::get('kafka.batch.max.message', 200));
        // Max wait time to acumulate before sending the batch
        $conf->set('linger.ms', LibrenmsConfig::get('kafka.linger.ms', default: 50));
        // Change ACK
        $conf->set(
            'request.required.acks',
            // If idempotence is enabled, set to 'all' to ensure all messages are acknowledged
            // Otherwise, use the configured value or default to '1'
            // '1' means the leader will acknowledge the message, 'all' means all replicas must acknowledge
            LibrenmsConfig::get('kafka.idempotence', false) ? 'all' :
                (LibrenmsConfig::get('kafka.request.required.acks', '-1'))
        );

        // check if debug for ssl was set and enable it
        $confKafkaSSLDebug = LibrenmsConfig::get('kafka.security.debug', null);
        $confKafkaSSLDebug != null || strlen($confKafkaSSLDebug) !== 0 ? $conf->set('debug', $confKafkaSSLDebug) : null;

        // config ssl
        $isSslEnabled = LibrenmsConfig::get('kafka.ssl.enable', false);
        if ($isSslEnabled) {
            $conf->set('security.protocol', LibrenmsConfig::get('kafka.ssl.protocol', 'ssl'));
            $conf->set('ssl.endpoint.identification.algorithm', 'none');

            // prepare all necessary librenms kafka config with associated rdkafka key
            $kafkaSSLConfigs = [
                'kafka.ssl.keystore.location' => 'ssl.keystore.location',
                'kafka.ssl.keystore.password' => 'ssl.keystore.password',
                'kafka.ssl.ca.location' => 'ssl.ca.location',
                'kafka.ssl.certificate.location' => 'ssl.certificate.location',
                'kafka.ssl.key.location' => 'ssl.key.location',
                'kafka.ssl.key.password' => 'ssl.key.password',
            ];

            // fetch kafka config values, if exists, associate its value to rdkafka key
            foreach ($kafkaSSLConfigs as $configKey => $kafkaKey) {
                $configValue = LibrenmsConfig::get($configKey, null);
                $configValue != null || strlen($configValue) !== 0 ? $conf->set($kafkaKey, $configValue) : null;
            }
        }

        return new Producer($conf);
    }

    public function safeFlush()
    {
        // check if client instance exists
        if ($this->client === null) {
            return;
        }

        try {
            // get total number of messages in the queue
            $outQLen = $this->client->getOutQLen();

            if ($outQLen > 0) {
                // During shutdown, Log facades might not work properly, use d_echo as fallback
                error_log("KAFKA: SafeFlush | Flushing {$outQLen} remaining messages");

                // Use cached timeout value to avoid Config during shutdown
                $result = $this->client->flush($this->kafkaFlushTimeout);

                if (RD_KAFKA_RESP_ERR_NO_ERROR !== $result) {
                    $error_msg = sprintf(
                        'KAFKA: SafeFlush | Flush failed. Error: %s, Code: %d, Remaining: %d',
                        Library::rd_kafka_err2str($result),
                        $result,
                        $this->client->getOutQLen()
                    );

                    error_log($error_msg);
                }
            }
        } catch (\Throwable $e) {
            $error_msg = 'KAFKA: SafeFlush | failed with exception. Error: ' . $e->getMessage() . '. Trace: ' . $e->getTraceAsString();
            error_log($error_msg);
        } finally {
            // Reset the client to null to avoid further operations
            $this->client = null;
        }
    }

    public function getName(): string
    {
        return 'Kafka';
    }

    public static function isEnabled(): bool
    {
        return LibrenmsConfig::get('kafka.enable', false);
    }

    public function getKafkaFlushTimeout()
    {
        return $this->kafkaFlushTimeout;
    }

    /**
     * @inheritDoc
     */
    public function write(string $measurement, array $fields, array $tags = [], array $meta = []): void
    {
        try {
            $device_data = $this->getDevice($meta);
            // get the singleton instance of the produced
            /** @var Producer $producer */
            $producer = $this->client;
            $topic = $producer->newTopic($this->topicName);

            // Check if the device is excluded from Kafka processing
            $total_groups_excluded = $device_data->groups->whereIn('id', $this->excluded_groups)->count();
            if ($total_groups_excluded > 0) {
                Log::debug('KAFKA: Skipped parsing to Kafka, measurement ' . $measurement . ' device is in excluded group', [
                    'device_id' => $device_data->device_id,
                    'measurement' => $measurement,
                    'excluded_groups_id' => $this->excluded_groups,
                ]);

                return;
            }

            // If the measurement is in the excluded list, skip processing
            if (in_array($measurement, $this->excluded_measurement)) {
                Log::debug('KAFKA: Skipped parsing to Kafka, measurement is in measurement-excluded: ' . $measurement);

                return;
            }

            // start
            $stat = Measurement::start('write');

            // remove tags with empty values
            $tags = array_filter($tags, function ($value) {
                return $value !== '' && $value !== null;
            });

            if (empty($fields)) {
                Log::warning('KAFKA: All fields empty, skipping update', [
                    'device_id' => $device_data->device_id,
                ]);

                return;
            }

            // add current sent time in unix timestamp format
            $tags['current_polled_time'] = Carbon::now()->timestamp;
            // if hostname is not set, use device hostname
            if (! isset($tags['hostname'])) {
                $tags['hostname'] = $device_data->hostname;
            }

            $resultArr = [
                'measurement' => $measurement,
                'fields' => $fields,
                'tags' => $tags,
            ];

            if (LibrenmsConfig::get('kafka.debug') === true) {
                Log::debug('Kafka data: ', [
                    'device_id' => $device_data->device_id,
                    'measurement' => $measurement,
                    'fields' => $fields,
                ]);
            }

            $dataArr = json_encode($resultArr);
            $topic->produce(RD_KAFKA_PARTITION_UA, 0, $dataArr, $device_data->device_id);

            // If debug is enabled, log the total size of the data being sent
            if (LibrenmsConfig::get('kafka.debug') === true) {
                $outQLen = $this->client->getOutQLen();
                Log::debug('KAFKA: Flush | Data size', [
                    'device_id' => $device_data->device_id,
                    'measurement' => $measurement,
                    'size' => $outQLen,
                ]);
            }

            $producer->poll(0);

            // end
            $this->recordStatistic($stat->end());
        } catch (\Throwable $e) {
            Log::error('KAFKA: Put failed with exception', [
                'device_id' => $device_data->device_id,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);
        }
    }
}
