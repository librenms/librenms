<?php
/**
 * process_config.inc.php
 *
 * LibreNMS file to post process $config into something usable
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

if (empty($config['email_from'])) {
    $config['email_from'] = '"' . $config['project_name'] . '" <' . $config['email_user'] . '@' . php_uname('n') . '>';
}

// We need rrdtool so ensure it's set
if (empty($config['rrdtool'])) {
    $config['rrdtool'] = '/usr/bin/rrdtool';
}
if (empty($config['rrdtool_version'])) {
    $config['rrdtool_version'] = 1.4;
}
