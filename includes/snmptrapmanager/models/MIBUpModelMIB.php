<?php namespace snmptrapmanager;

class MIBUpModelMIB extends MIBUpModel
{
    /**
     * Get the latest available version for MIB.
  *
     * @param  int $iMIBId
     * @return mixed false if no version registered, int for the latest version.
     */
    public function getLatestVersion($iMIBId)
    {
        $sQMibVersion = "select max(version) as version from MIBUploaderMIBContent where id_mib = ?";
        $aParams = array((int) $iMIBId);
        $aMIBVersion = dbFetch($sQMibVersion, $aParams);

        if ($aMIBVersion === false) {
            throw new MIBUpQueryException();
        }

        if ($aMIBVersion[0]['version'] === null) {
            $res = false;
        } else {
            $res = (int) $aMIBVersion[0]['version'];
        }

        return $res;
    }

    /**
     * Get MIB's current version.
  *
     * @param  int $iMIBId
     * @throws MIBUpException
     * @return int mib's current version
     */
    public function getCurrentVersion($iMIBId)
    {
        $sQuery = "select current_version from MIBUploaderMIBS where id = ?";
        $aParams = array((int) $iMIBId);

        $res = dbFetch($sQuery, $aParams);

        if ($res === false) {
            throw new MIBUpQueryException();
        }

        if (count($res) !== 1) {
            throw new MIBUpException('MIB with id ' . $iMIBId . ' not found.');
        }

        return (int) $res[0]['current_version'];
    }

    /**
     * Check if a MIB version exists.
     *
     * @param  int $iMIBId
     * @param  int $iVersion
     * @return 0 if no version exists, count(*) if one or more.
     */
    public function versionExists($iMIBId, $iVersion)
    {
        $sQuery = "select count(*) as count from MIBUploaderMIBContent where version = ? and id_mib = ?";
        $aParams = array(
        (int) $iVersion,
        (int) $iMIBId
        );

        $oRes = dbFetch($sQuery, $aParams);

        $iC = (int) $oRes[0]['count'];
        return $iC;
    }

    /**
     * Get all available versions for one MIB.
     *
     * @param  int $iMIBId MIB ID
     * @return Array : Array(Array('version' => ..., 'date' => ...), Array...)
     */
    public function getVersions($iMIBId)
    {
        $sQuery = "select version, date from MIBUploaderMIBContent where id_mib = ?";
        $aParams = array((int) $iMIBId);
        $aMIBVersions = dbFetch($sQuery, $aParams);
        return $aMIBVersions;
    }

    /**
     * Get MIB's ID from it's name.
     * Names are unique so a unique ID is returned.
     *
     * Returns false in no MIB was found with this name.
     *
     * @param  string MIB Name
     * @return int
     */
    public function getID($sMIBName)
    {
        $sQMibId = "select id from MIBUploaderMIBS where name like ?";
        $aParams = array($sMIBName);
        $aMibId = dbFetch($sQMibId, $aParams);

        if (count($aMibId) == 1) {
            $res = (int) $aMibId[0]['id'];
        } else {
            $res = false;
        }

        return $res;
    }

    /**
     * @param string $sName    MIB Name
     * @param int    $iVersion optional version. if something else than an integer is passed, all MIB versions are returned.
     *                      is passed, all MIB versions are returned.
     * @param bool   $bSHA2    also return SHA2(content, 256), as 'sha256', instead of raw content.
     * @return mixed Array of Array if no version was specified, or Array of values. False if MIB was not found or no content registered.
     */
    public function getContent($iMIBId, $iVersion = null, $bSHA2 = false)
    {
        $aParams = array((int) $iMIBId);

        $sQuery = 'select id_mib, version, content';
        if ($bSHA2) {
            $sQuery = 'select id_mib, version, content, SHA2(content, 256) as sha256';
        }

        $sQuery .= ' from MIBUploaderMIBContent where id_mib = ?';

        if (is_int($iVersion)) {
            $sQuery .= ' and version = ?';
            array_push($aParams, (int) $iVersion);
        }

        $aMIBContent = dbFetch($sQuery, $aParams);

        if (count($aMIBContent) === 1 && $iVersion !== null) {
            return $aMIBContent[0];
        } elseif (count($aMIBContent) < 1) {
            return false;
        }

        return $aMIBContent;
    }

    /**
     * @param string                                          $sName    MIB Name
     * @param string                                          $sContent MIB Content
     * @param $iVersion optional version, same as getContent(). Can be used to check content against                    specific version.
     * @return Array the list of mibs found, if any. false if no mib, such as defined by $sName, can be found.
     */
    public function contentExists($sName, $sContent, $iVersion = null)
    {
        $iMIBId = $this->getID($sName);

        if ($iMIBId === false) {
            return false;
        }

        $sQuery = 'select * from MIBUploaderMIBContent where id_mib = ? and SHA2(content, 256) like ?';

        $aParams = array(
        (int) $iMIBId,
        hash('sha256', $sContent) // We avoid MySQL call to SHA2 to avoid replacing any new line, carriage return and other special chars...
        );

        if ($iVersion !== null) {
            $sQuery .= ' and version = ?';
            array_push($aParams, (int) $iVersion);
        }

        $res = dbFetch($sQuery, $aParams);

        return $res;
    }

    /**
     * Insert a new MIB, or a new version if you re-upload
     * the same MIB.
     */
    public function insert($sName, $sContent, $bUpdateVersion = true)
    {
        dbBeginTransaction();

        try {
            $iId = $this->getID($sName);

            if ($iId === false) {
                $this->createMIBEntry($sName);
                $iId = $this->getID($sName);
            }

            if ($iId === false) {
                throw new MIBUpException('Cannot select inserted MIB');
            }

            $iVersion = $this->getLatestVersion($iId);
            if ($iVersion === false) {
                $iVersion = 0;
            } else {
                $iVersion += 1;
            }

            $res = $this->insertContent($iId, $sContent, $iVersion);

            if ($bUpdateVersion) {
                $this->setCurrentVersion($iId, $iVersion);
            }
        } catch (MIBUpException $ex) {
            dbRollbackTransaction();
            throw $ex;
        }

        dbCommitTransaction();
    }

    public function createMIBEntry($sName)
    {
        $sQuery = "insert into MIBUploaderMIBS (name, current_version) values (?, 0);";
        $aParams = array($sName);
        $res = dbQuery($sQuery, $aParams);

        if ($res === false) {
            throw new MIBUpException('Cannot create MIB entry');
        }

        return $res;
    }

    /**
     * Change a MIB's current_version to $iVersion
  *
     * @param  int $iMIBId
     * @param  int $iVersion
     * @return bool true if success, false if error
     */
    public function setCurrentVersion($iMIBId, $iVersion)
    {
        $sQUpdateLatestVersion = "update MIBUploaderMIBS set current_version = ? where id = ?";

        $aParams = array(
        (int) $iVersion,
        (int) $iMIBId
        );

        $res = dbQuery($sQUpdateLatestVersion, $aParams);

        if ($res === false) {
            throw new MIBUpException('Cannot set MIB\'s current version');
        }

        return $res;
    }

    /**
     * Insert a MIB content for the given version.
     *
     * If this version already exists, it will be updated.
  *
     * @param  int    $iMIBId
     * @param  string $sContent
     * @param  int    $iVersion
     * @return bool true if success, false if error
     */
    public function insertContent($iMIBId, $sContent, $iVersion)
    {
        if ($this->versionExists($iMIBId, $iVersion) > 0) {
            $sType = 'update';
            $sQuery = "update MIBUploaderMIBContent set content = ?, date = NOW() where id_mib = ?";
            $aParams = array(
            $sContent,
            (int) $iMIBId
            );
        } else {
            $sType = 'insert';
            $sQuery = "insert into MIBUploaderMIBContent (id_mib, version, content) values (?, ?, ?)";
            $aParams = array(
            (int) $iMIBId,
            (int) $iVersion,
            $sContent
            );
        }

        $res = dbQuery($sQuery, $aParams);

        if ($res === false) {
            throw new MIBUpQueryException('Cannot ' . $sType . ' MIB version');
        }

        return $res;
    }

    /**
     * Get the list of all registered mibs.
     * Only metadata, not content/versions.
  *
     * @return Array
     */
    public function getAll()
    {
        $oRes = dbFetch('select * from MIBUploaderMIBS order by name');

        return $oRes;
    }

    /**
     * Get MIB's name from id
     *
     * @param  int $iMIBId
     * @return string mib's name, false if no mib found with this name
     */
    public function getName($iMIBId)
    {
        $sQuery = 'select name from MIBUploaderMIBS where id = ?';
        $aParams = array((int) $iMIBId);

        $oRes = dbFetch($sQuery, $aParams);

        if (is_array($oRes) && count($oRes) == 1 && isset($oRes[0]['name'])) {
            return $oRes[0]['name'];
        }

        return false;
    }

    /**
     * Delete the given MIB (via id) and every versions.
  *
     * @param  int $iMIBId
     * @return bool true if success, false if error
     */
    public function delete($iMIBId)
    {
        $sQuery = 'delete from MIBUploaderMIBS where id = ?';
        $aParams = array((int) $iMIBId);

        $oRes = dbQuery($sQuery, $aParams);

        return $oRes;
    }
}
