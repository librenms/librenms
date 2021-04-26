<?php
/*
 * Copyright (C) 2014  <singh@devilcode.org>
 * Modified and Relicensed by <f0o@devilcode.org> under the expressed
 * permission by the Copyright-Holder <singh@devilcode.org>.
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
 * */

namespace LibreNMS;

use LibreNMS\Authentication\LegacyAuth;
use LibreNMS\DB\Eloquent;
use LibreNMS\Enum\AlertState;
use LibreNMS\Util\Number;
use LibreNMS\Util\Time;
use Permissions;

class IRCBot
{
    private $config;

    private $user;

    private $last_activity = 0;

    private $data = '';

    private $authd = [];

    private $debug = false;

    private $server = '';

    private $port = '';

    private $ssl = false;

    private $pass = '';

    private $nick = 'LibreNMS';

    private $tempnick = null;

    private $chan = [];

    private $commands = [
        'auth',
        'quit',
        'listdevices',
        'device',
        'port',
        'down',
        'version',
        'status',
        'log',
        'help',
        'reload',
        'join',
    ];

    private $command = '';

    private $external = [];

    private $tick = 62500;

    private $j = 0;

    private $socket = [];

    private $floodcount = 0;

    private $max_retry = 5;

    private $nickwait;

    private $buff;

    private $tokens;

    public function __construct()
    {
        $this->log('Setting up IRC-Bot..');

        $this->config = Config::getAll();
        $this->debug = $this->config['irc_debug'];
        $this->config['irc_authtime'] = $this->config['irc_authtime'] ? $this->config['irc_authtime'] : 3;
        $this->max_retry = $this->config['irc_maxretry'];
        $this->server = $this->config['irc_host'];
        if ($this->config['irc_port'][0] == '+') {
            $this->ssl = true;
            $this->port = substr($this->config['irc_port'], 1);
        } else {
            $this->port = $this->config['irc_port'];
        }

        if ($this->config['irc_nick']) {
            $this->nick = $this->config['irc_nick'];
        }

        if ($this->config['irc_alert_chan']) {
            if (strstr($this->config['irc_alert_chan'], ',')) {
                $this->config['irc_alert_chan'] = explode(',', $this->config['irc_alert_chan']);
            } elseif (! is_array($this->config['irc_alert_chan'])) {
                $this->config['irc_alert_chan'] = [$this->config['irc_alert_chan']];
            }
            $this->chan = $this->config['irc_alert_chan'];
        }

        if ($this->config['irc_pass']) {
            $this->pass = $this->config['irc_pass'];
        }

        $this->loadExternal();
        $this->log('Starting IRC-Bot..');
        $this->init();
    }

    //end __construct()

    private function loadExternal()
    {
        if (! $this->config['irc_external']) {
            return true;
        }

        $this->log('Caching external commands...');
        if (! is_array($this->config['irc_external'])) {
            $this->config['irc_external'] = explode(',', $this->config['irc_external']);
        }

        foreach ($this->config['irc_external'] as $ext) {
            $this->log("Command $ext...");
            if (($this->external[$ext] = file_get_contents('includes/ircbot/' . $ext . '.inc.php')) == '') {
                $this->log('failed!');
                unset($this->external[$ext]);
            }
        }

        return $this->log('Cached ' . sizeof($this->external) . ' commands.');
    }

    //end load_external()

    private function init()
    {
        if ($this->config['irc_alert']) {
            $this->connectAlert();
        }

        $this->last_activity = time();

        $this->j = 2;

        $this->connect();
        $this->log('Connected');
        if ($this->pass) {
            fwrite($this->socket['irc'], 'PASS ' . $this->pass . "\n\r");
        }

        $this->doAuth();
        $this->nickwait = 0;
        while (true) {
            foreach ($this->socket as $n => $socket) {
                if (! is_resource($socket) || feof($socket)) {
                    $this->log("Socket '$n' closed. Restarting.");
                    break 2;
                }
            }

            if (isset($this->tempnick)) {
                if ($this->nickwait > 100) {
                    $this->ircRaw('NICK ' . $this->nick);
                    $this->nickwait = 0;
                }
                $this->nickwait += 1;
            }

            $this->getData();
            if ($this->config['irc_alert']) {
                $this->alertData();
            }

            if ($this->config['irc_conn_timeout']) {
                $inactive_seconds = time() - $this->last_activity;
                $max_inactive = $this->config['irc_conn_timeout'];
                if ($inactive_seconds > $max_inactive) {
                    $this->log('No data from server since ' . $max_inactive . ' seconds. Restarting.');
                    break;
                }
            }

            usleep($this->tick);
        }

        return $this->init();
    }

    //end init()

    private function connectAlert()
    {
        $f = $this->config['install_dir'] . '/.ircbot.alert';
        if ((file_exists($f) && filetype($f) != 'fifo' && ! unlink($f)) || (! file_exists($f) && ! shell_exec("mkfifo $f && echo 1"))) {
            $this->log('Error - Cannot create Alert-File');

            return false;
        }

        if (($this->socket['alert'] = fopen($f, 'r+'))) {
            $this->log('Opened Alert-File');
            stream_set_blocking($this->socket['alert'], false);

            return true;
        }

        $this->log('Error - Cannot open Alert-File');

        return false;
    }

    //end connect_alert()

    private function read($buff)
    {
        $r = fread($this->socket[$buff], 8192);
        $this->buff[$buff] .= $r;
        $r = strlen($r);
        if (strstr($this->buff[$buff], "\n")) {
            $tmp = explode("\n", $this->buff[$buff], 2);
            $this->buff[$buff] = substr($this->buff[$buff], (strlen($tmp[0]) + 1));
            if ($this->debug) {
                $this->log("Returning buffer '$buff': '" . trim($tmp[0]) . "'");
            }

            return $tmp[0];
        }

        if ($this->debug && $r > 0) {
            $this->log("Expanding buffer '$buff' = '" . trim($this->buff[$buff]) . "'");
        }

        return false;
    }

    //end read()

    private function alertData()
    {
        if (($alert = $this->read('alert')) !== false) {
            $alert = json_decode($alert, true);
            if (! is_array($alert)) {
                return false;
            }
            if ($this->debug) {
                $this->log('Alert received ' . $alert['title']);
                $this->log('Alert state ' . $alert['state']);
                $this->log('Alert severity ' . $alert['severity']);
                $this->log('Alert channels ' . print_r($this->config['irc_alert_chan'], true));
            }

            switch ($alert['state']) {
                case AlertState::WORSE:
                    $severity_extended = '+';
                    break;
                case AlertState::BETTER:
                    $severity_extended = '-';
                    break;
                default:
                    $severity_extended = '';
            }
            $severity = '';
            if (isset($alert['severity'])) {
                $severity = str_replace(['warning', 'critical', 'normal'], [$this->_color('Warning', 'yellow'), $this->_color('Critical', 'red'), $this->_color('Info', 'lightblue')], $alert['severity']) . $severity_extended . ' ';
            }

            if ($alert['state'] == AlertState::RECOVERED and $this->config['irc_alert_utf8']) {
                $severity = str_replace(['Warning', 'Critical'], ['̶W̶a̶r̶n̶i̶n̶g', '̶C̶r̶i̶t̶i̶c̶a̶l'], $severity);
            }

            if ($this->config['irc_alert_chan']) {
                foreach ($this->config['irc_alert_chan'] as $chan) {
                    $this->sendAlert($chan, $severity, $alert);
                    $this->ircRaw('BOTFLOODCHECK');
                }
            } else {
                foreach ($this->authd as $nick => $data) {
                    if ($data['expire'] >= time()) {
                        $this->sendAlert($nick, $severity, $alert);
                    }
                }
            }
        }
    }

    //end alertData()

    private function sendAlert($sendto, $severity, $alert)
    {
        $sendto = explode(' ', $sendto)[0];
        $this->ircRaw('PRIVMSG ' . $sendto . ' :' . $severity . trim($alert['title']));
        if ($this->config['irc_alert_short']) {
            // Only send the title if set to short

            return;
        }

        foreach (explode("\n", $alert['msg']) as $line) {
            $line = trim($line);
            if (strlen($line) < 1) {
                continue;
            }
            $line = $this->_html2irc($line);
            $line = strip_tags($line);

            // We don't need to repeat the title
            if (trim($line) != trim($alert['title'])) {
                $this->log("Sending alert $line");
                if ($this->config['irc_floodlimit'] > 100) {
                    $this->floodcount += strlen($line);
                } elseif ($this->config['irc_floodlimit'] > 1) {
                    $this->floodcount += 1;
                }
                if (($this->config['irc_floodlimit'] > 0) && ($this->floodcount > $this->config['irc_floodlimit'])) {
                    $this->log('Reached floodlimit ' . $this->floodcount);
                    $this->ircRaw('BOTFLOODCHECK');
                    sleep(2);
                    $this->floodcount = 0;
                }
                $this->ircRaw('PRIVMSG ' . $sendto . ' :' . $line);
            }
        }
    }

    //end sendAlert()

    private function getData()
    {
        if (($data = $this->read('irc')) !== false) {
            $this->last_activity = time();
            $this->data = $data;
            $ex = explode(' ', $this->data);
            if ($ex[0] == 'PING') {
                return $this->ircRaw('PONG ' . $ex[1]);
            }

            if ($ex[1] == 376 || $ex[1] == 422 || ($ex[1] == 'MODE' && $ex[2] == $this->nick)) {
                if ($this->j == 2) {
                    $this->joinChan();
                    $this->j = 0;
                }
            }

            if (($this->config['irc_ctcp']) && (preg_match('/^:' . chr(1) . '.*/', $ex[3]))) {
                // Handle CTCP
                $ctcp = trim(preg_replace('/[^A-Z]/', '', $ex[3]));
                $ctcp_reply = null;
                $this->log('Received irc CTCP: ' . $ctcp . ' from ' . $this->getUser($this->data));
                switch ($ctcp) {
                    case 'VERSION':
                        $ctcp_reply = chr(1) . "$ctcp " . $this->config['irc_ctcp_version'] . chr(1);
                        break;
                    case 'PING':
                        $ctcp_reply = chr(1) . "$ctcp " . $ex[4] . ' ' . $ex[5] . chr(1);
                        break;
                    case 'TIME':
                        $ctcp_reply = chr(1) . "$ctcp " . date('c') . chr(1);
                        break;
                }
                if ($ctcp_reply !== null) {
                    $this->log('Sending irc CTCP: ' . 'NOTICE ' . $this->getUser($this->data) . ' :' . $ctcp_reply);

                    return $this->ircRaw('NOTICE ' . $this->getUser($this->data) . ' :' . $ctcp_reply);
                }
            }

            if (($ex[1] == 'NICK') && (preg_replace('/^:/', '', $ex[2]) == $this->nick)) {
                // Nickname changed successfully
                if ($this->debug) {
                    $this->log('Regained our real nick');
                }
                unset($this->tempnick);
            }
            if (($ex[1] == 433) || ($ex[1] == 437)) {
                // Nickname already in use / temp unavailable
                if ($this->debug) {
                    $this->log('Nickname already in use...');
                }
                if ($ex[2] != '*') {
                    $this->tempnick = $ex[2];
                }
                if (! isset($this->tempnick)) {
                    $this->tempnick = $this->nick . rand(0, 99);
                }
                if ($this->debug) {
                    $this->log('Using temp nick ' . $this->tempnick);
                }

                return $this->ircRaw('NICK ' . $this->tempnick);
            }
            if ($ex[1] == 421) {
                // Unknown command
                if ($ex[3] == 'BOTFLOODCHECK') {
                    $this->floodcount = 0;
                }
            }
            $this->command = str_replace([chr(10), chr(13)], '', $ex[3]);
            if (strstr($this->command, ':.')) {
                $this->handleCommand();
            }
        }
    }

    //end getData()

    private function joinChan($chan = false)
    {
        if ($chan) {
            $this->chan[] = $chan;
        }

        foreach ($this->chan as $chan) {
            $this->ircRaw('JOIN ' . $chan);
        }

        return true;
    }

    //end joinChan()

    private function handleCommand()
    {
        $this->command = str_replace(':.', '', $this->command);
        $tmp = explode(':.' . $this->command . ' ', $this->data);
        $this->user = $this->getAuthdUser();
        $this->log('isAuthd-1? ' . $this->isAuthd());
        if (! $this->isAuthd() && (isset($this->config['irc_auth']))) {
            $this->hostAuth();
        }
        $this->log('isAuthd-2? ' . $this->isAuthd());
        if ($this->isAuthd() || trim($this->command) == 'auth') {
            $this->proceedCommand(str_replace("\n", '', trim($this->command)), trim($tmp[1]));
        }

        $this->authd[$this->getUser($this->data)] = $this->user;

        return false;
    }

    //end handleCommand()

    private function proceedCommand($command, $params)
    {
        $command = strtolower($command);
        if (in_array($command, $this->commands)) {
            $this->chkdb();
            $this->log($command . " ( '" . $params . "' )");

            return $this->{'_' . $command}($params);
        } elseif ($this->external[$command]) {
            $this->chkdb();
            $this->log($command . " ( '" . $params . "' ) [Ext]");

            return eval($this->external[$command]);
        }

        return false;
    }

    //end proceedCommand()

    private function respond($msg)
    {
        $chan = $this->getChan($this->data);

        return $this->sendMessage($msg, strstr($chan, '#') ? $chan : $this->getUser($this->data));
    }

    //end respond()

    private function getChan($param)
    {
        $data = explode('PRIVMSG ', $this->data, 3);
        $data = explode(' ', $data[1], 2);

        return $data[0];
    }

    //end getChan()

    private function getUser($param)
    {
        $arrData = explode('!', $param, 2);

        return str_replace(':', '', $arrData[0]);
    }

    //end getUser()

    private function getUserHost($param)
    {
        $arrData = explode(' ', $param, 2);

        return str_replace(':', '', $arrData[0]);
    }

    //end getUserHost()

    private function connect($try = 0)
    {
        if ($try > $this->max_retry) {
            $this->log('Failed too many connection attempts, aborting');

            return exit();
        }

        $this->log('Trying to connect (' . ($try + 1) . ') to ' . $this->server . ':' . $this->port . ($this->ssl ? ' (SSL)' : ''));
        if ($this->socket['irc']) {
            $this->ircRaw('QUIT :Reloading');
            fclose($this->socket['irc']);
        }

        if ($this->ssl) {
            $server = 'ssl://' . $this->server;
        } else {
            $server = $this->server;
        }

        if ($this->ssl && $this->config['irc_disable_ssl_check']) {
            $ssl_context_params = ['ssl'=>['allow_self_signed'=> true, 'verify_peer' => false, 'verify_peer_name' => false]];
            $ssl_context = stream_context_create($ssl_context_params);
            $this->socket['irc'] = stream_socket_client($server . ':' . $this->port, $errno, $errstr, 30, STREAM_CLIENT_CONNECT, $ssl_context);
        } else {
            $this->socket['irc'] = fsockopen($server, $this->port);
        }

        if (! is_resource($this->socket['irc'])) {
            if ($try < 5) {
                sleep(5);
            } elseif ($try < 10) {
                sleep(60);
            } else {
                sleep(300);
            }

            return $this->connect($try + 1);
        } else {
            stream_set_blocking($this->socket['irc'], false);

            return true;
        }
    }

    //end connect()

    private function doAuth()
    {
        if ($this->ircRaw('USER ' . $this->nick . ' 0 ' . $this->nick . ' :' . $this->nick) && $this->ircRaw('NICK ' . $this->nick)) {
            return true;
        }

        return false;
    }

    //end doAuth()

    private function sendMessage($message, $chan)
    {
        if ($this->debug) {
            $this->log("Sending 'PRIVMSG " . trim($chan) . ' :' . trim($message) . "'");
        }

        return $this->ircRaw('PRIVMSG ' . trim($chan) . ' :' . trim($message));
    }

    //end sendMessage()

    private function log($msg)
    {
        $log = '[' . date('r') . '] IRCbot ' . trim($msg) . "\n";
        echo $log;
        file_put_contents($this->config['log_dir'] . '/irc.log', $log, FILE_APPEND);

        return true;
    }

    //end log()

    private function chkdb()
    {
        if (! Eloquent::isConnected()) {
            try {
                Eloquent::boot();
            } catch (\PDOException $e) {
                $this->log('Cannot connect to MySQL: ' . $e->getMessage());

                return exit();
            }
        }

        return true;
    }

    //end chkdb()

    private function isAuthd()
    {
        if ($this->user['expire'] >= time()) {
            $this->user['expire'] = (time() + ($this->config['irc_authtime'] * 3600));

            return true;
        } else {
            return false;
        }
    }

    //end isAuthd()

    private function getAuthdUser()
    {
        return $this->authd[$this->getUser($this->data)];
    }

    //end getAuthUser()

    private function hostAuth()
    {
        $this->log('HostAuth');
        global $authorizer;
        foreach ($this->config['irc_auth'] as $nms_user => $hosts) {
            foreach ($hosts as $host) {
                $host = preg_replace("/\*/", '.*', $host);
                if ($this->debug) {
                    $this->log("HostAuth on irc matching $host to " . $this->getUserHost($this->data));
                }
                if (preg_match("/$host/", $this->getUserHost($this->data))) {
                    $user_id = LegacyAuth::get()->getUserid($nms_user);
                    $user = LegacyAuth::get()->getUser($user_id);
                    $this->user['name'] = $user['username'];
                    $this->user['id'] = $user_id;
                    $this->user['level'] = LegacyAuth::get()->getUserlevel($user['username']);
                    $this->user['expire'] = (time() + ($this->config['irc_authtime'] * 3600));
                    if ($this->user['level'] < 5) {
                        $this->user['devices'] = Permissions::devicesForUser($this->user['id'])->toArray();
                        $this->user['ports'] = Permissions::portsForUser($this->user['id'])->toArray();
                    }
                    if ($this->debug) {
                        $this->log("HostAuth on irc for '" . $user['username'] . "', ID: '" . $user_id . "', Host: '" . $host);
                    }

                    return true;
                }
            }
        }

        return false;
    }

    //end hostAuth

    private function ircRaw($params)
    {
        return fputs($this->socket['irc'], $params . "\r\n");
    }

    //end irc_raw()

    private function _auth($params)
    {
        global $authorizer;
        $params = explode(' ', $params, 2);
        if (strlen($params[0]) == 64) {
            if ($this->tokens[$this->getUser($this->data)] == $params[0]) {
                $this->user['expire'] = (time() + ($this->config['irc_authtime'] * 3600));
                $tmp_user = LegacyAuth::get()->getUser($this->user['id']);
                $tmp = LegacyAuth::get()->getUserlevel($tmp_user['username']);
                $this->user['level'] = $tmp;
                if ($this->user['level'] < 5) {
                    $this->user['devices'] = Permissions::devicesForUser($this->user['id'])->toArray();
                    $this->user['ports'] = Permissions::portsForUser($this->user['id'])->toArray();
                }

                return $this->respond('Authenticated.');
            } else {
                return $this->respond('Nope.');
            }
        } else {
            $user_id = LegacyAuth::get()->getUserid($params[0]);
            $user = LegacyAuth::get()->getUser($user_id);
            if ($user['email'] && $user['username'] == $params[0]) {
                $token = hash('gost', openssl_random_pseudo_bytes(1024));
                $this->tokens[$this->getUser($this->data)] = $token;
                $this->user['name'] = $params[0];
                $this->user['id'] = $user['user_id'];
                if ($this->debug) {
                    $this->log("Auth for '" . $params[0] . "', ID: '" . $user['user_id'] . "', Token: '" . $token . "', Mail: '" . $user['email'] . "'");
                }

                if (send_mail($user['email'], 'LibreNMS IRC-Bot Authtoken', "Your Authtoken for the IRC-Bot:\r\n\r\n" . $token . "\r\n\r\n") === true) {
                    return $this->respond('Token sent!');
                } else {
                    return $this->respond('Sorry, seems like mail doesnt like us.');
                }
            } else {
                return $this->respond('Who are you again?');
            }
        }//end if
    }

    //end _auth()

    private function _reload($params)
    {
        if ($this->user['level'] == 10) {
            if ($params == 'external') {
                $this->respond('Reloading external scripts.');

                return $this->loadExternal();
            }
            $new_config = Config::load();
            $this->respond('Reloading configuration & defaults');
            if ($new_config != $this->config) {
                $this->__construct();

                return;
            }
        } else {
            return $this->respond('Permission denied.');
        }
    }

    //end _reload()

    private function _join($params)
    {
        if ($this->user['level'] == 10) {
            return $this->joinChan($params);
        } else {
            return $this->respond('Permission denied.');
        }
    }

    //end _join()

    private function _quit($params)
    {
        if ($this->user['level'] == 10) {
            $this->ircRaw('QUIT :Requested');

            return exit();
        } else {
            return $this->respond('Permission denied.');
        }
    }

    //end _quit()

    private function _help($params)
    {
        $msg = join(', ', $this->commands);
        if (count($this->external) > 0) {
            $msg .= ', ' . join(', ', array_keys($this->external));
        }

        return $this->respond("Available commands: $msg");
    }

    //end _help()

    private function _version($params)
    {
        $versions = version_info();
        $schema_version = $versions['db_schema'];
        $version = $versions['local_ver'];

        $msg = $this->config['project_name'] . ', Version: ' . $version . ', DB schema: ' . $schema_version . ', PHP: ' . PHP_VERSION;

        return $this->respond($msg);
    }

    //end _version()

    private function _log($params)
    {
        $num = 1;
        $hostname = '';
        $params = explode(' ', $params);
        if ($params[0] > 1) {
            $num = $params[0];
        }
        if (strlen($params[1]) > 0) {
            $hostname = preg_replace("/[^A-z0-9\.\-]/", '', $params[1]);
        }
        $hostname = $hostname . '%';
        if ($this->user['level'] < 5) {
            $tmp = dbFetchRows('SELECT `event_id`, eventlog.device_id, devices.hostname, `datetime`,`message`, eventlog.type FROM `eventlog`, `devices` WHERE eventlog.device_id=devices.device_id and devices.hostname like "' . $hostname . '" and eventlog.device_id IN (' . implode(',', $this->user['devices']) . ') ORDER BY `event_id` DESC LIMIT ' . (int) $num);
        } else {
            $tmp = dbFetchRows('SELECT `event_id`, eventlog.device_id, devices.hostname, `datetime`,`message`, eventlog.type FROM `eventlog`, `devices` WHERE eventlog.device_id=devices.device_id and devices.hostname like "' . $hostname . '" ORDER BY `event_id` DESC LIMIT ' . (int) $num);
        }

        foreach ($tmp as $logline) {
            $response = $logline['datetime'] . ' ';
            $response .= $this->_color($logline['hostname'], null, null, 'bold') . ' ';
            if ($this->config['irc_alert_utf8']) {
                if (preg_match('/critical alert/', $logline['message'])) {
                    $response .= preg_replace('/critical alert/', $this->_color('critical alert', 'red'), $logline['message']) . ' ';
                } elseif (preg_match('/warning alert/', $logline['message'])) {
                    $response .= preg_replace('/warning alert/', $this->_color('warning alert', 'yellow'), $logline['message']) . ' ';
                } elseif (preg_match('/recovery/', $logline['message'])) {
                    $response .= preg_replace('/recovery/', $this->_color('recovery', 'green'), $logline['message']) . ' ';
                } else {
                    $response .= $logline['message'] . ' ';
                }
            } else {
                $response .= $logline['message'] . ' ';
            }
            if ($logline['type'] != 'NULL') {
                $response .= $logline['type'] . ' ';
            }
            if ($this->config['irc_floodlimit'] > 100) {
                $this->floodcount += strlen($response);
            } elseif ($this->config['irc_floodlimit'] > 1) {
                $this->floodcount += 1;
            }
            if (($this->config['irc_floodlimit'] > 0) && ($this->floodcount > $this->config['irc_floodlimit'])) {
                $this->ircRaw('BOTFLOODCHECK');
                sleep(2);
                $this->floodcount = 0;
            }
            $this->respond($response);
        }

        if (! $tmp) {
            $this->respond('Nothing to see, maybe a bug?');
        }

        return true;
    }

    //end _log()

    private function _down($params)
    {
        if ($this->user['level'] < 5) {
            $tmp = dbFetchRows('SELECT `hostname` FROM `devices` WHERE status=0 AND `device_id` IN (' . implode(',', $this->user['devices']) . ')');
        } else {
            $tmp = dbFetchRows('SELECT `hostname` FROM `devices` WHERE status=0');
        }

        $msg = '';
        foreach ($tmp as $db) {
            if ($db['hostname']) {
                $msg .= ', ' . $db['hostname'];
            }
        }

        $msg = substr($msg, 2);
        $msg = $msg ? $msg : 'Nothing to show :)';

        return $this->respond($msg);
    }

    //end _down()

    private function _device($params)
    {
        $params = explode(' ', $params);
        $hostname = $params[0];
        $device = dbFetchRow('SELECT * FROM `devices` WHERE `hostname` = ?', [$hostname]);
        if (! $device) {
            return $this->respond('Error: Bad or Missing hostname, use .listdevices to show all devices.');
        }

        if ($this->user['level'] < 5 && ! in_array($device['device_id'], $this->user['devices'])) {
            return $this->respond('Error: Permission denied.');
        }

        $status = $device['status'] ? 'Up ' . Time::formatInterval($device['uptime']) : 'Down';
        $status .= $device['ignore'] ? '*Ignored*' : '';
        $status .= $device['disabled'] ? '*Disabled*' : '';

        return $this->respond($device['os'] . ' ' . $device['version'] . ' ' . $device['features'] . ' ' . $status);
    }

    //end _device()

    private function _port($params)
    {
        $params = explode(' ', $params);
        $hostname = $params[0];
        $ifname = $params[1];
        if (! $hostname || ! $ifname) {
            return $this->respond('Error: Missing hostname or ifname.');
        }

        $device = dbFetchRow('SELECT * FROM `devices` WHERE `hostname` = ?', [$hostname]);
        $port = dbFetchRow('SELECT * FROM `ports` WHERE (`ifName` = ? OR `ifDescr` = ?) AND device_id = ?', [$ifname, $ifname, $device['device_id']]);
        if ($this->user['level'] < 5 && ! in_array($port['port_id'], $this->user['ports']) && ! in_array($device['device_id'], $this->user['devices'])) {
            return $this->respond('Error: Permission denied.');
        }

        $bps_in = Number::formatSi($port['ifInOctets_rate'] * 8, 2, 3, 'bps');
        $bps_out = Number::formatSi($port['ifOutOctets_rate'] * 8, 2, 3, 'bps');
        $pps_in = Number::formatBi($port['ifInUcastPkts_rate'], 2, 3, 'pps');
        $pps_out = Number::formatBi($port['ifOutUcastPkts_rate'], 2, 3, 'pps');

        return $this->respond($port['ifAdminStatus'] . '/' . $port['ifOperStatus'] . ' ' . $bps_in . ' > bps > ' . $bps_out . ' | ' . $pps_in . ' > PPS > ' . $pps_out);
    }

    //end _port()

    private function _listdevices($params)
    {
        if ($this->user['level'] < 5) {
            $tmp = dbFetchRows('SELECT `hostname` FROM `devices` WHERE `device_id` IN (' . implode(',', $this->user['devices']) . ')');
        } else {
            $tmp = dbFetchRows('SELECT `hostname` FROM `devices`');
        }

        $msg = '';
        foreach ($tmp as $device) {
            $msg .= ', ' . $device['hostname'];
        }

        $msg = substr($msg, 2);
        $msg = $msg ? $msg : 'Nothing to show..?';

        return $this->respond($msg);
    }

    //end _listdevices()

    private function _status($params)
    {
        $params = explode(' ', $params);
        $statustype = $params[0];

        $d_w = '';
        $d_a = '';
        $p_w = '';
        $p_a = '';
        if ($this->user['level'] < 5) {
            $d_w = ' WHERE device_id IN (' . implode(',', $this->user['devices']) . ')';
            $d_a = ' AND   device_id IN (' . implode(',', $this->user['devices']) . ')';
            $p_w = ' WHERE  port_id IN (' . implode(',', $this->user['ports']) . ') OR device_id IN (' . implode(',', $this->user['devices']) . ')';
            $p_a = ' AND (I.port_id IN (' . implode(',', $this->user['ports']) . ') OR I.device_id IN (' . implode(',', $this->user['devices']) . '))';
        }

        switch ($statustype) {
            case 'devices':
            case 'device':
            case 'dev':
                $devcount = dbFetchCell('SELECT count(*) FROM devices' . $d_w);
                $devup = dbFetchCell("SELECT count(*) FROM devices  WHERE status = '1' AND `ignore` = '0'" . $d_a);
                $devdown = dbFetchCell("SELECT count(*) FROM devices WHERE status = '0' AND `ignore` = '0'" . $d_a);
                $devign = dbFetchCell("SELECT count(*) FROM devices WHERE `ignore` = '1'" . $d_a);
                $devdis = dbFetchCell("SELECT count(*) FROM devices WHERE `disabled` = '1'" . $d_a);
                if ($devup > 0) {
                    $devup = $this->_color($devup, 'green');
                }
                if ($devdown > 0) {
                    $devdown = $this->_color($devdown, 'red');
                    $devcount = $this->_color($devcount, 'yellow', null, 'bold');
                } else {
                    $devcount = $this->_color($devcount, 'green', null, 'bold');
                }
                $msg = 'Devices: ' . $devcount . ' (' . $devup . ' up, ' . $devdown . ' down, ' . $devign . ' ignored, ' . $devdis . ' disabled' . ')';
                break;

            case 'ports':
            case 'port':
            case 'prt':
                $prtcount = dbFetchCell('SELECT count(*) FROM ports' . $p_w);
                $prtup = dbFetchCell("SELECT count(*) FROM ports AS I, devices AS D  WHERE I.ifOperStatus = 'up' AND I.ignore = '0' AND I.device_id = D.device_id AND D.ignore = '0'" . $p_a);
                $prtdown = dbFetchCell("SELECT count(*) FROM ports AS I, devices AS D WHERE I.ifOperStatus = 'down' AND I.ifAdminStatus = 'up' AND I.ignore = '0' AND D.device_id = I.device_id AND D.ignore = '0'" . $p_a);
                $prtsht = dbFetchCell("SELECT count(*) FROM ports AS I, devices AS D WHERE I.ifAdminStatus = 'down' AND I.ignore = '0' AND D.device_id = I.device_id AND D.ignore = '0'" . $p_a);
                $prtign = dbFetchCell("SELECT count(*) FROM ports AS I, devices AS D WHERE D.device_id = I.device_id AND (I.ignore = '1' OR D.ignore = '1')" . $p_a);
//                $prterr   = dbFetchCell("SELECT count(*) FROM ports AS I, devices AS D WHERE D.device_id = I.device_id AND (I.ignore = '0' OR D.ignore = '0') AND (I.ifInErrors_delta > '0' OR I.ifOutErrors_delta > '0')".$p_a);
                if ($prtup > 0) {
                    $prtup = $this->_color($prtup, 'green');
                }
                if ($prtdown > 0) {
                    $prtdown = $this->_color($prtdown, 'red');
                    $prtcount = $this->_color($prtcount, 'yellow', null, 'bold');
                } else {
                    $prtcount = $this->_color($prtcount, 'green', null, 'bold');
                }
                $msg = 'Ports: ' . $prtcount . ' (' . $prtup . ' up, ' . $prtdown . ' down, ' . $prtign . ' ignored, ' . $prtsht . ' shutdown' . ')';
                break;

            case 'services':
            case 'service':
            case 'srv':
                $status_counts = [];
                $status_colors = [0 => 'green', 3 => 'lightblue', 1 => 'yellow', 2 => 'red'];
                $srvcount = dbFetchCell('SELECT COUNT(*) FROM services' . $d_w);
                $srvign = dbFetchCell('SELECT COUNT(*) FROM services WHERE service_ignore = 1' . $d_a);
                $srvdis = dbFetchCell('SELECT COUNT(*) FROM services WHERE service_disabled = 1' . $d_a);
                $service_status = dbFetchRows("SELECT `service_status`, COUNT(*) AS `count` FROM `services` WHERE `service_disabled`=0 AND `service_ignore`=0 $d_a GROUP BY `service_status`");
                $service_status = array_column($service_status, 'count', 'service_status'); // key by status

                foreach ($status_colors as $status => $color) {
                    if (isset($service_status[$status])) {
                        $status_counts[$status] = $this->_color($service_status[$status], $color);
                        $srvcount = $this->_color($srvcount, $color, null, 'bold'); // upgrade the main count color
                    } else {
                        $status_counts[$status] = 0;
                    }
                }

                $msg = "Services: $srvcount ({$status_counts[0]} up, {$status_counts[2]} down, {$status_counts[1]} warning, {$status_counts[3]} unknown, $srvign ignored, $srvdis disabled)";
                break;

            default:
                $msg = 'Error: STATUS requires one of the following: <devices|device|dev>|<ports|port|prt>|<services|service|src>';
                break;
        }//end switch

        return $this->respond($msg);
    }

    //end _status()

    private function _color($text, $fg_color, $bg_color = null, $other = null)
    {
        $colors = [
            'white' => '00',
            'black' => '01',
            'blue' => '02',
            'green' => '03',
            'red' => '04',
            'brown' => '05',
            'purple' => '06',
            'orange' => '07',
            'yellow' => '08',
            'lightgreen' => '09',
            'cyan' => '10',
            'lightcyan' => '11',
            'lightblue' => '12',
            'pink' => '13',
            'grey' => '14',
            'lightgrey' => '15',
        ];
        $ret = chr(3);
        if (array_key_exists($fg_color, $colors)) {
            $ret .= $colors[$fg_color];
            if (array_key_exists($bg_color, $colors)) {
                $ret .= ',' . $colors[$fg_color];
            }
        }
        switch ($other) {
            case 'bold':
                $ret .= chr(2);
                break;
            case 'underline':
                $ret .= chr(31);
                break;
            case 'italics':
            case 'reverse':
                $ret .= chr(22);
                break;
        }
        $ret .= $text;
        $ret .= chr(15);

        return $ret;
    }

    // end _color

    private function _html2irc($string)
    {
        $string = urldecode($string);
        $string = preg_replace('#<b>#i', chr(2), $string);
        $string = preg_replace('#</b>#i', chr(2), $string);
        $string = preg_replace('#<i>#i', chr(22), $string);
        $string = preg_replace('#</i>#i', chr(22), $string);
        $string = preg_replace('#<u>#i', chr(31), $string);
        $string = preg_replace('#</u>#i', chr(31), $string);

        $colors = [
            'white'     => '00',
            'black'     => '01',
            'blue'      => '02',
            'green'     => '03',
            'red'       => '04',
            'brown'     => '05',
            'purple'    => '06',
            'orange'    => '07',
            'yellow'    => '08',
            'lightgreen' => '09',
            'cyan'      => '10',
            'lightcyan' => '11',
            'lightblue' => '12',
            'pink'      => '13',
            'grey'      => '14',
            'lightgrey' => '15',
        ];

        foreach ($colors as $color => $code) {
            $string = preg_replace("#<$color>#i", chr(3) . $code, $string);
            $string = preg_replace("#</$color>#i", chr(3), $string);
        }

        return $string;
    }

    // end _html2irc
}//end class
