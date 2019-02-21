<?php
/* Copyright (C) 2014 Daniel Preussker <f0o@devilcode.org>
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
 * along with this program.  If not, see <http://www.gnu.org/licenses/>. */

/**
 * Mail Transport
 * @author f0o <f0o@devilcode.org>
 * @copyright 2014 f0o, LibreNMS
 * @license GPL
 * @package LibreNMS
 * @subpackage Alerts
 */
namespace LibreNMS\Alert\Transport;

use App\Models\Device;
use App\Models\User;
use LibreNMS\Alert\Transport;
use LibreNMS\Config;

class Mail extends Transport
{
    public function deliverAlert($obj, $opts)
    {
        $contacts = $this->buildContacts($obj, $opts['transports']);
        dd($contacts);

        return $this->contactMail($obj);
    }



    public function contactMail($obj)
    {
        if (empty($this->config['email'])) {
            $email = $obj['contacts'];
        } else {
            $email = $this->config['email'];
        }
        return send_mail($email, $obj['title'], $obj['msg'], (Config::get('email_html') == 'true') ? true : false);
    }

    public static function configTemplate()
    {
        return [
            'config' => [
                [
                    'title' => 'Email',
                    'name' => 'email',
                    'descr' => 'Email address of contact',
                    'type'  => 'text',
                ]
            ],
            'validation' => [
                'email' => 'required|email'
            ]
        ];
    }

    private function buildContacts($obj, $transports)
    {
        if (Config::get('alert.default_only')) {
            $default_email = Config::get('alert.default_mail');
            $contacts = $default_email ? [$default_email => ''] : [];
        } else {
            $device = Device::find($obj['device_id']);
            $contacts = $this->getSystemContacts($device) + $this->getUserContacts($obj['faults']);
        }



        // Always add transport contacts
        $transport_mails = \App\Models\AlertTransport::findMany($transports->pluck('transport_id'));
        $contacts = $transport_mails->reduce(function ($output, $transport) {
            if (isset($transport->transport_config['email'])) {
                $output[$transport->transport_config['email']] = '';
            }
            return $output;
        }, $contacts);


        return $contacts;
    }

    /**
     * @param Device $device
     */
    private function getSystemContacts($device)
    {
        $contacts = [];

        // sysContact
        if ($device) {
            $attribs = $device->attribs()->whereIn('attrib_type', ['override_sysContact_bool', 'override_sysContact_string'])->get()->keyBy('attrib_type');
            if ($attribs->has('override_sysContact_bool')) {
                if ($sysContactAttrib = $attribs->get('override_sysContact_string')) {
                    $sysContact = $sysContactAttrib->attrib_value;
                }
            } else {
                $sysContact = $device->sysContact;
            }

            if (!empty($sysContact)) {
                $contacts[$sysContact] = '';
            }
        }
    }

    private function getPermittedFaults($faults)
    {
        $permitted = [];
        foreach ($faults as $index => $fault) {
            if (!empty($fault['bill_id'])) {

            }
        }
    }

    private function getUserContacts($faults)
    {
        $query = User::thisAuth()->select('users.*');

        $query->where(function ($query) use ($faults) {
            if (Config::get('alert.admins')) {
                $query->orWhere('level', 10);
            }
            if (Config::get('alert.globals')) {
                $query->orWhere('level', 5);
            }
            if (Config::get('alert.users')) {

            }
        });

        $users = LegacyAuth::get()->getUserlist();
        $contacts = array();
        $uids = array();
        foreach ($results as $result) {
            $tmp  = null;
            if (is_numeric($result["bill_id"])) {
                $tmpa = dbFetchRows("SELECT user_id FROM bill_perms WHERE bill_id = ?", array($result["bill_id"]));
                foreach ($tmpa as $tmp) {
                    $uids[$tmp['user_id']] = $tmp['user_id'];
                }
            }
            if (is_numeric($result["port_id"])) {
                $tmpa = dbFetchRows("SELECT user_id FROM ports_perms WHERE port_id = ?", array($result["port_id"]));
                foreach ($tmpa as $tmp) {
                    $uids[$tmp['user_id']] = $tmp['user_id'];
                }
            }
            if (is_numeric($result["device_id"])) {
                if ($config['alert']['syscontact'] == true) {
                    if (dbFetchCell("SELECT attrib_value FROM devices_attribs WHERE attrib_type = 'override_sysContact_bool' AND device_id = ?", [$result["device_id"]])) {
                        $tmpa = dbFetchCell("SELECT attrib_value FROM devices_attribs WHERE attrib_type = 'override_sysContact_string' AND device_id = ?", array($result["device_id"]));
                    } else {
                        $tmpa = dbFetchCell("SELECT sysContact FROM devices WHERE device_id = ?", array($result["device_id"]));
                    }
                    if (!empty($tmpa)) {
                        $contacts[$tmpa] = '';
                    }
                }
                $tmpa = dbFetchRows("SELECT user_id FROM devices_perms WHERE device_id = ?", array($result["device_id"]));
                foreach ($tmpa as $tmp) {
                    $uids[$tmp['user_id']] = $tmp['user_id'];
                }
            }
        }
        foreach ($users as $user) {
            if (empty($user['email'])) {
                continue; // no email, skip this user
            }
            if (empty($user['realname'])) {
                $user['realname'] = $user['username'];
            }
            if (empty($user['level'])) {
                $user['level'] = LegacyAuth::get()->getUserlevel($user['username']);
            }
            if ($config['alert']['globals'] && ( $user['level'] >= 5 && $user['level'] < 10 )) {
                $contacts[$user['email']] = $user['realname'];
            } elseif ($config['alert']['admins'] && $user['level'] == 10) {
                $contacts[$user['email']] = $user['realname'];
            } elseif ($config['alert']['users'] == true && in_array($user['user_id'], $uids)) {
                $contacts[$user['email']] = $user['realname'];
            }
        }

        $tmp_contacts = array();
        foreach ($contacts as $email => $name) {
            if (strstr($email, ',')) {
                $split_contacts = preg_split('/[,\s]+/', $email);
                foreach ($split_contacts as $split_email) {
                    if (!empty($split_email)) {
                        $tmp_contacts[$split_email] = $name;
                    }
                }
            } else {
                $tmp_contacts[$email] = $name;
            }
        }

        if (!empty($tmp_contacts)) {
            // Validate contacts so we can fall back to default if configured.
            $mail = new PHPMailer();
            foreach ($tmp_contacts as $tmp_email => $tmp_name) {
                if ($mail->validateAddress($tmp_email) != true) {
                    unset($tmp_contacts[$tmp_email]);
                }
            }
        }

        # Copy all email alerts to default contact if configured.
        if (!isset($tmp_contacts[$config['alert']['default_mail']]) && ($config['alert']['default_copy'])) {
            $tmp_contacts[$config['alert']['default_mail']] = '';
        }

        # Send email to default contact if no other contact found
        if ((count($tmp_contacts) == 0) && ($config['alert']['default_if_none']) && (!empty($config['alert']['default_mail']))) {
            $tmp_contacts[$config['alert']['default_mail']] = '';
        }

        return $tmp_contacts;
    }
}
