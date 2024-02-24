<?php
/**
 * Oxidized.php
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
 * @link       https://www.librenms.org
 *
 * @copyright  2022 Tony Murray
 * @author     Tony Murray <murraytony@gmail.com>
 */

namespace App\ApiClients;

use LibreNMS\Config;

class Oxidized extends BaseApi
{
    private bool $enabled;

    public function __construct()
    {
        $this->timeout = 90;
        $this->base_uri = Config::get('oxidized.url') ?? '';
        $this->enabled = Config::get('oxidized.enabled') === true && $this->base_uri;
    }

    /**
     * Ask oxidized to refresh the node list for the source (likely the LibreNMS API).
     */
    public function reloadNodes(): void
    {
        if ($this->enabled && Config::get('oxidized.reload_nodes') === true) {
            $this->getClient()->get('/reload.json');
        }
    }

    /**
     * Queues a hostname to be refreshed by Oxidized
     */
    public function updateNode(string $hostname, string $msg, string $username = 'not_provided'): bool
    {
        if ($this->enabled) {
            // Work around https://github.com/rack/rack/issues/337
            $msg = str_replace('%', '', $msg);

            return $this->getClient()
                ->put("/node/next/$hostname", ['user' => $username, 'msg' => $msg])
                ->successful();
        }

        return false;
    }

    /* Get content of the page */
    public function getContent(string $uri): string
    {
        if ($this->enabled) {
            return $this->getClient()->get($uri);
        } else {
            return '';
        }
    }
}
