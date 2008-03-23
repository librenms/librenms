#!/bin/bash
./discovery.php
./discover-cdp.php
./cleanup.php
./generate-map.sh
./update-interface.php
./check-errors.php
