<?php
include "includes/syslog.php";

class SyslogTest extends \PHPUnit_Framework_TestCase
{

    // The format is:
    // $SOURCEIP||$FACILITY||$PRIORITY||$LEVEL||$TAG||$YEAR-$MONTH-$DAY $HOUR:$MIN:$SEC||$MSG||$PROGRAM
    // There add an IP for each OS you want to test and use that in the input file

    private function fillLine($line) {
        $entry = array();
        list($entry['host'],$entry['facility'],$entry['priority'], $entry['level'], $entry['tag'], $entry['timestamp'], $entry['msg'], $entry['program']) = explode("||", trim($line));
        return $entry;
    }

    private function createData($line, $resultDelta) {
        $entry = $this->fillLine($line);
        $data = array();
        $data['input'] = $entry;
        unset($entry['msg']); // empty msg
        $data['result'] = array_merge($entry, $resultDelta);
        return $data;
    }

    public function testCiscoSyslog()
    {
        // populate fake $dev_cache and $config
        global $config, $dev_cache;
        $dev_cache['1.1.1.1'] = ['device_id' => 1, 'os' => 'ios', 'version' => 1];;
        $config = array();
        $config['syslog_filter'] = array();

        // populate test data
        $testdata = array();

        // ---- IOS ----
        $testdata[] = $this->createData(
            "1.1.1.1||user||info||info||0e||2016-02-28 00:23:34||%CARD-SEVERITY-MSG:SLOT %FACILITY-SEVERITY-MNEMONIC: Message-text||",
            ['device_id'=>1, 'program'=>'%CARD-SEVERITY-MSG:SLOT %FACILITY-SEVERITY-MNEMONIC', 'msg'=>'Message-text']
        );
        $testdata[] = $this->createData(
            "1.1.1.1||user||info||info||0e||2016-02-28 00:23:34||%FACILITY-SUBFACILITY-SEVERITY-MNEMONIC: Message-text||",
            ['device_id'=>1, 'program'=>'%FACILITY-SUBFACILITY-SEVERITY-MNEMONIC', 'msg'=>'Message-text']
        );

        // ---- CatOS ----
        $testdata[] = $this->createData(
            "1.1.1.1||user||info||info||0e||2016-02-28 00:23:34||%IP-3-UDP_SOCKOVFL:UDP socket overflow||",
            ['device_id'=>1, 'program'=>'%IP-3-UDP_SOCKOVFL', 'msg'=>'UDP socket overflow']
        );
        $testdata[] = $this->createData(
            "1.1.1.1||user||info||info||0e||2016-02-28 00:23:34||DTP-1-ILGLCFG: Illegal config (on, isl--on,dot1q) on Port [mod/port]||",
            ['device_id'=>1, 'program'=>'DTP-1-ILGLCFG', 'msg'=>'Illegal config (on, isl--on,dot1q) on Port [mod/port]']
        );
        $testdata[] = $this->createData(
            "1.1.1.1||user||info||info||0e||2016-02-28 00:23:34||Cannot enable text mode config if ACL config is cleared from nvram||",
            ['device_id'=>1, 'program'=>'', 'msg'=>'Cannot enable text mode config if ACL config is cleared from nvram']
        );
        $testdata[] = $this->createData(
            "1.1.1.1||user||info||info||0e||2016-02-28 00:23:34||%PAGP-5-PORTFROMSTP / %PAGP-5-PORTTOSTP||",
            ['device_id'=>1, 'program'=>'%PAGP-5-PORTFROMSTP / %PAGP-5-PORTTOSTP']
        );
        $testdata[] = $this->createData(
            "1.1.1.1||user||info||info||0e||2016-02-28 00:23:34||%SYS-3-EOBC_CHANNELREINIT||",
            ['device_id'=>1, 'program'=>'%SYS-3-EOBC_CHANNELREINIT']
        );
        $testdata[] = $this->createData(
            "1.1.1.1||user||info||info||0e||2016-02-28 00:23:34||%SYS-4-MODHPRESET:||",
            ['device_id'=>1, 'program'=>'%SYS-4-MODHPRESET', 'msg'=>'']
        );
        $testdata[] = $this->createData(
            "1.1.1.1||user||info||info||0e||2016-02-28 00:23:34||InbandPingProcessFailure:Module x not responding over inband||",
            ['device_id'=>1, 'program'=>'INBANDPINGPROCESSFAILURE', 'msg'=>'Module x not responding over inband']
        );
        $testdata[] = $this->createData(
            "1.1.1.1||user||info||info||0e||2016-02-28 00:23:34||RxSBIF_SEQ_NUM_ERROR:slot=x||",
            ['device_id'=>1, 'program'=>'RXSBIF_SEQ_NUM_ERROR', 'msg'=>'slot=x']
        );


        // run tests
        foreach($testdata as $data) {
            $res = process_syslog($data['input'], 0);
            $this->assertEquals($data['result'], $res);
	}
    }
}

