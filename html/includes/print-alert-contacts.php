<?php

use LibreNMS\Authentication\Auth;

$no_refresh = true;

?>
<div class="row">
    <div class="col-sm-12">
        <span id="message"></span>
    </div>
</div>
<?php

require_once 'includes/modal/edit_alert_contact.inc.php';

?>

<div class="table-responsive">
    <table class="table table-hover table-condensed">
    <tr>
    <th>#</th>
    <th>Contact Name</th>
    <th>Transport Type</th>
    <th>Details</th>
    <th style="width:86px;">Action</th>
    </tr>
    <td colspan="6">
<?php
if (Auth::user()->hasGlobalAdmin()) {
    echo "<button type='button' class='btn btn-primary btn-sm' data-toggle='modal' data-target='#edit-alert-contact'><i class='fa fa-plus'></i> Create alert contact</button>";
    echo "<i> - OR - </i>";
    echo "<button type='button' class='btn btn-primary btn-sm' data-toggle='modal' data-target='#edit-contact-group' disabled><i class='fa fa-plus'></i> Create contact group</button>";
}

echo "</td>";

// Iterate through each alert contact
$query = "SELECT `contact_id` AS `id`, `contact_name` AS `name`, `transport_type` AS `type` FROM `alert_contacts`";
foreach (dbFetchRows($query) as $contact) {
    echo "<tr>";
    echo "<td><i>#".((int)$contact['id'])."</i></td>";
    echo "<td>".$contact['name']."</td>";
    echo "<td>".$contact['type']."</td>";
    
    $query = "SELECT `config_name` AS `name`, `config_value` AS `value` FROM `alert_configs` WHERE `contact_id`=?";
    $param = [$contact['id']];
    echo "<td class='col-sm-4'>";

    //Iterate through alert contact config details
    foreach (dbFetchRows($query, $param) as $detail) {
        echo "<i>".$detail['name'].": ".$detail['value']."<br /></i>";
    }
    echo "</td>";
    echo "<td>";

    // Add action buttons for admin users only
    if (Auth::user()->hasGlobalAdmin()) {
        echo "<div class='btn-group btn-group-sm' role='group'>";
        echo "<button type='button' class='btn btn-primary' data-toggle='modal' data-target='#edit-alert-contact' data-contact_id='".$contact['id']."' name='edit-alert-rule' data-container='body'><i class='fa fa-lg fa-pencil' aria-hidden='true'></i></button> ";
        echo "<button type='button' class='btn btn-danger' aria-label='Delete' data-toggle='modal' data-target='#delete-alert-contact' data-contact_id='".$contact['id']."' name='delete-alert-contact' data-container='body'><i class='fa fa-lg fa-trash' aria-hidden='true'></i></button>";
    }
    echo "</td>";
    echo "</tr>\r\n";
}
?>
    </table>
</div>
