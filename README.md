jQuery Bootgrid Plugin [![Build Status](http://img.shields.io/travis/rstaib/jquery-bootgrid/master.svg?style=flat-square)](https://travis-ci.org/rstaib/jquery-bootgrid) ![Bower version](http://img.shields.io/bower/v/jquery.bootgrid.svg?style=flat-square) ![NuGet version](http://img.shields.io/nuget/v/jquery.bootgrid.svg?style=flat-square) ![NPM version](http://img.shields.io/npm/v/jquery-bootgrid.svg?style=flat-square) ![Gratipay](http://img.shields.io/gratipay/RafaelStaib.svg?style=flat-square)
============

Nice, sleek and intuitive. A grid control especially designed for bootstrap.

## Getting Started

**jQuery Bootgrid** is a UI component written for **jQuery** and **Bootstrap** (Bootstrap isn't necessarily required).

Everything you need to start quickly is:

1. Include **jQuery**, **jQuery Bootgrid** and **Bootstrap** libraries in your HTML code.
2. Define your table layout and your data columns by adding the `data-column-id` attribute.
3. Specify your data URL used to fill your data table and set ajax option to `true` directly on your table via data API.

```html
<!DOCTYPE html>
<html>
    <head>
        <title>Demo</title>
        <meta charset="utf-8">
        <!-- Styles -->
        <link href="bootstrap.css" rel="stylesheet">
        <link href="jquery.bootgrid.css" rel="stylesheet">
    </head>
    <body>
        <table id="grid" data-toggle="bootgrid" data-ajax="true" data-url="/api/data/basic" class="table table-condensed table-hover table-striped">
            <thead>
                <tr>
                    <th data-column-id="id">ID</th>
                    <th data-column-id="name">Sender</th>
                </tr>
            </thead>
        </table>
        <!-- Scripts -->
        <script src="jquery.js"></script> 
        <script src="jquery.bootgrid.js"></script>
    </body>
</html>
```

> For more information [check the documentation](http://www.jquery-bootgrid.com/Documentation).

### Examples

Examples you find [here](http://www.jquery-bootgrid.com/Examples).

## Reporting an Issue

Instructions will follow soon!

## Asking questions

I'm always happy to help answer your questions. The best way to get quick answers is to go to [stackoverflow.com](http://stackoverflow.com) and tag your questions always with **jquery-bootgrid**.

## Contributing

Instructions will follow soon!

## License

Copyright (c) 2013 Rafael J. Staib Licensed under the [MIT license](https://github.com/rstaib/jquery-bootgrid/blob/master/LICENSE.txt).
