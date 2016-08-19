<?php

$no_refresh = true;

if ($_SESSION['userlevel'] < 10) {
    include 'includes/error-no-perm.inc.php';

    exit;
}

if ($_POST['hostname']) {
    echo '<div class="row">
            <div class="col-sm-3">
            </div>
            <div class="col-sm-6">';
    if ($_SESSION['userlevel'] > '5') {
        // Settings common to SNMPv2 & v3
        $hostname = mres($_POST['hostname']);
        if ($_POST['port']) {
            $port = mres($_POST['port']);
        } else {
            $port = $config['snmp']['port'];
        }

        if ($_POST['transport']) {
            $transport = mres($_POST['transport']);
        } else {
            $transport = 'udp';
        }

        if ($_POST['snmpver'] === 'v2c' or $_POST['snmpver'] === 'v1') {
            if ($_POST['community']) {
                $config['snmp']['community'] = array($_POST['community']);
            }

            $snmpver = mres($_POST['snmpver']);
            print_message("Adding host $hostname communit".(count($config['snmp']['community']) == 1 ? 'y' : 'ies').' '.implode(', ', $config['snmp']['community'])." port $port using $transport");
        } elseif ($_POST['snmpver'] === 'v3') {
            $v3 = array(
                   'authlevel'  => mres($_POST['authlevel']),
                   'authname'   => mres($_POST['authname']),
                   'authpass'   => mres($_POST['authpass']),
                   'authalgo'   => mres($_POST['authalgo']),
                   'cryptopass' => mres($_POST['cryptopass']),
                   'cryptoalgo' => mres($_POST['cryptoalgo']),
                  );

            array_push($config['snmp']['v3'], $v3);

            $snmpver = 'v3';
            print_message("Adding SNMPv3 host $hostname port $port");
        } else {
            print_error('Unsupported SNMP Version. There was a dropdown menu, how did you reach this error ?');
        }//end if
        $poller_group = $_POST['poller_group'];
        $force_add    = ($_POST['force_add'] == 'on');

        $port_assoc_mode = $_POST['port_assoc_mode'];
        try {
            $device_id = addHost($hostname, $snmpver, $port, $transport, $poller_group, $force_add, $port_assoc_mode);
            print_message("Device added ($device_id)");
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
    }//end if
    echo '    </div>
            <div class="col-sm-3">
            </div>
        </div>';
}//end if

$pagetitle[] = 'Add host';

?>

<div class="row">
  <div class="col-sm-3">
  </div>
  <div class="col-sm-6">
<form name="form1" method="post" action="" class="form-horizontal" role="form">
  <div><h2>Add Device</h2></div>
  <div class="alert alert-info">Devices will be checked for Ping and SNMP reachability before being probed. Only devices with recognised OSes will be added.</div>
  <div class="well well-lg">
    <div class="form-group">
      <label for="hostname" class="col-sm-3 control-label">Hostname</label>
      <div class="col-sm-9">
        <input type="text" id="hostname" name="hostname" class="form-control input-sm" placeholder="Hostname">
      </div>
    </div>
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
foreach ($config['snmp']['transports'] as $transport) {
    echo "<option value='".$transport."'";
    if ($transport == $device['transport']) {
        echo " selected='selected'";
    }

    echo '>'.$transport.'</option>';
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


foreach (get_port_assoc_modes() as $mode) {
    $selected = "";
    if ($mode == $config['default_port_association_mode']) {
        $selected = "selected";
    }

    echo "          <option value=\"$mode\" $selected>$mode</option>\n";
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
          <input type="text" name="authname" id="authname" class="form-control input-sm">
        </div>
      </div>
      <div class="form-group">
        <label for="authpass" class="col-sm-3 control-label">Auth Password</label>
        <div class="col-sm-9">
          <input type="text" name="authpass" id="authpass" placeholder="AuthPass" class="form-control input-sm">
        </div>
      </div>
      <div class="form-group">
        <label for="authalgo" class="col-sm-3 control-label">Auth Algorithm</label>
        <div class="col-sm-9">
          <select name="authalgo" id="authalgo" class="form-control input-sm">
            <option value="MD5" selected>MD5</option>
            <option value="SHA">SHA</option>
          </select>
        </div>
      </div>
      <div class="form-group">
        <label for="cryptopass" class="col-sm-3 control-label">Crypto Password</label>
        <div class="col-sm-9">
          <input type="text" name="cryptopass" id="cryptopass" placeholder="Crypto Password" class="form-control input-sm">
        </div>
      </div>
      <div class="form-group">
        <label for="cryptoalgo" class="col-sm-3 control-label">Crypto Algorithm</label>
        <div class="col-sm-9">
          <select name="cryptoalgo" id="cryptoalgo" class="form-control input-sm">
            <option value="AES" selected>AES</option>
            <option value="DES">DES</option>
          </select>
        </div>
      </div>
    </div>
<?php
if ($config['distributed_poller'] === true) {
    echo '
      <div class="form-group">
          <label for="poller_group" class="col-sm-3 control-label">Poller Group</label>
          <div class="col-sm-9">
              <select name="poller_group" id="poller_group" class="form-control input-sm">
                  <option value="0"> Default poller group</option>
    ';

    foreach (dbFetchRows('SELECT `id`,`group_name` FROM `poller_groups`') as $group) {
        echo '<option value="'.$group['id'].'">'.$group['group_name'].'</option>';
    }

    echo '
              </select>
          </div>
      </div>
      <div class="form-group">
          <div class="col-sm-offset-3 col-sm-9">
              <div class="checkbox">
                  <label>
                      <input type="checkbox" name="force_add" id="force_add"> Force add
                  </label>
              </div>
          </div>
      </div>
    ';
}//end if

?>
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
</script>
