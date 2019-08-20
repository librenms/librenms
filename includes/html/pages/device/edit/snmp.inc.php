<?php

use LibreNMS\Config;

if ($_POST['editing']) {
    if (Auth::user()->hasGlobalAdmin()) {
        $poller_group = isset($_POST['poller_group']) ? clean($_POST['poller_group']) : 0;
        $snmp_enabled = ($_POST['snmp'] == 'on');
        if ($snmp_enabled) {
            $no_checks    = ($_POST['no_checks'] == 'on');
            $community    = clean($_POST['community']);
            $snmpver      = clean($_POST['snmpver']);
            $transport    = $_POST['transport'] ? clean($_POST['transport']) : $transport = 'udp';
            $port = $_POST['port'] ? clean($_POST['port']) : Config::get('snmp.port');
            $timeout      = clean($_POST['timeout']);
            $retries      = clean($_POST['retries']);
            $port_assoc_mode = clean($_POST['port_assoc_mode']);
            $max_repeaters = clean($_POST['max_repeaters']);
            $max_oid      = clean($_POST['max_oid']);
            $v3           = array(
                'authlevel'  => clean($_POST['authlevel']),
                'authname'   => clean($_POST['authname']),
                'authpass'   => clean($_POST['authpass']),
                'authalgo'   => clean($_POST['authalgo']),
                'cryptopass' => clean($_POST['cryptopass']),
                'cryptoalgo' => clean($_POST['cryptoalgo']),
            );

            // FIXME needs better feedback
            $update = array(
                'community'    => $community,
                'snmpver'      => $snmpver,
                'port'         => $port,
                'transport'    => $transport,
                'poller_group' => $poller_group,
                'port_association_mode' => $port_assoc_mode,
                'snmp_disable' => 0,
            );

            if ($timeout) {
                $update['timeout'] = $timeout;
            } else {
                $update['timeout'] = array('NULL');
            }

            if ($retries) {
                $update['retries'] = $retries;
            } else {
                $update['retries'] = array('NULL');
            }
            $update = array_merge($update, $v3);
        } else {
            $update['snmp_disable'] = 1;
            $update['os']           = $_POST['os'] ? clean($_POST['os_id']) : "ping";
            $update['hardware']     = clean($_POST['hardware']);
            $update['features']     = null;
            $update['version']      = null;
            $update['icon']         = null;
            $update['sysName']      = $_POST['sysName'] ? clean($_POST['sysName']) : null;
            $update['poller_group'] = $poller_group;
        }

        $device_tmp = deviceArray($device['hostname'], $community, $snmpver, $port, $transport, $v3, $port_assoc_mode);
        if ($no_checks === true || !$snmp_enabled || isSNMPable($device_tmp)) {
            $rows_updated = dbUpdate($update, 'devices', '`device_id` = ?', array($device['device_id']));

            $max_repeaters_set = 0;
            $max_oid_set = 0;

            if (is_numeric($max_repeaters) && $max_repeaters != 0) {
                $max_repeaters_set = set_dev_attrib($device, 'snmp_max_repeaters', $max_repeaters);
            } else {
                $max_repeaters_set = del_dev_attrib($device, 'snmp_max_repeaters');
            }

            if (is_numeric($max_oid) && $max_oid != 0) {
                $max_oid_set = set_dev_attrib($device, 'snmp_max_oid', $max_oid);
            } else {
                $max_oid_set = del_dev_attrib($device, 'snmp_max_oid');
            }

            if ($rows_updated > 0) {
                $update_message[] = $rows_updated.' Device record updated.';
            }
            if ($max_repeaters_set) {
                $update_message[] = 'SNMP Max repeaters updated.';
            } elseif ($max_repeaters_set === false) {
                $update_failed_message[] = 'SNMP Max repeaters update failed.';
            }
            if ($max_oid_set) {
                $update_message[] = 'SNMP Max OID updated updated.';
            } elseif ($max_oid_set === false) {
                $update_failed_message[] = 'SNMP Max OID updated failed.';
            }
            if (!isset($update_message) && !isset($update_failed_message)) {
                $update_message[] = 'Device record unchanged. No update necessary.';
            }
        } else {
            $update_failed_message[] = 'Could not connect to device with new SNMP details';
        }
    }//end if
}//end if

$device = dbFetchRow('SELECT * FROM `devices` WHERE `device_id` = ?', array($device['device_id']));
$descr  = $device['purpose'];

if (isset($update_message)) {
    print_message(join("<br />", $update_message));
}
if (isset($update_failed_message)) {
    print_error(join("<br />", $update_failed_message));
}

$max_repeaters = get_dev_attrib($device, 'snmp_max_repeaters');
$max_oid = get_dev_attrib($device, 'snmp_max_oid');

echo "
    <form id='edit' name='edit' method='post' action='' role='form' class='form-horizontal'>
    " . csrf_field() . "
    <div class='form-group'>
    <label for='hardware' class='col-sm-2 control-label'>SNMP</label>
    <div class='col-sm-4'>
    <input type='checkbox' id='snmp' name='snmp' data-size='small' onChange='disableSnmp(this);'".($device['snmp_disable'] ? "" : " checked").">
    </div>
    </div>
    <div id='snmp_override' style='display: ".($device['snmp_disable'] ? "block" : "none").";'>
    <div class='form-group'>
    <label for='sysName' class='col-sm-2 control-label'>sysName (optional)</label>
    <div class='col-sm-4'>
    <input id='sysName' class='form-control' name='sysName' value='".$device['sysName']."'/>
    </div>
    </div>
    <div class='form-group'>
    <label for='hardware' class='col-sm-2 control-label'>Hardware (optional)</label>
    <div class='col-sm-4'>
    <input id='hardware' class='form-control' name='hardware' value='".$device['hardware']."'/>
    </div>
    </div>
    <div class='form-group'>
    <label for='os' class='col-sm-2 control-label'>OS (optional)</label>
    <div class='col-sm-4'>
    <input id='os' class='form-control' name='os' value='" . Config::get("os.{$device['os']}.text") . "'/>
    <input type='hidden' id='os_id' class='form-control' name='os_id' value='".$device['os']."'/>
    </div>
    </div>
    </div>
    <div id='snmp_conf' style='display: ".($device['snmp_disable'] ? "none" : "block").";'>
    <input type=hidden name='editing' value='yes'>
    <div class='form-group'>
    <label for='snmpver' class='col-sm-2 control-label'>SNMP Details</label>
    <div class='col-sm-1'>
    <select id='snmpver' name='snmpver' class='form-control input-sm' onChange='changeForm();'>
    <option value='v1'>v1</option>
    <option value='v2c' ".($device['snmpver'] == 'v2c' ? 'selected' : '').">v2c</option>
    <option value='v3' ".($device['snmpver'] == 'v3' ? 'selected' : '').">v3</option>
    </select>
    </div>
    <div class='col-sm-2'>
    <input type='text' name='port' placeholder='port' class='form-control input-sm' value='" . ($device['port'] == Config::get('snmp.port') ? "" : $device['port']) . "'>
    </div>
    <div class='col-sm-1'>
    <select name='transport' id='transport' class='form-control input-sm'>";
foreach (Config::get('snmp.transports') as $transport) {
    echo "<option value='".$transport."'";
    if ($transport == $device['transport']) {
        echo " selected='selected'";
    }

    echo '>'.$transport.'</option>';
}

echo "      </select>
    </div>
    </div>
    <div class='form-group'>
    <div class='col-sm-2'>
    </div>
    <div class='col-sm-1'>
    <input id='timeout' name='timeout' class='form-control input-sm' value='".($device['timeout'] ? $device['timeout'] : '')."' placeholder='seconds' />
    </div>
    <div class='col-sm-1'>
    <input id='retries' name='retries' class='form-control input-sm' value='".($device['timeout'] ? $device['retries'] : '')."' placeholder='retries' />
    </div>
    </div>
    <div class='form-group'>
      <label for='port_assoc_mode' class='col-sm-2 control-label'>Port Association Mode</label>
      <div class='col-sm-1'>
        <select name='port_assoc_mode' id='port_assoc_mode' class='form-control input-sm'>
";

foreach (get_port_assoc_modes() as $pam_id => $pam) {
    echo "           <option value='$pam_id'";

    if ($pam_id == $device['port_association_mode']) {
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
            <input id='max_repeaters' name='max_repeaters' class='form-control input-sm' value='".$max_repeaters."' placeholder='max rep' />
        </div>
    </div>
    <div class='form-group'>
        <label for='max_oid' class='col-sm-2 control-label'>Max OIDs</label>
        <div class='col-sm-1'>
            <input id='max_oid' name='max_oid' class='form-control input-sm' value='".$max_oid."' placeholder='max oids' />
        </div>
    </div>
    <div id='snmpv1_2'>
    <div class='form-group'>
    <label class='col-sm-3 control-label text-left'><h4><strong>SNMPv1/v2c Configuration</strong></h4></label>
    </div>
    <div class='form-group'>
    <label for='community' class='col-sm-2 control-label'>SNMP Community</label>
    <div class='col-sm-4'>
    <input id='community' class='form-control' name='community' value='".$device['community']."'/>
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
    <option value='authNoPriv' ".($device['authlevel'] == 'authNoPriv' ? 'selected' : '').">authNoPriv</option>
    <option value='authPriv' ".($device['authlevel'] == 'authPriv' ? 'selected' : '').">authPriv</option>
    </select>
    </div>
    </div>
    <div class='form-group'>
    <label for='authname' class='col-sm-2 control-label'>Auth User Name</label>
    <div class='col-sm-4'>
    <input type='text' id='authname' name='authname' class='form-control' value='".$device['authname']."' autocomplete='off'>
    </div>
    </div>
    <div class='form-group'>
    <label for='authpass' class='col-sm-2 control-label'>Auth Password</label>
    <div class='col-sm-4'>
    <input type='password' id='authpass' name='authpass' class='form-control' value='".$device['authpass']."' autocomplete='off'>
    </div>
    </div>
    <div class='form-group'>
    <label for='authalgo' class='col-sm-2 control-label'>Auth Algorithm</label>
    <div class='col-sm-4'>
    <select id='authalgo' name='authalgo' class='form-control'>
    <option value='MD5'>MD5</option>
    <option value='SHA' ".($device['authalgo'] === 'SHA' ? 'selected' : '').">SHA</option>
    </select>
    </div>
    </div>
    <div class='form-group'>
    <label for='cryptopass' class='col-sm-2 control-label'>Crypto Password</label>
    <div class='col-sm-4'>
    <input type='password' id='cryptopass' name='cryptopass' class='form-control' value='".$device['cryptopass']."' autocomplete='off'>
    </div>
    </div>
    <div class='form-group'>
    <label for='cryptoalgo' class='col-sm-2 control-label'>Crypto Algorithm</label>
    <div class='col-sm-4'>
    <select id='cryptoalgo' name='cryptoalgo' class='form-control'>
    <option value='AES'>AES</option>
    <option value='DES' ".($device['cryptoalgo'] === 'DES' ? 'selected' : '').">DES</option>
    </select>
    </div>
    </div>
    </div>";

?>
<div class="form-group">
    <div class="col-sm-offset-2 col-sm-9">
        <div class="checkbox">
            <label>
                <input type="checkbox" name="no_checks" id="no_checks"> Don't perform ICMP or SNMP checks?
            </label>
        </div>
    </div>
</div>
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
        echo '<option value="'.$group['id'].'"';
        if ($device['poller_group'] == $group['id']) {
            echo ' selected';
        }

        echo '>'.$group['group_name'].'</option>';
    }

    echo '
        </select>
        </div>
        </div>
        ';
}//end if


echo '
    <div class="row">
        <div class="col-md-1 col-md-offset-2">
            <button type="submit" name="Submit"  class="btn btn-success"><i class="fa fa-check"></i> Save</button>
        </div>
    </div>
    </form>
    ';

?>
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
if ($snmpver == 'v3' || $device['snmpver'] == 'v3') {
    echo "$('#snmpv1_2').toggle();";
} else {
    echo "$('#snmpv3').toggle();";
}

?>
</script>
