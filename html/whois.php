<?php


$output = `/usr/bin/whois $_GET[query] | grep -v \%`;

$output = trim($output);

echo("<pre>$output</pre>");

?>
