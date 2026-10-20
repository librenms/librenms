<?php

/**
 * ProxmoxSecretDefinition.php
 *
 * Proxmox VE API token
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

class ProxmoxSecretDefinition extends SecretDefinition
{
    /**
     * @inheritDoc
     */
    public function fields(): array
    {
        return [
            'token_id' => FieldDefinition::make('token_id', 'text')
                ->label('API Token ID')
                ->placeholder('user@realm!tokenname')
                ->rules(['required', 'string', 'regex:/^[^@\s]+@[^!\s]+![^\s=]+$/']),

            'token_secret' => FieldDefinition::make('token_secret', 'password')
                ->label('API Token Secret')
                ->rules(['required', 'string']),
        ];
    }
}
