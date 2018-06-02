<?php namespace snmptrapmanager;

class MIBUpPlugin
{

    private static $ModeToCtrl = array(
    'version_manager' => 'VersionManager',
    'snmptt' => 'SNMPTT',
    'upload' => 'MIBUpload',
    'info' => 'Info',
    'index' => 'Index',
    'api' => 'API',
    'trap' => 'Trap',
    'default' => 'Index'
    );

    public function __construct()
    {
        MIBUpI18N::setup();

        $oDBSetup = new MIBUpDBSetup();
        $oDBSetup->setup();
    }

    public function processPlugin()
    {
        if (isset($_GET['mode'])) {
            $sMode = $_GET['mode'];

            if (isset(self::$ModeToCtrl[$sMode])) {
                $oCtrl = MIBUpCtrl::load(self::$ModeToCtrl[$sMode]);
            }
        } else {
            $oCtrl = MIBUpCtrl::load(self::$ModeToCtrl['default']);
        }

        $oCtrl->run();
    }

    public function processAPI()
    {
        MIBUpCtrl::load(self::$ModeToCtrl['api'])->run();
    }
}
