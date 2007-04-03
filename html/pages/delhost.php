<?php

if($_POST['id'] && $_SESSION['userlevel'] > '9') {
  delHost($id);  
} elseif ($_POST['id']) {
echo("<p class='errorbox'><b>Error:</b> You don't have the necessary privileges to remove hosts.</p>");  
}

?>

<form name="form1" method="post" action="/?page=delhost">
  <h1>Delete Host</h1>
  <br />
  <p><select name="id">
<?php

$query = mysql_query("SELECT id,hostname FROM `devices` ORDER BY `hostname`");

while($data = mysql_fetch_array($query)) {

  echo("<option value='$data[id]'>$data[hostname]</option>");

}

?>
    </select>
  
    <input type="submit" name="Submit" value="Delete Host">
</p>
</form>

