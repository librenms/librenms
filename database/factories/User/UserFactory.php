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

namespace Database\Factories\User;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Factories\Factory;
use LibreNMS\Util\IPv4;

class ModelFactory extends Factory
{
    /**
     * The name of the factory's corresponding model.
     *
     * @var string
     */
    protected $model = \App\Models\User::class;

    /**
     * Define the model's default state.
     *
     * @return array
     */
    public function definition()
    {
        static $password;

        return [
            'auth_type' => 'mysql',
            'username' => $this->faker->unique()->userName,
            'realname' => $this->faker->name,
            'email' => $this->faker->safeEmail,
            'password' => $password ?: $password = bcrypt('secret'),
            'level' => 1,
        ];
    }

    public function admin()
    {
        return $this->state(function () {
            return [
                'level' => '10',
            ];
        });
    }

    public function read()
    {
        return $this->state(function () {
            return [
                'level' => '5',
            ];
        });
    }

    /**
     * Define the model's default state.
     *
     * @return array
     */
    public function definition()
    {
        return [
            'bill_name' => $this->faker->text,
        ];
    }

    /**
     * Define the model's default state.
     *
     * @return array
     */
    public function definition()
    {
        return [
            'hostname' => $this->faker->domainWord . '-' . $this->faker->domainWord . '-' . $this->faker->domainWord . '.' . $this->faker->domainName,
            'ip' => $this->faker->randomElement([$this->faker->ipv4, $this->faker->ipv6]),
            'type' => $this->faker->randomElement([
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
            'status_reason' => $status == 0 ? $this->faker->randomElement(['snmp', 'icmp']) : '', // allow invalid states?
        ];
    }

    /**
     * Define the model's default state.
     *
     * @return array
     */
    public function definition()
    {
        return [
            'name' => $this->faker->domainWord,
            'desc' => $this->faker->text(255),
            'type' =>'static',
        ];
    }

    /**
     * Define the model's default state.
     *
     * @return array
     */
    public function definition()
    {
        return [
            'ifIndex' => $this->faker->unique()->numberBetween(),
            'ifName' => $this->faker->text(20),
            'ifDescr' => $this->faker->text(255),
            'ifLastChange' => $this->faker->unixTime(),
        ];
    }

    /**
     * Define the model's default state.
     *
     * @return array
     */
    public function definition()
    {
        return [
            'bgpPeerIdentifier' => $this->faker->ipv4,
            'bgpLocalAddr' => $this->faker->ipv4,
            'bgpPeerRemoteAddr' => $this->faker->ipv4,
            'bgpPeerRemoteAs' => $this->faker->numberBetween(1, 65535),
            'bgpPeerState' => $this->faker->randomElement(['established', 'idle']),
            'astext' => $this->faker->sentence(),
            'bgpPeerAdminStatus' => $this->faker->randomElement(['start', 'stop']),
            'bgpPeerInUpdates' => $this->faker->randomDigit,
            'bgpPeerOutUpdates' => $this->faker->randomDigit,
            'bgpPeerInTotalMessages' => $this->faker->randomDigit,
            'bgpPeerOutTotalMessages' => $this->faker->randomDigit,
            'bgpPeerFsmEstablishedTime' => $this->faker->unixTime,
            'bgpPeerInUpdateElapsedTime' => $this->faker->unixTime,
        ];
    }

    /**
     * Define the model's default state.
     *
     * @return array
     */
    public function definition()
    {
        $prefix = $this->faker->numberBetween(0, 32);
        $ip = new IPv4($this->faker->ipv4 . '/' . $prefix);

        return [
            'ipv4_address' => $ip->uncompressed(),
            'ipv4_prefixlen' => $prefix,
            'port_id' => function () {
                return \App\Models\Port::factory()->create()->port_id;
            },
            'ipv4_network_id' => function () use ($ip) {
                return \App\Models\Ipv4Network::factory()->create(['ipv4_network' => $ip->getNetworkAddress() . '/' . $ip->cidr])->ipv4_network_id;
            },
        ];
    }

    /**
     * Define the model's default state.
     *
     * @return array
     */
    public function definition()
    {
        return [
            'ipv4_network' => $this->faker->ipv4 . '/' . $this->faker->numberBetween(0, 32),
        ];
    }

    /**
     * Define the model's default state.
     *
     * @return array
     */
    public function definition()
    {
        $facilities = ['kern', 'user', 'mail', 'daemon', 'auth', 'syslog', 'lpr', 'news', 'uucp', 'cron', 'authpriv', 'ftp', 'ntp', 'security', 'console', 'solaris-cron', 'local0', 'local1', 'local2', 'local3', 'local4', 'local5', 'local6', 'local7'];
        $levels = ['emerg', 'alert', 'crit', 'err', 'warning', 'notice', 'info', 'debug'];

        return [
            'facility' => $this->faker->randomElement($facilities),
            'priority' => $this->faker->randomElement($levels),
            'level' => $this->faker->randomElement($levels),
            'tag' => $this->faker->asciify(str_repeat('*', $this->faker->numberBetween(0, 10))),
            'timestamp' => Carbon::now(),
            'program' => $this->faker->asciify(str_repeat('*', $this->faker->numberBetween(0, 32))),
            'msg' => $this->faker->text(),
        ];
    }

    /**
     * Define the model's default state.
     *
     * @return array
     */
    public function definition()
    {
        return [
            'vm_type' => $this->faker->text(16),
            'vmwVmVMID' => $this->faker->randomDigit,
            'vmwVmDisplayName' => $this->faker->domainWord . '.' . $this->faker->domainName,
            'vmwVmGuestOS' => $this->faker->text(128),
            'vmwVmMemSize' => $this->faker->randomDigit,
            'vmwVmCpus' => $this->faker->randomDigit,
            'vmwVmState' => $this->faker->randomElement(['powered on', 'powered off', 'suspended']),
        ];
    }

    /**
     * Define the model's default state.
     *
     * @return array
     */
    public function definition()
    {
        return [
            'id' => $this->faker->randomDigit,
            'ospfNbrIpAddr' => $this->faker->ipv4,
            'ospfNbrAddressLessIndex' => $this->faker->randomDigit,
            'ospfNbrRtrId' => $this->faker->ipv4,
            'ospfNbrOptions' => 0,
            'ospfNbrPriority' => 1,
            'ospfNbrEvents' => $this->faker->randomDigit,
            'ospfNbrLsRetransQLen' => 0,
            'ospfNbmaNbrStatus' => 'active',
            'ospfNbmaNbrPermanence' => 'dynamic',
            'ospfNbrHelloSuppressed' => 'false',
        ];
    }

    /**
     * Define the model's default state.
     *
     * @return array
     */
    public function definition()
    {
        return [
            'id' => $this->faker->randomDigit,
            'ospf_port_id' => $this->faker->randomDigit,
            'ospfIfIpAddress' => $this->faker->ipv4,
            'ospfAddressLessIf' => $this->faker->randomDigit,
            'ospfIfAreaId' => '0.0.0.0',
        ];
    }

    /**
     * Define the model's default state.
     *
     * @return array
     */
    public function definition()
    {
        return [
            'device_id' => $this->faker->randomDigit,
            'type' => $this->faker->regexify('[A-Za-z0-9]{4,20}'),
        ];
    }

    /**
     * Define the model's default state.
     *
     * @return array
     */
    public function definition()
    {
        $sensor_class = ['airflow', 'ber', 'charge', 'chromatic_dispersion', 'cooling', 'count', 'current', 'dbm', 'delay', 'eer', 'fanspeed', 'frequency', 'humidity', 'load', 'loss', 'power', 'power_consumed', 'power_factor', 'pressure', 'quality_factor', 'runtime', 'signal', 'snr', 'state', 'temperature', 'voltage', 'waterflow'];
        $sensor_oid = '.1.3.6.1.4.1.4115.1.4.3.3.' . $this->faker->numberBetween(0, 10) . '.' . $this->faker->numberBetween(0, 10) . '.' . $this->faker->numberBetween(0, 10);

        return [
            'sensor_index' => $this->faker->randomDigit,
            'sensor_class' => $this->faker->randomElement($sensor_class),
            'sensor_current' => $this->faker->randomDigit,
            'sensor_oid' => $sensor_oid,
        ];
    }

    /**
     * Define the model's default state.
     *
     * @return array
     */
    public function definition()
    {
        return [
            'title' => $this->faker->name,
            'notes' => $this->faker->text,
            'recurring' => 0,
        ];
    }

    public function recurring()
    {
        return $this->state(function () {
            return [
                'recurring' => 1,
            ];
        });
    }
}
