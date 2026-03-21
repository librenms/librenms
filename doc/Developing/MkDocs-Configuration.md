# MkDocs Configuration (LibreNMS)

LibreNMS documentation is built with [MkDocs](https://www.mkdocs.org/) using the
[Material for MkDocs](https://squidfunk.github.io/mkdocs-material/) theme.
This page explains how LibreNMS uses `mkdocs.yml`, with links to upstream docs
for the meaning of individual keys.

## Source Files and Navigation

- `docs_dir: doc` — LibreNMS places documentation source files under `doc/`.
  See [MkDocs - docs_dir](https://www.mkdocs.org/user-guide/configuration/#docs_dir).
- `nav:` is the table of contents. Paths in `nav:` are relative to `doc/`.
  When adding a new page, include it in `nav:` under the relevant section,
  unless the relevant section is directory-driven via `include_dir_to_nav`.
  See [MkDocs - nav](https://www.mkdocs.org/user-guide/configuration/#nav).

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

## Repository Links

LibreNMS sets `repo_url`, `repo_name`, and `edit_uri` to point to the GitHub
repository. These control the repository link and Edit button in the UI.
If the repository location changes (for example, moving to a different org
or renaming), update these values in `mkdocs.yml`.

## Extra Assets

`extra_css`, `extra_javascript`, and `extra:` include additional assets and
variables used by the docs (LibreNMS uses these for analytics and
site-specific settings).
