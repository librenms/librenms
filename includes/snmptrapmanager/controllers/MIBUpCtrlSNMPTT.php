<?php namespace snmptrapmanager;

class MIBUpCtrlSNMPTT extends MIBUpCtrl
{

    private $oMIB = null;
    private $oMIBFile = null;
    private $oSNMPTT = null;
    private $oInfo = null;

    public function __construct()
    {
        parent::__construct();
        $this->oMIB = $this->loadModel('MIB');
        $this->oMIBFile = $this->loadModel('MIBFile');
        $this->oSNMPTT = $this->loadModel('SNMPTT');
        $this->oInfo = $this->loadModel('Info');

        $sConfPath = MIBUpUtils::getConf('snmptt_conf');
        $sSCMB = MIBUpUtils::getConf('snmpttconvertmib_bin');

        $this->oSNMPTT->setConfFilePath($sConfPath);
        $this->oSNMPTT->setSNMPTTConvertMIBBin($sSCMB);
    }

    private function writeAndConvertMIB($iMIBId, $sMIBPath, $sSNMPTTExec)
    {
        $iCVersion = $this->oMIB->getCurrentVersion($iMIBId);
        $sMIBFilePath = $sMIBPath . DIRECTORY_SEPARATOR . $this->oMIB->getName($iMIBId);

        $aContent = $this->oMIB->getContent($iMIBId, $iCVersion);
        $sContent = $aContent['content'];

        $this->oMIBFile->writeMIBFile($sMIBFilePath, $sContent);
        return $this->oSNMPTT->convertMIB($sMIBFilePath, $sSNMPTTExec);
    }

    private function getSNMPTranslateTotals($sOutput)
    {
        $aMTotal = array();
        $aMSuccess = array();
        $aMFailed = array();

        $sOutput = str_replace(array("\n", "\r"), array('', ''), $sOutput);

        $iPMT = preg_match('#.*DoneTotal translations:[ \t]*([0-9]+)Successful translations:[ \t]*([0-9]+)Failed translations:[ \t]*([0-9]+).*#', $sOutput, $aMAll);

        if ($iPMT !== 1) {
            throw new MIBUpException('Cannot ensure correct totals');
        }

        $iMT = (int) $aMAll[1];
        $iMS = (int) $aMAll[2];
        $iMF = (int) $aMAll[3];

        return array($iMT, $iMS, $iMF);
    }

    public function generateConfiguration($aMIBIds = array())
    {

        $sSNMPTTState = $this->oInfo->getSNMPTTState();

        if ($sSNMPTTState != 'idle') {
            echo $this->loadView('mibup.snmptt.gen.result')
                ->set('sState', 'SNMPTT Configuration is already re-generating.')
                ->render();
            return;
        }

        if (empty($aMIBIds)) {
            $aMIBList = $this->oMIB->getAll();
            foreach ($aMIBList as $aMIB) {
                array_push($aMIBIds, (int) $aMIB['id']);
            }
        }

        $sMIBPath = MIBUpUtils::getConf('snmpttmibdir');
        $sSNMPTTExec = MIBUpUtils::getConf('snmptt_exec');
        $aMessages = array();
        $iConfGenMaxTime = (int) MIBUpUtils::getConf('snmptt_confgen_maxtime');
        $aConvertResList = array();

        if (!isset($_POST['snmptt_restart_only'])) {
            $this->oSNMPTT->resetConf();

            $iMaxExecutionTime = ini_get('max_execution_time');

            ignore_user_abort(true);
            set_time_limit($iConfGenMaxTime);

            foreach ($aMIBIds as $iMIBId) {
                $sMIBName = $this->oMIB->getName($iMIBId);
                try {
                    $this->oInfo->setSNMPTTState($sMIBName);
                    $aConvertRes = $this->writeAndconvertMIB($iMIBId, $sMIBPath, $sSNMPTTExec);

                    $aConvertRes = $this->getSNMPTranslateTotals($aConvertRes[1]);

                    array_push($aConvertResList, $aConvertRes);
                    array_push($aMessages, array(0, $sMIBName));
                } catch (MIBUpException $ex) {
                    array_push($aConvertResList, array(0, 0, 0));
                    array_push($aMessages, array(1, $sMIBName, $ex->getMessage()));
                }
            }

            set_time_limit($iMaxExecutionTime);
            ignore_user_abort(false);
        }

        return array($aMessages, $aConvertResList);
    }

    private function restartSNMPTT($sHost)
    {
        $sGenericCmd = MIBUpUtils::getConf('ssh_command');
        $sSNMPTTRestart = MIBUpUtils::getConf('snmptt_restart');
        $sCmd = preg_replace(
            array('#@ssh_host#', '#@cmd#'),
            array($sHost, $sSNMPTTRestart),
            $sGenericCmd
        );
        return MIBUpUtils::shellExec($sCmd);
    }

    private function isSNMPTTStarted($sHost)
    {
        $sGenericCmd = MIBUpUtils::getConf('ssh_command');
        $sSNMPTTRestart = MIBUpUtils::getConf('snmptt_started');
        $sCmd = preg_replace(
            array('#@ssh_host#', '#@cmd#'),
            array($sHost, $sSNMPTTRestart),
            $sGenericCmd
        );
        return MIBUpUtils::shellExec($sCmd);
    }

    public function mlist()
    {
        $aMIBList = $this->oMIB->getAll();
        echo $this->loadView('mibup.snmptt.gen.form')
            ->set('aMIBList', $aMIBList)
            ->render();
    }

    private function getPostMibGenList()
    {
        if (isset($_POST['snmptt_mib_gen_ids'])) {
            $aMIBIds = $_POST['snmptt_mib_gen_ids'];
        } else {
            $aMIBIds = array();
        }
        return $aMIBIds;
    }

    private function syncAndRestart()
    {
        $aHosts = MIBUpUtils::getConf('ssh_hosts');
        $sGenericCmd = MIBUpUtils::getConf('sync_command');
        $aFiles = array(
        'snmpttmibdir' => MIBUpUtils::getConf('snmpttmibdir'),
        'snmptt_conf' => MIBUpUtils::getConf('snmptt_conf')
        );

        $aSyncRes = array();
        $aExecRes = array();
        $aPRS = array();

        $this->oInfo->setSNMPTTState('sync');
        foreach ($aFiles as $sFileConfName => $sFileSrc) {
            foreach ($aHosts as $sHost => $aHostConf) {
                $sFileDest = $aHostConf[$sFileConfName];
                $sCmd = preg_replace(
                    array('#@filesrc#', '#@filedest#', '#@ssh_host#'),
                    array($sFileSrc, $sFileDest, $sHost),
                    $sGenericCmd
                );
                logfile($sCmd);
                $aSyncRes[$sHost] = MIBUpUtils::shellExec($sCmd);
            }
        }

        $this->oInfo->setSNMPTTState('restarting');
        foreach (array_keys($aHosts) as $sHost) {
            if ($aSyncRes[$sHost][0] == 0) {
                $aExecRes[$sHost] = $this->restartSNMPTT($sHost);
            }
        }

        // wait before probing SNMPTT state
        sleep(3);
        $this->oInfo->setSNMPTTState('statecheck');

        foreach (array_keys($aHosts) as $sHost) {
            $aPRS[$sHost] = $this->isSNMPTTStarted($sHost);
        }

        $this->oInfo->setSNMPTTIdle();

        return array($aExecRes, $aPRS, $aSyncRes);
    }

    public function run()
    {
        if (isset($_POST['snmptt_mib_gen_submit'])) {
            $aMIBIds = $this->getPostMibGenList();
            $aResG = $this->generateConfiguration($aMIBIds);
            $aResS = $this->syncAndRestart();

            echo $this->loadView('mibup.css.img')->render();
            echo $this->loadView('mibup.snmptt.gen.result')
                ->set('aExecRes', $aResS[0])
                ->set('aPRSs', $aResS[1])
                ->set('aSyncRess', $aResS[2])
                ->set('aMessages', $aResG[0])
                ->set('aConvertResList', $aResG[1])
                ->render();
        } else {
            $this->mlist();
        }
    }
}
