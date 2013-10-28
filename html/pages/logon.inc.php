<div style="width: 470px; margin: auto; margin-top: 30px;">
    <div style="margin: auto; width:650px; padding:5px;">
      <table border="0" cellpadding="0" cellspacing="0">
        <tr>
          <td><img alt="Login required" /></td>		<!-- FIXME: add logo -->
          <td>
            <form action="" method="post" name="logonform">
              <h3>Please log in:</h3>
              <div style="height: 0px;"></div>
              <table border="0" align="left">
                <tr>
                  <td>Username</td>
                  <td><input type="text" name="username" /></td>
                </tr>
                <tr>
                  <td>Password</td>
                  <td><input type="password" name="password" /></td>
                </tr>
                <tr>
                  <td colspan="2" align="right"><input type="checkbox" name="remember" />
                  <font size="2">Remember Me</font></td>
                </tr>
                <tr>
                  <td colspan="2" align="right"><input class="submit" name="submit" type="submit" value="Login" /></td>
                </tr>
<?php
if (isset($auth_message))
{
  echo('<tr><td colspan="2" style="font-weight: bold; color: #cc0000;">' . $auth_message . '</td></tr>');
}
?>
            </table>
            </form>
          </td>
        </tr>
      </table>
<?php
if (isset($config['login_message']))
{
  echo('<div style="margin-top: 10px;text-align: center; font-weight: bold; color: #cc0000; width: 470px;">'.$config['login_message'].'</div>');
}
?>
<script type="text/javascript">
<!--
document.logonform.username.focus();
// -->
</script>

    </div>
</div>
