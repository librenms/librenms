# jQuery Mouse Wheel Plugin

A [jQuery](http://jquery.com/) plugin that adds cross-browser mouse wheel support with delta normalization.

In order to use the plugin, simply bind the `mousewheel` event to an element.

It also provides two helper methods called `mousewheel` and `unmousewheel`
that act just like other event helper methods in jQuery.

The event object is updated with the normalized `deltaX` and `deltaY` properties.
In addition there is a new property on the event object called `deltaFactor`. Multiply
the `deltaFactor` by `deltaX` or `deltaY` to get the scroll distance that the browser
has reported.

Here is an example of using both the bind and helper method syntax:

```js
// using on
$('#my_elem').on('mousewheel', function(event) {
    console.log(event.deltaX, event.deltaY, event.deltaFactor);
});

// using the event helper
$('#my_elem').mousewheel(function(event) {
    console.log(event.deltaX, event.deltaY, event.deltaFactor);
});
```

The old behavior of adding three arguments (`delta`, `deltaX`, and `deltaY`) to the
event handler is now deprecated and will be removed in later releases.


## See it in action
[See the tests on Github](http://brandonaaron.github.io/jquery-mousewheel/test).

## Using with [Browserify](http://browserify.org)

Support for browserify is baked in.

```bash
npm install jquery-mousewheel
npm install jquery-browserify
```

In your server-side node.js code:

```js
var express = require('express');
var app = express.createServer();

app.use(require('browserify')({
    require : [ 'jquery-browserify', 'jquery-mousewheel' ]
}));
```

In your browser-side javascript:

```js
var $ = require('jquery-browserify');
require('jquery-mousewheel')($);
```

## License

This plugin is licensed under the [MIT License](LICENSE.txt).

Copyright (c) 2013 [Brandon Aaron](http://brandon.aaron.sh)
