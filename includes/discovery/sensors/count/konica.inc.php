<?php
/*
 * LibreNMS support for kyocera print counters
 *
 * This program is free software: you can redistribute it and/or modify it
 * under the terms of the GNU General Public License as published by the
 * Free Software Foundation, either version 3 of the License, or (at your
 * option) any later version.  Please see LICENSE.txt at the top level of
 * the source code distribution for details.
 *
 * @package    LibreNMS
 * @link       http://librenms.org
 * @copyright  2019 LibreNMS
 * @author     Teura ORBECK
 */

 /* KONCA MINOLTA BizHub Printers
 1.3.6.1.4.1.18334.1.1.1.5.7.2.1.8.0	# of originals	Scanner
1.3.6.1.4.1.18334.1.1.1.5.7.2.1.10.0	Total Printed	Printer
1.3.6.1.4.1.18334.1.1.1.5.7.2.1.9.0	Total sheets	Printer
1.3.6.1.4.1.18334.1.1.1.5.7.2.2.1.7.4.1	Copy 2 colors Large	Copy Large
1.3.6.1.4.1.18334.1.1.1.5.7.2.2.1.5.4.1	Copy 2 colors Total	Copy
1.3.6.1.4.1.18334.1.1.1.5.7.2.2.1.7.2.1	Copy Color Large	Copy Large
1.3.6.1.4.1.18334.1.1.1.5.7.2.2.1.5.2.1	Copy Color Total	Copy
1.3.6.1.4.1.18334.1.1.1.5.7.2.2.1.7.3.1	Copy Mono Large	Copy Large
1.3.6.1.4.1.18334.1.1.1.5.7.2.1.6.0	Copy Mono Total	Copy
1.3.6.1.4.1.18334.1.1.1.5.7.2.2.1.5.3.1	Copy Mono Total	Copy
1.3.6.1.4.1.18334.1.1.1.5.7.2.2.1.7.1.1	Copy Black Large	Copy Large
1.3.6.1.4.1.18334.1.1.1.5.7.2.2.1.5.1.1	Copy Black Total	Copy
1.3.6.1.4.1.18334.1.1.1.5.7.2.2.1.7.4.2	Print 2 colors Large	Print Large
1.3.6.1.4.1.18334.1.1.1.5.7.2.1.7.0	Print 2 colors Large	Print Large
1.3.6.1.4.1.18334.1.1.1.5.7.2.2.1.5.4.2	Print 2 colors Total	Print
1.3.6.1.4.1.18334.1.1.1.5.7.2.2.1.7.2.2	Print Color Large	Print Large
1.3.6.1.4.1.18334.1.1.1.5.7.2.2.1.5.2.2	Print Color Total	Print
1.3.6.1.4.1.18334.1.1.1.5.7.2.2.1.7.3.2	Print Mono Large	Print Large
1.3.6.1.4.1.18334.1.1.1.5.7.2.2.1.5.3.2	Print Mono Total	Print
1.3.6.1.4.1.18334.1.1.1.5.7.2.2.1.7.1.2	Print Black Large	Print Large
1.3.6.1.4.1.18334.1.1.1.5.7.2.2.1.5.1.2	Print Black Total	Print
1.3.6.1.4.1.18334.1.1.1.5.7.2.1.2.0	Scan Large	Scan Large
1.3.6.1.4.1.18334.1.1.1.5.7.2.3.1.6.1	Scan Large	Scan Large
1.3.6.1.4.1.18334.1.1.1.5.7.2.1.5.0	Scan	Scan
1.3.6.1.4.1.18334.1.1.1.5.7.2.3.1.5.1	Scan	Scan
1.3.6.1.4.1.18334.1.1.1.5.7.2.1.1.0	Total Printed
1.3.6.1.4.1.18334.1.1.1.5.7.2.1.3.0	Total Duplex Printed	Total
1.3.6.1.4.1.18334.1.1.1.5.7.2.3.1.24.1	Scan Color Transmission	Scan Transmission
1.3.6.1.4.1.18334.1.1.1.5.7.2.3.1.23.1	Scan Black Transmission	Scan Transmission
*/
$session_rate = [
'# of originals'=>['konica','.1.3.6.1.4.1.18334.1.1.1.5.7.2.1.8.0','scnOriginals','Total'],
'Total Printed'=>['konica','.1.3.6.1.4.1.18334.1.1.1.5.7.2.1.10.0','printTotal','Printer'],
'Total sheets'=>['konica','.1.3.6.1.4.1.18334.1.1.1.5.7.2.1.9.0','printTotalShhets','Total'],
'Copy 2 colors Large'=>['konica','.1.3.6.1.4.1.18334.1.1.1.5.7.2.2.1.7.4.1','copy2ColLrg','Copy Large'],
'Copy 2 colors Total'=>['konica','.1.3.6.1.4.1.18334.1.1.1.5.7.2.2.1.5.4.1','copy2Col','Copy'],
'Copy Color Large'=>['konica','.1.3.6.1.4.1.18334.1.1.1.5.7.2.2.1.7.2.1','copyColorLrg','Copy Large'],
'Copy Color Total'=>['konica','.1.3.6.1.4.1.18334.1.1.1.5.7.2.2.1.5.2.1','copyColor','Copy'],
'Copy Mono Large'=>['konica','.1.3.6.1.4.1.18334.1.1.1.5.7.2.2.1.7.3.1','copyMonoLrg','Copy Large'],
'Copy Mono Total'=>['konica','.1.3.6.1.4.1.18334.1.1.1.5.7.2.1.6.0','copyMono','Copy'],
//'Copy Mono Total'=>['konica','.1.3.6.1.4.1.18334.1.1.1.5.7.2.2.1.5.3.1','copyMonoBHC','Copy'],
'Copy Black Large'=>['konica','.1.3.6.1.4.1.18334.1.1.1.5.7.2.2.1.7.1.1','copyTotalLrg','Copy Large'],
'Copy Black Total'=>['konica','.1.3.6.1.4.1.18334.1.1.1.5.7.2.2.1.5.1.1','copyTotal','Copy'],
//'Print 2 colors Large'=>['konica','.1.3.6.1.4.1.18334.1.1.1.5.7.2.2.1.7.4.2','print2colBHCLrg','Print Large'],
'Print 2 colors Large'=>['konica','.1.3.6.1.4.1.18334.1.1.1.5.7.2.1.7.0','print2colLrg','Print Large'],
'Print 2 colors Total'=>['konica','.1.3.6.1.4.1.18334.1.1.1.5.7.2.2.1.5.4.2','print2col','Print'],
'Print Color Large'=>['konica','.1.3.6.1.4.1.18334.1.1.1.5.7.2.2.1.7.2.2','printColorLrg','Print Large'],
'Print Color Total'=>['konica','.1.3.6.1.4.1.18334.1.1.1.5.7.2.2.1.5.2.2','printColor','Print'],
'Print Mono Large'=>['konica','.1.3.6.1.4.1.18334.1.1.1.5.7.2.2.1.7.3.2','printMonoLrg','Print Large'],
'Print Mono Total'=>['konica','.1.3.6.1.4.1.18334.1.1.1.5.7.2.2.1.5.3.2','printMono','Print'],
'Print Black Large'=>['konica','.1.3.6.1.4.1.18334.1.1.1.5.7.2.2.1.7.1.2','printBlackLrg','Print Large'],
'Print Black Total'=>['konica','.1.3.6.1.4.1.18334.1.1.1.5.7.2.2.1.5.1.2','printBlack','Print'],
'Scan Large'=>['konica','.1.3.6.1.4.1.18334.1.1.1.5.7.2.1.2.0','scanLrg','Scan Large'],
//'Scan Large'=>['konica','.1.3.6.1.4.1.18334.1.1.1.5.7.2.3.1.6.1','scanBHCLrg','Scan Large'],
'Scan'=>['konica','.1.3.6.1.4.1.18334.1.1.1.5.7.2.1.5.0','scanTotal','Scan'],
//'Scan'=>['konica','.1.3.6.1.4.1.18334.1.1.1.5.7.2.3.1.5.1','scanBHCTotal','Scan'],
'Total'=>['konica','.1.3.6.1.4.1.18334.1.1.1.5.7.2.1.1.0','totalCounter','Total'],
'Total Duplex Printed'=>['konica','.1.3.6.1.4.1.18334.1.1.1.5.7.2.1.3.0','toalDuplexCounter','Total'],
'Scan Color Transmission'=>['konica','.1.3.6.1.4.1.18334.1.1.1.5.7.2.3.1.24.1','scanColoTrans','Scan Transmission'],
'Scan Black Transmission'=>['konica','.1.3.6.1.4.1.18334.1.1.1.5.7.2.3.1.23.1','scanBlackTrans','Scan Transmission'],
];

foreach ($session_rate as $descr => $oid) {
    $vendorRef = $oid[0];
    $oid_num = $oid[1];
    $oid_ref = $oid[2];
    $group = $oid[3];
    $result = snmp_get($device, $oid_num, '-Ovq');
if($result>0)
{
    discover_sensor(
        $valid['sensor'],
        'count',
        $device,
        $oid_num,
        $oid_ref . '.0',
        $vendorRef,
        $descr,
        1,
        1,
        null,
        null,
        null,
        null,
        $result
    );
}
}

