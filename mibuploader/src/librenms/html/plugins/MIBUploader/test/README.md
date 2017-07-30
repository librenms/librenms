# Tests

NEVER RUN TESTS ON A PRODUCTION SERVER: YOU WILL ERASE ALL DATA.

## Setup

You should run tests into a fresh, test dedicated LibreNMS installation. If you cannot automate the thing, you should, at least, use a full database dump before running any test, and reload this dump between each "test session".

## Running tests

Install the plugin as usual, then:

```
cd /opt/librenms/html/plugins/MIBUploader/test
./test.sh
```