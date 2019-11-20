source: General/index.md
path: blob/master/doc/

# Copyright and Licensing

All contributors to LibreNMS retain copyright to their own code and are not
required to sign over their rights to any other party. Code should be licensed
under the GPLv3 and MUST NOT contain non-GPL Observium code or GPL
incompatible code. To be safe do not view Observium code at all.  See
[Licensing](Licensing.md) for more details.

# General Guidelines

- Test your patches first.
- Don't break the poller.  User interface blemishes are not critical, but
  losing data from network monitoring systems might be.
- As a general rule, if you're replacing lines of code with new lines of
  code, don't comment them out, just delete them.  Commented out code makes
  the patch and the resultant code harder to read, and there's no good
  reason to it since we can easily get them back from git.
- If you're fixing a bug or making another minor change, don't reformat the
  code at the same time.  This makes it harder to see what's changed.  If
  you need to reformat it after making the change, do so in a separate
  commit.
- Please join us in [discord](https://discord.gg/librenms) if you are able.
  Collaborating in real time makes the coordination of contributions easier.
- Ensure you read the [Code Guidelines](Code-Guidelines.md)
  documentation and understand the code
  style that should be adhered to. You can [validate that your code
  adheres to these guidelines](Validating-Code.md) before submitting.
- Check [Style Guidelines](Style-Guidelines.md) for Web UI guidelines and conventions.

# How to Contribute

- [Getting Started](Getting-Started.md) Set up your development
  environment to make things easier.
- [Using Git](Using-Git.md) gives you step-by-step instructions on
  using git to submit a pull request.
- [Code Structure](Code-Structure.md) can help you understand where
  specific code exists in LibreNMS.
- [Creating Documentation](Creating-Documentation.md) It is very
  important for the continued improvement of LibreNMS that we have
  good documentation.  If you see anything that that needs improvement
  please submit a pull request to fix it.

Don't be afraid to submit a GitHub Pull Request.  We will help you
with anything that needs to be change or suggest ways of improving
your patch. Because the maintainers are volunteers too sometimes
response may be delayed or brief, please be patient or ask for
clarification if needed. Thanks!

## Good places to start to learn PHP or improve your coding

- [PHP: The Right Way](http://www.phptherightway.com/) A community
  curated list of best practices and quick help.
- [Laracasts](https://laracasts.com/skills/php) Video coding tutorials
  that are easy to follow. Many of the beginner videos are
  free. Suggested Series:
  - [The PHP Practitioner](https://laracasts.com/series/php-for-beginners)
  - [Object-Oriented Bootcamp](https://laracasts.com/series/object-oriented-bootcamp-in-php)
  - [Simple Rules for Simpler Code](https://laracasts.com/series/simple-rules-for-simpler-code)
  - [Laravel Documentation](https://laravel.com/docs/)
