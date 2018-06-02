<?php namespace snmptrapmanager;

class MIBUpCtrlTrap extends MIBUpCtrl
{

    private $oTrap = null;

    public function __construct()
    {
        $this->oTrap = $this->loadModel('Trap');
    }

    public function autoClean()
    {
        $iTrapAutoClean = MIBUpUtils::getConf('trap_auto_clean', 0);
        $this->oTrap->autoClean($iTrapAutoClean);
    }

    public function trap($iDeviceID, $sOid, $sValues)
    {
        $this->oTrap->setTrap($iDeviceID, $sOid, $sValues);
        log_event('oid: ' . $sOid, $iDeviceID, 'trap');
        $this->autoClean();
    }

    public function trapList()
    {
        $this->autoClean();
        $aTraps = $this->oTrap->getTrap(null, null, true);

        echo $this->loadView('mibup.trap.list')
            ->set('aTraps', $aTraps)
            ->render();
    }

    public function run()
    {
        $this->trapList();
    }
}
