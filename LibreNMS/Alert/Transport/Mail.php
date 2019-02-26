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
use Illuminate\Support\Collection;
use LibreNMS\Alert\Transport;
use LibreNMS\Config;
use PHPMailer\PHPMailer\PHPMailer;

class Mail extends Transport
{
    private $permissions;

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
                    'type' => 'text',
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

    public function getPermittedFaults($faults)
    {


        $user_map = User::query()
//            ->where('level', '<', 5)
            ->leftJoin('bill_perms', 'users.user_id', 'bill_perms.user_id')
            ->leftJoin('ports_perms', 'users.user_id', 'ports_perms.user_id')
            ->leftJoin('devices_perms', 'users.user_id', 'devices_perms.user_id')
            ->select(['users.user_id', 'bill_id', 'port_id', 'device_id'])
            ->get()->groupBy('user_id');

        return $user_map->toArray();

        $types = ['bill_id', 'port_id', 'device_id'];
        $ids = [];
        foreach ($faults as $index => $fault) {
            foreach ($types as $id) {
                if (!empty($fault[$id])) {
                    $ids[$id][] = $fault[$id];
                }
            }
        }

        $maps = [];
        if (!empty($ids['bill_id'])) {
            $maps['bills'] = 1;
        }


        $result = ['user_id' => 'faults_array'];

        return $permitted;
    }

    private function canAccessFault(User $user, $fault)
    {
        $permissions = $this->getPermissions();

        if (!empty($fault['device_id']) && $permissions->get('devices')->get($user->user_id, new Collection())->contains($fault['device_id'])) {
            return true;
        }

        if (!empty($fault['port_id']) && $permissions->get('ports')->get($user->user_id, new Collection())->contains($fault['port_id'])) {
            return true;
        }

        return !empty($fault['bill_id']) && $permissions->get('bills')->get($user->user_id, new Collection())->contains($fault['bill_id']);
    }



    public function getUsersForFaults($faults) {
        $fields = ['users.user_id', 'users.username', 'users.realname', 'users.email', 'users.descr', 'users.level'];
        /** @var Builder $query */
        $query = User::thisAuth();

        $query->where(function ($sub_query) use ($faults, $query) {
            /** @var Builder $sub_query */
            if (Config::get('alert.admins')) {
                $sub_query->orWhere('level', 10);
            }
            if (Config::get('alert.globals')) {
                $sub_query->orWhere('level', 5);
            }
            if (Config::get('alert.users')) {
                $fault_ids = $this->getFaultIds($faults);

                if ($fault_ids->get('devices')->isNotEmpty()) {
                    $query->leftJoin('devices_perms', 'users.user_id', 'devices_perms.user_id');
                    $sub_query->orWhereIn('devices_perms.device_id', $fault_ids->get('devices'));
                }

                if ($fault_ids->get('ports')->isNotEmpty()) {
                    $query->leftJoin('ports_perms', 'users.user_id', 'ports_perms.user_id');
                    $sub_query->orWhereIn('ports_perms.device_id', $fault_ids->get('ports'));
                }

                if ($fault_ids->get('bills')->isNotEmpty()) {
                    $query->leftJoin('bill_perms', 'users.user_id', 'bill_perms.user_id');
                    $sub_query->orWhereIn('bill_perms.device_id', $fault_ids->get('bills'));
                }
            }
        });

        return $query->select($fields)->groupBy($fields)->get();
    }

    private function getFaultIds($faults) {
        $devices = new Collection();
        $ports = new Collection();
        $bills = new Collection();

        foreach ($faults as $fault) {
            if (!empty($fault['device_id'])) {
                $devices->push($fault['device_id']);
            }
            if (!empty($fault['port_id'])) {
                $ports->push($fault['port_id']);
            }
            if (!empty($fault['bill_id'])) {
                $bills->push($fault['bill_id']);
            }
        }

        return new Collection([
            'devices' => $devices->unique(),
            'ports' => $ports->unique(),
            'bills' => $bills->unique(),
        ]);
    }

    private function getUserContacts($faults)
    {
        $users = $this->getUsersForFaults($faults);

        $users = LegacyAuth::get()->getUserlist();
        $contacts = [];
        $uids = [];

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
            if ($config['alert']['globals'] && ($user['level'] >= 5 && $user['level'] < 10)) {
                $contacts[$user['email']] = $user['realname'];
            } elseif ($config['alert']['admins'] && $user['level'] == 10) {
                $contacts[$user['email']] = $user['realname'];
            } elseif ($config['alert']['users'] == true && in_array($user['user_id'], $uids)) {
                $contacts[$user['email']] = $user['realname'];
            }
        }

        $tmp_contacts = [];
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

    public function sendMail($emails, $subject, $message, $html = false)
    {
        if (is_array($emails) || ($emails = self::parseEmail($emails))) {
            d_echo("Attempting to email $subject to: " . implode('; ', array_keys($emails)) . PHP_EOL);
            $mail = new PHPMailer(true);
            try {
                $mail->Hostname = php_uname('n');

                foreach (self::parseEmail(Config::get('email_from')) as $from => $from_name) {
                    $mail->setFrom($from, $from_name);
                }
                foreach ($emails as $email => $email_name) {
                    $mail->addAddress($email, $email_name);
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
     * Extracts one or more emails from a string.
     *
     * @param string $emails
     * @return array|bool Array will be keyed by email with the value being the friendly name
     */
    public static function parseEmail($emails)
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
                $result[$email] = Config::get('email_user');
            }
        }

        return $result;
    }
}
