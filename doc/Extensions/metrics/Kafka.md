# Enabling support for Kafka

Before we get started it is important that you know and understand
that Kafka support is currently alpha at best. All it provides is
the sending of data to a Kafka brocker topic. Due to the current changes
that are constantly being made to Kafka itself then we cannot
guarantee that your data will be ok so enabling this support is at
your own risk!

It is also important to understand that Kafka only supports the
PHP Kafka Client used in librdkafka version 2.0 or higher. If you are
looking to send data to any other version of Kafka than you should adapt the source code.

## Requirements

- Extensions FFI and xmlwriter enabled
- librdkafka
- librdkafka-dev

The setup of the above is completely out of scope here and we aren't
really able to provide any help with this side of things.

## What you don't get

- Support for Kafka, we would highly recommend that you
  have some level of experience with these.

RRD will continue to function as normal so LibreNMS itself should
continue to function as normal.

## Configuration

!!! setting "poller/kafka"
```bash
lnms config:set kafka.enable true
lnms config:set kafka.debug false
lnms config:set kafka.security.debug 'security'
lnms config:set kafka.broker.list 'kafka:9092'
lnms config:set kafka.idempotence true
lnms config:set kafka.topic 'librenms'
lmns config:set kafka.groups-exclude ["group_name_1","group_name_2"]
lnms config:set kafka.ssl.enable true
lnms config:set kafka.ssl.protocol 'ssl'
lnms config:set kafka.ssl.ca.location '/etc/kafka/secrets/ca-cert'
lnms config:set kafka.ssl.certificate.location '/etc/kafka/secrets/cert.pem'
lnms config:set kafka.ssl.key.location '/etc/kafka/secrets/cert.key'
lnms config:set kafka.ssl.key.password 'pass'
lnms config:set kafka.ssl.keystore.location '/etc/kafka/secrets/keystore.jks'
lnms config:set kafka.ssl.keystore.password 'pass'
lnms config:set kafka.flush.timeout 50
```

For more information about the configuration, please consult https://github.com/confluentinc/librdkafka/blob/master/CONFIGURATION.md

The same data stored within rrd will be sent to Kafka and
recorded. You can then create graphs within Grafana or Kafka to display the
information you need.

Please note that polling will slow down when the poller isn't able to reach or write data to Kafka.