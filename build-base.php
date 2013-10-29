<?php

include( "config.php" );

$sql_file   = 'build.sql';
$sql_fh     = fopen( $sql_file, 'r' );
$connection = mysql_connect( $config['db_host'], $config['db_user'], $config['db_pass'] );
mysql_select_db( $config['db_name']  );

while( !feof( $sql_fh ) ) {
  $line     = fgetss( $sql_fh );
  $creation = mysql_query( $line );
  if( !$creation ) {
    echo( mysql_error() . "\n" );
  }
}

fclose($sql_fh);

?>
