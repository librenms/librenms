<?php

/*
|--------------------------------------------------------------------------
| Model Factories
|--------------------------------------------------------------------------
|
| Here you may define all of your model factories. Model factories give
| you a convenient way to create models for testing and seeding your
| database. Just tell the factory how a default model should look.
|
 */

/** @var \Illuminate\Database\Eloquent\Factory $factory */

use Carbon\Carbon;
use LibreNMS\Util\IPv4;

$factory->define(App\Models\User::class, function (Faker\Generator $faker) {
    static $password;

    return [
        'auth_type' => 'mysql',
        'username' => $faker->unique()->userName,
        'realname' => $faker->name,
        'email' => $faker->safeEmail,
        'password' => $password ?: $password = bcrypt('secret'),
        'level' => 1,
    ];
});

$factory->state(App\Models\User::class, 'admin', function ($faker) {
    return [
        'level' => '10',
    ];
});

$factory->state(App\Models\User::class, 'read', function ($faker) {
    return [
        'level' => '5',
    ];
});

$factory->define(\App\Models\Bill::class, function (Faker\Generator $faker) {
    return [
        'bill_name' => $faker->text,
    ];
});

$factory->define(\App\Models\Device::class, function (Faker\Generator $faker) {
    return [
        'hostname' => $faker->domainWord . '-' . $faker->domainWord . '-' . $faker->domainWord . '.' . $faker->domainName,
        'ip' => $faker->randomElement([$faker->ipv4, $faker->ipv6]),
        'type' => $faker->randomElement([
            'appliance',
            'camera',
            'collaboration',
            'encoder',
            'environment',
            'firewall',
            'loadbalancer',
            'management',
            'network',
            'power',
            'printer',
            'proxy',
            'sensor',
            'server',
            'storage',
            'timing',
            'wireless',
            'workstation',
        ]),
        'status' => $status = random_int(0, 1),
        'status_reason' => $status == 0 ? $faker->randomElement(['snmp', 'icmp']) : '', // allow invalid states?
    ];
});

$factory->define(\App\Models\DeviceGroup::class, function (Faker\Generator $faker) {
    return [
        'name' => $faker->domainWord,
        'desc' => $faker->text(255),
        'type' =>'static',
    ];
});

$factory->define(\App\Models\Port::class, function (Faker\Generator $faker) {
    return [
        'ifIndex' => $faker->unique()->numberBetween(),
        'ifName' => $faker->text(20),
        'ifDescr' => $faker->text(255),
        'ifLastChange' => $faker->unixTime(),
    ];
});

$factory->define(\App\Models\BgpPeer::class, function (Faker\Generator $faker) {
    return [
        'bgpPeerIdentifier' => $faker->ipv4,
        'bgpLocalAddr' => $faker->ipv4,
        'bgpPeerRemoteAddr' => $faker->ipv4,
        'bgpPeerRemoteAs' => $faker->numberBetween(1, 65535),
        'bgpPeerState' => $faker->randomElement(['established', 'idle']),
        'astext' => $faker->sentence(),
        'bgpPeerAdminStatus' => $faker->randomElement(['start', 'stop']),
        'bgpPeerInUpdates' => $faker->randomDigit,
        'bgpPeerOutUpdates' => $faker->randomDigit,
        'bgpPeerInTotalMessages' => $faker->randomDigit,
        'bgpPeerOutTotalMessages' => $faker->randomDigit,
        'bgpPeerFsmEstablishedTime' => $faker->unixTime,
        'bgpPeerInUpdateElapsedTime' => $faker->unixTime,
    ];
});

$factory->define(\App\Models\Ipv4Address::class, function (Faker\Generator $faker) {
    $prefix = $faker->numberBetween(0, 32);
    $ip = new IPv4($faker->ipv4 . '/' . $prefix);

    return [
        'ipv4_address' => $ip->uncompressed(),
        'ipv4_prefixlen' => $prefix,
        'port_id' => function () {
            return factory(\App\Models\Port::class)->create()->port_id;
        },
        'ipv4_network_id' => function () use ($ip) {
            return factory(\App\Models\Ipv4Network::class)->create(['ipv4_network' => $ip->getNetworkAddress() . '/' . $ip->cidr])->ipv4_network_id;
        },
    ];
});

$factory->define(\App\Models\Ipv4Network::class, function (Faker\Generator $faker) {
    return [
        'ipv4_network' => $faker->ipv4 . '/' . $faker->numberBetween(0, 32),
    ];
});

$factory->define(\App\Models\Syslog::class, function (Faker\Generator $faker) {
    $facilities = ['kern', 'user', 'mail', 'daemon', 'auth', 'syslog', 'lpr', 'news', 'uucp', 'cron', 'authpriv', 'ftp', 'ntp', 'security', 'console', 'solaris-cron', 'local0', 'local1', 'local2', 'local3', 'local4', 'local5', 'local6', 'local7'];
    $levels = ['emerg', 'alert', 'crit', 'err', 'warning', 'notice', 'info', 'debug'];

    return [
        'facility' => $faker->randomElement($facilities),
        'priority' => $faker->randomElement($levels),
        'level' => $faker->randomElement($levels),
        'tag' => $faker->asciify(str_repeat('*', $faker->numberBetween(0, 10))),
        'timestamp' => Carbon::now(),
        'program' => $faker->asciify(str_repeat('*', $faker->numberBetween(0, 32))),
        'msg' => $faker->text(),
    ];
});

$factory->define(\App\Models\Vminfo::class, function (Faker\Generator $faker) {
    return [
        'vm_type' => $faker->text(16),
        'vmwVmVMID' => $faker->randomDigit,
        'vmwVmDisplayName' => $faker->domainWord . '.' . $faker->domainName,
        'vmwVmGuestOS' => $faker->text(128),
        'vmwVmMemSize' => $faker->randomDigit,
        'vmwVmCpus' => $faker->randomDigit,
        'vmwVmState' => $faker->randomElement(['powered on', 'powered off', 'suspended']),
    ];
});

$factory->define(\App\Models\OspfNbr::class, function (Faker\Generator $faker) {
    return [
        'id' => $faker->randomDigit,
        'ospfNbrIpAddr' => $faker->ipv4,
        'ospfNbrAddressLessIndex' => $faker->randomDigit,
        'ospfNbrRtrId' => $faker->ipv4,
        'ospfNbrOptions' => 0,
        'ospfNbrPriority' => 1,
        'ospfNbrEvents' => $faker->randomDigit,
        'ospfNbrLsRetransQLen' => 0,
        'ospfNbmaNbrStatus' => 'active',
        'ospfNbmaNbrPermanence' => 'dynamic',
        'ospfNbrHelloSuppressed' => 'false',
    ];
});

$factory->define(\App\Models\OspfPort::class, function (Faker\Generator $faker) {
    return [
        'id' => $faker->randomDigit,
        'ospf_port_id' => $faker->randomDigit,
        'ospfIfIpAddress' => $faker->ipv4,
        'ospfAddressLessIf' => $faker->randomDigit,
        'ospfIfAreaId' => '0.0.0.0',
    ];
});

$factory->define(\App\Models\Component::class, function (Faker\Generator $faker) {
    return [
        'device_id' => $faker->randomDigit,
        'type' => $faker->regexify('[A-Za-z0-9]{4,20}'),
    ];
});
$factory->define(\App\Models\Sensor::class, function (Faker\Generator $faker) {
    $sensor_class = ['airflow', 'ber', 'charge', 'chromatic_dispersion', 'cooling', 'count', 'current', 'dbm', 'delay', 'eer', 'fanspeed', 'frequency', 'humidity', 'load', 'loss', 'power', 'power_consumed', 'power_factor', 'pressure', 'quality_factor', 'runtime', 'signal', 'snr', 'state', 'temperature', 'voltage', 'waterflow'];
    $sensor_oid = '.1.3.6.1.4.1.4115.1.4.3.3.' . $faker->numberBetween(0, 10) . '.' . $faker->numberBetween(0, 10) . '.' . $faker->numberBetween(0, 10);

    return [
        'sensor_index' => $faker->randomDigit,
        'sensor_class' => $faker->randomElement($sensor_class),
        'sensor_current' => $faker->randomDigit,
        'sensor_oid' => $sensor_oid,
    ];
});

$factory->define(\App\Models\AlertSchedule::class, function (Faker\Generator $faker) {
    return [
        'title' => $faker->name,
        'notes' => $faker->text,
        'recurring' => 0,
    ];
});
$factory->state(\App\Models\AlertSchedule::class, 'recurring', function ($faker) {
    return [
        'recurring' => 1,
    ];
});
