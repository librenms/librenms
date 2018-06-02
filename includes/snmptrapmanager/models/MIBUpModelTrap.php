<?php namespace snmptrapmanager;

class MIBUpModelTrap extends MIBUpModel
{

    private function getDeviceTrap($iDeviceID, $sOid)
    {
        $sQuery = 'select id from MIBUploaderTraps where `oid` like ? ' .
        'and `device_id` = ?';
        $aParams = array($sOid, (int) $iDeviceID);

        $aRes = dbFetch($sQuery, $aParams);

        if ($aRes === false) {
            throw new MIBUpQueryException('cannot fetch oid ' . $sOid . ' for device ' . $iDeviceID);
        }

        return $aRes;
    }

    /**
     * Get a list of trap(s), if any.
     *
     * All parameters are optional and will restrain results.
     *
     * @param  int  $iDeviceID   get traps for this device
     * @param  int  $sOid        get traps with this OID
     * @param  bool $bWithDevice get informations about trap's device from table devices
     * @return Array list of traps
     */
    public function getTrap($iDeviceID = null, $sOid = null, $bWithDevice = false)
    {
        $sQuery = 'select *, unix_timestamp(last_update) as last_update_ts from MIBUploaderTraps as mut';

        if ($bWithDevice) {
            $sQuery .= ', devices as dev';
        }

        $aParams = array();

        $aConds = array();

        if ($iDeviceID !== null) {
            array_push($aConds, 'mut.device_id = ?');
            array_push($aParams, (int) $iDeviceID);
        }

        if ($sOid !== null) {
            array_push($aConds, 'mut.oid like ?');
            array_push($aParams, $sOid);
        }

        if ($bWithDevice) {
            array_push($aConds, 'dev.device_id like mut.device_id');
        }

        if (!empty($aConds)) {
            $sQuery .= ' where ' . implode(' and ', $aConds);
        }

        $sQuery .= ' order by mut.last_update desc';

        $aRes = dbFetch($sQuery, $aParams);

        return $aRes;
    }

    public function setTrap($iDeviceID, $sOid, $sValues)
    {

        $aDTrap = $this->getDeviceTrap($iDeviceID, $sOid);

        if (count($aDTrap) === 1) {
            $sQuery = 'update MIBUploaderTraps set `values` = ?, `last_update` = from_unixtime(?) where id = ?';
            $aParams = array($sValues, time(), $aDTrap[0]['id']);
        } elseif (count($aDTrap) === 0) {
            $sQuery = 'insert into MIBUploaderTraps values (?, ?, ?, ?, from_unixtime(?))';
            $aParams = array(null, $iDeviceID, $sOid, $sValues, time());
        } else {
            throw new MIBUpException('Multiple trap registered for the same OID: ' . $sOid . ' for device ' . $iDeviceID);
        }

        return dbQuery($sQuery, $aParams);
    }

    public function autoClean($iTrapAutoClean)
    {
        if ($iTrapAutoClean === 0) {
            return true;
        }

        $sQuery = "delete from MIBUploaderTraps where ? - unix_timestamp(last_update) > ?";
        $aParams = array(time(), (int) $iTrapAutoClean);

        return dbQuery($sQuery, $aParams);
    }
}
