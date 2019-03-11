<?php

print_optionbar_start();

echo '<form action="'.generate_link( $link_array, array('nfsen' => 'stats')).'" id="FlowStats" method="POST">';

echo "
<select name='topN' id='topN' size=1>
    <OPTION value='0' >10</OPTION>
    <OPTION value='1' selected>20</OPTION>
    <OPTION value='2' >50</OPTION>
    <OPTION value='3' >100</OPTION>
    <OPTION value='4' >200</OPTION>
    <OPTION value='5' >500</OPTION>
</select>
<select name='stattype' id='StatTypeSelector' size=1>
    <OPTION value='0' >Flow Records</OPTION>
    <OPTION value='1' >Any IP Address</OPTION>
    <OPTION value='2' selected>SRC IP Address</OPTION>
    <OPTION value='3' >DST IP Address</OPTION>
    <OPTION value='4' >Any Port</OPTION>
    <OPTION value='5' >SRC Port</OPTION>
    <OPTION value='6' >DST Port</OPTION>
    <OPTION value='7' >SRC TOS</OPTION>
    <OPTION value='8' >DST TOS</OPTION>
    <OPTION value='9' >TOS</OPTION>
</select>
<select name='statorder' id='statorder' size=1>
    <OPTION value='0' >flows</OPTION>
    <OPTION value='1' >packets</OPTION>
    <OPTION value='2' >bytes</OPTION>
    <OPTION value='3' selected>pps</OPTION>
    <OPTION value='4' >bps</OPTION>
    <OPTION value='5' >bpp</OPTION>
</select>
<input type='submit' name='process' value='process' size='1'>
";
echo '</form>';

print_optionbar_end();
