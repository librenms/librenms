<?php

namespace LibreNMS\Tests\Unit\Data;

use LibreNMS\Config;
use LibreNMS\Data\Store\Kafka;
use LibreNMS\Tests\TestCase;
use RdKafka\Producer;

class KafkaDBStoreTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();
        Config::set('kafka.enable', true);
        Config::set('kafka.broker.list', '127.1.2.3:9092');
        Config::set('kafka.idempotence', false);
        Config::set('kafka.buffer.max.message', 10);
        Config::set('kafka.batch.max.message', 25);
        Config::set('kafka.linger.ms', 50);
        Config::set('kafka.request.required.acks', 0);
    }

    public function testDataPushToKafka()
    {
        $kafka = \Mockery::mock(Kafka::class);
        $producer = \Mockery::mock(Producer::class);
        $topic = \Mockery::mock(\RdKafka\ProducerTopic::class);

        $kafka->shouldReceive('put')->once();
        $kafka->shouldReceive('getClient')->andReturn($producer);
        $producer->shouldReceive('newTopic')->andReturn($topic);

        $device = ['device_id' => 1, 'hostname' => 'testhost'];
        $measurement = 'excluded_measurement';
        $tags = ['ifName' => 'testifname', 'type' => 'testtype'];
        $fields = ['ifIn' => 234234, 'ifOut' => 53453];

        $kafka->put($device, $measurement, $tags, $fields);
    }
}
