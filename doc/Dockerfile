FROM squidfunk/mkdocs-material:9.7

WORKDIR /app
COPY ./pyproject.toml /app/pyproject.toml
RUN \
  pip install --no-cache-dir -e .\
     && rm -rf /tmp/*
