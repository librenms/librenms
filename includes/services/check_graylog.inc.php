<?php
// For use with Graylog2 plugin by Catinello found at https://marketplace.graylog.org/addons/9ee98819-804e-41c3-b0ac-6ca7975c1a48
// To use on Centos first install go and then the graylog plugin with
// $ sudo yum install golang
// $ go get github.com/catinello/nagios-check-graylog2
// $ sudo mv $GOPATH/bin/nagios-check-graylog2 /usr/lib64/nagios/plugins/check_graylog
// example parameters: -l https://graylog1.example.com:9000/api -insecure -p password -u username
$check_cmd = $config['nagios_plugins'] . "/check_graylog ".$service['service_param'];
