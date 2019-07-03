<?php
/**
 * 3com.inc.php
 *
 * LibreNMS mempool poller module for 3com
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
 * @copyright  2017 Neil Lathwood
 * @author     Neil Lathwood <neil@lathwood.co.uk>
 */

if ($device['os'] === '3com') {
    echo '3COM:';

    $tmp_data = snmp_get_multi_oid($device, ['.1.3.6.1.4.1.43.45.1.6.1.2.1.1.3.65536', '.1.3.6.1.4.1.43.45.1.6.1.2.1.1.2.65536']);

    $mempool['total'] = $tmp_data['.1.3.6.1.4.1.43.45.1.6.1.2.1.1.2.65536'];
    $mempool['free'] = $tmp_data['.1.3.6.1.4.1.43.45.1.6.1.2.1.1.3.65536'];
    $mempool['used'] = $mempool['total'] - $mempool['free'];
}
