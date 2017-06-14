#!/bin/bash
GH_REPO="@github.com/librenms/librenms.git"
FULL_REPO="https://${GH_TOKEN}:x-oauth-basic$GH_REPO"

DBTEST=1 ./scripts/build-schema.php
STATUS=$(git status -s misc/db_schema.yaml)

if [[ "$STATUS" != "" ]]; then
  mkdir -p _schema
  cd _schema

  git init
  git remote add origin $FULL_REPO
  git fetch
  git config user.name "lnms-docs"
  git config user.email "travis@librenms.org"
  git checkout master
  cp ../misc/db_schema.yaml misc/db_schema.yaml
  git commit -a -m "DB Schema updated by travis after $TRAVIS_COMMIT"
  git push -q origin master 
fi
