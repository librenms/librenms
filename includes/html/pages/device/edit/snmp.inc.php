<?php

use LibreNMS\Config;
use LibreNMS\Enum\PortAssociationMode;

$device = DeviceCache::getPrimary();

if (isset($_POST['editing'])) {
    if (Auth::user()->hasGlobalAdmin()) {
        $force_save = isset($_POST['force_save']) && $_POST['force_save'] == 'on';
        $device->poller_group = $_POST['poller_group'] ?? 0;
        $snmp_enabled = ($_POST['snmp'] == 'on');

        if ($snmp_enabled) {
            $device->snmp_disable = 0;
            $device->snmpver = $_POST['snmpver'];
            $device->port = $_POST['port'] ?: Config::get('snmp.port');
            $device->transport = $_POST['transport'] ?: $transport = 'udp';
            $device->port_association_mode = $_POST['port_assoc_mode'];
            $max_repeaters = $_POST['max_repeaters'] ?? '';
            $max_oid = $_POST['max_oid'];
            $device->retries = $_POST['retries'] ?: null;
            $device->timeout = $_POST['timeout'] ?: null;

            if ($device->snmpver == 'v3') {
                $device->community = ''; // if v3 works, we don't need a community
                $device->authalgo = $_POST['authalgo'];
                $device->authlevel = $_POST['authlevel'];
                $device->authname = $_POST['authname'];
                $device->authpass = $_POST['authpass'];
                $device->cryptoalgo = $_POST['cryptoalgo'];
                $device->cryptopass = $_POST['cryptopass'];
            } elseif ($_POST['community'] != '********') {
                $device->community = $_POST['community'];
            }
        } else {
            // snmp is disabled
            $device->features = null;
            $device->hardware = $_POST['hardware'];
            $device->icon = null;
            $device->os = $_POST['os'] ? $_POST['os_id'] : 'ping';
            $device->snmp_disable = 1;
            $device->sysName = $_POST['sysName'] ?: null;
            $device->version = null;
        }

        $device_is_snmpable = false;
        $device_updated = false;

        if ($force_save !== true && $snmp_enabled) {
            $helper = new \LibreNMS\Polling\ConnectivityHelper($device);
            $device_is_snmpable = $helper->isSNMPable();
        }

        if ($force_save === true || ! $snmp_enabled || $device_is_snmpable) {
            // update devices table
            $device_updated = $device->save();
        } else {
            $device->refresh(); // forget all pending changes
        }

        if ($snmp_enabled && ($force_save === true || $device_is_snmpable)) {
            // update devices_attribs table

            // note:
            // set_dev_attrib and del_dev_attrib *only* return (bool)
            // setAttrib() returns true if it was set and false if it was not (e.g. it didn't change)
            // forgetAttrib() returns true if it was deleted and false if it was not (e.g. it didn't exist)
            // Symfony throws FatalThrowableError on error

            $devices_attribs = ['snmp_max_repeaters', 'snmp_max_oid'];

            foreach ($devices_attribs as $devices_attrib) {
                // defaults
                $feedback_prefix = $devices_attrib;
                $form_value = null;
                $form_value_is_numeric = false; // does not need to be a number greater than zero

                if ($devices_attrib == 'snmp_max_repeaters') {
                    $feedback_prefix = 'SNMP Max Repeaters';
                    $form_value = $max_repeaters;
                    $form_value_is_numeric = true;
                }

                if ($devices_attrib == 'snmp_max_oid') {
                    $feedback_prefix = 'SNMP Max OID';
                    $form_value = $max_oid;
                    $form_value_is_numeric = true;
                }

                $get_devices_attrib = get_dev_attrib($device, $devices_attrib);
                $set_devices_attrib = false; // testing $set_devices_attrib === false is not a true indicator of a failure

                if ($form_value != $get_devices_attrib && $form_value_is_numeric && is_numeric($form_value) && $form_value != 0) {
                    $device->setAttrib($devices_attrib, $form_value);
                }

                if ($form_value != $get_devices_attrib && ! $form_value_is_numeric) {
                    $device->setAttrib($devices_attrib, $form_value);
                }

                if ($form_value != $get_devices_attrib && $form_value_is_numeric && ! is_numeric($form_value)) {
                    $device->forgetAttrib($devices_attrib);
                }

                if ($form_value != $get_devices_attrib && ! $form_value_is_numeric && $form_value == '') {
                    $device->forgetAttrib($devices_attrib);
                }

                if ($form_value != $get_devices_attrib && $set_devices_attrib) {
                    unset($device->attribs); // unload relation
                    $set_devices_attrib = $device->getAttrib($devices_attrib); // re-check the db value
                }

                if ($form_value != $get_devices_attrib && $form_value == $set_devices_attrib && (is_null($set_devices_attrib) || $set_devices_attrib == '')) {
                    $update_message[] = "$feedback_prefix deleted";
                }

                if ($form_value != $get_devices_attrib && $form_value == $set_devices_attrib && (! is_null($set_devices_attrib) && $set_devices_attrib != '')) {
                    $update_message[] = "$feedback_prefix updated to $set_devices_attrib";
                }

                if ($form_value != $get_devices_attrib && $form_value != $set_devices_attrib) {
                    $update_failed_message[] = "$feedback_prefix update failed.";
                }

                unset($get_devices_attrib, $set_devices_attrib);
            }
            unset($devices_attrib);
        }

        if ($device_updated) {
            $update_message[] = 'Device record updated';
        }

        if ($snmp_enabled && ($force_save !== true && ! $device_is_snmpable)) {
            $update_failed_message[] = 'Could not connect to ' . htmlspecialchars($device->hostname) . ' with those SNMP settings.  To save anyway, turn on Force Save.';
            $update_message[] = 'SNMP settings reverted';
        }

        if (! $device_updated && ! isset($update_message) && ! isset($update_failed_message)) {
            $update_message[] = 'SNMP settings did not change';
        }
    }//end if (Auth::user()->hasGlobalAdmin())
}//end if ($_POST['editing'])

// the following unsets are for security; the database is authoritative
// i.e. prevent unintentional artifacts from being saved or used (again), posting around the form, etc.
unset($_POST);
// these are only used for editing and should not be used as-is
unset($force_save, $poller_group, $snmp_enabled);
unset($community, $max_repeaters, $max_oid, $port, $port_assoc_mode, $retries, $snmpver, $transport, $timeout);

// get up-to-date database values for use on the form
$max_oid = $device->getAttrib('snmp_max_oid');
$max_repeaters = $device->getAttrib('snmp_max_repeaters');

// use PHP Flasher to print normal (success) messages, similar to Device Settings
if (isset($update_message)) {
    if (is_array($update_message)) {
        foreach ($update_message as $message) {
            flash()->addSuccess($message);
        }
    }

    if (is_string($update_message)) {
        flash()->addSuccess($update_message);
    }

    unset($message, $update_message);
}

// use flash()->addError to call attention to the problem; don't let it time out
if (isset($update_failed_message)) {
    if (is_array($update_failed_message)) {
        foreach ($update_failed_message as $error) {
            flash()
                ->option('timeout', 30000)
                ->addError($error);
        }
    }

    if (is_string($update_failed_message)) {
        flash()
            ->option('timeout', 30000)
            ->addError($update_failed_message);
    }

    unset($error, $update_failed_message);
}

echo "
    <form id='edit' name='edit' method='post' action='' role='form' class='form-horizontal'>
    " . csrf_field() . "
    <div class='form-group'>
    <label for='hardware' class='col-sm-2 control-label'>SNMP</label>
    <div class='col-sm-4'>
    <input type='checkbox' id='snmp' name='snmp' data-size='small' onChange='disableSnmp(this);'" . ($device->snmp_disable ? '' : ' checked') . ">
    </div>
    </div>
    <div id='snmp_override' style='display: " . ($device->snmp_disable ? 'block' : 'none') . ";'>
    <div class='form-group'>
    <label for='sysName' class='col-sm-2 control-label'>sysName (optional)</label>
    <div class='col-sm-4'>
    <input id='sysName' class='form-control' name='sysName' value='" . htmlspecialchars($device->sysName ?? '') . "'/>
    </div>
    </div>
    <div class='form-group'>
    <label for='hardware' class='col-sm-2 control-label'>Hardware (optional)</label>
    <div class='col-sm-4'>
    <input id='hardware' class='form-control' name='hardware' value='" . htmlspecialchars($device->hardware ?? '') . "'/>
    </div>
    </div>
    <div class='form-group'>
    <label for='os' class='col-sm-2 control-label'>OS (optional)</label>
    <div class='col-sm-4'>
    <input id='os' class='form-control' name='os' value='" . htmlspecialchars(Config::get("os.{$device->os}.text")) . "'/>
    <input type='hidden' id='os_id' class='form-control' name='os_id' value='" . $device->os . "'/>
    </div>
    </div>
    </div>
    <div id='snmp_conf' style='display: " . ($device->snmp_disable ? 'none' : 'block') . ";'>
    <input type=hidden name='editing' value='yes'>
    <div class='form-group'>
    <label for='snmpver' class='col-sm-2 control-label'>SNMP Details</label>
    <div class='col-sm-1'>
    <select id='snmpver' name='snmpver' class='form-control input-sm' onChange='changeForm();'>
    <option value='v1'>v1</option>
    <option value='v2c' " . ($device->snmpver == 'v2c' ? 'selected' : '') . ">v2c</option>
    <option value='v3' " . ($device->snmpver == 'v3' ? 'selected' : '') . ">v3</option>
    </select>
    </div>
    <div class='col-sm-2'>
    <input type='number' name='port' placeholder='port' class='form-control input-sm' value='" . htmlspecialchars($device->port == Config::get('snmp.port') ? '' : $device->port) . "'>
    </div>
    <div class='col-sm-1'>
    <select name='transport' id='transport' class='form-control input-sm'>";
foreach (Config::get('snmp.transports') as $transport) {
    echo "<option value='" . $transport . "'";
    if ($transport == $device->transport) {
        echo " selected='selected'";
    }

    echo '>' . $transport . '</option>';
}

echo "      </select>
    </div>
    </div>
    <div class='form-group'>
    <div class='col-sm-2'>
    </div>
    <div class='col-sm-1'>
    <input type='number' id='timeout' name='timeout' class='form-control input-sm' value='" . htmlspecialchars($device->timeout ?: '') . "' placeholder='seconds' />
    </div>
    <div class='col-sm-1'>
    <input type='number' id='retries' name='retries' class='form-control input-sm' value='" . htmlspecialchars($device->retries ?: '') . "' placeholder='retries' />
    </div>
    </div>
    <div class='form-group'>
      <label for='port_assoc_mode' class='col-sm-2 control-label'>Port Association Mode</label>
      <div class='col-sm-1'>
        <select name='port_assoc_mode' id='port_assoc_mode' class='form-control input-sm'>
";

foreach (PortAssociationMode::getModes() as $pam_id => $pam) {
    echo "           <option value='$pam_id'";

    if ($pam_id == $device->port_association_mode) {
        echo " selected='selected'";
    }

    echo ">$pam</option>\n";
}

echo "        </select>
      </div>
    </div>
    <div class='form-group'>
        <label for='max_repeaters' class='col-sm-2 control-label'>Max Repeaters</label>
        <div class='col-sm-1'>
            <input type='number' id='max_repeaters' name='max_repeaters' class='form-control input-sm' value='" . htmlspecialchars($max_repeaters ?? '') . "' placeholder='max repeaters' />
        </div>
    </div>
    <div class='form-group'>
        <label for='max_oid' class='col-sm-2 control-label'>Max OIDs</label>
        <div class='col-sm-1'>
            <input type='number' id='max_oid' name='max_oid' class='form-control input-sm' value='" . htmlspecialchars($max_oid ?? '') . "' placeholder='max oids' />
        </div>
    </div>
    <div id='snmpv1_2'>
    <div class='form-group'>
    <label class='col-sm-3 control-label text-left'><h4><strong>SNMPv1/v2c Configuration</strong></h4></label>
    </div>
    <div class='form-group'>
    <label for='community' class='col-sm-2 control-label'>SNMP Community</label>
    <div class='col-sm-4'>
    <input id='community' class='form-control' name='community' value='********' onfocus='this.value=(this.value===\"********\" ? decodeURIComponent(\"" . rawurlencode($device->community) . "\") : this.value);'/>
    </div>
    </div>
    </div>
    <div id='snmpv3'>
    <div class='form-group'>
    <label class='col-sm-3 control-label'><h4><strong>SNMPv3 Configuration</strong></h4></label>
    </div>
    <div class='form-group'>
    <label for='authlevel' class='col-sm-2 control-label'>Auth Level</label>
    <div class='col-sm-4'>
    <select id='authlevel' name='authlevel' class='form-control'>
    <option value='noAuthNoPriv'>noAuthNoPriv</option>
    <option value='authNoPriv' " . ($device->authlevel == 'authNoPriv' ? 'selected' : '') . ">authNoPriv</option>
    <option value='authPriv' " . ($device->authlevel == 'authPriv' ? 'selected' : '') . ">authPriv</option>
    </select>
    </div>
    </div>
    <div class='form-group'>
    <label for='authname' class='col-sm-2 control-label'>Auth User Name</label>
    <div class='col-sm-4'>
    <input type='text' id='authname' name='authname' class='form-control' value='" . htmlspecialchars($device->authname ?? '') . "' autocomplete='off'>
    </div>
    </div>
    <div class='form-group'>
    <label for='authpass' class='col-sm-2 control-label'>Auth Password</label>
    <div class='col-sm-4'>
    <input type='password' id='authpass' name='authpass' class='form-control' value='" . htmlspecialchars($device->authpass ?? '') . "' autocomplete='off'>
    </div>
    </div>
    <div class='form-group'>
    <label for='authalgo' class='col-sm-2 control-label'>Auth Algorithm</label>
    <div class='col-sm-4'>
    <select id='authalgo' name='authalgo' class='form-control'>";
foreach (\LibreNMS\SNMPCapabilities::authAlgorithms() as $algo => $enabled) {
    echo "<option value='$algo' " . ($device->authalgo === $algo ? 'selected' : '') . ($enabled ? '' : ' disabled') . ">$algo</option>\n";
}
echo '</select>';

if (! \LibreNMS\SNMPCapabilities::supportsSHA2()) {
    echo '<label class="text-left"><small>Some options are disabled. <a href="https://docs.librenms.org/Support/FAQ/#optional-requirements-for-snmpv3-sha2-auth">Read more here</a></small></label>';
}
echo "
    </div>
    </div>
    <div class='form-group'>
    <label for='cryptopass' class='col-sm-2 control-label'>Crypto Password</label>
    <div class='col-sm-4'>
    <input type='password' id='cryptopass' name='cryptopass' class='form-control' value='" . htmlspecialchars($device->cryptopass ?? '') . "' autocomplete='off'>
    </div>
    </div>
    <div class='form-group'>
    <label for='cryptoalgo' class='col-sm-2 control-label'>Crypto Algorithm</label>
    <div class='col-sm-4'>
    <select id='cryptoalgo' name='cryptoalgo' class='form-control'>";

foreach (\LibreNMS\SNMPCapabilities::cryptoAlgoritms() as $algo => $enabled) {
    echo "<option value='$algo' " . ($device->cryptoalgo === $algo ? 'selected' : '') . ($enabled ? '' : ' disabled') . ">$algo</option>\n";
}
echo '</select>
    ';
if (! \LibreNMS\SNMPCapabilities::supportsAES256()) {
    echo '<label class="text-left"><small>Some options are disabled. <a href="https://docs.librenms.org/Support/FAQ/#optional-requirements-for-snmpv3-sha2-auth">Read more here</a></small></label>';
}
    echo '
    </div>
    </div>
    </div>';
?>

</div>
<?php

if (Config::get('distributed_poller') === true) {
    echo '
        <div class="form-group">
        <label for="poller_group" class="col-sm-2 control-label">Poller Group</label>
        <div class="col-sm-4">
        <select name="poller_group" id="poller_group" class="form-control input-sm">
        <option value="0"> Default poller group</option>
        ';

    foreach (dbFetchRows('SELECT `id`,`group_name` FROM `poller_groups`') as $group) {
        echo '<option value="' . $group['id'] . '"';
        if ($device->poller_group == $group['id']) {
            echo ' selected';
        }

        echo '>' . htmlspecialchars($group['group_name']) . '</option>';
    }

    echo '
        </select>
        </div>
        </div>
        ';
}//end if
?>

<div class="form-group">
    <label for="force_save" class="control-label col-sm-2">Force Save</label>
    <div class="col-sm-9">
         <input type="checkbox" name="force_save" id="force_save" data-size="small">
    </div>
</div>

<div class="row">
    <div class="col-md-1 col-md-offset-2">
        <button type="submit" name="Submit"  class="btn btn-success"><i class="fa fa-check"></i> Save</button>
    </div>
</div>
</form>

<script>
$('[name="force_save"]').bootstrapSwitch();

function changeForm() {
    snmpVersion = $("#snmpver").val();
    if(snmpVersion === 'v1' || snmpVersion === 'v2c') {
        $('#snmpv1_2').show();
        $('#snmpv3').hide();
    }
    else if(snmpVersion === 'v3') {
        $('#snmpv1_2').hide();
        $('#snmpv3').show();
    }
}
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

<?php
if ($device->snmpver == 'v3') {
    echo "$('#snmpv1_2').hide();";
    echo "$('#snmpv3').show();";
} else {
    echo "$('#snmpv1_2').show();";
    echo "$('#snmpv3').hide();";
}

?>
</script>
