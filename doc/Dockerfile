FROM squidfunk/mkdocs-material:9.5.19

RUN \
  pip install --no-cache-dir \
    'markdown-include' \
    'mkdocs-awesome-pages-plugin' \
    'mkdocs-exclude' \
    'mkdocs-git-revision-date-localized-plugin' \
    'mkdocs-macros-plugin' \
    'mkdocs-material' \
    'mkdocs-material-extensions' \
    'pymdown-extensions' \
  && rm -rf /tmp/*
