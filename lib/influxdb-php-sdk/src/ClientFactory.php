<?php
namespace InfluxDB;

use Zend\Stdlib\Hydrator\ClassMethods;
use GuzzleHttp\Client as GuzzleClient;

/**
 * Create your static client
 */
abstract class ClientFactory
{
    /**
     * Create new client
     * @param array $options
     * @return Client
     * @throws InvalidArgumentException If not exist adapter name
     */
    public static function create(array $options)
    {
        $defaultOptions = [
            "adapter" => [
                "name" => false,
                "options" => [],
            ],
            "options" => [],
        ];
        $options = array_replace_recursive($defaultOptions, $options);

        $adapterOptions = new Options();

        $hydrator = new ClassMethods();
        $hydrator->hydrate($options["options"], $adapterOptions);

        $adapter = null;
        $adapterName = $options["adapter"]["name"];
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

        $client = new Client($adapter);

        return $client;
    }
}
