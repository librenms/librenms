<?php

$no_refresh = true;

?>
<div class="row">
    <div class="col-sm-12">
        <span id="message"></span>
    </div>
</div>
<?php

require_once 'includes/html/modal/edit_alert_transport.inc.php';
require_once 'includes/html/modal/edit_transport_group.inc.php';

if (Auth::user()->hasGlobalAdmin()) {
    echo "<button type='button' class='btn btn-primary btn-sm' data-toggle='modal' data-target='#edit-alert-transport'>Create alert transport</button>";
}
?>
<br>
<br>
<div class="table-responsive">
    <table class="table table-hover table-condensed">
    <tr>
        <th>Transport Name</th>
        <th>Transport Type</th>
        <th>Default</th>
        <th>Details</th>
        <th style="width:136px;">Action</th>
    </tr>
<?php

// Iterate through each alert transport
foreach (\App\Models\AlertTransport::orderBy('transport_name', 'asc')->get() as $transport) {
    $instance = $transport->instance();
    echo "<tr id=\"alert-transport-{$transport->transport_id}\">";
    echo '<td>' . $transport->transport_name . '</td>';
    echo '<td>' . $instance->name() . '</td>';
    echo $transport->is_default ? '<td>Yes</td>' : '<td>No</td>';
    echo '<td class="col-sm-4"><i>' . nl2br($instance->displayDetails()) . '</i></td>';

    echo '<td>';
    // Add action buttons for admin users only
    if (Auth::user()->hasGlobalAdmin()) {
        echo "<div class='btn-group btn-group-sm' role='group'>";
        echo "<button type='button' class='btn btn-primary btn-sm' data-toggle='modal' data-target='#edit-alert-transport' data-transport_id='" . $transport->transport_id . "' name='edit-alert-rule' data-container='body' data-toggle='popover' data-content='Edit transport'><i class='fa fa-lg fa-pencil' aria-hidden='true'></i></button> ";
        echo "<button type='button' class='btn btn-danger btn-sm' aria-label='Delete' data-toggle='modal' data-target='#delete-alert-transport' data-transport_id='" . $transport->transport_id . "' name='delete-alert-transport' data-container='body' data-toggle='popover' data-content='Delete transport'><i class='fa fa-lg fa-trash' aria-hidden='true'></i></button>";
        echo "<button type='button' class='btn btn-warning btn-sm' data-transport_id='" . $transport->transport_id . "' data-transport='{$transport->transport_type}' name='test-transport' id='test-transport' data-toggle='popover' data-content='Test transport'><i class='fa fa-lg fa-check' aria-hidden='true'></i></button> ";
        echo '</div>';
    }
    echo '</td>';
    echo "</tr>\r\n";
}
?>
    </table>
</div>
<br>
<?php
if (Auth::user()->hasGlobalAdmin()) {
    echo "<button type='button' class='btn btn-primary btn-sm' data-toggle='modal' data-target='#edit-transport-group'>Create transport group</button>";
}
?>

<br>
<br>
<div class="table-responsive">
    <table class="table table-hover table-condensed">
    <tr>
    <th>Transport Group</th>
    <th>Members</th>
    <th style="width:136px;">Action</th>
    </tr>
<?php

//Iterate through alert groups
$query = 'SELECT `transport_group_id` AS `id`, `transport_group_name` AS `name` FROM `alert_transport_groups` order by `name`';
foreach (dbFetchRows($query) as $group) {
    echo "<tr id=\"alert-transport-group-{$group['id']}\">";
    echo '<td>' . $group['name'] . '</td>';

    //List out the members of each group
    $query = 'SELECT `transport_type`, `transport_name` FROM `transport_group_transport` AS `a` LEFT JOIN `alert_transports` AS `b` ON `a`.`transport_id`=`b`.`transport_id` WHERE `transport_group_id`=? order by `transport_name`';
    $members = dbFetchRows($query, [$group['id']]);
    echo '<td>';
    foreach ($members as $member) {
        echo '<i>' . ucfirst($member['transport_type']) . ': ' . $member['transport_name'] . '<br /></i>';
    }
    echo '</td>';
    echo '<td>';
    if (Auth::user()->hasGlobalAdmin()) {
        echo "<div class='btn-group btn-group-sm' role='group'>";
        echo "<button type='button' class='btn btn-primary btn-sm' data-toggle='modal' data-target='#edit-transport-group' data-group_id='" . $group['id'] . "' data-container='body' data-toggle='popover' data-content='Edit transport group'><i class='fa fa-lg fa-pencil' aria-hidden='true'></i></button> ";
        echo "<button type='button' class='btn btn-danger btn-sm' aria-label='Delete' data-toggle='modal' data-target='#delete-transport-group' data-group_id='" . $group['id'] . "' data-container='body' data-toggle='popover' data-content='Delete transport group'><i class='fa fa-lg fa-trash' aria-hidden='true'></i></button>";
        echo '</div>';
    }
    echo '</td>';
    echo '</tr>';
}
?>
    </table>
</div>

<script>
    $("button#test-transport").on("click", function() {
        var $this = $(this);
        var transport_id = $this.data("transport_id");
        var transport = $this.data("transport");
        $.ajax({
            type: 'POST',
            url: '<?php echo route('alert.transports.test', ['transport' => ':transport_id']) ?>'.replace(':transport_id', transport_id),
            data: { type: "test-transport", transport_id: transport_id },
            dataType: "json",
            success: function(data){
                if (data.status === 'ok') {
                    toastr.success('Test to ' + transport + ' ok');
                } else {
                    toastr.error('Test to ' + transport + ' failed<br />' + data.message);
                }
            },
            error: function(){
                toastr.error('Test to ' + transport + ' failed - general error');
            }
        });
    });

    $("[data-toggle='popover']").popover({
        trigger: 'hover',
        placement: 'top'
    });
</script>
