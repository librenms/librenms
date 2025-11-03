## Translating LibreNMS

LibreNMS uses [Laravel Localization](https://laravel.com/docs/localization) to handle translations.

- Common strings (and some others) are stored in `lang/<locale>.json`.
- Most strings are stored in `lang/<locale>/<group>.php`. The PHP files return an array which
  is flattened to dot notation (e.g., `['nav' => ['devices' => 'Devices']]` in the file menu.php
  becomes `menu.nav.devices`).

### Finding untranslated strings

Note: The Lost in Translation tool is provided by a development dependency. Make sure Composer dev requirements are installed before running it:

```bash
./scripts/composer_wrapper.php install --dev
```

Use the Lost in Translation command to list missing strings for a locale:
```bash
./artisan lost-in-translation:find <locale>
```

You may also invoke it via lnms if available in your environment:

```bash
./lnms lost-in-translation:find <locale>
```

### Updating frontend translations

If you need to manually update the frontend translations, you can run:

```bash
./lnms translation:generate
```

This process is run during update, so normal users should not need to run this.
