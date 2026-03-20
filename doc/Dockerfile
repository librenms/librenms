FROM squidfunk/mkdocs-material:9.7

COPY ./pyproject.toml  pyproject.toml
RUN \
  pip install --no-cache-dir -e .\
     && rm -rf /tmp/*
