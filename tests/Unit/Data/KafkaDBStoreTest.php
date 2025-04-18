<?php

namespace LibreNMS\Tests\Unit\Data;

use LibreNMS\Config;
use LibreNMS\Data\Store\Kafka;
use LibreNMS\Tests\TestCase;

class KafkaDBStoreTest extends TestCase
{
    private ?\RdKafka\Test\MockCluster $cluster = null;

    private function createMockCluster(): \RdKafka\Test\MockCluster
    {
        $clusterConf = new \RdKafka\Conf();
        $clusterConf->setLogCb(null);

        $numberOfBrokers = 1;
        $cluster = \RdKafka\Test\MockCluster::create($numberOfBrokers, $clusterConf);

        return $cluster;
    }

    protected function setUp(): void
    {
        parent::setUp();

        $this->cluster = $this->createMockCluster();

        Config::set('kafka.enable', true);
        Config::set('kafka.broker.list', $this->cluster->getBootstraps());
        Config::set('kafka.idempotence', false);
        Config::set('kafka.buffer.max.message', 10);
        Config::set('kafka.batch.max.message', 25);
        Config::set('kafka.linger.ms', 50);
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

        $kafka->put($device, $measurement, $tags, $fields);
    }

    protected function tearDown(): void
    {
        $this->cluster = null;
        Config::set('kafka.enable', false);
        parent::tearDown();
    }
}
