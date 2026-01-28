## Time Concepts

Most times in LibreNMS are absolute points in time when data has been collected.  What this means is that midnight UTC is the same as 8pm in the -0400 timezone, and 8am in +0800.  When dealing with these points in time, the preference is:

- Dates being manipulated in PHP should always use the timezone configured in PHP.
  - They can be stored in the database as bigint unix epoch values
  - They can be stored in the database as datetime fields, using the timezone configured for the OS and PHP.
  - It is best practice to use UTC for PHP and the OS, otherwise there can be ambigous parsing of times around the daylight savings boundaries.
- Dates being encoded in JSON on the URL should always use either the unix epoch or the ISO8601 representation in Zulu/UTC timezone.
- Dates being displayed in HTML pages should be converted to the timezone that the user has selected (browser timezone by default).
- Dates being parsed from user input should be interpreted using the user's selected timezone, and converted to a JSON/URL encoded format.

There will be some exceptions to the above, for example scheduled maintenance where the server timezone observes daylight savings differently to the devices being scheduled.  When this happens, we should store the timezone along with the time information so we can interpret the time correctly relative to ths server's time.

## PHP Time Functions

LibreNMS has a set of time functions that should be used for all point in time operations to ensure consistent handling of times.  These are in the `LibreNMS\Util\Time` class, and functions can either be called fully qualified or as shown below after adding `use LibreNMS\Util\Time;` to the top of your PHP file.

The following functions all return a Carbon object representing a point in time in the PHP timezone:
- `Time::now()` - This takes no input arguments and will return the current time.
- `Time::fromTimestamp()` - This will take an integer representing the unix epoch as input.
- `Time::parse()` - This will take a string as input.  This will correctly interpret:
  - ISO8601 times with "Z" at the end as UTC times
  - ISO8601 times with a UTC offset (-1200 to +1200) at the end
  - Datetime fields from the database with no UTC offset (assumes the time from the database is in the PHP timezone)

The following functions all take a Carbon object generated above, and change it to the correct output format:
- `Time::toTimestamp()` - Outputs a unix epoch in seconds
- `Time::toIso()` - Outputs a string in ISO8601 representation in Zulu/UTC timezone.
- `Time::format()` - Takes both a Carbon object and a format string as inputs, and outputs the time in the user's selected timezone using the format string.

## Javascript Time Library

LibreNMS uses the moment-timezone javascript library for cases where we need to manipulate times in Javascript. This is generally needed if you want to convert an input date and time into a unix epoch on the client side for submitting in a URL or AJAX query.

To use the library, you will need to include the following in the script section of a laravel page:
```
<script src="{{ asset('js/RrdGraphJS/moment-timezone-with-data.js') }}"></script>
```

You will also need to make sure the user's timezone is sent to the page as part of the page data:
```php
return view('page.view', [
    'tz' => session('preferences.timezone'),
...
]);
```

It is a good idea to save the timezone into a variable:
```js
timezone="{{ $tz }}"
```

Now, when you want to parse a time using the timezone, you can use the moment-timezone library as shown below.  The input can be a string for moment to parse, or a unix epoch.
```js
usertime = moment.tz(input, timezone);
```

If the input was a unix epoch or a UTC time, you can use moment's format() function to print the string representation of the date in the chosen timezone.

The moment object can always output an ISO8601 date by using the `.toISOString()` method.

The moment object can always output a unix epoch by using the `.unix()` method.
