<?php

namespace LibreNMS\Tests\Unit\Data;

use LibreNMS\Config;
use LibreNMS\Data\Store\Kafka;
use LibreNMS\Tests\TestCase;
use PHPUnit\Framework\Attributes\Group;

#[Group('external-dependencies')]
class KafkaDBStoreTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        Config::set('kafka.enable', true);
        Config::set('kafka.broker.list', 'localhost:9092');
        Config::set('kafka.topic', 'librenms');
        Config::set('kafka.idempotence', false);
        Config::set('kafka.buffer.max.message', 10);
        Config::set('kafka.batch.max.message', 25);
        Config::set('kafka.linger.ms', 5000);
        Config::set('kafka.request.required.acks', 0);
    }

    public function testDataPushToKafka()
    {
        $producer = \Mockery::mock(Kafka::getClient());
        $producer->shouldReceive('newTopic')->once();

        /** @var \RdKafka\Producer $producer */
        $producer = $producer;
        $kafka = new Kafka($producer);

        $device = ['device_id' => 1, 'hostname' => 'testhost'];
        $measurement = 'excluded_measurement';
        $tags = ['ifName' => 'testifname', 'type' => 'testtype'];
        $fields = ['ifIn' => 234234, 'ifOut' => 53453];

        $metadata = [
            'device' => $device
        ];
        $kafka->write($measurement, $tags, $fields, $metadata);
    }

    protected function tearDown(): void
    {
        Config::set('kafka.enable', false);
        parent::tearDown();
    }
}
