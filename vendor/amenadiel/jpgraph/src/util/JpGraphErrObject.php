<?php
namespace Amenadiel\JpGraph\Util;

//
// First of all set up a default error handler
//

//=============================================================
// The default trivial text error handler.
//=============================================================
class JpGraphErrObject
{
    protected $iTitle = "JpGraph error: ";
    protected $iDest  = false;

    public function __construct()
    {
        // Empty. Reserved for future use
    }

    public function SetTitle($aTitle)
    {
        $this->iTitle = $aTitle;
    }

    public function SetStrokeDest($aDest)
    {
        $this->iDest = $aDest;
    }

    // If aHalt is true then execution can't continue. Typical used for fatal errors
    public function Raise($aMsg, $aHalt = false)
    {
        if ($this->iDest != '') {
            if ($this->iDest == 'syslog') {
                error_log($this->iTitle . $aMsg);
            } else {
                $str = '[' . date('r') . '] ' . $this->iTitle . $aMsg . "\n";
                $f   = @fopen($this->iDest, 'a');
                if ($f) {
                    @fwrite($f, $str);
                    @fclose($f);
                }
            }
        } else {
            $aMsg = $this->iTitle . $aMsg;
            // Check SAPI and if we are called from the command line
            // send the error to STDERR instead
            if (PHP_SAPI == 'cli') {
                fwrite(STDERR, $aMsg);
            } else {
                echo $aMsg;
            }
        }
        if ($aHalt) {
            exit(1);
        }
    }
}
