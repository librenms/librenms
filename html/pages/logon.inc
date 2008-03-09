<?php

    echo("
  <center><br />
<table  border='0' cellpadding='20' cellspacing='20' bgcolor='#e8e8e8'>
    <tr>
      <td><div align='right'><img src='/images/password.png' width='128' height='128'></div></td>
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
            </tr>
            <tr>
              <td colspan=2 align='right'>Demo login : demo/demo</td>
            </tr>
          </table>
      </form></td>
    </tr>
  </table>
</center>");

?>
