<?php

use App\Actions\Device\ValidateDeviceAndCreate;
use LibreNMS\Config;
use LibreNMS\Enum\PortAssociationMode;
use LibreNMS\Exceptions\HostUnreachableException;
use LibreNMS\Util\IP;

$no_refresh = true;

if (! Auth::user()->hasGlobalAdmin()) {
    include 'includes/html/error-no-perm.inc.php';

    exit;
}

echo '<div class="row">
            <div class="col-sm-3">
            </div>
            <div class="col-sm-6">';

// first load enabled, after that check snmp variable
$snmp_enabled = ! isset($_POST['hostname']) || isset($_POST['snmp']);

if (! empty($_POST['hostname'])) {
    $hostname = strip_tags($_POST['hostname']);
    if (! \LibreNMS\Util\Validate::hostname($hostname) && ! IP::isValid($hostname)) {
        print_error("Invalid hostname or IP: $hostname");
    }

    $new_device = new \App\Models\Device(['hostname' => $hostname]);

    if (Auth::user()->hasGlobalRead()) {
        // Settings common to SNMPv2 & v3
        if ($_POST['port']) {
            $new_device->port = strip_tags($_POST['port']);
        }

        if ($_POST['transport']) {
            $new_device->transport = strip_tags($_POST['transport']);
        }

        $additional = [];
        if (! $snmp_enabled) {
            $new_device->snmp_disable = 1;
            $new_device->os = $_POST['os'] ? strip_tags($_POST['os_id']) : 'ping';
            $new_device->hardware = strip_tags($_POST['hardware']);
            $new_device->sysName = strip_tags($_POST['sysName']);
        } elseif ($_POST['snmpver'] === 'v2c' || $_POST['snmpver'] === 'v1') {
            $new_device->snmpver = strip_tags($_POST['snmpver']);
            $communities = Config::get('snmp.community');
            if ($_POST['community']) {
                $new_device->community = $_POST['community'];
                $communities = [$_POST['community']];
            }
            print_message("Adding host $hostname communit" . (count($communities) == 1 ? 'y' : 'ies') . ' ' . implode(', ', array_map('htmlspecialchars', $communities)) . " port $new_device->port using $new_device->transport");
        } elseif ($_POST['snmpver'] === 'v3') {
            $new_device->snmpver = 'v3';
            $new_device->authlevel = strip_tags($_POST['authlevel']);
            $new_device->authname = $_POST['authname'];
            $new_device->authpass = $_POST['authpass'];
            $new_device->authalgo = strip_tags($_POST['authalgo']);
            $new_device->cryptopass = $_POST['cryptopass'];
            $new_device->cryptoalgo = $_POST['cryptoalgo'];

            print_message("Adding SNMPv3 host: $hostname port: $port");
        } else {
            print_error('Unsupported SNMP Version. There was a dropdown menu, how did you reach this error ?');
        }//end if

        try {
            $new_device->poller_group = strip_tags($_POST['poller_group'] ?? '');
            $new_device->port_association_mode = PortAssociationMode::getId($_POST['port_assoc_mode']);

            $force_add = (isset($_POST['force_add']) && $_POST['force_add'] == 'on');
            $result = (new ValidateDeviceAndCreate($new_device, $force_add))->execute();

            if ($result) {
                $link = \LibreNMS\Util\Url::deviceUrl($new_device->device_id);
                print_message("Device added <a href='$link'>$hostname ($new_device->device_id)</a>");
            }
        } catch (HostUnreachableException $e) {
            print_error($e->getMessage());
            foreach ($e->getReasons() as $reason) {
                print_error($reason);
            }
        } catch (Exception $e) {
            print_error($e->getMessage());
        }
    } else {
        print_error("You don't have the necessary privileges to add hosts.");
    }
}
echo '    </div>
        <div class="col-sm-3">
        </div>
    </div>';

$pagetitle[] = 'Add host';

?>

<div class="row">
  <div class="col-sm-3">
  </div>
  <div class="col-sm-6">
<form name="form1" method="post" action="" class="form-horizontal" role="form">
    <?php echo csrf_field() ?>
  <div><h2>Add Device</h2></div>
  <div class="alert alert-info">Devices will be checked for Ping/SNMP reachability before being probed.</div>
  <div class="well well-lg">
      <div class="form-group">
          <label for="hostname" class="col-sm-3 control-label">Hostname or IP</label>
          <div class="col-sm-9">
              <input type="text" id="hostname" name="hostname" class="form-control input-sm" placeholder="Hostname">
          </div>
      </div>
      <div class='form-group'>
        <label for='hardware' class='col-sm-3 control-label'>SNMP</label>
        <div class='col-sm-4'>
            <input type="checkbox" id="snmp" name="snmp" data-size="small" onChange="disableSnmp(this);" checked>
        </div>
    </div>
    <div id='snmp_override' style="display: none;">
        <div class='form-group'>
            <label for='sysName' class='col-sm-3 control-label'>sysName (optional)</label>
            <div class='col-sm-9'>
                <input id='sysName' class='form-control' name='sysName' placeholder="sysName (optional)"/>
            </div>
        </div>
        <div class='form-group'>
            <label for='hardware' class='col-sm-3 control-label'>Hardware (optional)</label>
            <div class='col-sm-9'>
                <input id='hardware' class='form-control' name='hardware' placeholder="Hardware (optional)"/>
            </div>
        </div>
        <div class='form-group'>
            <label for='os' class='col-sm-3 control-label'>OS (optional)</label>
            <div class='col-sm-9'>
                <input id='os' class='form-control' name='os' placeholder="OS (optional)"/>
                <input type='hidden' id='os_id' class='form-control' name='os_id' />
            </div>
        </div>
    </div>
    <div id="snmp_conf" style="display: block;">
        <div class="form-group">
          <label for="snmpver" class="col-sm-3 control-label">SNMP Version</label>
          <div class="col-sm-3">
            <select name="snmpver" id="snmpver" class="form-control input-sm" onChange="changeForm();">
              <option value="v1">v1</option>
              <option value="v2c" selected>v2c</option>
              <option value="v3">v3</option>
            </select>
          </div>
          <div class="col-sm-3">
            <input type="text" name="port" placeholder="port" class="form-control input-sm">
          </div>
          <div class="col-sm-3">
            <select name="transport" id="transport" class="form-control input-sm">
<?php
foreach (Config::get('snmp.transports') as $transport) {
    echo "<option value='" . $transport . "'>" . $transport . '</option>';
}
?>
            </select>
          </div>
        </div>
        <div class="form-group">
          <label for="port_association_mode" class="col-sm-3 control-label">Port Association Mode</label>
          <div class="col-sm-3">
            <select name="port_assoc_mode" id="port_assoc_mode" class="form-control input-sm">
<?php

foreach (PortAssociationMode::getModes() as $mode) {
    $selected = '';
    if ($mode == Config::get('default_port_association_mode')) {
        $selected = 'selected';
    }

    echo "              <option value=\"$mode\" $selected>$mode</option>\n";
}

?>
            </select>
          </div>
        </div>
        <div id="snmpv1_2">
          <div class="form-group">
            <div class="col-sm-12 alert alert-info">
              <label class="control-label text-left input-sm">SNMPv1/2c Configuration</label>
            </div>
          </div>
          <div class="form-group">
            <label for="community" class="col-sm-3 control-label">Community</label>
            <div class="col-sm-9">
              <input type="text" name="community" id="community" placeholder="Community" class="form-control input-sm">
            </div>
          </div>
        </div>
        <div id="snmpv3">
          <div class="form-group">
            <div class="col-sm-12 alert alert-info">
              <label class="control-label text-left input-sm">SNMPv3 Configuration</label>
            </div>
          </div>
          <div class="form-group">
            <label for="authlevel" class="col-sm-3 control-label">Auth Level</label>
            <div class="col-sm-3">
              <select name="authlevel" id="authlevel" class="form-control input-sm">
                <option value="noAuthNoPriv" selected>noAuthNoPriv</option>
                <option value="authNoPriv">authNoPriv</option>
                <option value="authPriv">authPriv</option>
              </select>
            </div>
          </div>
          <div class="form-group">
            <label for="authname" class="col-sm-3 control-label">Auth User Name</label>
            <div class="col-sm-9">
              <input type="text" name="authname" id="authname" class="form-control input-sm" autocomplete="off">
            </div>
          </div>
          <div class="form-group">
            <label for="authpass" class="col-sm-3 control-label">Auth Password</label>
            <div class="col-sm-9">
              <input type="text" name="authpass" id="authpass" placeholder="AuthPass" class="form-control input-sm" autocomplete="off">
            </div>
          </div>
          <div class="form-group">
            <label for="authalgo" class="col-sm-3 control-label">Auth Algorithm</label>
            <div class="col-sm-9">
              <select name="authalgo" id="authalgo" class="form-control input-sm">
                  <?php
                  foreach (\LibreNMS\SNMPCapabilities::authAlgorithms() as $algo => $enabled) {
                      echo "<option value=\"$algo\"" . ($enabled ?: ' disabled') . ">$algo</option>";
                  }
                  ?>
              </select>
              <?php if (! \LibreNMS\SNMPCapabilities::supportsSHA2()) {?>
              <label class="text-left"><small>Some options are disabled. <a href="https://docs.librenms.org/Support/FAQ/#optional-requirements-for-snmpv3-sha2-auth">Read more here</a></small></label>
              <?php } ?>
            </div>
          </div>
          <div class="form-group">
            <label for="cryptopass" class="col-sm-3 control-label">Crypto Password</label>
            <div class="col-sm-9">
              <input type="text" name="cryptopass" id="cryptopass" placeholder="Crypto Password" class="form-control input-sm" autocomplete="off">
            </div>
          </div>
          <div class="form-group">
            <label for="cryptoalgo" class="col-sm-3 control-label">Crypto Algorithm</label>
            <div class="col-sm-9">
              <select name="cryptoalgo" id="cryptoalgo" class="form-control input-sm">
                  <?php
                  foreach (\LibreNMS\SNMPCapabilities::cryptoAlgoritms() as $algo => $enabled) {
                      echo "<option value=\"$algo\"" . ($enabled ?: ' disabled') . ">$algo</option>";
                  }
                  ?>
              </select>
              <?php if (! \LibreNMS\SNMPCapabilities::supportsAES256()) {?>
              <label class="text-left"><small>Some options are disabled. <a href="https://docs.librenms.org/Support/FAQ/#optional-requirements-for-snmpv3-sha2-auth">Read more here</a></small></label>
              <?php } ?>
            </div>
          </div>
        </div>
      </div>
<?php
if (Config::get('distributed_poller') === true) {
                      echo '
          <div class="form-group">
              <label for="poller_group" class="col-sm-3 control-label">Poller Group</label>
              <div class="col-sm-9">
                  <select name="poller_group" id="poller_group" class="form-control input-sm">
                      <option value="0"> Default poller group</option>
    ';

                      foreach (dbFetchRows('SELECT `id`,`group_name` FROM `poller_groups` ORDER BY `group_name`') as $group) {
                          echo '<option value="' . $group['id'] . '">' . $group['group_name'] . '</option>';
                      }

                      echo '
                  </select>
              </div>
          </div>
    ';
                  }//endif
?>
      <div class="form-group">
          <label for="force_add" class="col-sm-3 control-label">Force add<br><small>(No ICMP or SNMP checks performed)</small></label>
          <div class="col-sm-9">
                  <input type="checkbox" name="force_add" id="force_add" data-size="small">
          </div>
      </div>
    <hr>
    <center><button type="submit" class="btn btn-default" name="Submit">Add Device</button></center>
  </div>
</form>
  </div>
  <div class="col-sm-3">
  </div>
</div>
<script>
    function changeForm() {
        snmpVersion = $("#snmpver").val();
        if(snmpVersion == 'v1' || snmpVersion == 'v2c') {
            $('#snmpv1_2').show();
            $('#snmpv3').hide();
        }
        else if(snmpVersion == 'v3') {
            $('#snmpv1_2').hide();
            $('#snmpv3').show();
        }
    }
    $('#snmpv3').toggle();

    function disableSnmp(e) {
        if(e.checked) {
            $('#snmp_conf').show();
            $('#snmp_override').hide();
        } else {
            $('#snmp_conf').hide();
            $('#snmp_override').show();
        }
    }

    var os_suggestions = new Bloodhound({
        datumTokenizer: Bloodhound.tokenizers.obj.whitespace('text'),
        queryTokenizer: Bloodhound.tokenizers.whitespace,
        remote: {
            url: "ajax_ossuggest.php?term=%QUERY",
            filter: function (output) {
                return $.map(output, function (item) {
                    return {
                        text: item.text,
                        os: item.os,
                    };
                });
            },
            wildcard: "%QUERY"
        }
    });
    os_suggestions.initialize();
    $('#os').typeahead({
            hint: true,
            highlight: true,
            minLength: 1,
            classNames: {
                menu: 'typeahead-left'
            }
        },
        {
            source: os_suggestions.ttAdapter(),
            async: true,
            displayKey: 'text',
            valueKey: 'os',
            templates: {
                suggestion: Handlebars.compile('<p>&nbsp;{{text}}</p>')
            },
            limit: 20
        });

    $("#os").on("typeahead:selected typeahead:autocompleted", function(e,datum) {
        $("#os_id").val(datum.os);
        $("#os").html('<mark>' + datum.text + '</mark>');
    });

    $("[name='snmp']").bootstrapSwitch('offColor','danger');
    $("[name='force_add']").bootstrapSwitch();
<?php
if (! $snmp_enabled) {
    echo '  $("[name=\'snmp\']").trigger(\'click\');';
}
?>
</script>
