<?php
/*
 * SnmpQueryMock.php
 *
 * Load data from snmprec files
 *
 * This program is free software: you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation, either version 3 of the License, or
 * (at your option) any later version.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with this program.  If not, see <http://www.gnu.org/licenses/>.
 *
 * @package    LibreNMS
 * @link       http://librenms.org
 * @copyright  2021 Tony Murray
 * @author     Tony Murray <murraytony@gmail.com>
 */

namespace LibreNMS\Tests\Mocks;

use App\Models\Device;
use DeviceCache;
use Exception;
use Illuminate\Support\Str;
use LibreNMS\Data\Source\SnmpQuery;
use LibreNMS\Data\Source\SnmpQueryInterface;
use LibreNMS\Data\Source\SnmpResponse;

class SnmpQueryMock implements SnmpQueryInterface
{

    private static $cache;
    private $device;
    /**
     * @var string
     */
    private $context;
    /**
     * @var string|null
     */
    private $mibDir;
    /**
     * @var bool
     */
    private $numeric = false;
    /**
     * @var array|mixed
     */
    private $options = [];

    public static function make(): SnmpQueryInterface
    {
        $new = new static;
        $new->device = DeviceCache::primary();

        return $new;
    }

    public function device(Device $device): SnmpQueryInterface
    {
        $this->device = $device;

        return $this;
    }

    public function deviceArray(array $device): SnmpQueryInterface
    {
        $this->device = new Device($device);

        return $this;
    }

    public function context(string $context): SnmpQueryInterface
    {
        $this->context = $context;

        return $this;
    }

    public function get($oid): SnmpResponse
    {
        $community = $this->device->community;
        $num_oid = $this->translateNumber($oid);
        $data = $this->getSnmprec($community)[$num_oid] ?? [0, ''];

        return new SnmpResponse($this->outputLine($oid, $num_oid, $data[0], $data[1]));
    }

    /**
     * Get the numeric oid of an oid
     * The leading dot is ommited by default to be compatible with snmpsim
     *
     * @param  string  $oid  the oid to tranlslate
     * @param  string  $mib  mib to use
     * @return string the oid in numeric format (1.3.4.5)
     *
     * @throws Exception Could not translate the oid
     */
    private function translateNumber($oid, $mib = null)
    {
        // optimizations (35s -> 1.6s on my laptop)
        switch ($oid) {
            case 'SNMPv2-MIB::sysDescr.0':
                return '1.3.6.1.2.1.1.1.0';
            case 'SNMPv2-MIB::sysObjectID.0':
                return '1.3.6.1.2.1.1.2.0';
            case 'ENTITY-MIB::entPhysicalDescr.1':
                return '1.3.6.1.2.1.47.1.1.1.1.2.1';
            case 'SML-MIB::product-Name.0':
                return '1.3.6.1.4.1.2.6.182.3.3.1.0';
            case 'ENTITY-MIB::entPhysicalMfgName.1':
                return '1.3.6.1.2.1.47.1.1.1.1.12.1';
            case 'GAMATRONIC-MIB::psUnitManufacture.0':
                return '1.3.6.1.4.1.6050.1.1.2.0';
            case 'SYNOLOGY-SYSTEM-MIB::systemStatus.0':
                return '1.3.6.1.4.1.6574.1.1.0';
        }

        if ($this->isNumeric($oid)) {
            return ltrim($oid, '.');
        }

        $options = ['-IR'];
        if ($mib) {
            $options[] = "-m $mib";
        }

        $number = SnmpQuery::make()->mibDir($this->mibDir)
            ->options(array_merge($options, $this->options))->numeric()->translate($oid)->value();


        if (empty($number)) {
            throw new Exception('Could not translate oid: ' . $oid . PHP_EOL);
        }

        return $number;
    }

    private function isNumeric($oid): bool
    {
        return (bool) preg_match('/^[.\d]*$/', $oid);
    }

    public function translate($oid): SnmpResponse
    {
        throw new Exception('Translate unsupported');
    }

    public function numeric(): SnmpQueryInterface
    {
        $this->numeric = true;

        return $this;
    }

    public function options($options = []): SnmpQueryInterface
    {
        $this->options = $options;

        return $this;
    }

    public function mibDir(?string $dir): SnmpQueryInterface
    {
        $this->mibDir = $dir;

        return $this;
    }

    /**
     * Get all data of the specified $community from the snmprec cache
     *
     * @param  string  $community  snmp community to return
     * @return array array of the data containing: [$oid][$type, $data]
     *
     * @throws Exception this $community is not cached
     */
    private function getSnmprec(string $community): array
    {
        if (! isset(self::$cache[$community])) {
            $this->cacheSnmprec($community);

        }

        if (isset(self::$cache[$community])) {
            return self::$cache[$community];
        }

        throw new Exception("SNMPREC: community $community not cached");
    }

    private function cacheSnmprec($file): void
    {
        if (isset(self::$cache[$file])) {
            return;
        }
        self::$cache[$file] = [];

        $data = file_get_contents(base_path("/tests/snmpsim/$file.snmprec"));
        $line = strtok($data, "\r\n");
        while ($line !== false) {
            [$oid, $type, $data] = explode('|', $line, 3);
            if ($type == '4') {
                $data = trim($data);
            } elseif ($type == '6') {
                $data = trim($data, '.');
            } elseif ($type == '4x') {
                // MacAddress type is stored as hex string, but we don't understand mibs
                if (Str::startsWith($oid, [
                    '1.3.6.1.2.1.2.2.1.6', // IF-MIB::ifPhysAddress
                    '1.3.6.1.2.1.17.1.1.0', // BRIDGE-MIB::dot1dBaseBridgeAddress.0
                    '1.3.6.1.4.1.890.1.5.13.13.8.1.1.20', // IES5206-MIB::slotModuleMacAddress
                ])) {
                    $data = \LibreNMS\Util\Rewrite::readableMac($data);
                } else {
                    $data = hex2str($data);
                }
            }

            self::$cache[$file][$oid] = [$type, $data];
            $line = strtok("\r\n");
        }
    }

    private function outputLine($oid, $num_oid, $type, $data)
    {
        if ($type == 6) {
            $data = '.' . $data;
        }

        if ($this->numeric) {
            return "$num_oid = $data";
        }

        if (! empty($oid) && $this->isNumeric($oid)) {
            $oid = SnmpQuery::make()->translate($oid)->value();
        }

        return "$oid = $data";
    }

    public function walk($oid): SnmpResponse
    {
        $community = $this->device->community;
        $num_oid = $this->translateNumber($oid);
        $dev = $this->getSnmprec($community);

        $output = '';
        foreach ($dev as $key => $data) {
            if (Str::startsWith($key, $num_oid)) {
                $output .= $this->outputLine($oid, $num_oid, $data[0], $data[1]);
            }
        }

        d_echo("[SNMP] snmpwalk $community $num_oid: ");
        return new SnmpResponse($output);
    }

    public function next($oid): SnmpResponse
    {
        $community = $this->device->community;
        $num_oid = $this->translateNumber($oid);
        $dev = $this->getSnmprec($community);

        d_echo("[SNMP] snmpwalk $community $num_oid: ");
        while (Str::contains($num_oid, '.')) {
            foreach ($dev as $key => $data) {
                if (Str::startsWith($key, $num_oid)) {
                    return new SnmpResponse($this->outputLine($oid, $num_oid, $data[0], $data[1]));
                }

            }

            $num_oid = substr($num_oid, 0, strrpos($num_oid, '.'));
            dd(get_defined_vars());
        }

        return new SnmpResponse('');
    }

    private function translateType($oid, $mib = null, $mibdir = null)
    {
        $options = ['-IR', '-Td'];
        if ($mib) {
            $options[] = "-m $mib";
        }

        $result = SnmpQuery::make()->mibDir($mibdir)
            ->options($options)->translate($oid)->raw();

        if (empty($result)) {
            throw new Exception('Could not translate oid: ' . $oid . PHP_EOL . 'Tried: ' . $cmd);
        }

        if (Str::contains($result, 'OCTET STRING')) {
            return 4;
        }
        if (Str::contains($result, 'Integer32')) {
            return 2;
        }
        if (Str::contains($result, 'NULL')) {
            return 5;
        }
        if (Str::contains($result, 'OBJECT IDENTIFIER')) {
            return 6;
        }
        if (Str::contains($result, 'IpAddress')) {
            return 64;
        }
        if (Str::contains($result, 'Counter32')) {
            return 65;
        }
        if (Str::contains($result, 'Gauge32')) {
            return 66;
        }
        if (Str::contains($result, 'TimeTicks')) {
            return 67;
        }
        if (Str::contains($result, 'Opaque')) {
            return 68;
        }
        if (Str::contains($result, 'Counter64')) {
            return 70;
        }

        throw new Exception('Unknown type');
    }
}
