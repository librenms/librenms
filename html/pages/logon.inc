<?php

    echo("
  <center><br />
<table  border='0' cellpadding='20' cellspacing='0' background='images/login.png' width=479 height=212>
    <tr>
      <td width=128></td>
      <td>
        <form action='". $_SERVER['REQUEST_URI'] ."' method=post>
          <h3>Please log in:</h3>

          <div style='height: 0px;'></div>
          <table border=0 align=left>
            <tr>
              <td>Username</td>
              <td><input type='text' name='username'></td>
            </tr>
            <tr>
              <td>Password</td>

              <td><input type='password' name='password'></td>
            </tr>
            <tr>
              <td colspan=2 align='right'><input name='submit' type='submit' value='Login'></td>
            </tr>");

if($auth_message) {
  echo("<tr><td colspan=2><span style='font-weight: bold; color: #cc0000;'>$auth_message</span></td></tr>");
}

echo("          </table>
      </form></td>
    </tr>
  </table>
</center>");

if($config['login_message']) {
  echo("<div style='text-align: center; font-weight: bold; color: #cc0000; width=300px;'>".$config['login_message']."</div>");
}

?>
