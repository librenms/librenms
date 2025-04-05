<?php

namespace LibreNMS\Tests\Unit\Data;

use LibreNMS\Config;
use LibreNMS\Data\Store\Kafka;
use LibreNMS\Tests\TestCase;
use RdKafka\Producer;
use Illuminate\Support\Facades\Log;

class KafkaDBStoreTest extends TestCase
{
    private function getKafkaMockedClusterConfig()
    {
        $clusterConf = new \RdKafka\Conf();
        $clusterConf->setLogCb(
            function (Producer $producer, int $level, string $facility, string $message): void {
            }
        );

        return $clusterConf;
    }

    public function getMockedKafkaCluster()
    {
        // Create mock cluster
        $numberOfBrokers = 3;
        $clusterConf = self::getKafkaMockedClusterConfig();

        return \RdKafka\Test\MockCluster::create($numberOfBrokers, $clusterConf);
    }

    protected function setUp(): void
    {
        parent::setUp();
        $cluster = self::getMockedKafkaCluster();
        Config::set('kafka.enable', true);
        Config::set('kafka.broker.list', $cluster->getBootstraps());
        Config::set('kafka.idempotence', true);
        Config::set('kafka.buffer.max.message', 100_000);
        Config::set('kafka.batch.max.message', 25);
        Config::set('kafka.linger.ms', 500);
        Config::set('kafka.request.required.acks', 'all');
    }

    public function testKafkaConfiguration()
    {
        $kafka = new Kafka();
        $this->assertInstanceOf(Producer::class, $this->getPrivateProperty($kafka, 'client'));
    }

    private function getPrivateProperty($object, $property)
    {
        $reflection = new \ReflectionClass($object);
        $property = $reflection->getProperty($property);
        $property->setAccessible(true);

        return $property->getValue($object);
    }

    public function testDataPushToKafka()
    {
        $kafka = \Mockery::mock(Kafka::class);
        $producer = \Mockery::mock(\RdKafka\Producer::class);
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
