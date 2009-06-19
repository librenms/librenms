<?

## Common Functions

function mres($string) { // short function wrapper because the real one is stupidly long and ugly. aestetics.
  return mysql_real_escape_string($string);
}

function getifhost($id) {
     $sql = mysql_query("SELECT `device_id` from `interfaces` WHERE `interface_id` = '$id'");
     $result = @mysql_result($sql, 0);
     return $result;
}

function gethostbyid($id) {
     $sql = mysql_query("SELECT `hostname` FROM `devices` WHERE `device_id` = '$id'");
     $result = @mysql_result($sql, 0);
     return $result;
}

function strgen ($length = 16)
{
    $entropy = array(0,1,2,3,4,5,6,7,8,9,'a','A','b','B','c','C','d','D','e',
    'E','f','F','g','G','h','H','i','I','j','J','k','K','l','L','m','M','n',
    'N','o','O','p','P','q','Q','r','R','s','S','t','T','u','U','v','V','w',
    'W','x','X','y','Y','z','Z');
    $string = "";
    for ($i=0; $i<$length; $i++) {
        $key = mt_rand(0,61);
        $string .= $entropy[$key];
    }
    return $string;
}

function getpeerhost($id) {
     $sql = mysql_query("SELECT `device_id` from `bgpPeers` WHERE `bgpPeer_id` = '$id'");
     $result = @mysql_result($sql, 0);
     return $result;
}

function getifindexbyid($id) {
     $sql = mysql_query("SELECT `ifIndex` FROM `interfaces` WHERE `interface_id` = '$id'");
     $result = @mysql_result($sql, 0);
     return $result;
}

function getifbyid($id) {
     $sql = mysql_query("SELECT `ifDescr` FROM `interfaces` WHERE `interface_id` = '$id'");
     $result = @mysql_result($sql, 0);
     return $result;
}

function getidbyname($domain){
     $sql = mysql_query("SELECT `device_id` FROM `devices` WHERE `hostname` = '$domain'");
     $result = @mysql_result($sql, 0);
     return $result;
}

function gethostosbyid($id) {
     $sql = mysql_query("SELECT `os` FROM `devices` WHERE `device_id` = '$id'");
     $result = @mysql_result($sql, 0);
     return $result;
}



?>
