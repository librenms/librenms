<?php

class MIBUploader {

    public function menu() {
        require_once dirname(__FILE__) . '/../../includes/snmptrapmanager/system/MIBUpAutoload.php';

        MIBUpAutoload::register();

        try {
            $oCtrl = new MIBUpCtrl();
            echo $oCtrl->loadView('mibup.dropdownmenu')->set('sMenu', get_class())->render();
        } catch (MIBUpException $ex) {
        }
    }

}
