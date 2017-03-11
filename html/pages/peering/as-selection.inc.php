<?php

// Exclude Private and reserved ASN ranges
// 64512 - 65535
// 4200000000 - 4294967295
$asns = dbFetchRows("SELECT `bgpLocalAs` FROM `devices` WHERE `disabled` = 0 AND `ignore` = 0 AND `bgpLocalAs` > 0 AND (`bgpLocalAs` < 64512 OR `bgpLocalAs` > 65535) AND `bgpLocalAs` < 4200000000 GROUP BY `bgpLocalAs`");

?>
<div class="row">
    <div class="col-sm-4">
        <table class="table table-bordered table-hover">
            <tr>
                <th>AS</th>
                <th>AS Name</th>
                <th>&nbsp;</th>
            </tr>
<?php
foreach ($asns as $asn) {
    $astext = get_astext($asn['bgpLocalAs']);
    echo "<tr>";
    echo "<td>{$asn['bgpLocalAs']}</td>";
    echo "<td>$astext</td>";
    echo "<td><a class='btn btn-sm btn-primary' href='" . generate_url(array('page' => 'peering', 'section' => 'ix-list', 'asn' => $asn['bgpLocalAs'])) . "' role='button'>Show connectd IXes</a></td";
    echo "</tr>";
}
?>
        </table>
    </div>
</div>
