# Notes On Application Development

## LibreNMS JSON SNMP Extends

The polling function `json_app_get` makes it easy to poll complex data
using SNMP extends and JSON.

The following exceptions are provided by it.

It takes three parameters, in order in the list below.

- Integer :: Device ID to fetch it for.
- String :: The extend name. For example, if 'zfs' is passed it will
  be converted to 'nsExtendOutputFull.3.122.102.115'.
- Integer :: Minium expected version of the JSON return.

The required keys for the returned JSON are as below.

- version :: The version of the snmp extend script. Should be numeric
  and at least 1.
- error :: Error code from the snmp extend script. Should be > 0
   (0 will be ignored and negatives are reserved)
- errorString :: Text to describe the error.
- data :: An key with an array with the data to be used.

The supported exceptions are as below.

- JsonAppPollingFailedException :: Empty return from SNMP.
- JsonAppParsingFailedException :: Could not parse the JSON
- JsonAppBlankJsonException :: Blank JSON.
- JsonAppMissingKeysException :: Missing required keys.
- JsonAppWrongVersionException :: Older version than supported.
- JsonAppExtendErroredException :: Polling and parsing was good, but
  the returned data has an error set. This may be checked via
  $e->getParsedJson() and then checking the keys error and
  errorString.

The error value can be accessed via $e->getCode(). The output can be
accessed via $->getOutput() Only returned
JsonAppParsingFailedException. The parsed JSON can be access via
$e->getParsedJson().

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

## Application Data Storage

The variable `data` can be used to store arrays via the Application
model.

```
use App\Models\Application;

// find the app in question via it's ID integer
$app = Application::find($app_id);

// set the varaible data to $foo
$app->data = [
    'item_A' => 123,
    'item_B' => 4.5,
    'type' => 'foo',
    'other_items' => [ 'a', 'b', 'c' ],
];

// save the change
$app->save()

// var_dump the contents of the variable
var_dump($app->data);
```

When writing app pollers, the Application model for the app in
question is made available as `$app_model` with the app data made
available as `$app_data`, which is always be a array, initializing it
as a array if needed. Any changes to `$app_data` will be saved
automatically after the poller has returned.
