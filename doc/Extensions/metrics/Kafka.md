# Enabling support for Kafka

Kafka support is alpha quality. It only sends the data to a Kafka brocker topic.
Kafka changes often, so we cannot guarantee the integrity of your data.
Use this support at your own risk.

Kafka supports only the PHP Kafka Client of librdkafka version 2.0 and
later. For any other Kafka version, change the source code.

## Requirements

- Extensions FFI and xmlwriter enabled
- In case of debian, install at system level the librdkafka-dev package, or equivalent for your OS

This document does not describe the setup of these components. We
cannot help with them.

## What you do not get

- Support for Kafka. You need experience with this tool.

RRD continues to work in the normal way. LibreNMS therefore also
continues to work in the normal way.

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

For more information about the configuration, read
https://github.com/confluentinc/librdkafka/blob/master/CONFIGURATION.md

LibreNMS sends the same data from rrd to Kafka, and Kafka records it.
You can then create graphs in Grafana or in Kafka for the information
that you need.

Note: the polling becomes slower when the poller cannot reach Kafka or
cannot write data to it.

# Kafka Data Store Testing

This document describes the tests of the Kafka data store in LibreNMS.

## Prerequisites

The Kafka tests need these components:

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

You need a running Kafka instance. The tests use `localhost:9092`.

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

To install and run Kafka on your machine, obey the [Apache Kafka
Quickstart Guide](https://kafka.apache.org/quickstart).

## Running Kafka Tests

The Kafka tests have the tag `external-dependencies`. The default test
suite excludes them.

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

- The Kafka tests use a real Kafka connection in the unit tests
- The `external-dependencies` group includes and excludes these tests