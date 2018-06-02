<?php namespace snmptrapmanager;

class MIBUpCtrlVersionManager extends MIBUpCtrl
{

    private $oMIB = null;

    public function __construct()
    {
        parent::__construct();
        $this->oMIB = $this->loadModel('MIB');
    }

    public function mlist()
    {
        $aMIBList = $this->oMIB->getAll();

        foreach ($aMIBList as $i => $aMIB) {
            $aVersions = array();
            $aHVersions = $this->oMIB->getVersions($aMIB['id']);
            foreach ($aHVersions as $aHVersion) {
                array_push(
                    $aVersions,
                    array(
                    'version' => $aHVersion['version'],
                    'date' => $aHVersion['date']
                    )
                );
            }
            $aMIBList[$i]['versions'] = $aVersions;
        }

        echo $this->loadView('mibup.versionmanager.list')
            ->set('aMIBList', $aMIBList)
            ->render();
    }

    public function update()
    {
        if (!isset($_POST['mibup_versionmanager_list_version'])) {
            return false;
        }

        if (isset($_POST['form_versionmanager_update_latestversion'])) {
            $aMIB_NVersions = $this->mibListToMIBNVersions();
        } else {
            $aMIB_NVersions = $_POST['mibup_versionmanager_list_version'];
        }

        $aMessages = array();

        foreach ($aMIB_NVersions as $sMIB_NVersion) {
            try {
                $aUpInfos = $this->updateMIBVersion($sMIB_NVersion);
                if ($aUpInfos !== null && $aUpInfos[1] !== true) {
                    array_push($aMessages, array(0, $aUpInfos[0], $aUpInfos[1]));
                } elseif ($aUpInfos !== null && $aUpInfos[1] === true) {
                    array_push($aMessages, array(0, $aUpInfos[0], -1));
                }
            } catch (MIBUpException $ex) {
                array_push($aMessages, array(1, $ex->getMessage()));
            }
        }

        echo $this->loadView('mibup.css.img')->render();
        echo $this->loadView('mibup.versionmanager.update')
            ->set('aMessages', $aMessages)
            ->render();
    }

    /**
     * For all registered MIB, return an array of strings
     * like: <mibid>.<miblatestversion>.0.<mibname>
     */
    private function mibListToMIBNVersions()
    {
        $aMIBList = $this->oMIB->getAll();
        $aMIBNVersions = array();

        foreach ($aMIBList as $aMIB) {
            $iMIBID = $aMIB['id'];
            $iMIBLatestVersion = $this->oMIB->getLatestVersion($iMIBID);
            $sMIBName = $aMIB['name'];

            $sMIBNVersion = $iMIBID . '.' . $iMIBLatestVersion . '.0.' . $sMIBName;

            array_push($aMIBNVersions, $sMIBNVersion);
        }

        return $aMIBNVersions;
    }

    /**
     * @param $sMIB_NVersion "<mibid>.<mibnewversion>.<selected>.<mibname>"
                 <selected> must be set to 0 to force update. Any other value
                 will skip the update.
     * @return Array [MIBName, MIB New Version], null if version isn't changed.
     */
    private function updateMIBVersion($sMIB_NVersion)
    {
        $aInfos = explode('.', $sMIB_NVersion);
        if (count($aInfos) < 3) {
            throw new MIBUpException('invalid selection ' . $sMIB_NVersion);
        }

        $iMIBID = (int) $aInfos[0];
        $iMIBNVersion = (int) $aInfos[1];
        $iSelected = (int) $aInfos[2];
        $sMIBName = $aInfos[3];

        if ($iSelected != 0) {
            return null;
        }

        if ($iMIBNVersion == -1) {
            $bDeleted = $this->oMIB->delete($iMIBID);
            return array($sMIBName, $bDeleted);
        }

        if (!is_int($iMIBID) || !is_int($iMIBNVersion)) {
            throw new MIBUpException('invalid selection ' . $sMIB_NVersion);
        }

        $res = $this->oMIB->setCurrentVersion($iMIBID, $iMIBNVersion);

        if ($res === false) {
            throw new MIBUpException('cannot update ' . $sMIB_NVersion);
        }

        return array($sMIBName, $iMIBNVersion);
    }

    public function run()
    {
        if (isset($_POST['form_versionmanager_update_version'])) {
            $this->update();
        } else {
            $this->mlist();
        }
    }
}
