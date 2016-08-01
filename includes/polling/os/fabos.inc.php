<?php

$version  = trim(snmp_get($device, '1.3.6.1.4.1.1588.2.1.1.1.1.6.0', '-Ovq'), '"');
$gethardware = trim(snmp_get($device, 'SNMPv2-SMI::mib-2.75.1.1.4.1.3.1', '-Ovq'), '"');
$revboard = str_replace("SNMPv2-SMI::enterprises.1588.2.1.1.", "", $gethardware);
if(strpos($revboard, ".") !== false) {
	$getid = strstr(str_replace($revboard, "", $gethardware), ".", true);
} else {
	$getid = $revboard;
}

switch($getid)
{
	case "1":
		$hardware = "Brocade 1000 Switch";
		break;
	case "2":
		$hardware = "Brocade 2800 Switch";
		break;
	case "3":
		$hardware = "Brocade 2100/2400 Switch";
		break;
	case "4":
		$hardware = "Brocade 20x0 Switch";
		break;
	case "5":
		$hardware = "Brocade 22x0 Switch";
		break;
	case "6":
		$hardware = "Brocade 2800 Switch";
		break;
	case "7":
		$hardware = "Brocade 2000 Switch";
		break;
	case "9":
		$hardware = "Brocade 3800 Switch";
		break;
	case "10":
		$hardware = "Brocade 12000 Director";
		break;
	case "12":
		$hardware = "Brocade 3900 Switch";
		break;
	case "16":
		$hardware = "Brocade 3200 Switch";
		break;
	case "18":
		$hardware = "Brocade 3000 Switch";
		break;
	case "21":
		$hardware = "Brocade 24000 Director";
		break;
	case "22":
		$hardware = "Brocade 3016 Switch";
		break;
	case "26":
		$hardware = "Brocade 3850 Switch";
		break;
	case "27":
		$hardware = "Brocade 3250 Switch";
		break;
	case "29":
		$hardware = "Brocade 4012 Embedded Switch";
		break;
	case "32":
		$hardware = "Brocade 4100 Switch";
		break;
	case "33":
		$hardware = "Brocade 3014 Switch";
		break;
	case "34":
		$hardware = "Brocade 200E Switch";
		break;
	case "37":
		$hardware = "Brocade 4020 Embedded Switch";
		break;
	case "38":
		$hardware = "Brocade 7420 SAN Router";
		break;
	case "40":
		$hardware = "Fibre Channel Routing (FCR) Front Domain";
		break;
	case "41":
		$hardware = "Fibre Channel Routing (FCR) Xlate Domain";
		break;
	case "42":
		$hardware = "Brocade 48000 Director";
		break;
	case "43":
		$hardware = "Brocade 4024 Embedded Switch";
		break;
	case "44":
		$hardware = "Brocade 4900 Switch";
		break;
	case "45":
		$hardware = "Brocade 4016 Embedded Switch";
		break;
	case "46":
		$hardware = "Brocade 7500 Switch";
		break;
	case "51":
		$hardware = "Brocade 4018 Embedded Switch";
		break;
	case "55.2":
		$hardware = "Brocade 7600 Switch";
		break;
	case "58":
		$hardware = "Brocade 5000 Switch";
		break;
	case "61":
		$hardware = "Brocade 4424 Embedded Switch";
		break;
	case "62":
		$hardware = "Brocade DCX Backbone";
		break;
	case "64":
		$hardware = "Brocade 5300 Switch";
		break;
	case "66":
		$hardware = "Brocade 5100 Switch";
		break;
	case "67":
		$hardware = "Brocade Encryption Switch";
		break;
	case "69":
		$hardware = "Brocade 5410 Blade";
		break;
	case "70":
		$hardware = "Brocade 5410 Embedded Switch";
		break;
	case "71":
		$hardware = "Brocade 300 Switch";
		break;
	case "72":
		$hardware = "Brocade 5480 Embedded Switch";
		break;
	case "73":
		$hardware = "Brocade 5470 Embedded Switch";
		break;
	case "75":
		$hardware = "Brocade M5424 Embedded Switch";
		break;
	case "76":
		$hardware = "Brocade 8000 Switch";
		break;
	case "77":
		$hardware = "Brocade DCX-4S Backbone";
		break;
	case "83":
		$hardware = "Brocade 7800 Extension Switch";
		break;
	case "86":
		$hardware = "Brocade 5450 Embedded Switch";
		break;
	case "87":
		$hardware = "Brocade 5460 Embedded Switch";
		break;
	case "90":
		$hardware = "Brocade 8470 Embedded Switch";
		break;
	case "92":
		$hardware = "Brocade VA-40FC Switch";
		break;
	case "95":
		$hardware = "Brocade VDX 6720-24 Data Center Switch";
		break;
	case "96":
		$hardware = "Brocade VDX 6730-32 Data Center Switch";
		break;
	case "97":
		$hardware = "Brocade VDX 6720-60 Data Center Switch";
		break;
	case "98":
	        $hardware = "Brocade VDX 6720-76 Data Center Switch";
                break;
	case "108":
		$hardware = "Dell M84280k FCoE Embedded Switch";
		break;
	case "109":
		$hardware = "Brocade 6510 Switch";
		break;
	case "116":
		$hardware = "Brocade VDX 6710 Data Center Switch";
		break;
	case "117":
		$hardware = "Brocade 6547 Embedded Switch";
		break;
	case "118":
		$hardware = "Brocade 6505 Switch";
		break;
	case "120":
		$hardware = "Brocade DCX 8510-8 Backbone";
		break;
	case "121":
		$hardware = "Brocade DCX 8510-4 Backbone";
		break;
	default:
		$hardware = "Unknown Brocade FC Switch";
}
