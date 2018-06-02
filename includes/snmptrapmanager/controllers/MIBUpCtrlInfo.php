<?php namespace snmptrapmanager;

class MIBUpCtrlInfo extends MIBUpCtrl
{

    private $oInfo = null;

    public function __construct()
    {
        parent::__construct();
        $this->oInfo = $this->loadModel('Info');
    }

    public function run()
    {
        echo $this->loadView('mibup.info.state')
            ->set('sSNMPTTState', $this->oInfo->getSNMPTTState())
            ->set('aMIBStats', $this->oInfo->getMIBStats())
            ->set('iDBSchemVersion', $this->oInfo->getDBSchemaVersion())
            ->render();
    }
}
