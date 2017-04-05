#!/bin/bash
GH_REPO="@github.com/librenms/librenms.git"
FULL_REPO="https://${GH_TOKEN}$GH_REPO"

git config user.name "librenms-docs"
git config user.email "travis@librenms.org"

DBTEST=1 ./scripts/build-schema.php > misc/db_schema.yaml
STATUS=$(git status -s misc/db_schema.yaml)

if [[ "$STATUS" != "" ]]; then
  git commit -m "DB Schema updated by travis after $TRAVIS_COMMIT"
  git push -q origin master 
fi
