# Creating Documentation

One of the goals of the LibreNMS project is to enable users to get all of the
help they need from our documentation.

The documentation uses the [Markdown](https://en.wikipedia.org/wiki/Markdown)
markup language and is generated with [MkDocs](https://www.mkdocs.org/) using
the [Material for MkDocs](https://squidfunk.github.io/mkdocs-material/) theme.
To edit or create Markdown you only need a text editor, but it is recommended to
build your docs before submitting, in order to check them visually. The section
on this page has instructions for this step.

## Writing docs

When you are adding a new feature or extension, we need to have full
documentation to go along with it. It's quite simple to do this:

- Find the relevant directory to store your new document in, General, Support
  and Extensions are the most likely choices.
- Think of a descriptive name that's not too long, it should match what they may
  be looking for or describes the feature.
- Add the new document into the `nav` section of `mkdocs.yml` if it needs to
  appear in the table of contents.
- In the body of the document, be descriptive but keep things simple. Some tips:
  - If the document could cover different distros like CentOS and Ubuntu please
    try and include the information for them all. If that's not possible then at
    least put a placeholder in asking for contributions.
  - Ensure you use the correct formatting for `commands` and `code blocks` by
    wrapping one liners in backticks or blocks in ```.
  - Put content into sub-headings where possible to organise the content.
- If you rename a file, please add a redirect for the old file in `mkdocs.yml` like so:

  ```yaml
  plugins:
    - redirects:
        redirect_maps:
          'old/page.md': 'new/page.md'
  ```

Please ensure you add the document to the relevant section within `nav` of
`mkdocs.yml` so that it's in the correct menu and is built. Forgetting this
step will result in your document never seeing the light of day :)

## Formatting docs

Our docs are based on Markdown using MkDocs. We also enable a few Markdown
extensions and features from Material for MkDocs:

- admonition
- tables
- pymdownx.details
- pymdownx.highlight
- pymdownx.snippets
- pymdownx.superfences
- pymdownx.tabbed
- pymdownx.tasklist
- pymdownx.tilde

This means you can use:

- `~~strikethrough~~` to perform ~~strikethrough~~.
- Task lists: `- [x] Done` and `- [ ] Todo`.
- URLs can be made `[like this](https://www.librenms.org)`.
- Code can be placed in backticks for single line, or fenced blocks for multiline:

```bash
./lnms --help
```

- Admonitions, for example:

```markdown
!!! note
    This is a note.
```

- `#` Can be used for main headings which translates to a `<h1>` tag,
  increasing the `#`'s will increase the hX tags.
- `###` Can be used for sub-headings which will appear in the TOC to the left.
- [Content tabs](https://squidfunk.github.io/mkdocs-material/reference/content-tabs/):
  use `=== "Tab title"` blocks to present alternatives.
- Settings should be prefixed with `!!! setting "<webui setting path>"`

[Markdown CheatSheet Link](https://github.com/adam-p/markdown-here/wiki/Markdown-Cheatsheet)


## Building docs

This is achieved with `mkdocs`.

Build from the LibreNMS base directory (for example: `cd /opt/librenms`).

LibreNMS uses `pyproject.toml` to manage the Python dependencies for building the docs. You can use either `uv`, `venv + pip` or `docker` to set up the environment and build the documentation.

=== "uv"

    [`uv`](https://docs.astral.sh/uv/) is a fast Python package manager and
    runner. Install it (for example: `pipx install uv`) and then:

    1. Create the environment and install dependencies:

    ```bash
    uv sync
    ```

    To use the lock file exactly (fail if it would change):

    ```bash
    uv sync --frozen
    ```

    2. Build the docs:

    ```bash
    uv run mkdocs build
    ```

    To fail on warnings (useful before submitting changes):

    ```bash
    uv run mkdocs build --strict
    ```

=== "venv + pip"

    To build the docs with `venv` and `pip`, you will need to have Python installed on your machine.
    Make a new virtual environment and activate it:

    ```bash
    python -m venv .python_venvs/docs
    source .python_venvs/docs/bin/activate
    ```

    ```bash
    pip install -e .
    ```

    If you encounter permissions issues, these might be resolved by using the user
    option, with whatever user you are building as, e.g. `-u librenms`.

    Build the docs:

    ```bash
    mkdocs build
    ```

    To fail on warnings (useful before submitting changes):

    ```bash
    mkdocs build --strict
    ```

=== "docker"

    Build the Docker image from the repository root:

    ```bash
    docker build -t librenms-docs -f doc/Dockerfile .
    ```

    Build the docs inside the container:

    ```bash
    docker run --rm -v "$(pwd):/app" librenms-docs mkdocs build
    ```

    To fail on warnings (useful before submitting changes):

    ```bash
    docker run --rm -v "$(pwd):/app" librenms-docs mkdocs build --strict
    ```


This will output all the documentation in HTML format to `/opt/librenms/out`
(this folder will be ignored from any commits).

A configuration file for building LibreNMS docs is included in the
distribution: `/opt/librenms/mkdocs.yml`. The various configuration directives
are documented [here](https://www.mkdocs.org/user-guide/configuration/).


## Viewing docs

MkDocs includes its own lightweight web server for this purpose.

Viewing is as simple as running the following command:

Tip: `mkdocs serve` supports live-reloading in the browser. If you need to force
enable it, add `--livereload`.

=== "uv"

    ```bash
    uv run mkdocs serve
    ```

=== "venv + pip"

    ```bash
    mkdocs serve
    ```

=== "docker"

    Run the Docker container:

    ```bash
    docker run --rm -p 8000:8000 -v "$(pwd):/app" librenms-docs
    ```

Now you will find the complete set of LibreNMS documentation by opening your
browser to `localhost:8000`.

Note it is not necessary to `build` before viewing as the `serve` command
will do this for you. Also the server will update the documents it is serving
whenever changes to the Markdown are made, such as in another terminal.

### Viewing docs from another machine

By default the server will only listen for connections from the local machine.
If you are building on a different machine you can use the following directive
to listen on all interfaces:

=== "uv"

    ```bash
    uv run mkdocs serve --dev-addr=0.0.0.0:8000
    ```

=== "venv + pip"

    ```bash
    mkdocs serve --dev-addr=0.0.0.0:8000
    ```

=== "docker"

    ```bash
    docker run --rm -p 8000:8000 -v "$(pwd):/app" librenms-docs --dev-addr=0.0.0.0:8000
    ```

WARNING: this is not a secure web server, do this at your own risk, with
appropriate host security and do not leave the server running.
