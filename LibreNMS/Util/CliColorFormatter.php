<?php
/**
 * CliColorFormatter.php
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
 * @copyright  2018 Tony Murray
 * @author     Tony Murray <murraytony@gmail.com>
 */

namespace LibreNMS\Util;

class CliColorFormatter extends \Monolog\Formatter\LineFormatter
{
    private $console_color;
    private $console;

    public function __construct()
    {
        $this->console_color = new \Console_Color2();
        $this->console = \App::runningInConsole();

        parent::__construct(
            "%message% %context% %extra%\n",
            null,
            true,
            true
        );
    }

    public function format(array $record): string
    {
        // only format messages where color is enabled
        if (isset($record['context']['color']) && $record['context']['color']) {
            if ($this->console) {
                $record['message'] = $this->console_color->convert($record['message']);
            } else {
                $record['message'] = $this->console_color->strip($record['message']);
            }
            unset($record['context']['color']);
        }

        return parent::format($record);
    }
}
