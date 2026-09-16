# Notes On Application Development

## LibreNMS JSON SNMP Extends

The polling function `json_app_get` polls complex data with SNMP
extends and JSON.

It supplies the exceptions below.

It takes three parameters, in this order:

- Integer :: the device ID of the data.
- String :: the extend name. For example, 'zfs' becomes
  'nsExtendOutputFull.3.122.102.115'.
- Integer :: the minimum version of the JSON return.

The returned JSON needs these keys:

- version :: the version of the SNMP extend script. It is a number and
  it is at least 1.
- error :: the error code of the SNMP extend script. It is more than 0.
  LibreNMS ignores 0, and the negative values are reserved.
- errorString :: the description of the error.
- data :: a key with an array of the data.

These exceptions are available:

- JsonAppPollingFailedException :: Empty return from SNMP.
- JsonAppParsingFailedException :: the JSON parse failed.
- JsonAppBlankJsonException :: Blank JSON.
- JsonAppMissingKeysException :: a required key is absent.
- JsonAppWrongVersionException :: the version is too old.
- JsonAppExtendErroredException :: the polling and the parsing were
  correct, but the returned data holds an error. Read the error with
  `$e->getParsedJson()`. Then read the keys `error` and `errorString`.

`$e->getCode()` gives the error value. `$e->getOutput()` gives the
output. Only JsonAppParsingFailedException returns the output.
`$e->getParsedJson()` gives the parsed JSON.

An example below from `includes/polling/applications/zfs.inc.php`...

```php
try {
    $zfs = json_app_get($device, $name, 1)['data'];
} catch (JsonAppMissingKeysException $e) {
    //old version with out the data key
    $zfs = $e->getParsedJson();
} catch (JsonAppException $e) {
    echo PHP_EOL . $name . ':' . $e->getCode() . ':' . $e->getMessage() . PHP_EOL;
    update_application($app, $e->getCode() . ':' . $e->getMessage(), []);

    return;
}
```

### Compression

`json_app_get` also accepts compressed data as base64-encoded gzip. At
a base64 encoding in the SNMP return, it decompresses the data and then
parses it.

`https://github.com/librenms/librenms-agent/blob/master/utils/librenms_return_optimizer`
optimizes the JSON returns.

## Application Data Storage

Each application poller and graph gets the `$app` model. Read and
update the `$app->data` field to store arrays of data in the
Application model.

`update_application()` saves the `$app` model with each change to the
data field.

```
// set the variable data to $foo
$app->data = [
    'item_A' => 123,
    'item_B' => 4.5,
    'type' => 'foo',
    'other_items' => [ 'a', 'b', 'c' ],
];

// save the change
$app->save();

// var_dump the contents of the variable
var_dump($app->data);
```
