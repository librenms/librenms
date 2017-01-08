#!/bin/sh

curl --silent https://getcomposer.org/installer | php
php composer.phar install

# phpamqplib:phpamqplib_password has full access to phpamqplib_testbed

sudo rabbitmqctl add_vhost phpamqplib_testbed
sudo rabbitmqctl add_user phpamqplib phpamqplib_password
sudo rabbitmqctl set_permissions -p phpamqplib_testbed phpamqplib ".*" ".*" ".*"
