<?php

print_optionbar_start();

echo '<form action="'.generate_url( $link_array, array('nfsen' => 'stats')).'" id="FlowStats" method="POST">';

echo 'Top N:
<select name="topN" id="topN" size=1>
    <OPTION value="0" >10</OPTION>
    <OPTION value="1" selected>20</OPTION>
    <OPTION value="2" >50</OPTION>
    <OPTION value="3" >100</OPTION>
    <OPTION value="4" >200</OPTION>
    <OPTION value="5" >500</OPTION>
</select>
During the last:
<select name="lastN" id="lastN" size=1>
    <OPTION value="300" >5 minutes</OPTION>
    <OPTION value="500" >10 minutes</OPTION>
    <OPTION value="900" selected>15 minutes</OPTION>
    <OPTION value="1800" >30 minutes</OPTION>
    <OPTION value="3200" >hour</OPTION>
    <OPTION value="9600" >3 hours</OPTION>
    <OPTION value="19200" >6 hours</OPTION>
    <OPTION value="38400" >12 hours</OPTION>
    <OPTION value="76800" >24 hours</OPTION>
    <OPTION value="115200" >36 hours</OPTION>
    <OPTION value="153600" >48 hours</OPTION>
</select>
, Stat Type:
<select name="stattype" id="StatTypeSelector" size=1>
    <OPTION value="0" >Flow Records</OPTION>
    <OPTION value="1" >Any IP Address</OPTION>
    <OPTION value="2" selected>SRC IP Address</OPTION>
    <OPTION value="3" >DST IP Address</OPTION>
    <OPTION value="4" >Any Port</OPTION>
    <OPTION value="5" >SRC Port</OPTION>
    <OPTION value="6" >DST Port</OPTION>
    <OPTION value="7" >SRC TOS</OPTION>
    <OPTION value="8" >DST TOS</OPTION>
    <OPTION value="9" >TOS</OPTION>
</select>
, Order By:
<select name="statorder" id="statorder" size=1>
    <OPTION value="0" >flows</OPTION>
    <OPTION value="1" >packets</OPTION>
    <OPTION value="2" >bytes</OPTION>
    <OPTION value="3" selected>pps</OPTION>
    <OPTION value="4" >bps</OPTION>
    <OPTION value="5" >bpp</OPTION>
</select>
<input type="submit" name="process" value="process" size="1">
';
echo '</form>';

print_optionbar_end();

// process stuff now if we the button was clicked on
if (isset($vars['process'])){

    // Make sure we have a sane value for lastN
    $lastN=900;
    if (isset($vars['lastN']) &&
         is_int($vars['lastN']) &&
         ($vars['lastN'] <= $config['nfsen_last_max'])
        ){
        $lastN=$vars['lastN'];
    }

    // Make sure we have a sane value for lastN
    $topN=20;
    if (isset($vars['topN']) &&
         is_int($vars['topN']) &&
         ($vars['topN'] <= $config['nfsen_top_max'])
        ){
        $topN=$vars['topN'];
    }

    // Handle the stat order.
    $stat_order='pps'; // The default if not set or something invalid is set
    if (isset($vars['statorder'])){
        if (strcmp($vars['statorder'], '0') ==0 ){
            $stat_order='flows';
        }elseif(strcmp($vars['statorder'], '1') ==0 ){
            $stat_order='packets';
        }elseif(strcmp($vars['statorder'], '2') ==0 ){
            $stat_order='bytes';
        }elseif(strcmp($vars['statorder'], '3') ==0 ){
            $stat_order='pps';
        }elseif(strcmp($vars['statorder'], '4') ==0 ){
            $stat_order='bps';
        }elseif(strcmp($vars['statorder'], '5') ==0 ){
            $stat_order='bpp';
        }
    }

    // Handle the stat type.
    $stat_type='srcip'; // The default if not set or something invalid is set
    if (isset($vars['stattype'])){
        if (strcmp($vars['stattype'], '0') == 0){
            $stat_type='record';
        }elseif(strcmp($vars['stattype'], '1') == 0){
            $stat_type='ip';
        }elseif(strcmp($vars['stattype'], '2') == 0){
            $stat_type='srcip';
        }elseif(strcmp($vars['stattype'], '3') == 0){
            $stat_type='dstip';
        }elseif(strcmp($vars['stattype'], '4') == 0){
            $stat_type='port';
        }elseif(strcmp($vars['stattype'], '5') == 0){
            $stat_type='srcport';
        }elseif(strcmp($vars['stattype'], '6') == 0){
            $stat_type='dstport';
        }elseif(strcmp($vars['stattype'], '7') == 0){
            $stat_type='srctos';
        }elseif(strcmp($vars['stattype'], '8') == 0){
            $stat_type='dsttos';
        }elseif(strcmp($vars['stattype'], '9') == 0){
            $stat_type='tos';
        }
    }

    $current_time=lowest_five_minutes(time() - 300);
    $last_time=lowest_five_minutes($current_time - $lastN - 300);

    $command=$config['nfdump'].' -M '.$config['nfsen_base'][0].'/profiles-data/live/'.$nfsen_hostname.' -T -R '.
             time_to_nfsen_subpath($last_time).':'.time_to_nfsen_subpath($current_time).
             ' -n '.$topN.' -s '.$stat_type.'/'.$stat_order;

    echo '<pre>';
    system($command);
    echo '</pre>';

}
