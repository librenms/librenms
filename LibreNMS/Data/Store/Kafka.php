<?php

namespace LibreNMS\Data\Store;

use App\Facades\DeviceCache;
use App\Polling\Measure\Measurement;
use Carbon\Carbon;
use Illuminate\Support\Facades\Log;
use LibreNMS\Config;
use RdKafka\Conf;
use RdKafka\FFI\Library;
use RdKafka\Producer;

class Kafka extends BaseDatastore
{
    private $client;
    private $device_id;
    private $isShuttingDown = false;

    public function __construct()
    {
        parent::__construct();
        $conf = new Conf();
        // Set the kafka broker servers
        $conf->set('bootstrap.servers', Config::get('kafka.broker.list', 'kafka:9092'));
        // Set the idempotence
        $conf->set('enable.idempotence', Config::get('kafka.idempotence') ? 'true' : 'false');
        // Max queue allowed messages in poller memory
        $conf->set('queue.buffering.max.messages', Config::get('kafka.buffer.max.message', 100_000));
        // Num of messages each call to kafka
        $conf->set('batch.num.messages', Config::get('kafka.batch.max.message', 25));
        // Max wait time to acumulate before sending the batch
        $conf->set('linger.ms', Config::get('kafka.linger.ms', default: 500));
        // Change ACK
        $conf->set('request.required.acks', Config::get('kafka.request.required.acks', default: 1));

        // check if debug for ssl was set and enable it
        $confKafkaSSLDebug = Config::get('kafka.security.debug', null);
        $confKafkaSSLDebug != null || strlen($confKafkaSSLDebug) !== 0 ? $conf->set('debug', $confKafkaSSLDebug) : null;

        // config ssl
        $isSslEnabled = Config::get('kafka.ssl.enable', false);
        if ($isSslEnabled) {
            $conf->set('security.protocol', Config::get('kafka.ssl.protocol', 'ssl'));
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
                $configValue = Config::get($configKey, null);
                $configValue != null || strlen($configValue) !== 0 ? $conf->set($kafkaKey, $configValue) : null;
            }
        }

        $this->client = new Producer($conf);

        // Register shutdown function
        register_shutdown_function(function () {
            $this->isShuttingDown = true;
            $this->safeFlush();
        });
    }

   public function __destruct()
    {
        if (! $this->isShuttingDown) {
            $this->safeFlush();
        }
        // Clear reference
        $this->client = null;
    }

    private function safeFlush()
    {
        // check if client instance exists
        if (! $this->client) {
            return;
        }

        try {
            // get total number of messages in the queue
            $outQLen = $this->client->getOutQLen();

            if ($outQLen > 0) {
                Log::debug("KAFKA: Flushing {$outQLen} remaining messages");
                $result = $this->client->flush(self::getKafkaFlushTimeout());

                if (RD_KAFKA_RESP_ERR_NO_ERROR !== $result) {
                    Log::error('KAFKA: Flush failed', [
                        'error' => Library::rd_kafka_err2str($result),
                        'code' => $result,
                        'device_id' => $this->device_id,
                        'remaining' => $this->client->getOutQLen(),
                    ]);
                }
            }
        } catch (\Throwable $e) {
            Log::error('KAFKA: safeFlush failed with exception', [
                'device_id' => $this->device_id,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);
        }
    }

    public function getName()
    {
        return 'Kafka';
    }

    public static function isEnabled()
    {
        return Config::get('kafka.enable', false);
    }

    public static function getKafkaFlushTimeout()
    {
        return Config::get('kafka.flush.timeout', 50);
    }

    /**
     * Datastore-independent function which should be used for all polled metrics.
     *
     * RRD Tags:
     *   rrd_def     RrdDefinition
     *   rrd_name    array|string: the rrd filename, will be processed with rrd_name()
     *   rrd_oldname array|string: old rrd filename to rename, will be processed with rrd_name()
     *   rrd_step             int: rrd step, defaults to 300
     *
     * @param  array  $device
     * @param  string  $measurement  Name of this measurement
     * @param  array  $tags  tags for the data (or to control rrdtool)
     * @param  array|mixed  $fields  The data to update in an associative array, the order must be consistent with rrd_def,
     *                               single values are allowed and will be paired with $measurement
     */
    public function put($device, $measurement, $tags, $fields)
    {
        try {
            // get the singleton instance of the produced
            /** @var Producer $producer */
            $producer = $this->client;
            $this->device_id = $device['device_id'];
            $topic = $producer->newTopic(Kafka::getTopicName());

            $device_data = DeviceCache::get($device['device_id']);
            $excluded_groups = Config::get('kafka.groups-exclude'); // comman separated string
            $excluded_measurement = Config::get('kafka.measurement-exclude'); // comman separated string
            $excluded_device_fields = Config::get('kafka.device-fields-exclude'); // comman separated string
            $excluded_device_fields_arr = [];

            if ($excluded_groups != null && strlen($excluded_groups) > 0) {
                // convert into array
                $excluded_groups_arr = explode(',', strtoupper($excluded_groups));

                $device_groups = $device_data->groups;
                foreach ($device_groups as $group) {
                    // The group name will always be parsed as lowercase, even when uppercase in the GUI.
                    if (in_array(strtoupper($group->name), $excluded_groups_arr)) {
                        Log::debug('KAFKA: Skipped parsing to Kafka, device is in group: ' . $group->name);

                        return;
                    }
                }
            }

            if ($excluded_measurement != null && strlen($excluded_measurement) > 0) {
                // convert into array
                $excluded_measurement_arr = explode(',', $excluded_measurement);

                if (in_array($measurement, $excluded_measurement_arr)) {
                    Log::debug('KAFKA: Skipped parsing to Kafka, measurement is in measurement-excluded: ' . $measurement);

                    return;
                }
            }

            if ($excluded_device_fields != null && strlen($excluded_device_fields) > 0) {
                // convert into array
                $excluded_device_fields_arr = explode(',', $excluded_device_fields);
            }

            // start
            $stat = Measurement::start('write');

            $tmp_fields = [];
            $tmp_tags = [];
            // NETMETRIX-LIBRENMS-CUSTOM-ADDED-BEGIN
            // REASON: Add groups as tags
            $tmp_tags['device_groups'] = implode('|', $device_data->groups->pluck('name')->toArray());
            // NETMETRIX-LIBRENMS-CUSTOM-ADDED-END

            foreach ($tags as $k => $v) {
                if (empty($v)) {
                    $v = '_blank_';
                }
                $tmp_tags[$k] = $v;
            }
            foreach ($fields as $k => $v) {
                if ($k == 'time') {
                    $k = 'rtime';
                }

                if (($value = $this->forceType($v)) !== null) {
                    $tmp_fields[$k] = $value;
                }
            }

            if (empty($tmp_fields)) {
                Log::warning('KAFKA: All fields empty, skipping update', [
                    'orig_fields' => $fields,
                    'device_id' => $this->device_id,
                ]);

                return;
            }

            // create and organize data
            $filteredDeviceData = array_diff_key($device, array_flip($excluded_device_fields_arr));
            // add current time to the data
            $filteredDeviceData['current_polled_time'] = Carbon::now();

            $resultArr = [
                'measurement' => $measurement,
                'device' => $filteredDeviceData,
                'fields' => $tmp_fields,
                'tags' => $tmp_tags,
            ];

            if (Config::get('kafka.debug') === true) {
                Log::debug('Kafka data: ', [
                    'device_id' => $this->device_id,
                    'measurement' => $measurement,
                    'fields' => $tmp_fields,
                ]);
            }

            $dataArr = json_encode($resultArr);
            $topic->produce(RD_KAFKA_PARTITION_UA, 0, $dataArr);
            $producer->poll(0);

            // end
            $this->recordStatistic($stat->end());
        } catch (\Throwable $e) {
            Log::error('KAFKA: Put failed with exception', [
                'device_id' => $this->device_id,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
        }
    }

    private function forceType($data)
    {
        /*
         * It is not trivial to detect if something is a float or an integer, and
         * therefore may cause breakages on inserts.
         * Just setting every number to a float gets around this, but may introduce
         * inefficiencies.
         */

        if (is_numeric($data)) {
            return floatval($data);
        }

        return $data === 'U' ? null : $data;
    }

    public static function getTopicName()
    {
        return Config::get('kafka.topic', 'librenms');
    }

    /**
     * Checks if the datastore wants rrdtags to be sent when issuing put()
     *
     * @return bool
     */
    public function wantsRrdTags()
    {
        return false;
    }
    
}