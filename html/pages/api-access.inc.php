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

if ($_SESSION['userlevel'] == '10')
{
  echo('
  <table class="table table-bordered table-condensed">
    <tr>
      <th>User</th>
      <th>Token Hash</th>
      <th>Description</th>
    </tr>
');

  foreach (dbFetchRows("SELECT `AT`.*,`U`.`username` FROM `api_tokens` AS AT JOIN users AS U ON AT.user_id=U.user_id ORDER BY AT.user_id") as $api)
  {
    echo('
    <tr>
      <td>'.$api['username'].'</td>
      <td>'.$api['token_hash'].'</td>
      <td>'.$api['description'].'</td>
    </tr>
');
  }

  echo('
  </table>
');

} else {
  include("includes/error-no-perm.inc.php");
}

?>
