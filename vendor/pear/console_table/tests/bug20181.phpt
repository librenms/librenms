--TEST--
Bug #20181: setAlign() on non-zero column
--FILE--
<?php
error_reporting(E_ALL | E_NOTICE);
if (file_exists(dirname(__FILE__) . '/../Table.php')) {
    require_once dirname(__FILE__) . '/../Table.php';
} else {
    require_once 'Console/Table.php';
}
$table = new Console_Table();
$table->setAlign(1, CONSOLE_TABLE_ALIGN_RIGHT);
$table->setHeaders(array('f', 'bar'));
$table->addRow(array('baz', 'b'));

echo $table->getTable();
?>
--EXPECT--
+-----+-----+
| f   | bar |
+-----+-----+
| baz |   b |
+-----+-----+
