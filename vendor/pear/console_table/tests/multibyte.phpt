--TEST--
Multibyte strings
--FILE--
<?php

if (file_exists(dirname(__FILE__) . '/../Table.php')) {
    require_once dirname(__FILE__) . '/../Table.php';
} else {
    require_once 'Console/Table.php';
}

$table = new Console_Table();
$table->setHeaders(array('Schön', 'Häßlich'));
$table->addData(array(array('Ich', 'Du'), array('Ä', 'Ü')));
echo $table->getTable();

$table = new Console_Table();
$table->addRow(array("I'm from 中国"));
$table->addRow(array("我是中国人"));
$table->addRow(array("I'm from China"));
echo $table->getTable();

?>
--EXPECT--
+-------+---------+
| Schön | Häßlich |
+-------+---------+
| Ich   | Du      |
| Ä     | Ü       |
+-------+---------+
+----------------+
| I'm from 中国  |
| 我是中国人     |
| I'm from China |
+----------------+
