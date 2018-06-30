<?php

// Version is the last word in the sysDescr's first line
list($version) = explode("\r", substr($device['sysDescr'], (strpos($device['sysDescr'], 'Release') + 8)));
