<?php
/**
 * LINE Notify Transport
 */

namespace LibreNMS\Alert\Transport;

use LibreNMS\Alert\Transport;

class Linenotify extends Transport
{
    public function deliverAlert($obj, $opts)
    {
        $opts['line-notify-access-token'] = $this->config['line-notify-access-token'];

        return $this->contactLineNotify($obj, $opts);
    }

    private function contactLinenotify($obj, $opts)
    {
        $lineUrl = 'https://notify-api.line.me/api/notify';
        $lineHead = ['Authorization: Bearer ' . $opts['line-notify-access-token']];
        $lineFields = ['message' => $obj['msg']];

        $curl = curl_init();
        curl_setopt($curl, CURLOPT_URL, $lineUrl);
        curl_setopt($curl, CURLOPT_HTTPHEADER, $lineHead);
        curl_setopt($curl, CURLOPT_NOBODY, false);
        curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($curl, CURLOPT_POST, true);
        curl_setopt($curl, CURLOPT_POSTFIELDS, $lineFields);
        curl_exec($curl);
        $code = curl_getinfo($curl, CURLINFO_HTTP_CODE);
        curl_close($curl);
        if ($code != 200) {
            return 'HTTP Status code ' . $code;
        }

        return true;
    }

    public static function configTemplate()
    {
        return [
            'config' => [
                [
                    'title' => 'Token',
                    'name' => 'line-notify-access-token',
                    'descr' => 'LINE Notify Token',
                    'type' => 'text',
                ],
            ],
            'validation' => [
                'line-notify-access-token' => 'required|string',
            ],
        ];
    }
}
