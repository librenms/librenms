<?php namespace snmptrapmanager;

class MIBUploader
{

    public function menu()
    {
        require_once dirname(__FILE__) . '/../../includes/snmptrapmanager/system/MIBUpAutoload.php';
        /*
        $dir = dirname(__FILE__) . '/../../includes/snmptrapmanager/';
        $dh  = opendir($dir);
        $dir_list = array($dir);
        while (false !== ($filename = readdir($dh))) {
            if($filename!="."&&$filename!=".."&&is_dir($dir.$filename))
                 array_push($dir_list, $dir.$filename."/");
        }
        foreach ($dir_list as $dir) {
            foreach (glob($dir."*.php") as $filename)
                require_once $filename;
        }
        */
        MIBUpAutoload::register();

        try {
            $oCtrl = new \snmptrapmanager\MIBUpCtrl();
            echo $oCtrl->loadView('mibup.dropdownmenu')->set('sMenu', get_class())->render();
        } catch (MIBUpException $ex) {
        }
    }
}
