FROM squidfunk/mkdocs-material:9.6

RUN \
  pip install --no-cache-dir \
    'markdown-exec' \
    'markdown-include' \
    'mkdocs-awesome-pages-plugin' \
    'mkdocs-exclude' \
    'mkdocs-git-revision-date-localized-plugin' \
    'mkdocs-macros-plugin' \
    'mkdocs-include-dir-to-nav' \
  && rm -rf /tmp/*
