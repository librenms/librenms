<?php

if ($_SESSION['userlevel'] < 10)
{
  include("includes/error-no-perm.inc.php");

  exit;
}

if ($_POST['hostname'])
{
  echo('<div class="row">
            <div class="col-sm-3">
            </div>
            <div class="col-sm-6">');
  if ($_SESSION['userlevel'] > '5')
  {
    // Settings common to SNMPv2 & v3
    $hostname = mres($_POST['hostname']);
    if ($_POST['port']) { $port = mres($_POST['port']); } else { $port = $config['snmp']['port']; }
    if ($_POST['transport']) { $transport = mres($_POST['transport']); } else { $transport = "udp"; }

    if ($_POST['snmpver'] === "v2c" or $_POST['snmpver'] === "v1")
    {
      if ($_POST['community'])
      {
        $config['snmp']['community'] = array($_POST['community']);
      }

      $snmpver = mres($_POST['snmpver']);
      print_message("Adding host $hostname communit" . (count($config['snmp']['community']) == 1 ? "y" : "ies") . " "  . implode(', ',$config['snmp']['community']) . " port $port using $transport");
    }
    elseif ($_POST['snmpver'] === "v3")
    {
      $v3 = array (
        'authlevel' => mres($_POST['authlevel']),
        'authname' => mres($_POST['authname']),
        'authpass' => mres($_POST['authpass']),
        'authalgo' => mres($_POST['authalgo']),
        'cryptopass' => mres($_POST['cryptopass']),
        'cryptoalgo' => mres($_POST['cryptoalgo']),
      );

      array_push($config['snmp']['v3'], $v3);

      $snmpver = "v3";
      print_message("Adding SNMPv3 host $hostname port $port");
    }
    else
    {
      print_error("Unsupported SNMP Version. There was a dropdown menu, how did you reach this error ?");
    }
    $result = addHost($hostname, $snmpver, $port, $transport);
    if ($result)
    {
      print_message("Device added ($result)");
    }
  } else {
    print_error("You don't have the necessary privileges to add hosts.");
  }
  echo('    </div>
            <div class="col-sm-3">
            </div>
        </div>');
}

$pagetitle[] = "Add host";

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
          <option value="tcp">tcp</option>
          <option value="udp" selected>udp</option>
          <option value="tcp6">tcp6</option>
          <option value="udp6">udp6</option>
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
            <option value="noAuthNoPriv" selected>NoAuthNoPriv</option>
            <option value="authNoPriv">AuthNoPriv</option>
            <option value="authPriv">AuthPriv</option>
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
    <button type="submit" class="btn btn-default input-sm" name="Submit">Add Host</button>
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
        } else if(snmpVersion == 'v3') {
            $('#snmpv1_2').hide();
            $('#snmpv3').show();
        }
    }
    $('#snmpv3').toggle();
</script>
