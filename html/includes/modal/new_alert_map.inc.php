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

 <div class="modal fade bs-example-modal-sm" id="create-map" tabindex="-1" role="dialog" aria-labelledby="Create" aria-hidden="true">
    <div class="modal-dialog">
      <div class="modal-content">
        <div class="modal-header">
          <button type="button" class="close" data-dismiss="modal" aria-hidden="true">&times;</button>
          <h5 class="modal-title" id="Create">Alert Maps</h5>
        </div>
        <div class="modal-body">
            <form method="post" role="form" id="maps" class="form-horizontal maps-form">
            <input type="hidden" name="map_id" id="map_id" value="">
            <input type="hidden" name="type" id="type" value="create-map-item">
        <div class='form-group'>
            <label for='name' class='col-sm-3 control-label'>Rule: </label>
            <div class='col-sm-9'>
                <input type='text' id='rule' name='rule' class='form-control' maxlength='200'>
            </div>
        </div>
        <div class="form-group">
                <label for='target' class='col-sm-3 control-label'>Target: </label>
                <div class="col-sm-9">
                        <input type='text' id='target' name='target' class='form-control' placeholder='Group or Hostname'/>
                </div>
        </div>
        <div class="form-group">
                <div class="col-sm-offset-3 col-sm-3">
                        <button class="btn btn-default btn-sm" type="submit" name="map-submit" id="map-submit" value="save">Save map</button>
                </div>
        </div>
</form>
                        </div>
                </div>
        </div>
</div>

<script>
$('#create-map').on('show.bs.modal', function (event) {
    var button = $(event.relatedTarget);
    var map_id = button.data('map_id');
    var modal = $(this)
    $('#map_id').val(map_id);
    $.ajax({
        type: "POST",
        url: "ajax_form.php",
        data: { type: "parse-alert-map", map_id: map_id },
        dataType: "json",
        success: function(output) {
            $('#rule').val(output['rule']);
            $('#target').val(output['target']);
        }
    });
});
var cache = {};
var alert_rules = new Bloodhound({
  datumTokenizer: Bloodhound.tokenizers.obj.whitespace('name'),
  queryTokenizer: Bloodhound.tokenizers.whitespace,
  remote: {
      url: "ajax_search.php?search=%QUERY&type=alert-rules",
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
alert_rules.initialize();
$('#rule').typeahead({
    hint: true,
    highlight: true,
    minLength: 1,
    classNames: {
        menu: 'typeahead-left'
    }
},
{
  source: alert_rules.ttAdapter(),
  async: true,
  displayKey: 'name',
  valueKey: name,
    templates: {
        alert_rules: Handlebars.compile('<p>&nbsp;{{name}}</p>')
    }
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
$('#target').typeahead({
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

$('#map-submit').click('', function(e) {
    e.preventDefault();
    $.ajax({
        type: "POST",
        url: "ajax_form.php",
        data: $('form.maps-form').serialize(),
        success: function(msg){
            $("#message").html('<div class="alert alert-info">'+msg+'</div>');
            $("#create-map").modal('hide');
            if(msg.indexOf("ERROR:") <= -1) {
                setTimeout(function() {
                    location.reload(1);
                }, 1000);
            }
        },
        error: function(){
            $("#message").html('<div class="alert alert-info">An error occurred creating this map.</div>');
            $("#create-map").modal('hide');
        }
    });
});

</script>

<?php
}

?>
