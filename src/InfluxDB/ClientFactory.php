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
     * or not find adapter
     */
    public static function create(array $options)
    {
        $defaultOptions = [
            "adapter" => [
                "name" => false,
                "options" => [],
            ],
            "options" => [],
            "filters" => [
                "query" => false
            ],
        ];

        $options = array_replace_recursive($defaultOptions, $options);

        $adapterName = $options["adapter"]["name"];
        if (!class_exists($adapterName)) {
            throw new \InvalidArgumentException("Missing class: {$adapterName}");
        }
        $adapterOptions = new Options();

        $hydrator = new ClassMethods();
        $hydrator->hydrate($options["options"], $adapterOptions);

        $adapter = null;
        switch ($adapterName) {
            case 'InfluxDB\\Adapter\\V08\\UdpAdapter':
                $adapter = new $adapterName($adapterOptions);
                break;
            case 'InfluxDB\\Adapter\\V08\\GuzzleAdapter':
                $adapter = new $adapterName(new GuzzleClient($options["adapter"]["options"]), $adapterOptions);
                break;
            case 'InfluxDB\\Adapter\\V08\\HttpAdapter':
                $adapter = new $adapterName($adapterOptions, new GuzzleClient($options["adapter"]["options"]));
                break;
            default:
                throw new \InvalidArgumentException("Missing adapter {$adapter}");
        }

        $client = new Client();
        $client->setAdapter($adapter);

        if ($options["filters"]["query"]) {
            $client->setFilter(new $options["filters"]["query"]["name"]);
        }

        return $client;
    }
}
