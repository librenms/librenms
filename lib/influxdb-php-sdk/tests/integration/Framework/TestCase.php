<?php
namespace InfluxDB\Integration\Framework;

use InfluxDB\Options;
use InfluxDB\Adapter\GuzzleAdapter as InfluxHttpAdapter;
use GuzzleHttp\Client as GuzzleHttpClient;
use InfluxDB\Client;

class TestCase extends \PHPUnit_Framework_TestCase
{
    private $client;
    private $options;

    public function setUp()
    {
        $options = $this->options = new Options();
        $guzzleHttp = new GuzzleHttpClient();
        $adapter = new InfluxHttpAdapter($guzzleHttp, $options);

        $client = $this->client = new Client($adapter);

        $this->dropAll();
    }

    public function tearDown()
    {
        $this->dropAll();
    }

    private function dropAll()
    {
        $databases = $this->getClient()->getDatabases();
        if (array_key_exists("values", $databases["results"][0]["series"][0])) {
            foreach ($databases["results"][0]["series"][0]["values"] as $database) {
                $this->getClient()->deleteDatabase($database[0]);
            }
        }
    }

    public function assertValueExistsInSerie($database, $serieName, $column, $value)
    {
        $this->getOptions()->setDatabase($database);
        $body = $this->getClient()->query("select {$column} from \"{$serieName}\"");

        foreach ($body["results"][0]["series"][0]["values"] as $result) {
            if ($result[1] == $value) {
                return $this->assertTrue(true);
            }
        }

        return $this->fail("Missing value '{$value}'");
    }

    public function assertSerieCount($database, $serieName, $count)
    {
        $this->getOptions()->setDatabase($database);
        $body = $this->getClient()->query("select * from \"{$serieName}\"");

        $this->assertCount(1, $body["results"][0]["series"][0]["values"]);
    }

    public function assertSerieExists($database, $serieName)
    {
        $this->getOptions()->setDatabase($database);
        $body = $this->getClient()->query("show measurements");

        foreach ($body["results"][0]["series"][0]["values"] as $result) {
            if ($result[0] == $serieName) {
                return $this->assertTrue(true);
            }
        }

        return $this->fail("Missing serie with name '{$serieName}' in database '{$database}'");
    }

    public function assertDatabasesCount($count)
    {
        $databases = $this->client->getDatabases();
        $this->assertCount($count, $databases["results"][0]["series"][0]["values"]);
    }

    public function getOptions()
    {
        return $this->options;
    }

    public function getClient()
    {
        return $this->client;
    }
}
