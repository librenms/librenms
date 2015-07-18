<?php

/*
sysDescr = "Dell Networking OS
Operating System Version: 2.0
Application Software Version: 9.7(0.0P4)
Series: S4810
Copyright (c) 1999-2015 by Dell Inc. All Rights Reserved.
Build Time: Mon May 4 20:52:56 2015";
*/

list(,,$version,$hardware,,) = explode(PHP_EOL, $poll_device['sysDescr']);
list(,$version) = explode(': ',$version);
list(,$hardware) = explode(': ',$hardware);
