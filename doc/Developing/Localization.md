## Translating LibreNMS

LibreNMS uses [Laravel Localization](https://laravel.com/docs/localization) to handle translations.

- Common strings (and some others) are stored in `lang/<locale>.json`.
- Most strings are stored in `lang/<locale>/<group>.php`. The PHP files return an array which
  is flattened to dot notation. For example, `['nav' => ['devices' => 'Devices']]` in the file menu.php
  becomes `menu.nav.devices`).

### Finding untranslated strings

Note: a development dependency supplies the Lost in Translation tool.
Install the Composer dev requirements before the run:

```bash
./scripts/composer_wrapper.php install --dev
```

The Lost in Translation command lists the missing strings of a locale:
```bash
./artisan lost-in-translation:find <locale>
```

You can also start it with `lnms`, when `lnms` is available in your
environment:

```bash
./lnms lost-in-translation:find <locale>
```

### Updating frontend translations

To update the frontend translations manually, run:

```bash
./lnms translation:generate
```

The update process runs this command. A normal user therefore does not
need it.
