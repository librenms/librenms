source: Developing/Creating-Documentation.md
path: blob/master/doc/
# Creating Documentation

One of the goals of the LibreNMS project is to enable users to get all
of the help they need from our documentation.

The documentation is generated with
[mkdocs](https://www.mkdocs.org/). If you want to build a local copy,
install `mkdocs` and `mkdocs-material`, then run `make docs`. This
produces the HTML in `docs/out` directory.

Alternatively you can start a webserver at <http://localhost:8000> for
live preview by executing `mkdocs serve`

### Writing docs

When you are adding a new feature or extension, we need to have full
documentation to go along with it. It's quite simple to do this:

- Find the relevant directory to store your new document in, General,
  Support and Extensions are the most likely choices.
- Think of a descriptive name that's not too long, it should match
  what they may be looking for or describes the feature.
- Add the new document into the `nav` section of `mkdocs.yml` if it
  needs to appear in the table of contents
- Ensure the first line contains: `source: path/to/file.md` - don't
  include the initial `doc/`.
- In the body of the document, be descriptive but keep things simple. Some tips:
  - If the document could cover different distros like CentOS and
    Ubuntu please try and include the information for them all. If
    that's not possible then at least put a placeholder in asking for contributions.
  - Ensure you use the correct formating for `commands` and `code
    blocks` by wrapping one liners in backticks or blocks in ```.
  - Put content into sub-headings where possible to organise the
    content.
- If you rename a file, please add a redirect in for the old file by
  using `<meta http-equiv="refresh" content="0; url=/NewLocation/" />`
  within the old file name.

Please ensure you add the document to the relevant section within
`pages` of `mkdocs.yml` so that it's in the correct menu and is built.
Forgetting this step will result in your document never seeing the
light of day :)

### Formatting docs

Our docs are based on Markdown using mkdocs which adheres to markdown
specs and nothing more, because of that we also import a couple of extra libraries:

- pymdownx.tasklist
- pymdownx.tilde

This means you can use:

- `~~strikethrough~~` to perform ~~strikethrough~~
- [X] `- [X] List items`
- Url's can be made `[like this](http://www.librenms.org)` [like this](http://www.librenms.org)
- Code can be placed in `` for single line or ``` for multiline.
- `#` Can be used for main headings which translates to a `<h1>` tag,
  increasing the `#`'s will increase the hX tags.
- `###` Can be used for sub-headings which will appear in the TOC to
  the left.

 [Markdown CheatSheet Link](https://github.com/adam-p/markdown-here/wiki/Markdown-Cheatsheet)
