<?php
/**
 * Dns.php
 *
 * Get version info about LibreNMS and various components/dependencies
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
 * @link       http://librenms.org
 * @copyright  2021 Thomas Berberich
 * @author     Thomas Berberch <sourcehhdoctor@gmail.com>
 */

namespace LibreNMS\Util;

class Dns
{
    protected $resolver;

    public function __construct()
    {
        $dns_resolver_file = stream_resolve_include_path('Net/DNS2.php');

        if (! file_exists($dns_resolver_file)) {
            d_echo('FILE NOT FOUND: ' . $dns_resolver_file);
        } else {
            require_once $dns_resolver_file;

            $this->resolver = new Net_DNS2_Resolver();
        }
    }

    /**
     * @param $domain  Domain which has to be parsed
     * @param $record  DNS Record which should be searched
     * @return array   List of matching records
     */
    public function getRecord($domain, $record = 'A')
    {
        try {
            $ret = $this->resolver->query($domain, $record);

            return $ret->answer;
        } catch (Net_DNS2_Exception $e) {
            d_echo('::query() failed: ' . $e->getMessage());

            return [];
        }
    }
}
