<?php
/*
 * Extreme.php
 *
 * -Description-
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
 * along with this program.  If not, see <https://www.gnu.org/licenses/>.
 *
 * @package    LibreNMS
 * @link       https://www.librenms.org
 * @copyright  2020 Tony Murray
 * @author     Tony Murray <murraytony@gmail.com>
 */

namespace LibreNMS\OS\Shared;

use App\Models\Device;

class Extreme extends \LibreNMS\OS
{
    public function discoverOS(Device $device): void
    {
        parent::discoverOS($device); // yaml
        $device->hardware = $this->getHardware() ?: $device->hardware;
    }

    protected function getHardware()
    {
        $hardware = [
            '.1.3.6.1.4.1.1916.2.1' => 'Summit 1',
            '.1.3.6.1.4.1.1916.2.2' => 'Summit 2',
            '.1.3.6.1.4.1.1916.2.3' => 'Summit 3',
            '.1.3.6.1.4.1.1916.2.4' => 'Summit 4',
            '.1.3.6.1.4.1.1916.2.5' => 'Summit 4FX',
            '.1.3.6.1.4.1.1916.2.6' => 'Summit 48',
            '.1.3.6.1.4.1.1916.2.7' => 'Summit 24',
            '.1.3.6.1.4.1.1916.2.8' => 'BlackDiamond 6800',
            '.1.3.6.1.4.1.1916.2.11' => 'BlackDiamond 6808',
            '.1.3.6.1.4.1.1916.2.12' => 'Summit 7iSX',
            '.1.3.6.1.4.1.1916.2.13' => 'Summit 7iTX',
            '.1.3.6.1.4.1.1916.2.14' => 'Summit 1iTX',
            '.1.3.6.1.4.1.1916.2.15' => 'Summit 5i',
            '.1.3.6.1.4.1.1916.2.16' => 'Summit 48i',
            '.1.3.6.1.4.1.1916.2.17' => 'Alpine 3808',
            '.1.3.6.1.4.1.1916.2.19' => 'Summit 1iSX',
            '.1.3.6.1.4.1.1916.2.20' => 'Alpine 3804',
            '.1.3.6.1.4.1.1916.2.21' => 'Summit 5iLX',
            '.1.3.6.1.4.1.1916.2.22' => 'Summit 5iTX',
            '.1.3.6.1.4.1.1916.2.23' => 'EnetSwitch 24Port',
            '.1.3.6.1.4.1.1916.2.24' => 'BlackDiamond 6816',
            '.1.3.6.1.4.1.1916.2.25' => 'Summit 24e3',
            '.1.3.6.1.4.1.1916.2.26' => 'Alpine 3802',
            '.1.3.6.1.4.1.1916.2.27' => 'BlackDiamond 6804',
            '.1.3.6.1.4.1.1916.2.28' => 'Summit 48i1u',
            '.1.3.6.1.4.1.1916.2.30' => 'Summit Px1',
            '.1.3.6.1.4.1.1916.2.40' => 'Summit 24e2TX',
            '.1.3.6.1.4.1.1916.2.41' => 'Summit 24e2SX',
            '.1.3.6.1.4.1.1916.2.53' => 'Summit 200-24',
            '.1.3.6.1.4.1.1916.2.54' => 'Summit 200-48',
            '.1.3.6.1.4.1.1916.2.55' => 'Summit 300-48',
            '.1.3.6.1.4.1.1916.2.56' => 'BlackDiamond 10808',
            '.1.3.6.1.4.1.1916.2.58' => 'Summit 400-48t',
            '.1.3.6.1.4.1.1916.2.59' => 'Summit 400-24x',
            '.1.3.6.1.4.1.1916.2.61' => 'Summit 300-24',
            '.1.3.6.1.4.1.1916.2.62' => 'BlackDiamond 8810',
            '.1.3.6.1.4.1.1916.2.63' => 'Summit 400-24t',
            '.1.3.6.1.4.1.1916.2.64' => 'Summit 400-24p',
            '.1.3.6.1.4.1.1916.2.65' => 'Summit X450-24x',
            '.1.3.6.1.4.1.1916.2.66' => 'Summit X450-24t',
            '.1.3.6.1.4.1.1916.2.67' => 'SummitStack',
            '.1.3.6.1.4.1.1916.2.68' => 'SummitWM 100',
            '.1.3.6.1.4.1.1916.2.69' => 'SummitWM 1000',
            '.1.3.6.1.4.1.1916.2.70' => 'Summit 200-24fx',
            '.1.3.6.1.4.1.1916.2.71' => 'Summit X450a-24t',
            '.1.3.6.1.4.1.1916.2.72' => 'Summit X450e-24p',
            '.1.3.6.1.4.1.1916.2.74' => 'BlackDiamond 8806',
            '.1.3.6.1.4.1.1916.2.75' => 'Altitude 350',
            '.1.3.6.1.4.1.1916.2.76' => 'Summit X450a-48t',
            '.1.3.6.1.4.1.1916.2.77' => 'BlackDiamond 12804',
            '.1.3.6.1.4.1.1916.2.79' => 'Summit X450e-48p',
            '.1.3.6.1.4.1.1916.2.80' => 'Summit X450a-24tDC',
            '.1.3.6.1.4.1.1916.2.81' => 'Summit X450a-24t',
            '.1.3.6.1.4.1.1916.2.82' => 'Summit X450a-24xDC',
            '.1.3.6.1.4.1.1916.2.83' => 'Sentriant CE150',
            '.1.3.6.1.4.1.1916.2.84' => 'Summit X450a-24x',
            '.1.3.6.1.4.1.1916.2.85' => 'BlackDiamond 12802',
            '.1.3.6.1.4.1.1916.2.86' => 'Altitude 300',
            '.1.3.6.1.4.1.1916.2.87' => 'Summit X450a-48tDC',
            '.1.3.6.1.4.1.1916.2.88' => 'Summit X250-24t',
            '.1.3.6.1.4.1.1916.2.89' => 'Summit X250-24p',
            '.1.3.6.1.4.1.1916.2.90' => 'Summit X250-24x',
            '.1.3.6.1.4.1.1916.2.91' => 'Summit X250-48t',
            '.1.3.6.1.4.1.1916.2.92' => 'Summit X250-48p',
            '.1.3.6.1.4.1.1916.2.93' => 'Summit Ver2Stack',
            '.1.3.6.1.4.1.1916.2.94' => 'SummitWM 200',
            '.1.3.6.1.4.1.1916.2.95' => 'SummitWM 2000',
            '.1.3.6.1.4.1.1916.2.100' => 'Summit x150-24t',
            '.1.3.6.1.4.1.1916.2.114' => 'Summit x650-24x',
            '.1.3.6.1.4.1.1916.2.118' => 'Summit X650-24x(SSns)',
            '.1.3.6.1.4.1.1916.2.120' => 'Summit x650-24x(SS)',
            '.1.3.6.1.4.1.1916.2.129' => 'NWI-e450a',
            '.1.3.6.1.4.1.1916.2.133' => 'Summit x480-48t',
            '.1.3.6.1.4.1.1916.2.137' => 'Summit X480-24x',
            '.1.3.6.1.4.1.1916.2.139' => 'Summit X480-24x(10G4X)',
            '.1.3.6.1.4.1.1916.2.141' => 'Summit x480-48x',
            '.1.3.6.1.4.1.1916.2.167' => 'Summit x670-48x',
            '.1.3.6.1.4.1.1916.2.168' => 'Summit x670v-48x',
        ];

        return $hardware[$this->getDevice()->sysObjectID] ?? null;
    }
}
