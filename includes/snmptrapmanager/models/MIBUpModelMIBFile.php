<?php namespace snmptrapmanager;

class MIBUpModelMIBFile extends MIBUpModel
{

    /**
     * Write a MIB content into a given path.
     * This function doesn't check the $sContent is actually a MIB file.
     *
     * @param  string $sPath    path to the file to write
     * @param  string $sContent content to be written
     * @throws MIBUpException
     * @return bool write success
     */
    public function writeMIBFile($sPath, $sContent)
    {
        $fh = fopen($sPath, 'w');

        if ($fh === false) {
            throw new MIBUpException('Cannot write MIB file ' . $sPath);
        }

        return fwrite($fh, $sContent);
    }
}
