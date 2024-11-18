# Translations

## Supported Translations
LibreNMS currently supports the following translations:

- Brazilian Portuguese (pt-BR)
- Chinese (zh-CN)
- English (en)
- French (fr)
- German (de)
- Italian (it)
- Russian (ru)
- Serbian (sr)
- Traditional Chinese (zh-TW)
- Ukrainian (uk)

Translation files can be found in `lang/$translation_folder`.

## Adding a new language
To add support for a new translation (let's call it Librenese - ln), you would create a new
folder called `lang/ln`. Please then copy all of the files from `lang/en` and edit each
one to reflect the written language you would like to add.

## Generating the json language file
For use in the WebUI javascript and Blade templates we need to generate some additional 
files, to do that you can simply run `lnms translation:generate`

## Submit a pull request
You now need to submit a pull request on GitHub so that others can benefit from the amazing 
work you've done :)
