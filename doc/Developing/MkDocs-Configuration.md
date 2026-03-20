# MkDocs Configuration (LibreNMS)

LibreNMS documentation is built with [MkDocs](https://www.mkdocs.org/) using the
[Material for MkDocs](https://squidfunk.github.io/mkdocs-material/) theme.
This page explains how LibreNMS uses `mkdocs.yml`, with links to upstream docs
for the meaning of individual keys.

## Source Files and Navigation

- `docs_dir: doc` means documentation source files live under `doc/`.
  See [MkDocs - docs_dir](https://www.mkdocs.org/user-guide/configuration/#docs_dir).
- `nav:` is the table of contents. Paths in `nav:` are relative to `doc/`.
  See [MkDocs - nav](https://www.mkdocs.org/user-guide/configuration/#nav).
- LibreNMS also uses the `include_dir_to_nav` plugin to pull directories into
  the nav in some places (for example the `Applications` section).

LibreNMS convention:

- When you add a new page, include it in `nav:` unless the relevant section is
  directory-driven via `include_dir_to_nav`.

## Output Location and Canonical URL

- `site_dir: out` is where `mkdocs build` writes the generated site.
  See [MkDocs - site_dir](https://www.mkdocs.org/user-guide/configuration/#site_dir).
- `site_url` is the canonical published URL.
  See [MkDocs - site_url](https://www.mkdocs.org/user-guide/configuration/#site_url).

## Theme (Material) and Enabled Features

LibreNMS uses Material (see [MkDocs - theme](https://www.mkdocs.org/user-guide/configuration/#theme)).

Noteworthy theme settings in LibreNMS:

- `theme.logo` and `theme.language` control branding and language.
- `theme.palette` configures light/dark palettes.
- `theme.features` enables UI/authoring features used throughout the docs:
  - `content.tabs.link` keeps same-labeled tab groups in sync.
    See [Content tabs](https://squidfunk.github.io/mkdocs-material/reference/content-tabs/).
  - `content.action.edit` enables an Edit button.
    See [Edit button](https://squidfunk.github.io/mkdocs-material/setup/adding-a-git-repository/#edit-button).
  - `content.code.copy` adds a copy button to code blocks.
    See [Code copy button](https://squidfunk.github.io/mkdocs-material/reference/code-blocks/#copy-to-clipboard).
  - `navigation.instant` enables instant navigation.
    See [Instant loading](https://squidfunk.github.io/mkdocs-material/setup/setting-up-navigation/#instant-loading).

## Markdown Extensions Enabled in LibreNMS

LibreNMS enables a set of Markdown extensions via `markdown_extensions:`.
See [MkDocs - markdown_extensions](https://www.mkdocs.org/user-guide/configuration/#markdown_extensions).

Extensions you will commonly use while writing LibreNMS docs:

- `admonition` + `pymdownx.details` for callouts and collapsible sections.
  See [Admonitions](https://squidfunk.github.io/mkdocs-material/reference/admonitions/).
- `pymdownx.tabbed` for content tabs.
  See [Content tabs](https://squidfunk.github.io/mkdocs-material/reference/content-tabs/).
- `pymdownx.superfences` for improved fenced code blocks.
  See [Code blocks](https://squidfunk.github.io/mkdocs-material/reference/code-blocks/).
- `pymdownx.highlight` for syntax highlighting.
  LibreNMS config extends highlighting for PHP inline mode.

Examples:

Content tabs:

````markdown
=== "uv"

    ```bash
    uv run mkdocs serve
    ```

=== "venv + pip"

    ```bash
    mkdocs serve
    ```
````

Admonitions:

```markdown
!!! note
    Notes render as callouts.

??? tip
    This tip is collapsible.

!!! warning "Custom title"
    You can also specify a custom title.
```

## Plugins Used by LibreNMS Docs

Plugins are configured under `plugins:`.
See [MkDocs - plugins](https://www.mkdocs.org/user-guide/configuration/#plugins).

LibreNMS plugin notes:

- `redirects`: add entries under `redirect_maps:` when you rename or move pages.
  Reference: [mkdocs-redirects](https://github.com/datarobot/mkdocs-redirects).
- `include_dir_to_nav`: some nav sections are directory-driven.
- `markdown-exec`: can execute code blocks at build time; prefer examples that
  are portable and don't depend on local secrets.
  Reference: [markdown-exec](https://pawamoy.github.io/markdown-exec/).
- `exclude`: excludes files from the build (LibreNMS excludes the docs Dockerfile).
- `minify`: minifies HTML/JS/CSS and injects configured asset files.

## Repository Links and "Edit" Button

- `repo_url` and `repo_name` control the repository link shown in the UI.
  See [MkDocs - repo_url](https://www.mkdocs.org/user-guide/configuration/#repo_url).
- `edit_uri` controls where the Edit button points.
  See [MkDocs - edit_uri](https://www.mkdocs.org/user-guide/configuration/#edit_uri).

LibreNMS note:

- LibreNMS sets `edit_uri` to point to `doc/` on the `master` branch. If you
  change documentation paths or restructure folders, verify the Edit button
  still links to the correct file.

## Extra Assets and Site Extras

- `extra_css` and `extra_javascript` include additional assets used by the docs.
  See [MkDocs - extra_css](https://www.mkdocs.org/user-guide/configuration/#extra_css)
  and [MkDocs - extra_javascript](https://www.mkdocs.org/user-guide/configuration/#extra_javascript).
- `extra:` provides theme/plugin variables (LibreNMS uses it for analytics and
  site-specific settings).
  See [MkDocs - extra](https://www.mkdocs.org/user-guide/configuration/#extra).
