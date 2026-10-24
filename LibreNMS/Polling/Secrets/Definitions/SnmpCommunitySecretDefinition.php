<?php

/**
 * SnmpCommunitySecretDefinition.php
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
 * @copyright  2026 Tony Murray
 * @author     Tony Murray <murraytony@gmail.com>
 */

namespace LibreNMS\Polling\Secrets\Definitions;

use App\View\FieldSchema\FieldDefinition;

/**
 * A community for SNMP v1 and v2c, the version is a setting of the polling method.
 */
class SnmpCommunitySecretDefinition extends SecretDefinition
{
    /**
     * @inheritDoc
     */
    public function fields(): array
    {
        return [
            'community' => FieldDefinition::make('community', 'password')
                ->label('Community')
                ->rules(['required', 'string']),
        ];
    }
}
