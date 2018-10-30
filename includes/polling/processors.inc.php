<?php

use LibreNMS\Device\Processor;
use LibreNMS\OS;

Processor::poll(OS::make($device));
