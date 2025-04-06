<?php

namespace LibreNMS\Tests\Unit\Data;

use LibreNMS\Config;
use LibreNMS\Data\Store\Kafka;
use LibreNMS\Tests\TestCase;
use RdKafka\Producer;

class KafkaDBStoreTest extends TestCase
{
    protected $cluster;
    private function getKafkaMockedClusterConfig()
    {
        $clusterConf = new \RdKafka\Conf();
        $clusterConf->setLogCb(null);

        return $clusterConf;
    }

    public function getMockedKafkaCluster()
    {
        // Create mock cluster
        $numberOfBrokers = 1;
        $clusterConf = $this->getKafkaMockedClusterConfig();

        return \RdKafka\Test\MockCluster::create($numberOfBrokers, $clusterConf);
    }

    protected function setUp(): void
    {
        parent::setUp();

        $this->cluster = $this->getMockedKafkaCluster();
        Config::set('kafka.broker.list', $this->cluster->getBootstraps());

        Config::set('kafka.enable', true);
        Config::set('kafka.idempotence', false);
        Config::set('kafka.buffer.max.message', 10);
        Config::set('kafka.batch.max.message', 25);
        Config::set('kafka.linger.ms', 50);
        Config::set('kafka.request.required.acks', 0);
    }

    public function testDataPushToKafka()
    {
        $topic = $this->mock('overload:RdKafka\ProducerTopic');
        $topic->shouldReceive('produce')->once();

        $kafka = new Kafka(Kafka::getClient());
        
        $device = ['device_id' => 1, 'hostname' => 'testhost'];
        $measurement = 'excluded_measurement';
        $tags = ['ifName' => 'testifname', 'type' => 'testtype'];
        $fields = ['ifIn' => 234234, 'ifOut' => 53453];
        
        $kafka->put($device, $measurement, $tags, $fields);
    }
}
