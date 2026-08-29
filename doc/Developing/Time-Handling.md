## Time Concepts

Most times in LibreNMS are absolute points in time. They mark the
collection of data. Midnight UTC is therefore the same moment as 8 pm
in the -0400 timezone and 8 am in the +0800 timezone. For these points
in time, use these rules:

- A date object in PHP uses a Carbon object with timezone information.
  Save it in one of these forms:
  - bigint unix epoch values.
    - timestamp fields. The SQL server converts them to the correct
    timezone at each read.
- A date in a URL uses the unix epoch.
- A date in JSON uses the ISO8601 form in the Zulu (UTC) timezone. The
  JavaScript converter then shows it in the correct timezone.
- A date on an HTML page uses the timezone of the user. The default is
  the browser timezone.
- A date from user input uses the selected timezone of the user.
  Convert it to the JSON format or the URL format.

There are exceptions to these rules. One example is a scheduled
maintenance with a window that starts at 9 pm each night. Store the
timezone with the time information for such a case. The time is then
correct for the intended timezone.

Notes on the database fields:
- Do not use datetime fields. They hold no timezone. They cause
  problems at a daylight saving boundary. They also force an assumption
  about the timezone at the parse.
- timestamp fields have a maximum date in 2106. They store times with a
  granularity of microseconds. The default granularity is seconds.
- unix epoch fields have a granularity of 1 second.

## PHP Time Functions

LibreNMS uses the Carbon library for date handling. Use these functions
to generate a new time object:
- `Carbon::now()` - it takes no argument and returns the current time.
- `Carbon::createFromTimestamp()` - it takes an integer unix epoch
  value.
- `Carbon::parse($time_string)` - it takes a string. It reads these
  forms correctly:
  - ISO8601 times with "Z" at the end as UTC times
  - ISO8601 times with a UTC offset (-1200 to +1200) at the end
    - datetime fields from the database without a UTC offset. It assumes
    the PHP timezone

Use these methods on a Carbon object. They convert it to a unix epoch
timestamp or to an ISO8601 Zulu time string:
- `$object->unix()`
- `$object->toIso8601ZuluString()`

The function below formats a date on a web page. It is legacy code. A
better method gets the data from an AJAX endpoint in JSON. The
JavaScript formatting functions later on this page then format the
time. The date then uses the locale of the user, for example dd/mm/yy
or mm/dd/yy:
- `Time::format()` - it takes a Carbon object and a format string. It
  returns the time in the selected timezone of the user, in that
  format.

With the `Time::format()` function, use one of these configuration
options for the date format. An example of the default output follows
each option:
 - `dateformat.long` - Wed, 04 Feb 2026 09:25:00 +0800
 - `dateformat.compact` - 2026-02-04 09:25:00
 - `dateformat.byminute` - 2026-02-04 09:25
 - `dateformat.time` - 09:25:00

### Examples

To show a timestamp field from the database on a web page, use this
code:
```php
use App\Facades\LibrenmsConfig;
use LibreNMS\Util\Time;

$output = Time::format($dbtime, LibrenmsConfig::get('dateformat.long'));
```

To show a unix epoch input on a web page, use this code:
```php
use App\Facades\LibrenmsConfig;
use LibreNMS\Util\Time;

$output = Time::format(Time::fromTimestamp($epoch), LibrenmsConfig::get('dateformat.compact'));
```

An AJAX query can post an ISO8601 date. To convert this date to a unix
epoch for a SQL filter, use this code:
```php
use LibreNMS\Util\Time;

$epoch = Time::parse($iso8601_date)->unix();
```

To send a database timestamp field to an AJAX endpoint as an ISO8601
time, use this code:
```php
$jsontime = $dbtime->toIso8601ZuluString();
```

## Javascript Time Library

### User input

LibreNMS uses the moment-timezone JavaScript library to parse the times
from user input. Add this line to the script section of a Laravel page:
```
<script src="{{ asset('js/RrdGraphJS/moment-timezone-with-data.js') }}"></script>
```

Now, when you want to parse a time using the timezone, you can use the moment-timezone library as shown below.  The input can be a string for moment to parse, or a unix epoch.
```js
usertime = moment.tz(input, window.tz);
```

If the input was a unix epoch or a UTC time, you can use moment's format() function to print the string representation of the date in the chosen timezone.

The moment object can always output an ISO8601 date by using the `.toISOString()` method.

The moment object can always output a unix epoch by using the `.un.ix()` method.

### AJAX queries

For AJAX queries, we have a converter function in the librenms javascript library.  This is available for all pages, and can be used as follows assuming that the input date is in ISO8601 format:
```js
datestring = LibreNMS.Time.format(isoDate);
```

If you are using a data table, it can look like this:
```
_Need an example using data-converter_
```
