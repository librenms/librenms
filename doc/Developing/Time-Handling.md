## Time Concepts

Most times in LibreNMS are absolute points in time when data has been collected.  What this means is that midnight UTC is the same as 8pm in the -0400 timezone, and 8am in +0800.  When dealing with these points in time, the preference is:

- Date objects being manipulated in PHP should use Carbon objects that contain timezone information. When being saved they should use one of the following:
  - bigint unix epoch values.
  - timestamp fields, which have provisions on the SQL server side for converting to the appropriate timezone when read.
- Dates being encoded in the URL should use the unix epoch.
- Dates being encoded in JSON should use the ISO8601 representation in Zulu/UTC timezone, and the javascript converter to display in the correct timezone.
- Dates being displayed in HTML pages should be converted to the timezone that the user has selected (browser timezone by default).
- Dates being parsed from user input should be interpreted using the user's selected timezone, and converted to a JSON/URL encoded format.

There will be some exceptions to the above, for example scheduled maintenance where the user intends the maintenance window to start at 9pm every night.  When this happens, we should store the timezone along with the time information so we can interpret the time correctly relative to the intended timezone.

Some additional noted on database fields:
- datetime fields are not normally acceptable because they are not timezone aware. This creates issues near the boundaries of daylight savings as well as assumptions about timezone when parsing.
- timestamp fields currently have a maximum date in 2106, and can store times with a granularity of microseconds (seconds by default).
- unix epoch fields have a granulariy of 1 second.

## PHP Time Functions

LibreNMS uses the Carbon library for date handling.  The following functions should be used to generate new time objects:
- `Carbon::now()` - This takes no input arguments and will return the current time.
- `Carbon::createFromTimestamp()` - This will take an integer representing the unix epoch as input.
- `Carbon::parse($time_string)` - This will take a string as input.  This will correctly interpret:
  - ISO8601 times with "Z" at the end as UTC times
  - ISO8601 times with a UTC offset (-1200 to +1200) at the end
  - Datetime fields from the database with no UTC offset (assumes the time is in the PHP timezone)

The following methods should be used on Carbon objects to convert them to unix epoch timestamps or ISO8601 Zulu time strings:
- `$object->unix()`
- `$object->toIso8601ZuluString()`

The following function has been created for formatting dates on web pages, but are considered legacy because a better solution exists using JSON to fetch the data from an AJAX endpoint and then using the javascript formatting functions explained further down this page to format the time. This allows dates to be formatted using the locale of the end user (e.g. dd/mm/yy vs mm/dd/yy):
- `Time::format()` - Takes both a Carbon object and a format string as inputs, and outputs the time in the user's selected timezone using the format string.

When using the `Time::format()` function, you should choose from one of the following config options for chooing a date format.  An example of the default output is shown next to each:
 - `dateformat.long` - Wed, 04 Feb 2026 09:25:00 +0800
 - `dateformat.compact` - 2026-02-04 09:25:00
 - `dateformat.byminute` - 2026-02-04 09:25
 - `dateformat.time` - 09:25:00

### Examples

If you have a timestamp field from the database that you want to display on a web page, the following code would be needed:
```php
use App\Facades\LibrenmsConfig;
use LibreNMS\Util\Time;

$output = Time::format($dbtime, LibrenmsConfig::get('dateformat.long'));
```

If you have a unix epoch input that you want to display on a web page, the following code would be needed:
```php
use App\Facades\LibrenmsConfig;
use LibreNMS\Util\Time;

$output = Time::format(Time::fromTimestamp($epoch), LibrenmsConfig::get('dateformat.compact'));
```

If you receive a ISO8601 date as part of data posted from an AJAX query, and want to convert it to a unix epoch to use in a SQL filter, you would do the following:
```php
use LibreNMS\Util\Time;

$epoch = Time::parse($iso8601_date)->unix();
```

If you have a timestamp field from the database that you want to send to an AJAX endpoint as ISO8601 time, you would do the following:
```php
$jsontime = $dbtime->toIso8601ZuluString();
```

## Javascript Time Library

### User input

LibreNMS uses the moment-timezone javascript library to parse user input times in Javascript.  To use the library, you will need to include the following in the script section of a laravel page:
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
