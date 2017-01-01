--TEST--
Horizontal rules
--FILE--
<?php

if (file_exists(dirname(__FILE__) . '/../Table.php')) {
    require_once dirname(__FILE__) . '/../Table.php';
} else {
    require_once 'Console/Table.php';
}

$data = array(
    array('one', 'two'),
    CONSOLE_TABLE_HORIZONTAL_RULE,
    array('three', 'four'),
    CONSOLE_TABLE_HORIZONTAL_RULE,
    CONSOLE_TABLE_HORIZONTAL_RULE,
    array('five', 'six'),
    array('seven', 'eight'),
);

$table = new Console_Table();
$table->setHeaders(array('foo', 'bar'));
$table->addData($data);
$table->addSeparator();
echo $table->getTable();
echo "=========================\n";

$table = new Console_Table(CONSOLE_TABLE_ALIGN_LEFT, '');
$table->setHeaders(array('foo', 'bar'));
$table->addData($data);
$table->addSeparator();
echo $table->getTable();
echo "=========================\n";

$table = new Console_Table(CONSOLE_TABLE_ALIGN_LEFT, '#', 0);
$table->setHeaders(array('foo', 'bar'));
$table->addData($data);
$table->addSeparator();
echo $table->getTable();

?>
--EXPECT--
+-------+-------+
| foo   | bar   |
+-------+-------+
| one   | two   |
+-------+-------+
| three | four  |
+-------+-------+
+-------+-------+
| five  | six   |
| seven | eight |
+-------+-------+
+-------+-------+
=========================
 foo    bar   
 one    two   
 three  four  
 five   six   
 seven  eight 
=========================
#############
#foo  #bar  #
#############
#one  #two  #
#############
#three#four #
#############
#############
#five #six  #
#seven#eight#
#############
#############
