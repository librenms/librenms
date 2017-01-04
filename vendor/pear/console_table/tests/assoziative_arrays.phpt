--TEST--
Header and data as associative arrays.
--FILE--
<?php

if (file_exists(dirname(__FILE__) . '/../Table.php')) {
    require_once dirname(__FILE__) . '/../Table.php';
} else {
    require_once 'Console/Table.php';
}

$headers = array(
    'one' => 'foo',
    'two' => 'bar'
);

$data = array(
    array(
        'x' => 'baz',
    )
);

$table = new Console_Table();
$table->setHeaders($headers);
$table->addData($data);

echo $table->getTable();

?>
--EXPECT--
+-----+-----+
| foo | bar |
+-----+-----+
| baz |     |
+-----+-----+
