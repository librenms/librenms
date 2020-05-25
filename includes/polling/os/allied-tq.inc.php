<?php

/*Wireless support for
AT-TQ2403, AT-TQ2403EX, AT-TQ2450, AT-TQ3600,
AT-TQ3200, AT-TQ3400, AT-TQ4400, AT-TQ4600, AT-TQ4400e

Use sysDescr to get Hardware, SW version*/

list($hardware,$b,$version) = explode(' ', $device['sysDescr']);
