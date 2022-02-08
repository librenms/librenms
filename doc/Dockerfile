FROM squidfunk/mkdocs-material:8.1.8

RUN \
  pip install --no-cache-dir \
    'markdown-include' \
    'mkdocs-awesome-pages-plugin' \
    'mkdocs-exclude' \
    'mkdocs-git-revision-date-localized-plugin' \
    'mkdocs-macros-plugin' \
  && rm -rf /tmp/*
