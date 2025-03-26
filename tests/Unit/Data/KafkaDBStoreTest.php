<?php

namespace LibreNMS\Tests\Unit\Data;

use Illuminate\Support\Facades\Log;
use LibreNMS\Config;
use LibreNMS\Data\Store\Kafka;
use LibreNMS\Tests\TestCase;
use Mockery;
use RdKafka\Producer;
use RdKafka\ProducerTopic;



class KafkaDBStoreTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        Config::set('kafka.enable', true);
        Config::set('kafka.broker.list', '127.0.2.2:9092');
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

    public function testSuccessfulDataPush()
    {
        $kafka = Mockery::mock(Kafka::class)->makePartial();
        $producer = Mockery::mock(Producer::class);
        $topic = Mockery::mock(ProducerTopic::class);

        $kafka->shouldReceive('getClient')->andReturn($producer);
        $producer->shouldReceive('newTopic')->andReturn($topic);
        $topic->shouldReceive('produce')->once();

        $device = ['device_id' => 1, 'hostname' => 'testhost'];
        $measurement = 'testmeasure';
        $tags = ['ifName' => 'testifname', 'type' => 'testtype'];
        $fields = ['ifIn' => 234234, 'ifOut' => 53453];

        Log::shouldReceive('debug');
        Log::shouldReceive('error')->times(0);

        $kafka->put($device, $measurement, $tags, $fields);
    }

    public function testDataPushWithExcludedGroups()
    {
        Config::set('kafka.groups-exclude', 'EXCLUDED_GROUP');

        $kafka = Mockery::mock(Kafka::class)->makePartial();
        $device = ['device_id' => 1, 'hostname' => 'testhost'];
        $measurement = 'testmeasure';
        $tags = ['ifName' => 'testifname', 'type' => 'testtype'];
        $fields = ['ifIn' => 234234, 'ifOut' => 53453];

        $device_data = Mockery::mock(\App\Facades\DeviceCache::class);
        $device_data->shouldReceive('get')->andReturn((object) ['groups' => collect([(object) ['name' => 'excluded_group']])]);

        Log::shouldReceive('debug')->once()->with('KAFKA: Skipped parsing to Kafka, device is in group: excluded_group');

        $kafka->put($device, $measurement, $tags, $fields);
    }

    public function testDataPushWithExcludedMeasurements()
    {
        Config::set('kafka.measurement-exclude', 'excluded_measurement');

        $kafka = Mockery::mock(Kafka::class)->makePartial();
        $device = ['device_id' => 1, 'hostname' => 'testhost'];
        $measurement = 'excluded_measurement';
        $tags = ['ifName' => 'testifname', 'type' => 'testtype'];
        $fields = ['ifIn' => 234234, 'ifOut' => 53453];

        Log::shouldReceive('debug')->once()->with('KAFKA: Skipped parsing to Kafka, measurement is in measurement-excluded: excluded_measurement');

        $kafka->put($device, $measurement, $tags, $fields);
    }

    public function testDataPushWithExcludedDeviceFields()
    {
        Config::set('kafka.device-fields-exclude', 'excluded_field');

        $kafka = Mockery::mock(Kafka::class)->makePartial();
        $producer = Mockery::mock(Producer::class);
        $topic = Mockery::mock(ProducerTopic::class);

        $kafka->shouldReceive('getClient')->andReturn($producer);
        $producer->shouldReceive('newTopic')->andReturn($topic);
        $topic->shouldReceive('produce')->once();

        $device = ['device_id' => 1, 'hostname' => 'testhost', 'excluded_field' => 'value'];
        $measurement = 'testmeasure';
        $tags = ['ifName' => 'testifname', 'type' => 'testtype'];
        $fields = ['ifIn' => 234234, 'ifOut' => 53453];

        Log::shouldReceive('debug');
        Log::shouldReceive('error')->times(0);

        $kafka->put($device, $measurement, $tags, $fields);
    }

    public function testSafeFlush()
    {
        $kafka = Mockery::mock(Kafka::class)->makePartial();
        $producer = Mockery::mock(Producer::class);

        $kafka->shouldReceive('getClient')->andReturn($producer);
        $producer->shouldReceive('getOutQLen')->andReturn(1);
        $producer->shouldReceive('flush')->andReturn(0);

        Log::shouldReceive('debug')->once()->with('KAFKA: Flushing 1 remaining messages');
        Log::shouldReceive('error')->times(0);

        $kafka->safeFlush();
    }

    public function testExceptionHandling()
    {
        $kafka = Mockery::mock(Kafka::class)->makePartial();
        $producer = Mockery::mock(Producer::class);

        $kafka->shouldReceive('getClient')->andReturn($producer);
        $producer->shouldReceive('newTopic')->andThrow(new \Exception('Test exception'));

        $device = ['device_id' => 1, 'hostname' => 'testhost'];
        $measurement = 'testmeasure';
        $tags = ['ifName' => 'testifname', 'type' => 'testtype'];
        $fields = ['ifIn' => 234234, 'ifOut' => 53453];

        Log::shouldReceive('error')->once()->with('KAFKA: Put failed with exception', Mockery::any());

        $kafka->put($device, $measurement, $tags, $fields);
    }

    private function getPrivateProperty($object, $property)
    {
        $reflection = new \ReflectionClass($object);
        $property = $reflection->getProperty($property);
        $property->setAccessible(true);

        return $property->getValue($object);
    }
}