<?php
/*
 * LibreNMS pre-cache module for Dlink-dgs1210 OS
 *
 * This program is free software: you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation, either version 3 of the License, or
 * (at your option) any later version.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with this program.  If not, see <https://www.gnu.org/licenses/>.
 *
 * @package    LibreNMS
 * @link       https://www.librenms.org
 *
 * @copyright  2022 Peca Nesovanovic
 * @author     Peca Nesovanovic <peca.nesovanovic@sattrakt.com>
 */
echo 'ddmStatusTable ';
$oids20 = SnmpQuery::hideMib()->walk('DGS-1210-20ME-AX::ddmStatusTable')->table(1);
$oids28 = SnmpQuery::hideMib()->walk('DGS-1210-28ME-AX::ddmStatusTable')->table(1, $oids20);
echo 'ddmThresholdMgmtEntry ';
$oidm20 = SnmpQuery::hideMib()->walk('DGS-1210-20ME-AX::ddmThresholdMgmtEntry')->table(2, $oids28);
$pre_cache['dgs1210-ddm'] = SnmpQuery::hideMib()->walk('DGS-1210-28ME-AX::ddmThresholdMgmtEntry')->table(2, $oidm20);
