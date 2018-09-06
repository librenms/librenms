<?php

// Call runDiscovery with dependency injection to resolve OS
app()->call('LibreNMS\Device\Processor::runDiscovery');
