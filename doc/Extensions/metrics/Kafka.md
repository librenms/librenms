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
- In case of debian, install at system level the librdkafka-dev package, or equivalent for your OS

The setup of the above is completely out of scope here and we aren't
really able to provide any help with this side of things.

## What you don't get

- Support for Kafka, we would highly recommend that you
  have some level of experience with these.

RRD will continue to function as normal so LibreNMS itself should
continue to function as normal.

## Configuration

!!! installing required packages
```bash
lnms plugin:add idealo/php-rdkafka-ffi
lnms plugin:add ext-ffi
```

!!! available setting "poller/kafka"
```bash
lnms config:set kafka.enable true
lnms config:set kafka.debug false
lnms config:set kafka.security.debug 'security'
lnms config:set kafka.broker.list 'kafka:9092'
lnms config:set kafka.idempotence true
lnms config:set kafka.topic 'librenms'
lmns config:set kafka.groups-exclude "group_name_1,group_name_2"
lmns config:set kafka.measurement-exclude "measurement_name_1,measurement_name_2"
lmns config:set kafka.device-fields-exclude "device_id,ip"
lnms config:set kafka.ssl.enable true
lnms config:set kafka.ssl.protocol 'ssl'
lnms config:set kafka.ssl.ca.location '/etc/kafka/secrets/ca-cert'
lnms config:set kafka.ssl.certificate.location '/etc/kafka/secrets/cert.pem'
lnms config:set kafka.ssl.key.location '/etc/kafka/secrets/cert.key'
lnms config:set kafka.ssl.key.password 'pass'
lnms config:set kafka.ssl.keystore.location '/etc/kafka/secrets/keystore.jks'
lnms config:set kafka.ssl.keystore.password 'pass'
lnms config:set kafka.flush.timeout 1000
```

!!! setting example with ssl "poller/kafka"
```bash
lnms config:set kafka.enable true
lnms config:set kafka.broker.list 'kafka:9092'
lnms config:set kafka.idempotence true
lnms config:set kafka.topic 'librenms'
lmns config:set kafka.device-fields-exclude "device_id,ip"
lnms config:set kafka.ssl.enable true
lnms config:set kafka.ssl.protocol 'ssl'
lnms config:set kafka.ssl.ca.location '/etc/kafka/secrets/ca-cert'
lnms config:set kafka.ssl.keystore.location '/etc/kafka/secrets/keystore.jks'
lnms config:set kafka.ssl.keystore.password 'pass'
lnms config:set kafka.flush.timeout 1000
```

!!! setting example without ssl "poller/kafka"
```bash
lnms config:set kafka.enable true
lnms config:set kafka.broker.list 'kafka:9092'
lnms config:set kafka.idempotence true
lnms config:set kafka.topic 'librenms'
lmns config:set kafka.device-fields-exclude "device_id,ip"
lnms config:set kafka.flush.timeout 1000
```

For more information about the configuration, please consult https://github.com/confluentinc/librdkafka/blob/master/CONFIGURATION.md

The same data stored within rrd will be sent to Kafka and
recorded. You can then create graphs within Grafana or Kafka to display the
information you need.

Please note that polling will slow down when the poller isn't able to reach or write data to Kafka.

# Kafka Data Store Testing

This document describes how to test the Kafka data store functionality in LibreNMS.

## Prerequisites

Before running Kafka tests, you need to set up the following components:

### 1. System Dependencies

Install the required system packages:

```bash
# On Ubuntu/Debian
sudo apt update
sudo apt install librdkafka-dev libffi-dev
```

### 2. Composer Dependencies

Install the required PHP packages:

```bash
composer require idealo/php-rdkafka-ffi --dev
composer require ext-ffi --dev
```

### 3. Kafka Instance

You need a running Kafka instance. The tests expect it to be available at `localhost:9092`.

#### Option A: Using Docker

```bash
# Start Kafka with Docker Compose
docker run -d \
  --name kafka \
  -p 9092:9092 \
  -e KAFKA_ZOOKEEPER_CONNECT=zookeeper:2181 \
  -e KAFKA_ADVERTISED_LISTENERS=PLAINTEXT://localhost:9092 \
  -e KAFKA_OFFSETS_TOPIC_REPLICATION_FACTOR=1 \
  confluentinc/cp-kafka:latest
```

#### Option B: Local Installation

Follow the [Apache Kafka Quickstart Guide](https://kafka.apache.org/quickstart) to install and run Kafka locally.

## Running Kafka Tests

The Kafka tests are tagged with the `external-dependencies` group and are excluded from the default test suite.

### Run Kafka Tests Only

```bash
# Run only Kafka tests
./vendor/bin/phpunit --group external-dependencies

# Or specifically target the Kafka test class
./vendor/bin/phpunit tests/Unit/Data/KafkaDBStoreTest.php
```

### Run All Tests Including Kafka

```bash
# Run all tests (including external dependencies)
./vendor/bin/phpunit --no-exclude-group
```

## Test Configuration

The tests automatically configure Kafka settings during setup:

- **Broker**: `localhost:9092`
- **Topic**: `librenms`
- **Batch settings**: Max 25 messages, buffer max 10 messages
- **Linger time**: 5000ms
- **Required acks**: 0 (fire and forget)

## Troubleshooting

### Common Issues

1. **FFI Extension Not Available**
   ```bash
   # Verify FFI is enabled
   php -i | grep ffi
   ```

2. **Kafka Connection Failed**
   ```bash
   # Check if Kafka is running
   netstat -tlnp | grep :9092
   
   # Test Kafka connectivity
   telnet localhost 9092
   ```

3. **Missing librdkafka**
   ```bash
   # On Ubuntu/Debian
   # Verify librdkafka is installed
   apt list --installed | grep librdkafka
   ```

### Debug Mode

Enable debug logging in your test environment:

```php
Config::set('kafka.debug', 'all');
```

## Notes

- Kafka tests use an actual Kafka connection during unit testing
- The `external-dependencies` group allows you to easily include/exclude these tests