source: Developing/Code-Guidelines.md
path: blob/master/doc/

# Coding guidelines

This document is here to help code standards for contributions towards
LibreNMS. The original code base that we forked from had a lack of
standards and as such the code base has a variety of different
styles. Whilst we don't want to restrict how people write code, these
guidelines should mean we have a good standard going forward that
makes reading the code easier. All modern day ide's should be able to
assist in these guidelines without breaking your usual workflow.

## PHP-FIG PSR-2 Coding Style

All new code should follow the [PHP-FIG PSR-2 standard](http://www.php-fig.org/psr/psr-2/).
Below are a few key items from that specification, please make sure to
follow the full spec.

### [Indentation](http://www.php-fig.org/psr/psr-2/#2-4-indenting)

Please use four (4) spaces to indent code rather than a tab. Ensure
you increase indentation for nested code blocks.

```php
if ($foo == 5) {
    if ($foo == 5) {
        if ($foo == 5) {
```

### [Line length](http://www.php-fig.org/psr/psr-2/#1-overview)

Try to keep the length of a line under 80 characters. If you must
exceed 80 characters, please keep it under 120 characters.  This makes
reading the code easier and also enables compatibility for all screen sizes.

### [Control structures](http://www.php-fig.org/psr/psr-2/#5-control-structures)

A space must be used both before and after the parenthesis and also
surrounding the condition operator.

```php
if ($foo == 5) {
```

Do not put blocks of code on a single line, do use parenthesis

```php
if ($foo == 5) {
    echo 'foo is 5';
}
```

else and elseif should start on the same line as ending of the previous code block.

```php
if ($foo == 5) {
    echo 'foo is 5';
} elsif ($foo == 4) {
    echo 'foo is 4';
} else {
    echo 'foo is something else';
}
```

### Including files

Using parenthesis around file includes isn't required, instead just
place the file in between ''

```php
require_once 'includes/snmp.inc.php';
```

### [PHP tags](http://www.php-fig.org/psr/psr-1/#1-overview)

Ensure you use <?php rather than the shorthand version <?.

```php
<?php
```

The `?>` [must be
excluded](http://www.php-fig.org/psr/psr-2/#2-2-files) from all files
that only include PHP (no html). For instance anything in includes/ or
html/includes don't need the tag along with config.php.
