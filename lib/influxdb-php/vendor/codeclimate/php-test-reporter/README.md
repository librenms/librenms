[![Code Climate](https://codeclimate.com/github/codeclimate/php-test-reporter.png)](https://codeclimate.com/github/codeclimate/php-test-reporter) [![Build Status](https://travis-ci.org/codeclimate/php-test-reporter.svg?branch=master)](https://travis-ci.org/codeclimate/php-test-reporter)

# codeclimate-test-reporter

Collects test coverage data from your PHP test suite and sends it to
Code Climate's hosted, automated code review service.

Code Climate - https://codeclimate.com

**Important:** If you encounter an error involving SSL certificates, see the **Known Issue: SSL Certificate Error** section below.

# Important FYIs

Across the many different testing frameworks, setups, and environments, there are lots of variables at play. Before setting up test coverage, it's important to understand what we do and do not currently support:

* **Default branch only:** We only support test coverage for your [default branch](http://docs.codeclimate.com/article/151-glossary-default-branch). Be sure to check out this branch before running your tests.
* **Single payload:** We currently only support a single test coverage payload per commit. If you run your tests in multiple steps, or via parallel tests, Code Climate will only process the first payload that we receive. If you are using a CI, be sure to check if you are running your tests in a parallel mode.

  **Note:** There is one exception to this rule. We've specifically built an integration with [Solano Labs](https://www.solanolabs.com/) to support parallel tests.

  **Note:** If you've configured Code Climate to analyze multiple languages in the same repository (e.g., Ruby and JavaScript), we can nonetheless only process test coverage information for one of these languages. We'll process the first payload that we receive.
* **Invalid File Paths:** By default, our test reporters expect your application to exist at the root of your repository. If this is not the case, the file paths in your test coverage payload will not match the file paths that Code Climate expects.

## Requirements

There are several requirements you'll need in order to use the PHP test reporter on your system:

- [PHPUnit](http://phpunit.de)
- [Xdebug](http://xdebug.org)
- [Composer](http://getcomposer.org)

The test reporter uses the [PHPUnit](http://phpunit.de) testing tool to generate [code coverage](http://en.wikipedia.org/wiki/Code_coverage) information. These results show how much of your application's code is being executed by your unit tests. PHPUnit can't generate this information on it's own though - it needs another tool, [Xdebug](http://xdebug.org). This is *not* included as a part of the PHPUnit (or PHP) install by default so you'll need to install it yourself.

Xdebug is installed as an extension to PHP, not a library. You can find more information about installing the tool via PECL [on the project website](http://xdebug.org/docs/install).

If you execute your PHPUnit tests with the `--coverage-clover` option and receive the message "The Xdebug extension is not loaded. No code coverage will be generated." you will need to visit the Xdebug website and install the extension. If you do not, you'll most likely get an error something like this:

```
PHP Warning:  simplexml_load_file(): I/O warning : failed to load external entity "[...]/build/logs/clover.xml" in [...]/vendor/satooshi/php-coveralls/src/Contrib/Bundle/CoverallsV1Bundle/Api/Jobs.php on line 52
```

## Installation

This package requires a user, but not necessarily a paid account, on
Code Climate, so if you don't have one the first step is to signup at:
https://codeclimate.com.

To install php-test-reporter with Composer run the following command.

```shell
composer require codeclimate/php-test-reporter --dev
```

This will get you the latest version of the reporter and install it. If you do want the master, untagged, version you may use the command below:

```shell
composer require codeclimate/php-test-reporter:@dev --dev
```

## Usage

- Generate coverage data to `build/logs/clover.xml`

Add the following to phpunit.dist.xml:

```xml
<?xml version="1.0" encoding="UTF-8"?>
<phpunit ...>
  <logging>
    ...
    <log type="coverage-clover" target="build/logs/clover.xml"/>
    ...
  </logging>
</phpunit>
```

Or invoke `phpunit` as follows:

```
$ phpunit --coverage-clover build/logs/clover.xml
```

- Specifying your repo token as an environment variable, invoke the
  test-reporter:

```
$ CODECLIMATE_REPO_TOKEN="..." vendor/bin/test-reporter
```

The `CODECLIMATE_REPO_TOKEN` value is provided after you add your repo
to your Code Climate account by clicking on "Setup Test Coverage" on the
right hand side of your feed.

Please contact hello@codeclimate.com if you need any assistance setting
this up.

## Troubleshooting

If you're having trouble setting up or working with our test coverage feature, [see our detailed help doc](http://docs.codeclimate.com/article/220-help-im-having-trouble-with-test-coverage), which covers the most common issues encountered.

## Known Issue: SSL Certificate Error

If you encounter an error involving SSL certificates when trying to report
coverage data from your CI server, you can work around it by manually posting
the data via `curl`:

```yaml
after_script:
  - CODECLIMATE_REPO_TOKEN="..." bin/test-reporter --stdout > codeclimate.json
  - "curl -X POST -d @codeclimate.json -H 'Content-Type: application/json' -H 'User-Agent: Code Climate (PHP Test Reporter v0.1.1)' https://codeclimate.com/test_reports"
```

**Note:** In the command above, you may need to change `bin/test-reporter` to `vendor/bin/test-reporter`, depending on your project's directory structure.

More details can be found in [this issue][issue].

[issue]: https://github.com/codeclimate/php-test-reporter/issues/3


## Contributions

Patches, bug fixes, feature requests, and pull requests are welcome on
the GitHub page for this project:

https://github.com/codeclimate/php-test-reporter

This package is maintained by Bryan Helmkamp (bryan@codeclimate.com).

## Copyright

See LICENSE.txt

Portions of the implementation were inspired by the php-coveralls
project.

