<?php
/**
 * LINE Notify Transport
 * @package LibreNMS
 * @subpackage Alerts
 */
namespace LibreNMS\Alert\Transport;

use LibreNMS\Alert\Transport;

class Linenotify extends Transport
{
    public function deliverAlert($obj, $opts)
    {
        $token = $this->config['line-notify-access-token'];
        return $this->contactLineNotify($obj, $token);
    }

    private function contactLinenotify($obj, $token)
    {
        $lineUrl = 'https://notify-api.line.me/api/notify';
        $lineHead = array('Authorization: Bearer ' . $token);
        $lineFields = array('message' => $obj['msg']);

        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $lineUrl);
        curl_setopt($ch, CURLOPT_HTTPHEADER, $lineHead);
        curl_setopt($ch, CURLOPT_NOBODY, false);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $lineFields);
        curl_exec($ch);
        $code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);
        if ($code != 200)
        {
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
                    'type' => 'text'
                ]
            ],
            'validation' => [
                'line-notify-access-token' => 'required|string',
            ]
        ];
    }
}
