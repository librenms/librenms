<?php
/**
 * Messagebirdvoice.php
 *
 * LibreNMS Messagebird voice API Tranport
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
 * @copyright  2023 Sjef van Zeeland
 * @author     https://github.com/jepke/
 *
 * Messagebird voice will return 201 status if the call was initiated, if status is not 201 LibreNMS will log the full error
 *
 * @contributer f0o, sdef2
 * Thanks to F0o <f0o@devilcode.org> for creating the Slack transport which is the majority of this code.
 * Thanks to sdef2 for figuring out the differences needed to make Discord work.
 * Thanks to theherodied for discord transport used as a base for messagebird.
 */

namespace LibreNMS\Alert\Transport;

use LibreNMS\Alert\Transport;
use LibreNMS\Exceptions\AlertTransportDeliveryException;
use LibreNMS\Util\Http;

class Messagebirdvoice extends Transport
{
    protected string $name = 'Messagebird Voice';

    public function deliverAlert(array $alert_data): bool
    {
        $messagebird_msg = mb_strimwidth($alert_data['msg'], 0, 1000, '...');
        $api_url = 'https://rest.messagebird.com/voicemessages';
        $fields = [
            'recipients' => $this->config['messagebird-recipient'],
            'originator' => $this->config['messagebird-origin'],
            'language' => $this->config['messagebird-language'],
            'voice' => $this->config['messagebird-voice'],
            'body' => $messagebird_msg,
        ];

        $res = Http::client()
            ->withHeaders([
                'Authorization' => 'AccessKey ' . $this->config['messagebird-key'],
            ])
            ->post($api_url, $fields);
        if ($res->successful() && $res->status() == '201') {
            return true;
        }

        throw new AlertTransportDeliveryException($alert_data, $res->status(), $res->body(), $messagebird_msg, $fields);
    }

    public static function configTemplate(): array
    {
        return [
            'config' => [
                [
                    'title' => 'Messagebird API key',
                    'name' => 'messagebird-key',
                    'descr' => 'Messagebird API REST key',
                    'type' => 'password',
                ],
                [
                    'title' => 'Messagebird originator',
                    'name' => 'messagebird-origin',
                    'descr' => 'Originator in E.164 format eg. +1555###****',
                    'type' => 'text',
                ],
                [
                    'title' => 'Messagebird recipients',
                    'name' => 'messagebird-recipient',
                    'descr' => 'Recipient in E.164 format eg. +1555###****',
                    'type' => 'text',
                ],
                [
                    'title' => 'Language',
                    'name' => 'messagebird-language',
                    'descr' => 'Spoken Language',
                    'type' => 'select',
                    'options' => [
                        'Welsch' => 'cy-gb',
                        'Danish' => 'da-dk',
                        'German' => 'de-de',
                        'Greek' => 'el-gr',
                        'English (Australia)' => 'en-au',
                        'English (UK)' => 'en-gb',
                        'English (Welsch)' => 'en-gb-wls',
                        'English (India)' => 'en-in',
                        'English (US)' => 'en-us',
                        'Spanish' => 'es-es',
                        'Spanish (Mexico)' => 'es-mx',
                        'Spanish (US)' => 'es-us',
                        'French (Quebec)' => 'fr-ca',
                        'French' => 'fr-fr',
                        'Indonesian' => 'id-id',
                        'Icelandic' => 'is-is',
                        'Italian' => 'it-it',
                        'Japanese' => 'ja-jp',
                        'Korean' => 'ko-kr',
                        'Malay' => 'ms-my',
                        'Norwegian' => 'nb-no',
                        'Dutch' => 'nl-nl',
                        'Polish' => 'pl-pl',
                        'Portuguese (Brazil)' => 'pt-br',
                        'Portuguese' => 'pt-pt',
                        'Romanian' => 'ro-ro',
                        'Russian' => 'ru-ru',
                        'Swedish' => 'sv-se',
                        'Tamil' => 'ta-in',
                        'Thai' => 'th-th',
                        'Turkish' => 'tr-tr',
                        'Vietnamese' => 'vi-vn',
                        'Chinese (Simplified)' => 'zh-cn',
                        'Chinese (HongKong)' => 'zh-hk',
                    ],
                    'default' => 'en-us',
                ],
                [
                    'title' => 'Voice',
                    'name' => 'messagebird-voice',
                    'descr' => 'Male,Female',
                    'type' => 'select',
                    'options' => [
                        'Female' => 'female',
                        'Male' => 'male',
                    ],
                ],
                [
                    'title' => 'Message repeat',
                    'name' => 'messagebird-repeat',
                    'descr' => 'Number of times the message will be repeated',
                    'type' => 'text',
                    'default' => 1,
                ],
            ],
            'validation' => [
                'messagebird-key' => 'required',
                'messagebird-origin' => 'required',
                'messagebird-recipient' => 'required',
                'messagebird-language' => 'in:cy-gb,da-dk,de-de,el-gr,en-au,en-gb,en-gb-wls,en-in,en-us,es-es,es-mx,es-us,fr-ca,fr-fr,id-id,is-is,it-it,ja-jp,ko-kr,ms-my,nb-no,nl-nl,pl-pl,pt-br,pt-pt,ro-ro,ru-ru,sv-se,ta-in,th-th,tr-tr,vi-vn,zh-cn,zh-hk',
                'messagebird-voice' => 'in:male,female',
            ],
        ];
    }
}
