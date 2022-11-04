<?php
/**
 * AutonomousSystem.php
 *
 * Helper for dealing with AS
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

namespace LibreNMS\Util;

use ErrorException;
use Illuminate\Support\Facades\Cache;
use LibreNMS\Config;

class AutonomousSystem
{
    public function __construct(
        private int $asn
    ) {
    }

    public static function get(int|string $asn): self
    {
        return app(AutonomousSystem::class, ['asn' => (int) $asn]);
    }

    /**
     * Get the ASN text from Team Cymru.
     * May be overridden in the config with astext.<asn>
     * Caches results for 1 day
     */
    public function name(): string
    {
        return Cache::remember("astext.$this->asn", 86400, function () {
            if (Config::has("astext.$this->asn")) {
                return Config::get("astext.$this->asn");
            }

            try {
                $result = @dns_get_record("AS$this->asn.asn.cymru.com", DNS_TXT);

                if (! empty($result[0]['txt'])) {
                    $txt = explode('|', $result[0]['txt']);

                    return trim($txt[4], ' "');
                }
            } catch (ErrorException $e) {
            }

            return '';
        });
    }
}
