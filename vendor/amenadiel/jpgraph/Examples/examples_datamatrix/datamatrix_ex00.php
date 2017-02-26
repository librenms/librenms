<?php
require_once('jpgraph/datamatrix/datamatrix.inc.php');

$data = 'The first datamatrix';

$encoder = DatamatrixFactory::Create();
$backend = DatamatrixBackendFactory::Create($encoder);
$backend->Stroke($data);
?>
