FROM squidfunk/mkdocs-material:9.5.20

RUN \
  pip install --no-cache-dir \
    'markdown-include' \
    'mkdocs-awesome-pages-plugin' \
    'mkdocs-exclude' \
    'mkdocs-git-revision-date-localized-plugin' \
    'mkdocs-macros-plugin' \
    'mkdocs-include-dir-to-nav' \
  && rm -rf /tmp/*
