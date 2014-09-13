<?php
namespace InfluxDB;

use Zend\Stdlib\Hydrator\ClassMethods;
use GuzzleHttp\Client as GuzzleClient;

abstract class ClientFactory
{
    private static $options = [
        "adapter" => [
            "name" => false,
            "options" => [],
        ],
        "options" => [],
    ];

    public static function create(array $options)
    {
        $options = array_replace_recursive(self::$options, $options);

        $adapterName = $options["adapter"]["name"];
        if (!class_exists($adapterName)) {
            throw new \InvalidArgumentException("Missing class: {$adapterName}");
        }
        $adapterOptions = new Options();

        $hydrator = new ClassMethods();
        $hydrator->hydrate($options["options"], $adapterOptions);

        $adapter = null;
        switch ($adapterName) {
            case 'InfluxDB\\Adapter\\UdpAdapter':
                $adapter = new $adapterName($adapterOptions);
                break;
            case 'InfluxDB\\Adapter\\GuzzleAdapter':
                $adapter = new $adapterName(new GuzzleClient($options["adapter"]["options"]), $adapterOptions);
                break;
            default:
                throw new \InvalidArgumentException("Missing adapter {$adapter}");
        }

        $client = new Client();
        $client->setAdapter($adapter);

        return $client;
    }
}
