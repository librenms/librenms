--TEST--
Border: custom border character
--FILE--
<?php
error_reporting(E_ALL | E_NOTICE);
if (file_exists(dirname(__FILE__) . '/../Table.php')) {
    require_once dirname(__FILE__) . '/../Table.php';
} else {
    require_once 'Console/Table.php';
}
$table = new Console_Table(CONSOLE_TABLE_ALIGN_LEFT, '.');
$table->setHeaders(array('City', 'Mayor'));
$table->addRow(array('Leipzig', 'Major Tom'));
$table->addRow(array('New York', 'Towerhouse'));

echo $table->getTable();
?>
--EXPECT--
.........................
. City     . Mayor      .
.........................
. Leipzig  . Major Tom  .
. New York . Towerhouse .
.........................
