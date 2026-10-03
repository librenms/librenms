<?php

/*
 * Pick one billable source: a radio per source type, a device filter and a select per type.
 * Only the select of the chosen type is enabled, so the form posts a single source_type + source_id.
 *
 * Expects:
 *   $picker_label_cols  bootstrap columns of the labels
 *   $picker_config      extra select2 config as a js object literal, e.g. to set dropdownParent inside a modal
 *   $picker_selected    optional initial select2 values: ['device' => [...], '<source type>' => [...]]
 */

use App\Models\Bill;

$picker_selected ??= [];
$picker_label_class = 'col-sm-' . $picker_label_cols . ' control-label';
$picker_types = Bill::sourceTypes();
?>
<div class="form-group">
    <label class="<?php echo $picker_label_class ?>">Source</label>
    <div class="col-sm-8">
        <?php foreach ($picker_types as $type => $class) { ?>
        <label class="radio-inline">
            <input type="radio" name="source_type" value="<?php echo e($type) ?>" <?php echo $type === array_key_first($picker_types) ? 'checked' : '' ?> onchange="billSourceChanged()" />
            <?php echo e($class::billingTypeName()) ?>
        </label>
        <?php } ?>
    </div>
</div>
<div class="form-group">
    <label class="<?php echo $picker_label_class ?>" for="device">Device</label>
    <div class="col-sm-8">
        <select class="form-control input-sm" id="device" name="device" onchange="billDeviceChanged()"></select>
    </div>
</div>
<?php foreach ($picker_types as $type => $class) { ?>
<div class="form-group bill-source" data-source-type="<?php echo e($type) ?>">
    <label class="<?php echo $picker_label_class ?>" for="source-<?php echo e($type) ?>"><?php echo e($class::billingTypeName()) ?></label>
    <div class="col-sm-8">
        <select class="form-control input-sm" id="source-<?php echo e($type) ?>" name="source_id"></select>
    </div>
</div>
<?php } ?>
<script type="text/javascript">
    const billSourceData = function (param) {
        param.device = $('#device').val();
        return param;
    }
    init_select2('#device', 'device', {}, <?php echo json_encode($picker_selected['device'] ?? null) ?>, 'All Devices', <?php echo $picker_config ?>);
    <?php foreach ($picker_types as $type => $class) { ?>
    init_select2(<?php echo json_encode('#source-' . $type) ?>, <?php echo json_encode($class::billingSelectType()) ?>, billSourceData, <?php echo json_encode($picker_selected[$type] ?? null) ?>, <?php echo json_encode('Select ' . $class::billingTypeName()) ?>, <?php echo $picker_config ?>);
    <?php } ?>
    function billDeviceChanged() {
        $('.bill-source select').val(null).trigger('change'); // clear the source selections
    }
    function billSourceChanged() {
        const type = $('input[name=source_type]:checked').val();
        $('.bill-source').each(function () {
            const active = $(this).data('source-type') === type;
            $(this).toggle(active);
            $(this).find('select').prop('disabled', ! active); // only the visible source is submitted
        });
    }
    billSourceChanged();
</script>
