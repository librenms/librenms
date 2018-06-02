<?php namespace snmptrapmanager;

class MIBUpModelSNMPTT extends MIBUpModel
{

    private $sConfPath = null;
    private $sSCMB = null;

    /**
     * Check some internal variables to be initialized.
     * Throws an exception if anything isn't set.
     *
     * @throws MIBUpException
     * @return bool true
     */
    public function checkInitialized()
    {
        if ($this->sConfPath === null
            || $this->sSCMB === null
        ) {
            throw new MIBUpException('Initialize SNMPTT model before any use attempt.');
        }
        return true;
    }

    /**
     * Set LibreNMS's SNMPTT configuration file path before any use.
     *
     * @param string $sPath
     */
    public function setConfFilePath($sPath)
    {
        $this->sConfPath = $sPath;
    }

    /**
     * Set the snmpttconvertmib binary file path.
     *
     * @param string $sPath
     */
    public function setSNMPTTConvertMIBBin($sPath)
    {
        $this->sSCMB = $sPath;
    }

    /**
     * Flush LibreNMS's SNMPTT configuration file.
     */
    public function resetConf()
    {
        $this->checkInitialized();

        $fh = fopen($this->sConfPath, 'w');
        if ($fh === false) {
            throw new MIBUpException('Cannot write configuration file to ' . $this->sConfPath);
        }
        return fclose($fh);
    }

    /**
     * Call snmpttconvertmib to update configuration.
     *
     * @param string $sMIBFilePath where to output the new configuration
     * @param string $sExec        the SNMPTT EXEC line
     */
    public function convertMIB($sMIBFilePath, $sExec)
    {
        $this->checkInitialized();

        $sMIBFilePath = escapeshellarg($sMIBFilePath);
        $sConfPath = escapeshellarg($this->sConfPath);

        $sCmd = $this->sSCMB . ' --in=' . $sMIBFilePath .
        ' --out=' . $sConfPath .
        ' --exec=\'' . $sExec . '\'';

        return MIBUpUtils::shellExec($sCmd, '/tmp');
    }
}
