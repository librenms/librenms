--TEST--
Border: disable it
--FILE--
<?php
error_reporting(E_ALL | E_NOTICE);
if (file_exists(dirname(__FILE__) . '/../Table.php')) {
    require_once dirname(__FILE__) . '/../Table.php';
} else {
    require_once 'Console/Table.php';
}
$table = new Console_Table();
$table->setHeaders(array('City', 'Mayor'));
$table->addRow(array('Leipzig', 'Major Tom'));
$table->addRow(array('New York', 'Towerhouse'));

$table->setBorderVisibility(
    array(
        'left'  => false,
        'right' => false,
    )
);
echo "Horizontal borders only:\n";
echo $table->getTable() . "\n";

$table->setBorderVisibility(
    array(
        'top'    => false,
        'right'  => false,
        'bottom' => false,
        'left'   => false,
        'inner'  => false,
    )
);
echo "No borders:\n";
echo $table->getTable() . "\n";

$table->setBorderVisibility(
    array(
        'top'    => false,
        'right'  => true,
        'bottom' => false,
        'left'   => true,
        'inner'  => true,
    )
);
echo "Vertical and inner only:\n";
echo $table->getTable() . "\n";
?>
--EXPECT--
Horizontal borders only:
---------+-----------
City     | Mayor     
---------+-----------
Leipzig  | Major Tom 
New York | Towerhouse
---------+-----------

No borders:
City     | Mayor     
Leipzig  | Major Tom 
New York | Towerhouse

Vertical and inner only:
| City     | Mayor      |
+----------+------------+
| Leipzig  | Major Tom  |
| New York | Towerhouse |

