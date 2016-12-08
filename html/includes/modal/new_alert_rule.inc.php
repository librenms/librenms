<?php
/*
 * LibreNMS
 *
 * Copyright (c) 2014 Neil Lathwood <https://github.com/laf/ http://www.lathwood.co.uk/fa>
 *
 * This program is free software: you can redistribute it and/or modify it
 * under the terms of the GNU General Public License as published by the
 * Free Software Foundation, either version 3 of the License, or (at your
 * option) any later version.  Please see LICENSE.txt at the top level of
 * the source code distribution for details.
 */

if (is_admin() !== false) {
?>

 <div class="modal fade bs-example-modal-sm" id="create-alert" tabindex="-1" role="dialog" aria-labelledby="Create" aria-hidden="true">
    <div class="modal-dialog">
      <div class="modal-content">
        <div class="modal-header">
          <button type="button" class="close" data-dismiss="modal" aria-hidden="true">&times;</button>
          <h5 class="modal-title" id="Create">Alert Rules</h5>
        </div>
        <div class="modal-body">
            <form method="post" role="form" id="rules" class="form-horizontal alerts-form">
            <div class="row">
                <div class="col-md-12">
                    <span id="response"></span>
                </div>
            </div>
            <input type="hidden" name="device_id" id="device_id" value="">
            <input type="hidden" name="alert_id" id="alert_id" value="">
            <input type="hidden" name="type" id="type" value="create-alert-item">
            <input type="hidden" name="template_id" id="template_id" value="">
        <div class="form-group">
            <div class="col-sm-12">
                <span id="ajax_response"></span>
            </div>
        </div>
        <div class="form-group">
                <label for='entity' class='col-sm-3 control-label'>Entity: </label>
                <div class="col-sm-5">
                        <input type='text' id='suggest' name='entity' class='form-control has-feedback' placeholder='I.e: devices.status'/>
                </div>
        </div>
        <div class="form-group">
                <div class="col-sm-offset-3 col-sm-9">
                        <p>Start typing for suggestions, use '.' for indepth selection</p>
                </div>
        </div>
        <div class="form-group">
                <label for='condition' class='col-sm-3 control-label'>Condition: </label>
                <div class="col-sm-5">
                        <select id='condition' name='condition' placeholder='Condition' class='form-control'>
                                <option value='='>Equals</option>
                                <option value='!='>Not Equals</option>
                                <option value='~'>Like</option>
                                <option value='!~'>Not Like</option>
                                <option value='>'>Larger than</option>
                                <option value='>='>Larger than or Equals</option>
                                <option value='<'>Smaller than</option>
                                <option value='<='>Smaller than or Equals</option>
                        </select>
                </div>
        </div>
        <div class="form-group">
                <label for='value' class='col-sm-3 control-label'>Value: </label>
                <div class="col-sm-5">
                        <input type='text' id='value' name='value' class='form-control has-feedback'/> <span id="next-step-value"></span>
                </div>
        </div>

        <div class="form-group">
                <label for='rule-glue' class='col-sm-3 control-label'>Connection: </label>
                <div class="col-sm-5">
                        <button class="btn btn-default btn-sm btn-warning" type="submit" name="rule-glue" value="&&" id="and" name="and">And</button>
                        <button class="btn btn-default btn-sm btn-warning" type="submit" name="rule-glue" value="||" id="or" name="or">Or</button>
                        <span id="next-step-and"></span>
                </div>
        </div>
        <div class="form-group">
                <label for='severity' class='col-sm-3 control-label'>Severity: </label>
                <div class="col-sm-5">
                        <select name='severity' id='severity' placeholder='Severity' class='form-control'>
                                <option value='ok'>OK</option>
                                <option value='warning'>Warning</option>
                                <option value='critical' selected>Critical</option>
                        </select>
                </div>
        </div>
        <div class="form-group">
            <label for='count' class='col-sm-3 control-label'>Max alerts: </label>
            <div class='col-sm-2'>
                <input type='text' id='count' name='count' class='form-control'>
            </div>
            <label for='delay' class='col-sm-1 control-label'>Delay: </label>
            <div class='col-sm-2'>
                <input type='text' id='delay' name='delay' class='form-control'>
            </div>
            <label for='interval' class='col-sm-2 control-label'>Interval: </label>
            <div class='col-sm-2'>
                <input type='text' id='interval' name='interval' class='form-control'>
            </div>
        </div>
        <div class='form-group'>
            <label for='mute' class='col-sm-3 control-label'>Mute alerts: </label>
            <div class='col-sm-2'>
                <input type="checkbox" name="mute" id="mute">
            </div>
            <label for='invert' class='col-sm-3 control-label'>Invert match: </label>
            <div class='col-sm-2'>
                <input type='checkbox' name='invert' id='invert'>
            </div>
        </div>
        <div class='form-group'>
            <label for='name' class='col-sm-3 control-label'>Rule name: </label>
            <div class='col-sm-9'>
                <input type='text' id='name' name='name' class='form-control' maxlength='200'>
            </div>
        </div>
        <div id="preseed-maps">
            <div class="form-group">
                <label for='map-stub' class='col-sm-3 control-label'>Map To: </label>
                <div class="col-sm-5">
                        <input type='text' id='map-stub' name='map-stub' class='form-control'/>
                </div>
                <div class="col-sm-3">
                        <button class="btn btn-primary btn-sm" type="button" name="add-map" id="add-map" value="Add">Add</button>
                </div>
            </div>
            <div class="row">
                <div class="col-md-12">
                    <span id="map-tags"></span>
                </div>
            </div>
        </div>
        <div class='form-group'>
            <label for='proc' class='col-sm-3 control-label'>Procedure URL: </label>
            <div class='col-sm-9'>
                <input type='text' id='proc' name='proc' class='form-control' maxlength='80'>
            </div>
        </div>
        <div class="form-group">
                <div class="col-sm-offset-3 col-sm-3">
                        <button class="btn btn-success btn-sm" type="submit" name="rule-submit" id="rule-submit" value="save">Save Rule</button>
                </div>
        </div>
</form>
                        </div>
                </div>
        </div>
</div>

<script>

$("[name='mute']").bootstrapSwitch('offColor','danger');
$("[name='invert']").bootstrapSwitch('offColor','danger');

$('#create-alert').on('hide.bs.modal', function (event) {
    $('#response').data('tagmanager').empty();
    $('#map-tags').data('tagmanager').empty();
});

$('#add-map').click('',function (event) {
    $('#map-tags').data('tagmanager').populate([ $('#map-stub').val() ]);
    $('#map-stub').val('');
});

$('#create-alert').on('show.bs.modal', function (event) {
    var button = $(event.relatedTarget);
    var device_id = button.data('device_id');
    var alert_id = button.data('alert_id');
    var modal = $(this)
    var template_id = $('#template_id').val();
    $('#template_id').val('');
    var arr = [];
    if (template_id >= 0) {
        $.ajax({
            type: "POST",
            url: "ajax_form.php",
            data: { type: "parse-alert-rule", alert_id: null, template_id: template_id },
            dataType: "json",
            success: function(output) {
                $.each ( output['rules'], function( key, value ) {
                    arr.push(value);
                });
                $('#response').data('tagmanager').populate(arr);
                $('#name').val(output['name']);
                $('#device_id').val("-1");
            }
        });
    }
    $('#device_id').val(device_id);
    $('#alert_id').val(alert_id);
    $('#tagmanager').tagmanager();
    $('#response').tagmanager({
           strategy: 'array',
           tagFieldName: 'rules[]'
    });
    $('#map-tags').tagmanager({
           strategy: 'array',
           tagFieldName: 'maps[]',
           initialCap: false
    });
    if( $('#alert_id').val() == '' || template_id == '') {
        $('#preseed-maps').show();
    } else {
        $('#preseed-maps').hide();
    }
    $.ajax({
        type: "POST",
        url: "ajax_form.php",
        data: { type: "parse-alert-rule", alert_id: alert_id },
        dataType: "json",
        success: function(output) {
            $.each ( output['rules'], function( key, value ) {
                arr.push(value);
            });
            $('#response').data('tagmanager').populate(arr);
            $('#severity').val(output['severity']).change;
            var extra = $.parseJSON(output['extra']);
            $('#count').val(extra['count']);
            if((extra['delay'] / 86400) >= 1) {
                var delay = extra['delay'] / 86400 + ' d';
            } else if((extra['delay'] / 3600) >= 1) {
                var delay = extra['delay'] / 3600 + ' h';
            } else if((extra['delay'] / 60) >= 1) {
                var delay = extra['delay'] / 60 + ' m';
            } else {
                var delay = extra['delay'];
            }
            $('#delay').val(delay);
            if((extra['interval'] / 86400) >= 1) {
                var interval = extra['interval'] / 86400 + ' d';
            } else if((extra['interval'] / 3600) >= 1) {
                var interval = extra['interval'] / 3600 + ' h';
            } else if((extra['interval'] / 60) >= 1) {
                var interval = extra['interval'] / 60 + ' m';
            } else {
                var interval = extra['interval'];
            }
            $('#interval').val(interval);
            $("[name='mute']").bootstrapSwitch('state',extra['mute']);
            $("[name='invert']").bootstrapSwitch('state',extra['invert']);
            $('#name').val(output['name']);
            $('#proc').val(output['proc']);
        }
    });
});
</script>

<script>
var cache = {};
var suggestions = new Bloodhound({
  datumTokenizer: Bloodhound.tokenizers.obj.whitespace('name'),
  queryTokenizer: Bloodhound.tokenizers.whitespace,
  remote: {
      url: "ajax_rulesuggest.php?device_id=<?php echo $device['device_id'];?>&term=%QUERY",
        filter: function (output) {
            return $.map(output, function (item) {
                return {
                    name: item.name,
                };
            });
        },
      wildcard: "%QUERY"
  }
});
suggestions.initialize();
$('#suggest').typeahead({
    hint: true,
    highlight: true,
    minLength: 1,
    classNames: {
        menu: 'typeahead-left'
    }
},
{
  source: suggestions.ttAdapter(),
  async: true,
  displayKey: 'name',
  valueKey: name,
  templates: {
      suggestion: Handlebars.compile('<p>&nbsp;{{name}}</p>')
  },
  limit: 20
});

var map_devices = new Bloodhound({
  datumTokenizer: Bloodhound.tokenizers.obj.whitespace('name'),
  queryTokenizer: Bloodhound.tokenizers.whitespace,
  remote: {
      url: "ajax_search.php?search=%QUERY&type=device&map=1",
        filter: function (output) {
            return $.map(output, function (item) {
                return {
                    name: item.name,
                };
            });
        },
      wildcard: "%QUERY"
  }
});
var map_groups = new Bloodhound({
  datumTokenizer: Bloodhound.tokenizers.obj.whitespace('name'),
  queryTokenizer: Bloodhound.tokenizers.whitespace,
  remote: {
      url: "ajax_search.php?search=%QUERY&type=group&map=1",
        filter: function (output) {
            return $.map(output, function (item) {
                return {
                    name: item.name,
                };
            });
        },
      wildcard: "%QUERY"
  }
});
map_devices.initialize();
map_groups.initialize();
$('#map-stub').typeahead({
    hint: true,
    highlight: true,
    minLength: 1,
    classNames: {
        menu: 'typeahead-left'
    }
},
{
  source: map_devices.ttAdapter(),
  async: true,
  displayKey: 'name',
  valueKey: name,
    templates: {
        header: '<h5><strong>&nbsp;Devices</strong></h5>',
        suggestion: Handlebars.compile('<p>&nbsp;{{name}}</p>')
    }
},
{
  source: map_groups.ttAdapter(),
  async: true,
  displayKey: 'name',
  valueKey: name,
    templates: {
        header: '<h5><strong>&nbsp;Groups</strong></h5>',
        suggestion: Handlebars.compile('<p>&nbsp;{{name}}</p>')
    }
});

$('#and, #or').click('', function(e) {
    e.preventDefault();
    $("#next-step-and").html("");
    var entity = $('#suggest').val();
    var condition = $('#condition').val();
    var value = $('#value').val();
    var glue = $(this).val();
    if(entity != '' && condition != '') {
        $('#response').tagmanager({
           strategy: 'array',
           tagFieldName: 'rules[]'
        });
        if(value.indexOf("%") < 0) {
          value = '"'+value+'"';
        }
        if(entity.indexOf("%") < 0) {
          entity = '%'+entity;
        }
        $('#response').data('tagmanager').populate([ entity+' '+condition+' '+value+' '+glue ]);
    }
});

$('#rule-submit').click('', function(e) {
    e.preventDefault();
    $.ajax({
        type: "POST",
        url: "ajax_form.php",
        data: $('form.alerts-form').serialize(),
        success: function(msg){
            if(msg.indexOf("ERROR:") <= -1) {
                $("#message").html('<div class="alert alert-info">'+msg+'</div>');
                $("#create-alert").modal('hide');
                $('#response').data('tagmanager').empty();
                setTimeout(function() {
                    location.reload(1);
                }, 1000);
            } else {
                $('#ajax_response').html('<div class="alert alert-danger alert-dismissible"><button type="button" class="close" data-dismiss="alert" aria-label="Close"><span aria-hidden="true">&times;</span></button>'+msg+'</div>');
            }
        },
        error: function(){
            $("#ajax_response").html('<div class="alert alert-info">An error occurred creating this alert.</div>');
        }
    });
});

$( "#suggest, #value" ).blur(function() {
    var $this = $(this);
    var suggest = $('#suggest').val();
    var value = $('#value').val();
    if (suggest == "") {
        $("#next-step-and").html("");
        $("#value").closest('.form-group').removeClass('has-error');
        $("#suggest").closest('.form-group').addClass('has-error');
    } else if (value == "") {
        $("#next-step-and").html("");
        $("#value").closest('.form-group').addClass('has-error');
        $("#suggest").closest('.form-group').removeClass('has-error');
    } else {
        $("#suggest").closest('.form-group').removeClass('has-error');
        $("#value").closest('.form-group').removeClass('has-error');
        $("#next-step-and").html('<i class="fa fa-long-arrow-left fa-col-danger"></i> Click AND / OR');
    }
});

</script>

<?php
}
