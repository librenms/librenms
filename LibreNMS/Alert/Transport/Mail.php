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
use Exception;
use Illuminate\Database\Eloquent\Builder;
use LibreNMS\Alert\Transport;
use LibreNMS\Config;
use Permissions;
use PHPMailer\PHPMailer\PHPMailer;

class Mail extends Transport
{
    public static function configTemplate()
    {
        return [
            'config' => [
                [
                    'title' => 'Email',
                    'name' => 'email',
                    'descr' => 'Email address of contact',
                    'type' => 'text',
                ]
            ],
            'validation' => [
                'email' => 'required|email'
            ]
        ];
    }

    /**
     * Extracts one or more emails from a string.
     *
     * @param string $emails
     * @param string $name_fallback The name to put for emails that don't have a descriptive name
     * @return array|bool Array will be keyed by email with the value being the friendly name
     */
    public static function parseEmail($emails, $name_fallback = '')
    {
        $result = [];
        $regex = '/^[\"\']?([^\"\']+)[\"\']?\s{0,}<([^@]+@[^>]+)>$/';
        if (!is_string($emails)) {
            return false;
        }

        $emails = preg_split('/[,;]\s{0,}/', $emails);
        foreach ($emails as $email) {
            if (preg_match($regex, $email, $out, PREG_OFFSET_CAPTURE)) {
                $result[$out[2][0]] = $out[1][0];
            } elseif (str_contains($email, '@')) {
                $result[$email] = $name_fallback;
            }
        }

        return $result;
    }

    public function deliverAlert($obj, $opts)
    {
        $contacts = $this->buildContacts($obj['device_id'], $obj['faults'], $opts['transports']);

        return $this->sendMail($contacts, $obj['title'], $obj['msg'], (Config::get('email_html') == 'true') ? true : false);
    }

    public function getPermittedFaults($faults, $user)
    {
        return array_filter($faults, function ($fault) use ($user) {
            return (!empty($fault['device_id']) && Permissions::canAccessDevice($fault['device_id'], $user)) ||
                (!empty($fault['port_id']) && Permissions::canAccessPort($fault['port_id'], $user)) ||
                (!empty($fault['bill_id']) && Permissions::canAccessBill($fault['bill_id'], $user));
        });
    }

    public function getUsersForFaults($faults)
    {
        return User::thisAuth()->where(function ($sub_query) use ($faults) {
            /** @var Builder $sub_query */
            if (Config::get('alert.admins')) {
                $sub_query->orWhere('level', 10);
            }
            if (Config::get('alert.globals')) {
                $sub_query->orWhere('level', 5);
            }
            if (Config::get('alert.users')) {
                $user_ids = collect();

                foreach ($faults as $fault) {
                    if (!empty($fault['device_id'])) {
                        $user_ids->merge(Permissions::usersForDevice($fault['device_id']));
                    }
                    if (!empty($fault['port_id'])) {
                        $user_ids->merge(Permissions::usersForPort($fault['port_id']));
                    }
                    if (!empty($fault['bill_id'])) {
                        $user_ids->merge(Permissions::usersForBill($fault['bill_id']));
                    }
                }

                if ($user_ids->isNotEmpty()) {
                    $sub_query->orWhereIn('user_id', $user_ids->unique());
                }
            }
        })->get();
    }

    public function sendMail($emails, $subject, $message, $html = false)
    {
        if (is_array($emails) || ($emails = self::parseEmail($emails))) {
            d_echo("Attempting to email $subject to: " . implode('; ', array_keys($emails)) . PHP_EOL);
            $mail = new PHPMailer(true);
            try {
                $mail->Hostname = php_uname('n');

                foreach (self::parseEmail(Config::get('email_from'), Config::get('email_user')) as $from => $from_name) {
                    $mail->setFrom($from, $from_name);
                }
                foreach ($emails as $email => $email_name) {
                    if (Config::get('email_use_bcc')) {
                        $mail->addBCC($email, $email_name);
                    } else {
                        $mail->addAddress($email, $email_name);
                    }
                }
                $mail->Subject = $subject;
                $mail->XMailer = Config::get('project_name_version');
                $mail->CharSet = 'utf-8';
                $mail->WordWrap = 76;
                $mail->Body = $message;
                if ($html) {
                    $mail->isHTML(true);
                }
                switch (strtolower(trim(Config::get('email_backend')))) {
                    case 'sendmail':
                        $mail->Mailer = 'sendmail';
                        $mail->Sendmail = Config::get('email_sendmail_path');
                        break;
                    case 'smtp':
                        $mail->isSMTP();
                        $mail->Host = Config::get('email_smtp_host');
                        $mail->Timeout = Config::get('email_smtp_timeout');
                        $mail->SMTPAuth = Config::get('email_smtp_auth');
                        $mail->SMTPSecure = Config::get('email_smtp_secure');
                        $mail->Port = Config::get('email_smtp_port');
                        $mail->Username = Config::get('email_smtp_username');
                        $mail->Password = Config::get('email_smtp_password');
                        $mail->SMTPAutoTLS = Config::get('email_auto_tls');
                        $mail->SMTPDebug = false;
                        break;
                    default:
                        $mail->Mailer = 'mail';
                        break;
                }
                $mail->send();
                return true;
            } catch (\PHPMailer\PHPMailer\Exception $e) {
                return $e->errorMessage();
            } catch (Exception $e) {
                return $e->getMessage();
            }
        }

        return "No contacts found";
    }

    /**
     * @param int $device_id
     * @param array $faults
     * @param \Illuminate\Support\Collection $transports
     * @return array
     */
    private function buildContacts($device_id, $faults, $transports)
    {
        if (Config::get('alert.default_only')) {
            $default_email = Config::get('alert.default_mail');
            $contacts = $default_email ? [$default_email => ''] : [];
        } else {
            $device = Device::find($device_id);
            $contacts = $this->getSystemContacts($device) + $this->getUserContacts($faults);
        }

        // Always add transport contacts
        $transport_mails = \App\Models\AlertTransport::findMany($transports->pluck('transport_id'));
        $contacts = $transport_mails->reduce(function ($output, $transport) {
            if (isset($transport->transport_config['email'])) {
                $output[$transport->transport_config['email']] = $transport->transport_name != $transport->transport_config['email'] ? $transport->transport_name : '';
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


    private function getUserContacts($faults)
    {
        $contacts = [];
        $users = $this->getUsersForFaults($faults);
        foreach ($users as $user) {
            if ($email = self::parseEmail($user->email, $user->realname)) {
                $contacts = array_merge($contacts, $email);
            }
        }

        return $contacts;

        $users = LegacyAuth::get()->getUserlist();
        $contacts = [];
        $uids = [];

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
