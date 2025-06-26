<?php

namespace LibreNMS\Tests\Unit\Data;

use App\Facades\LibrenmsConfig;
use LibreNMS\Data\Store\Kafka;
use LibreNMS\Tests\TestCase;
use PHPUnit\Framework\Attributes\Group;

#[Group('external-dependencies')]
class KafkaDBStoreTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        LibrenmsConfig::set('kafka.enable', true);
        LibrenmsConfig::set('kafka.broker.list', 'localhost:9092');
        LibrenmsConfig::set('kafka.topic', 'librenms');
        LibrenmsConfig::set('kafka.idempotence', false);
        LibrenmsConfig::set('kafka.buffer.max.message', 10);
        LibrenmsConfig::set('kafka.batch.max.message', 25);
        LibrenmsConfig::set('kafka.linger.ms', 5000);
        LibrenmsConfig::set('kafka.request.required.acks', 0);
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
            'device' => $device,
        ];
        $kafka->write($measurement, $fields, $tags, $metadata);
    }

    protected function tearDown(): void
    {
        LibrenmsConfig::set('kafka.enable', false);
        parent::tearDown();
    }
}
