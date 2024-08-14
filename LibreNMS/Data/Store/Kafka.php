<?php

namespace LibreNMS\Data\Store;

use App\Facades\DeviceCache;
use App\Polling\Measure\Measurement;
use LibreNMS\Config;
use Illuminate\Support\Facades\Log;
use RdKafka\Conf;
use RdKafka\Producer;

class Kafka extends BaseDatastore
{
    public function getName()
    {
        return 'Kafka';
    }

    public static function isEnabled()
    {
        return Config::get('kafka.enable', false);
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
            $producer = Kafka::createFromConfig();
            $topic = $producer->newTopic(Kafka::getTopicName());

            $device_data = DeviceCache::get($device['device_id']);
            $excluded_groups = Config::get('kafka.groups-exclude');

            if (! empty($excluded_groups)) {
                $device_groups = $device_data->groups;
                foreach ($device_groups as $group) {
                    // The group name will always be parsed as lowercase, even when uppercase in the GUI.
                    if (in_array(strtoupper($group->name), array_map('strtoupper', $excluded_groups))) {
                        Log::warning('KAFKA: Skipped parsing to Kafka, device is in group: ' . $group->name);

                        return;
                    }
                }
            }

            // start
            $stat = Measurement::start('write');
            $tmp_fields = [];
            $tmp_tags['hostname'] = $device['hostname'];
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
                Log::warning('KAFKA: All fields empty, skipping update', ['orig_fields' => $fields]);

                return;
            }

            if (Config::get('kafka.debug') === true) {
                Log::debug('Kafka data: ', [
                    'measurement' => $measurement,
                    'tags' => $tmp_tags,
                    'fields' => $tmp_fields,
                ]);
            }

            // summary data
            $data['device'] = $device_data;
            $data['fields'] = $tmp_fields;
            $data['tags'] = $tmp_tags;
            $data['measurement'] = $measurement;

            // end
            $this->recordStatistic($stat->end());

            $dataArr = json_encode($data);
            $topic->produce(RD_KAFKA_PARTITION_UA, 0, $dataArr);
            $producer->poll(0);

            for ($flushRetries = 0; $flushRetries < 10; $flushRetries++) {
                $result = $producer->flush(10000);
                if (RD_KAFKA_RESP_ERR_NO_ERROR === $result) {
                    break;
                }
            }

            if (RD_KAFKA_RESP_ERR_NO_ERROR !== $result) {
                Log::warning('KAFKA: Was unable to flush, messages might be lost!');
            }
        } catch (\Throwable $th) {
            Log::warning($th);
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

    /**
     * Create a instance of Kafka Producer
     *
     * @return Producer
     */
    public static function createFromConfig()
    {
        $conf = new Conf();
        $conf->set('bootstrap.servers', Config::get('kafka.broker.list', 'kafka:9092'));
        $conf->set('enable.idempotence', Config::get('kafka.idempotence', 'false'));

        // config ssl
        $isSslEnabled = Config::get('kafka.ssl.enable', false);
        if ($isSslEnabled) {
            $conf->set('security.protocol', Config::get('kafka.ssl.protocol', 'ssl'));
            $conf->set('ssl.ca.location', Config::get('kafka.ssl.ca.location', '/etc/kafka/secrets/ca-cert'));
            $conf->set('ssl.certificate.location', Config::get('kafka.ssl.certificate.location', '/etc/kafka/secrets/cert.pem'));
            $conf->set('ssl.key.location', Config::get('kafka.ssl.key.location', '/etc/kafka/secrets/cert.key'));
            $conf->set('ssl.key.password', Config::get('kafka.ssl.key.password', 'datahub'));
        }

        return new Producer($conf);
    }

    public static function getTopicName()
    {
        return Config::get('kafka.topic', 'netmetrix');
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
