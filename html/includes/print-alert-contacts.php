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
    <th>Transport</th>
    <th>Transport Configuration</th>
    <th>Details</th>
    <th style="width:86px;">Action</th>
    </tr>
    <td colspan="6">
<?php
if (Auth::user()->hasGlobalAdmin()) {
    echo "<button type='button' class='btn btn-primary btn-sm' data-toggle='modal' data-target='#edit-alert-contact'><i class='fa fa-plus'></i> Create alert contact</button>";
    echo "<i> - OR - </i>";
    echo "<button type='button' class='btn btn-primary btn-sm' data-toggle='modal' data-target='#edit-contact-group' disabled><i class='fa fa-plus'></i> Create contact group</button>";
    echo "<i> - OR - </i>";
    echo "<button type='button' class='btn btn-primary btn-sm' data-toggle='modal' data-target='#edit-alert-transport' disabled><i class='fa fa-plus'></i> Add transport configuration</button>";
    echo "</td>";
}

// Iterate through each alert contact
$query = "SELECT `contact_id` as `id`, `contact_name` as `name`, `transport_type` as `type`, `transport_config` as `config` FROM `alert_contacts`";
foreach (dbFetchRows($query) as $contact) {
    echo "<tr>";
    echo "<td><i>#".((int)$contact['id'])."</i></td>";
    echo "<td>".$contact['name']."</td>";
    echo "<td>".$contact['type']."</td>";
    echo "<td>".$contact['config']."</td>";
    
    $query = "SELECT `config_name` as `name`, `config_value` as `value` FROM `alert_configs` WHERE `config_type`='contact' and `contact_or_transport_id`=?";
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
