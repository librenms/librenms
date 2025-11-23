<?php
/**
 * HappyEyeballsConnection.php
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
 * @copyright  2025 Tony Murray
 * @author     Tony Murray <murraytony@gmail.com>
 */

namespace LibreNMS\Data\Source\Net;

use Fiber;
use LibreNMS\Data\Source\Net\Service\ServiceConnector;

readonly class HappyEyeballsConnection
{
    public string $ip;
    public Fiber $fiber;

    public function __construct(
        public ServiceConnector $connector,
        callable $worker,
    ) {
        $this->ip = $this->connector->getIp();
        $this->fiber = new Fiber($worker);
    }
}
