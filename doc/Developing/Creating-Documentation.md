source: Developing/Creating-Documentation.md
path: blob/master/doc/

# Creating Documentation

One of the goals of the LibreNMS project is to enable users to get all of the
help they need from our documentation.

The documentation uses the [markdown](https://en.wikipedia.org/wiki/Markdown)
markup language and is generated with [mkdocs](https://www.mkdocs.org/). To edit
or create markdown you only need a text editor, but it is recommended to build
your docs before submitting, in order to check them visually. The section on
this page has instructions for this step.

## Writing docs

When you are adding a new feature or extension, we need to have full
documentation to go along with it. It's quite simple to do this:

- Find the relevant directory to store your new document in, General, Support
  and Extensions are the most likely choices.
- Think of a descriptive name that's not too long, it should match what they may
  be looking for or describes the feature.
- Add the new document into the `nav` section of `mkdocs.yml` if it needs to
  appear in the table of contents
- Ensure the first line contains: `source: path/to/file.md` - don't include the
  initial `doc/`.
- In the body of the document, be descriptive but keep things simple. Some tips:
  - If the document could cover different distros like CentOS and Ubuntu please
    try and include the information for them all. If that's not possible then at
least put a placeholder in asking for contributions.
  - Ensure you use the correct formatting for `commands` and `code blocks` by
    wrapping one liners in backticks or blocks in ```.
  - Put content into sub-headings where possible to organise the content.
- If you rename a file, please add a redirect in for the old file by using
  `<meta http-equiv="refresh" content="0; url=/NewLocation/" />` within the old
file name.

Please ensure you add the document to the relevant section within `pages` of
`mkdocs.yml` so that it's in the correct menu and is built.  Forgetting this
step will result in your document never seeing the light of day :)

## Formatting docs

Our docs are based on Markdown using mkdocs which adheres to markdown specs and
nothing more, because of that we also import a couple of extra libraries:

- pymdownx.tasklist
- pymdownx.tilde

This means you can use:

- `~~strikethrough~~` to perform ~~strikethrough~~
- [X] `- [X] List items`
- Url's can be made `[like this](https://www.librenms.org)` [like this](https://www.librenms.org)
- Code can be placed in \`\` for single line or \`\`\` for multiline.
- `#` Can be used for main headings which translates to a `<h1>` tag,
  increasing the `#`'s will increase the hX tags.
- `###` Can be used for sub-headings which will appear in the TOC to the left.

[Markdown CheatSheet Link](https://github.com/adam-p/markdown-here/wiki/Markdown-Cheatsheet)


## Building docs

This is achieved with `mkdocs`, a python package.

1. Install the required packages.

```
pip install mkdocs mkdocs-exclude mkdocs-material mkdocs-macros-plugin
```
If you encounter permissions issues, these might be reoslved by using the
user option, with whatever user you are building as, e.g. `-u librenms`

2. A configuration file for building LibreNMS docs is already included in the
distribution: `/opt/librenms/mkdocs.yml`. The various configuration
directives are documented
[here](https://www.mkdocs.org/user-guide/configuration/).

3. Build from the librenms base directory: `cd /opt/librenms`.

4. Building is simple:

```
mkdocs build
```

This will output all the documentation in html format to `/opt/librenms/out`
(this folder will be ignored from any commits).


## Viewing docs

mkdocs includes it's own light-weight webserver for this purpose.

Viewing is as simple as running the following command:

```
$ mkdocs serve
INFO    -  Building documentation...
<..>
INFO    -  Documentation built in 12.54 seconds
<..>
INFO    -  Serving on http://127.0.0.1:8000
<..>
INFO    -  Start watching changes
```

Now you will find the complete set of LibreNMS documentation by opening your
browser to `localhost:8000`.

Note it is not necessary to `build` before viewing as the `serve` command
will do this for you. Also the server will update the documents it is serving
whenever changes to the markdown are made, such as in another terminal.

### Viewing docs from another machine

By default the server will only listen for connections from the local machine.
If you are building on a different machine you can use the following directive
to listen on all interfaces:

```
mkdocs serve --dev-addr=http://0.0.0.0:8000
```

WARNING: this is not a secure webserver, do this at your own risk, with
appropriate host security and do not leave the server running.

