<?php
/**
 * SyslogTest.php
 *
 * Tests various syslog input for proper parsing
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
 * @copyright  2016 Tony Murray
 * @author     Tony Murray <murraytony@gmail.com>
 */

namespace LibreNMS\Tests;

class SyslogTest extends TestCase
{
    // The format is:
    // $SOURCEIP||$FACILITY||$PRIORITY||$LEVEL||$TAG||$YEAR-$MONTH-$DAY $HOUR:$MIN:$SEC||$MSG||$PROGRAM
    // There add an IP for each OS you want to test and use that in the input file

    private function fillLine($line)
    {
        $entry = [];
        [$entry['host'],$entry['facility'],$entry['priority'], $entry['level'], $entry['tag'], $entry['timestamp'], $entry['msg'], $entry['program']] = explode('||', trim($line));

        return $entry;
    }

    private function createData($line, $resultDelta)
    {
        $entry = $this->fillLine($line);
        $data = [];
        $data['input'] = $entry;
        unset($entry['msg']); // empty msg
        $data['result'] = array_merge($entry, $resultDelta);

        return $data;
    }

    /**
     * Test an input line with the modified fields
     *
     * @param string $inputline The line from the syslog daemon including the ||'s
     * @param array $modified of the modified fields, most likely containging the keys program and msg
     */
    private function checkSyslog($inputline, $modified)
    {
        $data = $this->createData($inputline, $modified);
        $res = process_syslog($data['input'], 0);
        $this->assertEquals($data['result'], $res);
    }

    public function testCiscoSyslog()
    {
        // populate fake $dev_cache
        global $dev_cache;
        $dev_cache['1.1.1.1'] = ['device_id' => 1, 'os' => 'ios', 'version' => 1, 'hostname' => 'cisco-switch1'];

        // ---- IOS ----
        $this->checkSyslog(
            '1.1.1.1||user||info||info||0e||2016-02-28 00:23:34||%CARD-SEVERITY-MSG:SLOT %FACILITY-SEVERITY-MNEMONIC: Message-text||',
            ['device_id'=>1, 'program'=>'%CARD-SEVERITY-MSG:SLOT %FACILITY-SEVERITY-MNEMONIC', 'msg'=>'Message-text']
        );
        $this->checkSyslog(
            '1.1.1.1||user||info||info||0e||2016-02-28 00:23:34||%FACILITY-SUBFACILITY-SEVERITY-MNEMONIC: Message-text||',
            ['device_id'=>1, 'program'=>'%FACILITY-SUBFACILITY-SEVERITY-MNEMONIC', 'msg'=>'Message-text']
        );
        $this->checkSyslog(
            '1.1.1.1||local7||info||info||be||2016-03-09 03:58:25||Mar 9 11:58:24.145 UTC: %SEC-6-IPACCESSLOGS: list MNGMNT denied 120.62.186.12 1 packet ||]',
            ['device_id'=>1, 'program'=>'%SEC-6-IPACCESSLOGS', 'msg'=>'list MNGMNT denied 120.62.186.12 1 packet']
        );
        $this->checkSyslog(
            '1.1.1.1||local7||info||info||be||2016-04-27 021:12:28||Apr 27 21:12:28: %SYS-5-CONFIG_I: Configured from console by vty0||',
            ['device_id'=>1, 'program'=>'%SYS-5-CONFIG_I', 'msg'=>'Configured from console by vty0']
        );
        $this->checkSyslog(
            '1.1.1.1||local7||info||info||be||2016-04-27 021:12:28||Mar 8 20:14:08.762: %FACILITY-SUBFACILITY-SEVERITY-MNEMONIC: Message-text||000956',
            ['device_id'=>1, 'program'=>'%FACILITY-SUBFACILITY-SEVERITY-MNEMONIC', 'msg'=>'Message-text']
        );

        // ---- CatOS ----
        $this->checkSyslog(
            '1.1.1.1||user||info||info||0e||2016-02-28 00:23:34||%IP-3-UDP_SOCKOVFL:UDP socket overflow||',
            ['device_id'=>1, 'program'=>'%IP-3-UDP_SOCKOVFL', 'msg'=>'UDP socket overflow']
        );
        $this->checkSyslog(
            '1.1.1.1||user||info||info||0e||2016-02-28 00:23:34||DTP-1-ILGLCFG: Illegal config (on, isl--on,dot1q) on Port [mod/port]||',
            ['device_id'=>1, 'program'=>'DTP-1-ILGLCFG', 'msg'=>'Illegal config (on, isl--on,dot1q) on Port [mod/port]']
        );
        $this->checkSyslog(
            '1.1.1.1||user||info||info||0e||2016-02-28 00:23:34||Cannot enable text mode config if ACL config is cleared from nvram||',
            ['device_id'=>1, 'program'=>'', 'msg'=>'Cannot enable text mode config if ACL config is cleared from nvram']
        );
        $this->checkSyslog(
            '1.1.1.1||user||info||info||0e||2016-02-28 00:23:34||%PAGP-5-PORTFROMSTP / %PAGP-5-PORTTOSTP||',
            ['device_id'=>1, 'program'=>'%PAGP-5-PORTFROMSTP / %PAGP-5-PORTTOSTP']
        );
        $this->checkSyslog(
            '1.1.1.1||user||info||info||0e||2016-02-28 00:23:34||%SYS-3-EOBC_CHANNELREINIT||',
            ['device_id'=>1, 'program'=>'%SYS-3-EOBC_CHANNELREINIT']
        );
        $this->checkSyslog(
            '1.1.1.1||user||info||info||0e||2016-02-28 00:23:34||%SYS-4-MODHPRESET:||',
            ['device_id'=>1, 'program'=>'%SYS-4-MODHPRESET', 'msg'=>'']
        );
        $this->checkSyslog(
            '1.1.1.1||user||info||info||0e||2016-02-28 00:23:34||InbandPingProcessFailure:Module x not responding over inband||',
            ['device_id'=>1, 'program'=>'INBANDPINGPROCESSFAILURE', 'msg'=>'Module x not responding over inband']
        );
        $this->checkSyslog(
            '1.1.1.1||user||info||info||0e||2016-02-28 00:23:34||RxSBIF_SEQ_NUM_ERROR:slot=x||',
            ['device_id'=>1, 'program'=>'RXSBIF_SEQ_NUM_ERROR', 'msg'=>'slot=x']
        );

        // With program from syslog
        $this->checkSyslog(
            '1.1.1.1||local7||notice||notice||bd||2016-04-04 15:18:43||Apr 4 13:18:42.670: %LINEPROTO-5-UPDOWN: Line protocol on Interface GigabitEthernet0/32, changed state to up||345735',
            ['device_id'=>1, 'program'=>'%LINEPROTO-5-UPDOWN', 'msg'=>'Line protocol on Interface GigabitEthernet0/32, changed state to up']
        );

        // Incorrect time
        $this->checkSyslog(
            '1.1.1.1||user||info||info||0e||2016-04-06 15:20:35||*Apr 4 21:26:41.778 UTC: %LWAPP-3-REPLAY_ERR: 1 wcm: Received replay error on slot 1, WLAN ID 1, count 1 from AP xxxx.xxxx.xxxx||',
            ['device_id'=>1, 'program'=>'%LWAPP-3-REPLAY_ERR', 'msg'=>'1 wcm: Received replay error on slot 1, WLAN ID 1, count 1 from AP xxxx.xxxx.xxxx']
        );

        $this->checkSyslog(
            '1.1.1.1||user||info||info||0e||2016-04-06 15:20:35||.Apr 4 21:26:41.778 UTC: %LWAPP-3-REPLAY_ERR: 1 wcm: Received replay error on slot 1, WLAN ID 1, count 1 from AP xxxx.xxxx.xxxx||',
            ['device_id'=>1, 'program'=>'%LWAPP-3-REPLAY_ERR', 'msg'=>'1 wcm: Received replay error on slot 1, WLAN ID 1, count 1 from AP xxxx.xxxx.xxxx']
        );
    }

    public function testLinuxSyslog()
    {
        // populate fake $dev_cache
        global $dev_cache;
        $dev_cache['1.1.1.1'] = ['device_id' => 1, 'os' => 'linux', 'version' => 1, 'hostname' => 'linux-server1'];

        // ---- PAM ----
        $this->checkSyslog(
            '1.1.1.1||authpriv||info||info||56||2016-02-28 00:23:34||pam_unix(cron:session): session opened for user librenms by (uid=0)||CRON',
            ['device_id'=>1, 'program'=>'CRON', 'msg'=>'pam_unix(cron:session): session opened for user librenms by (uid=0)']
        );
        $this->checkSyslog(
            '1.1.1.1||authpriv||info||info||55||2016-02-28 00:23:34||pam_unix(sudo:session): session opened for user librenms by root (uid=0)||sudo',
            ['device_id'=>1, 'program'=>'SUDO', 'msg'=>'pam_unix(sudo:session): session opened for user librenms by root (uid=0)']
        );
        $this->checkSyslog(
            '1.1.1.1||auth||info||info||0e||2016-02-28 00:23:34||pam_krb5(sshd:auth): authentication failure; logname=root uid=0 euid=0 tty=ssh ruser= rhost=123.213.132.231||sshd',
            ['device_id'=>1, 'program'=>'SSHD', 'msg'=>'pam_krb5(sshd:auth): authentication failure; logname=root uid=0 euid=0 tty=ssh ruser= rhost=123.213.132.231']
        );
        $this->checkSyslog(
            '1.1.1.1||auth||info||info||0e||2016-02-28 00:23:34||pam_krb5[sshd:auth]: authentication failure; logname=root uid=0 euid=0 tty=ssh ruser= rhost=123.213.132.231||sshd',
            ['device_id'=>1, 'program'=>'SSHD', 'msg'=>'pam_krb5[sshd:auth]: authentication failure; logname=root uid=0 euid=0 tty=ssh ruser= rhost=123.213.132.231']
        );

        // ---- Postfix ----
        $this->checkSyslog(
            '1.1.1.1||mail||info||info||16||2016-02-28 00:23:34||5C62E329EF: to=<admin@example.com>, relay=mail.example.com[127.0.0.1]:25, delay=0.11, delays=0.04/0.01/0/0.06, dsn=2.0.0, status=sent (250 2.0.0 Ok: queued as 5362E6A670E)||postfix/smtp',
            ['device_id'=>1, 'program'=>'POSTFIX/SMTP', 'msg'=>'5C62E329EF: to=<admin@example.com>, relay=mail.example.com[127.0.0.1]:25, delay=0.11, delays=0.04/0.01/0/0.06, dsn=2.0.0, status=sent (250 2.0.0 Ok: queued as 5362E6A670E)']
        );
        $this->checkSyslog(
            '1.1.1.1||mail||info||info||16||2016-02-28 00:23:34||D7256400EF: from=<librenms@librenms.example.com>, size=882, nrcpt=1 (queue active)||postfix/qmgr',
            ['device_id'=>1, 'program'=>'POSTFIX/QMGR', 'msg'=>'D7256400EF: from=<librenms@librenms.example.com>, size=882, nrcpt=1 (queue active)']
        );

        // ---- No program ----
        $this->checkSyslog(
            '1.1.1.1||user||info||info||0e||2016-02-28 00:23:34||some random message||',
            ['device_id'=>1, 'program'=>'USER', 'msg'=>'some random message']
        );

        // ---- Other ----
        $this->checkSyslog(
            '1.1.1.1||cron||info||info||4e||2016-02-28 00:23:34||(librenms) CMD (   /opt/librenms/alerts.php >> /var/log/librenms_alert.log 2>&1)||CRON',
            ['device_id'=>1, 'program'=>'CRON', 'msg'=>'(librenms) CMD (   /opt/librenms/alerts.php >> /var/log/librenms_alert.log 2>&1)']
        );
        $this->checkSyslog(
            '1.1.1.1||authpriv||notice||notice||55||2016-02-28 00:23:34||    root : TTY=pts/1 ; PWD=/opt/librenms ; USER=librenms ; COMMAND=/usr/bin/git status||sudo',
            ['device_id'=>1, 'program'=>'SUDO', 'msg'=>'root : TTY=pts/1 ; PWD=/opt/librenms ; USER=librenms ; COMMAND=/usr/bin/git status']
        );
    }

    public function testProcurveSyslog()
    {
        // populate fake $dev_cache
        global $dev_cache;
        $dev_cache['1.1.1.1'] = ['device_id' => 1, 'os' => 'procurve', 'version' => 1, 'hostname' => 'procurve-switch1'];

        // ---- 2900/2910/3800/5400 ----
        $this->checkSyslog(
            '1.1.1.1||user||info||info||0e||2016-02-28 00:23:34||chassis:  Slot A Ready||00422',
            ['device_id'=>1, 'program'=>'CHASSIS', 'msg'=>'Slot A Ready [00422]']
        );
        $this->checkSyslog(
            '1.1.1.1||user||info||info||0e||2016-02-28 00:23:34||ports:  port 21 is now on-line||00076',
            ['device_id'=>1, 'program'=>'PORTS', 'msg'=>'port 21 is now on-line [00076]']
        );
        $this->checkSyslog(
            '1.1.1.1||user||info||info||0e||2016-02-28 00:23:34||ports:  port 21 is now off-line||00077',
            ['device_id'=>1, 'program'=>'PORTS', 'msg'=>'port 21 is now off-line [00077]']
        );
        $this->checkSyslog(
            '1.1.1.1||user||warning||warning||0c||2016-02-28 00:23:34||FFI:  port 21-High collision or drop rate. See help.||00331',
            ['device_id'=>1, 'program'=>'FFI', 'msg'=>'port 21-High collision or drop rate. See help. [00331]']
        );

        // ---- 2610 ----
        $this->checkSyslog(
            '1.1.1.1||user||warning||warning||0c||2016-02-28 00:23:34||port 21-Excessive undersized/giant packets. See help.||FFI',
            ['device_id'=>1, 'program'=>'FFI', 'msg'=>'port 21-Excessive undersized/giant packets. See help.']
        );
        $this->checkSyslog(
            '1.1.1.1||user||info||info||0e||2016-02-28 00:23:34||updated time by -4 seconds||SNTP',
            ['device_id'=>1, 'program'=>'SNTP', 'msg'=>'updated time by -4 seconds']
        );
    }

    public function testZywallSyslog()
    {
        // populate fake $dev_cache
        global $dev_cache;
        $dev_cache['1.1.1.1'] = ['device_id' => 1, 'os' => 'zywall', 'version' => 1, 'hostname' => 'zywall'];

        // ---- USG60W ----
        $this->checkSyslog(
            '1.1.1.1||local1||info||info||8e||2017-06-14 17:51:25||0" dst="0.0.0.0:0" msg="DHCP server assigned 195.159.132.109 to Chromecast(6C:AD:F8:B1:10:1D)" note="DHCP ACK" user="unknown" devID="a0e4cb7d7f52" cat="DHCP"||src="0.0.0.0',
            ['device_id'=>1, 'program'=>'DHCP', 'msg'=>'src="0.0.0.0:0" dst="0.0.0.0:0" msg="DHCP server assigned 195.159.132.109 to Chromecast(6C:AD:F8:B1:10:1D)" note="DHCP ACK" user="unknown" devID="a0e4cb7d7f52" cat="DHCP"']
        );
    }
}
