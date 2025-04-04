<?php

namespace LibreNMS\Tests\Unit\Data;

use LibreNMS\Config;
use LibreNMS\Data\Store\Kafka;
use LibreNMS\Tests\TestCase;
use RdKafka\Producer;

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

    public function testDataPushWithExcludedMeasurements()
    {
        Config::set('kafka.measurement-exclude', 'excluded_measurement');

        $kafka = new Kafka();
        $device = ['device_id' => 1, 'hostname' => 'testhost'];
        $measurement = 'excluded_measurement';
        $tags = ['ifName' => 'testifname', 'type' => 'testtype'];
        $fields = ['ifIn' => 234234, 'ifOut' => 53453];

        \Log::shouldReceive('debug')->once()->with('KAFKA: Skipped parsing to Kafka, measurement is in measurement-excluded: excluded_measurement');

        $kafka->put($device, $measurement, $tags, $fields);
    }
}
