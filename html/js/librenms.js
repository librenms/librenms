$(document).ready(function() {
    // Device override ajax calls
    $("[name='override_config']").bootstrapSwitch('offColor','danger');
    $('input[name="override_config"]').on('switchChange.bootstrapSwitch',  function(event, state) {
        event.preventDefault();
        var $this = $(this);
        var attrib = $this.data('attrib');
        var device_id = $this.data('device_id');
        $.ajax({
            type: 'POST',
            url: 'ajax_form.php',
            data: { type: 'override-config', device_id: device_id, attrib: attrib, state: state },
            dataType: 'json',
            success: function(data) {
                if (data.status == 'ok') {
                    toastr.success(data.message);
                }
                else {
                    toastr.error(data.message);
                }
            },
            error: function() {
                toastr.error('Could not set this override');
            }
        });
    });

    // Device override for text inputs
    $(document).on('blur', 'input[name="override_config_text"]', function(event) {
        event.preventDefault();
        var $this = $(this);
        var attrib = $this.data('attrib');
        var device_id = $this.data('device_id');
        var value = $this.val();
        $.ajax({
            type: 'POST',
            url: 'ajax_form.php',
            data: { type: 'override-config', device_id: device_id, attrib: attrib, state: value },
            dataType: 'json',
            success: function(data) {
                if (data.status == 'ok') {
                    toastr.success(data.message);
                }
                else {
                    toastr.error(data.message);
                }
            },
            error: function() {
                toastr.error('Could not set this override');
            }
        });
    });

    // Checkbox config ajax calls
    $("[name='global-config-check']").bootstrapSwitch('offColor','danger');
    $('input[name="global-config-check"]').on('switchChange.bootstrapSwitch',  function(event, state) {
        event.preventDefault();
        var $this = $(this);
        var config_id = $this.data("config_id");
        $.ajax({
            type: 'POST',
            url: 'ajax_form.php',
            data: {type: "update-config-item", config_id: config_id, config_value: state},
            dataType: "json",
            success: function (data) {
                if (data.status == 'ok') {
                    toastr.success('Config updated');
                } else {
                    toastr.error(data.message);
                }
            },
            error: function () {
                toastr.error(data.message);
            }
        });
    });

    // Input field config ajax calls
    $(document).on('blur', 'input[name="global-config-input"]', function(event) {
        event.preventDefault();
        var $this = $(this);
        var config_id = $this.data("config_id");
        var config_value = $this.val();
        $.ajax({
            type: 'POST',
            url: 'ajax_form.php',
            data: {type: "update-config-item", config_id: config_id, config_value: config_value},
            dataType: "json",
            success: function (data) {
                if (data.status == 'ok') {
                    toastr.success('Config updated');
                } else {
                    toastr.error(data.message);
                }
            },
            error: function () {
                toastr.error(data.message);
            }
        });
    });

    // Select config ajax calls
    $( 'select[name="global-config-select"]').change(function(event) {
        event.preventDefault();
        var $this = $(this);
        var config_id = $this.data("config_id");
        var config_value = $this.val();
        $.ajax({
            type: 'POST',
            url: 'ajax_form.php',
            data: {type: "update-config-item", config_id: config_id, config_value: config_value},
            dataType: "json",
            success: function (data) {
                if (data.status == 'ok') {
                    toastr.success('Config updated');
                } else {
                    toastr.error(data.message);
                }
            },
            error: function () {
                toastr.error(data.message);
            }
        });
    });

});

function submitCustomRange(frmdata) {
    var reto = /to=([0-9a-zA-Z\-])+/g;
    var refrom = /from=([0-9a-zA-Z\-])+/g;
    var tsto = moment(frmdata.dtpickerto.value).unix();
    var tsfrom = moment(frmdata.dtpickerfrom.value).unix();
    frmdata.selfaction.value = frmdata.selfaction.value.replace(reto, 'to=' + tsto);
    frmdata.selfaction.value = frmdata.selfaction.value.replace(refrom, 'from=' + tsfrom);
    frmdata.action = frmdata.selfaction.value;
    return true;
}

$(document).on("click", '.collapse-neighbors', function(event)
{
    var caller = $(this);
    var button = caller.find('.neighbors-button');
    var list = caller.find('.neighbors-interface-list');
    var continued = caller.find('.neighbors-list-continued');

    if(button.hasClass("glyphicon-plus"))
    {
        button.addClass('glyphicon-minus').removeClass('glyphicon-plus');
    }else
    {
        button.addClass('glyphicon-plus').removeClass('glyphicon-minus');
    }
   
    list.toggle();
    continued.toggle();
});

