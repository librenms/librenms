<?php
echo "<h3>Authlog</h3>";
if ($_SESSION['userlevel'] >= '10') {
    echo '<table class="table table-hover table-condensed">';
    echo "<th>Timestamp</th><th>User</th><th>IP Address</th><th>Result</th>";
    foreach (dbFetchRows("SELECT *,DATE_FORMAT(datetime, '".$config['dateformat']['mysql']['compact']."') as humandate  FROM `authlog` ORDER BY `datetime` DESC LIMIT 0,250") as $entry) {
        if ($bg == $list_colour_a) {
            $bg = $list_colour_b;
        }
        else {
            $bg = $list_colour_a;
        }

        echo "<tr>
            <td>
            ".$entry['datetime'].'
            </td>
            <td>
            '.$entry['user'].'
            </td>
            <td>
            '.$entry['address'].'
            </td>
            <td>
            '.$entry['result'].'
            </td>
            ';
    }//end foreach

    $pagetitle[] = 'Authlog';

    echo '</table>';
}
else {
    include 'includes/error-no-perm.inc.php';
}//end if
