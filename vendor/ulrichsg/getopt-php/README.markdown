Getopt.PHP
==========

Getopt.PHP is a library for command-line argument processing. It supports PHP version 5.3 and above.

Features
--------

* Supports both short (eg. `-v`) and long (eg. `--version`) options
* Option aliasing, ie. an option can have both a long and a short version
* Collapsed short options (eg. `-abc` instead of `-a -b -c`)
* Cumulative options (eg. `-vvv`)
* Options may take optional or mandatory arguments
* Two alternative notations for long options with arguments: `--option value` and `--option=value`
* Collapsed short options with mandatory argument at the end (eg. `-ab 1` instead of `-a -b 1`)

Documentation
-------------

* [Documentation for the current version (2.0+)](http://ulrichsg.github.io/getopt-php/)
* [Legacy documentation (1.4)](https://github.com/ulrichsg/getopt-php/blob/2aa8ab1be57200af4cc51447d2a6c244b75ca70b/README.markdown)

License
-------

Getopt.PHP is published under the [MIT License](http://www.opensource.org/licenses/mit-license.php).

[![Build Status](https://travis-ci.org/ulrichsg/getopt-php.png)](https://travis-ci.org/ulrichsg/getopt-php)
