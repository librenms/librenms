#!/bin/bash
GH_REPO="@github.com/librenms-docs/docs.librenms.org.git"
FULL_REPO="https://${GH_TOKEN}$GH_REPO"

pip install --user mkdocs
pip install --user pymdown-extensions

mkdir -p out

cd out

git init
git remote add origin $FULL_REPO
git fetch
git config user.name "librenms-docs"
git config user.email "travis@librenms.org"
git checkout gh-pages

cd ../

mkdocs build --clean

cd out

touch .
git add -A .
git commit -m "GH-Pages update by travis after $TRAVIS_COMMIT"
git push -q origin gh-pages
