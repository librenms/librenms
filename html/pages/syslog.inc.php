<?php

$no_refresh = true;

$param = array();

if ($vars['action'] == 'expunge' && $_SESSION['userlevel'] >= '10') {
    dbQuery('TRUNCATE TABLE `syslog`');
    print_message('syslog truncated');
}

$pagetitle[] = 'Syslog';
?>

<div class="table-responsive">
<table id="syslog" class="table table-hover table-condensed table-striped">
    <thead>
        <tr>
            <th data-column-id="priority">&nbsp;</th>
            <th data-column-id="timestamp" data-order="desc">Datetime</th>
            <th data-column-id="device_id">Hostname</th>
            <th data-column-id="program">Program</th>
            <th data-column-id="msg">Message</th>
        </tr>
    </thead>
</table>
</div>

<script>

var grid = $("#syslog").bootgrid({
    ajax: true,
    templates: {
        header: "<div id=\"{{ctx.id}}\" class=\"{{css.header}}\"><div class=\"row\">"+
                "<div class=\"col-sm-9 actionBar\"><span class=\"pull-left\">"+
                "<form method=\"post\" action=\"\" class=\"form-inline\" role=\"form\" id=\"result_form\">"+
                "<div class=\"form-group\">"+
                "<select name=\"device\" id=\"device\" class=\"form-control input-sm\">"+
                "<option value=\"\">All Devices</option>"+
                <?php
                foreach (get_all_devices() as $hostname) {
                    $device_id = getidbyname($hostname);
                    if (device_permitted($device_id)) {
                        echo '"<option value=\"'.$device_id.'\"';
                        if ($device_id == $vars['device']) {
                            echo ' selected';
                        }

                        echo '>'.$hostname.'</option>"+';
                    }
                }
                ?>
                "</select>"+
                "</div>"+
                "<div class=\"form-group\">"+
                "<select name=\"program\" id=\"program\" class=\"form-control input-sm\">"+
                "<option value=\"\">All Programs</option>"+
                <?php
                foreach (dbFetchRows('SELECT DISTINCT `program` FROM `syslog` ORDER BY `program`') as $data) {
                    echo '"<option value=\"'.$data['program'].'\"';
                    if ($data['program'] == $vars['program']) {
                        echo ' selected';
                    }

                    echo '>'.$data['program'].'</option>"+';
                }
                ?>
                "</select>"+
                "</div>"+
                "<div class=\"form-group\">"+
                "<select name=\"priority\" id=\"priority\" class=\"form-control input-sm\">"+
                "<option value=\"\">All Priorities</option>"+
                <?php
                foreach (dbFetchRows('SELECT DISTINCT `priority` FROM `syslog` ORDER BY `level`') as $data) {
                    echo '"<option value=\"'.$data['priority'].'\"';
                    if ($data['priority'] == $vars['priority']) {
                        echo ' selected';
                    }

                    echo '>'.$data['priority'].'</option>"+';
                }
                ?>
                "</select>"+
                "</div>"+
                "<div class=\"form-group\">"+
                "<input name=\"from\" type=\"text\" class=\"form-control input-sm\" id=\"dtpickerfrom\" maxlength=\"16\" value=\"<?php echo $vars['from']; ?>\" placeholder=\"From\" data-date-format=\"YYYY-MM-DD HH:mm\">"+
                "</div>"+
                "<div class=\"form-group\">"+
                "<input name=\"to\" type=\"text\" class=\"form-control input-sm\" id=\"dtpickerto\" maxlength=\"16\" value=\"<?php echo $vars['to']; ?>\" placeholder=\"To\" data-date-format=\"YYYY-MM-DD HH:mm\">"+
                "</div>"+
                "<button type=\"submit\" class=\"btn btn-default input-sm\">Filter</button>"+
                "</form></span></div>"+
                "<div class=\"col-sm-3 actionBar\"><p class=\"{{css.actions}}\"></p></div></div></div>"

    },
    post: function ()
    {
        return {
            id: "syslog",
            device: '<?php echo htmlspecialchars($vars['device']); ?>',
            program: '<?php echo htmlspecialchars($vars['program']); ?>',
            priority: '<?php echo htmlspecialchars($vars['priority']); ?>',
            to: '<?php echo htmlspecialchars($vars['to']); ?>',
            from: '<?php echo htmlspecialchars($vars['from']); ?>',
        };
    },
    url: "ajax_table.php"
});

$(function () {
    $("#dtpickerfrom").datetimepicker();
    $("#dtpickerfrom").on("dp.change", function (e) {
        $("#dtpickerto").data("DateTimePicker").minDate(e.date);
    });
    $("#dtpickerto").datetimepicker();
    $("#dtpickerto").on("dp.change", function (e) {
        $("#dtpickerfrom").data("DateTimePicker").maxDate(e.date);
    });
    if( $("#dtpickerfrom").val() != "" ) {
        $("#dtpickerto").data("DateTimePicker").minDate($("#dtpickerfrom").val());
    }
    if( $("#dtpickerto").val() != "" ) {
        $("#dtpickerfrom").data("DateTimePicker").maxDate($("#dtpickerto").val());
    } else {
        $("#dtpickerto").data("DateTimePicker").maxDate('<?php echo date($config['dateformat']['byminute']); ?>');
    }
});
</script>
