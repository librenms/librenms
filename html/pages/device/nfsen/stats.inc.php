<?php

print_optionbar_start();

echo '<form action="'.generate_url( $link_array, array('nfsen' => 'stats')).'" id="FlowStats" method="SUBMIT">';

echo 'Top N:
<select name="topN" id="topN" size=1>
';

$option_default=$config['nfsen_top_default'];
if (isset($vars['topN'])) {
    $option_default=$vars['topN'];
}

$option_int=0;
foreach ($config['nfsen_top_N'] as $option) {
    if (strcmp($option_default, $option) == 0) {
        echo '<OPTION value="'.$option.'" selected>'.$option.'</OPTION>';
    } else {
        echo '<OPTION value="'.$option.'">'.$option.'</OPTION>';
    }
}

echo '
</select>
During the last:
<select name="lastN" id="lastN" size=1>
';

$option_default=$config['nfsen_last_default'];
if (isset($vars['lastN'])) {
    $option_default=$vars['lastN'];
}

$option_keys=array_keys($config['nfsen_lasts']);
foreach ( $option_keys as $option ) {
    if (strcmp($option_default, $option) == 0) {
        echo '<OPTION value="'.$option.'" selected>'.$config['nfsen_lasts'][$option].'</OPTION>';
    } else {
        echo '<OPTION value="'.$option.'">'.$config['nfsen_lasts'][$option].'</OPTION>';
    }
}

echo '
</select>
, Stat Type:
<select name="stattype" id="StatTypeSelector" size=1>
';

$option_default=$config['nfsen_stat_default'];
if (isset($vars['stattype'])) {
    $option_default=$vars['stattype'];
}

// WARNING: order is relevant as it has to match the
// check later in the process part of this page.
$stat_types=array(
    'Flow Records',
    'Any IP Address',
    'SRC IP Address',
    'DST IP Address',
    'Any Port',
    'SRC Port',
    'DST Port',
    'SRC TOS',
    'DST TOS',
    'TOS',
);

// puts together the drop down options
$options_int=0;
foreach ($stat_types as $option) {
    if (strcmp($option_default, $options_int) == 0) {
        echo '<OPTION value="'.$options_int.'" selected>'.$option."</OPTION>\n";
    } else {
        echo '<OPTION value="'.$options_int.'">'.$option."</OPTION>\n";
    }

    $options_int++;
}

echo '
</select>
, Order By:
<select name="statorder" id="statorder" size=1>
';

$option_default=$config['nfsen_order_default'];
if (isset($vars['statorder'])) {
    $option_default=$vars['statorder'];
}


// WARNING: order is relevant as it has to match the
// check later in the process part of this page.
$order_types=array(
    'flows',
    'packets',
    'bytes',
    'pps',
    'bps',
    'bpp',
);

// puts together the drop down options
$options_int=0;
foreach ($order_types as $option) {
    if (strcmp($option_default, $options_int) == 0) {
        echo '<OPTION value="'.$options_int.'" selected>'.$option."</OPTION>\n";
    } else {
        echo '<OPTION value="'.$options_int.'">'.$option."</OPTION>\n";
    }

    $options_int++;
}

echo '
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
         is_numeric($vars['lastN']) &&
         ($vars['lastN'] <= $config['nfsen_last_max'])
        ){
        $lastN=$vars['lastN'];
    }

    // Make sure we have a sane value for lastN
    $topN=20;
    if (isset($vars['topN']) &&
         is_numeric($vars['topN']) &&
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
