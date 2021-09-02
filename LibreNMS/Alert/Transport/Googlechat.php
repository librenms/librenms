<?php
/**
 * LibreNMS Google Chat alerting transport
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
 * @copyright  2021 Pablo Baldovi
 * @author     Pablo Baldovi <pbaldovi@gmail.com>
 */

namespace LibreNMS\Alert\Transport;

use LibreNMS\Alert\Transport;
use Log;

class Googlechat extends Transport
{
    public function deliverAlert($obj, $opts)
    {
        $googlechat_conf['webhookurl'] = $this->config['googlechat-webhook'];

        return $this->contactGooglechat($obj, $googlechat_conf);
    }

    public static function contactGooglechat($obj, $data)
    {
        $payload = '{"text": "' . $obj['msg'] . '"}';

        Log::debug($payload);

        // Create a new cURL resource
        $ch = curl_init($data['webhookurl']);

        // Attach encoded JSON string to the POST fields
        curl_setopt($ch, CURLOPT_POSTFIELDS, $payload);

        // Set the content type to application/json
        curl_setopt($ch, CURLOPT_HTTPHEADER, ['Content-Type:application/json']);

        // Return response instead of outputting
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);

        // Execute the POST request
        $result = curl_exec($ch);

        // Close cURL resource

        $code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);

        Log::debug($code);

        if ($code != 200) {
            Log::error('Google Chat Transport Error');
            Log::error($result);

            return 'HTTP Status code ' . $code;
        }

        return true;
    }

    public static function configTemplate()
    {
        return [
            'config' => [
                [
                    'title' => 'Webhook URL',
                    'name' => 'googlechat-webhook',
                    'descr' => 'Google Chat Room Webhook',
                    'type' => 'text',
                ],
            ],
            'validation' => [
                'googlechat-webhook' => 'required|string',
            ],
        ];
    }
}
