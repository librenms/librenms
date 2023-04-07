<?php
/**
 * AlertTransportDeliveryException.php
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

namespace LibreNMS\Exceptions;

class AlertTransportDeliveryException extends \Exception
{
    public function __construct(
        array $data,
        int $code = 0,
        protected string $response = '',
        protected string $template = '',
        protected array $params = []
    ) {
        $name = $data['transport_name'] ?? '';

        $message = "Transport delivery failed with $code for $name: $response";

        parent::__construct($message, $code);
    }
}
