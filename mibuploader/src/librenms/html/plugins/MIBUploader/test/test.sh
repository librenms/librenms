#!/bin/sh
./phpunit-old.phar --bootstrap bootstrap.php --no-globals-backup $* tests
