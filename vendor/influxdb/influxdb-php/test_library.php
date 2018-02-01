<?php
/**
 * @author Stephen "TheCodeAssassin" Hoogendijk
 */
require 'vendor/autoload.php';

// vagrant ip
$host = '192.168.33.10';


function randFloat($min, $max)
{
    $range = $max-$min;
    $num = $min + $range * mt_rand(0, 32767)/32767;

    $num = round($num, 4);

    return ((float) $num);
}

$client = new \InfluxDB\Client($host);

$database = $client->selectDB('test');

if ($database->exists()) {
    $database->drop();
}

$database->create(new \InfluxDB\Database\RetentionPolicy('test', '12w', 1, true));


$start = microtime(true);

$countries = ['nl', 'gb', 'us', 'be', 'th', 'jp', 'nl', 'sa'];
$colors = ['orange', 'black', 'yellow', 'white', 'red', 'purple'];
$points = [];

for ($i=0; $i < 1000; $i++) {
    $points[] = new \InfluxDB\Point(
        'flags',
        randFloat(1, 999),
        ['country' => $countries[array_rand($countries)]],
        ['color' => $colors[array_rand($colors)]],
        (int) shell_exec('date +%s%N')+mt_rand(1,1000)
    );
};

// insert the points
$database->writePoints($points);

$end = microtime(true);

$country = $countries[array_rand($countries)];
$color = $colors[array_rand($colors)];

$results = $database->query("SELECT * FROM flags WHERE country = '$country' LIMIT 5")->getPoints();
$results2 = $database->query("SELECT * FROM flags WHERE color = '$color' LIMIT 5")->getPoints();

echo "Showing top 5 flags from country $country:" . PHP_EOL;
print_r($results);
echo PHP_EOL;

echo "Showing top 5 flags with color $color:" . PHP_EOL;
print_r($results2);


echo PHP_EOL;
echo sprintf('Executed 1000 inserts in %.2f seconds', $end - $start);
