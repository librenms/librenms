<?php
/**
 * ChecksSnmpsim.php
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
 * @copyright  2018 Tony Murray
 * @author     Tony Murray <murraytony@gmail.com>
 */

namespace LibreNMS\Tests;

trait SnmpsimHelpers
{
    public function requireSnmpsim(): void
    {
        if (! getenv('SNMPSIM')) {
            $this->markTestSkipped('Snmpsim required for this test. Start snmpsim in another console first with lnms dev:simulate and set SNMPSIM=127.1.6.1:1161');
        }
    }

    public function getSnmpsimIp(): ?string
    {
        $snmpsim = explode(':', getenv('SNMPSIM'));

        return $snmpsim[0] ?? null;
    }

    public function getSnmpsimPort(): int
    {
        $snmpsim = explode(':', getenv('SNMPSIM'));

        return (int) ($snmpsim[1] ?? 161);
    }
}
