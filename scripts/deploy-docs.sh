#!/usr/bin/env bash
GH_REPO="@github.com/librenms-docs/librenms-docs.github.io.git"
FULL_REPO="https://${GH_TOKEN}$GH_REPO"
THEME_REPO="https://github.com/librenms-docs/theme_v2.git"

if [ "$EXECUTE_BUILD_DOCS" != "true" ]; then
    echo "Doc build skipped"
    exit 0
fi

pip install --user 'jinja2<2.9' mkdocs pymdown-extensions
pip install --user git+git://github.com/aleray/mdx_del_ins.git

mkdir -p out

cd out

git init
git remote add origin $FULL_REPO
git fetch
git config user.name "librenms-docs"
git config user.email "travis@librenms.org"
git checkout master

cd ../

git clone $THEME_REPO

mkdocs build --clean
build_result=$?

# Only deploy after merging to master
if [ "$build_result" == "0" -a "$TRAVIS_PULL_REQUEST" == "false" -a "$TRAVIS_BRANCH" == "master" ]; then
    cd out

    touch .
    git add -A .
    git commit -m "GH-Pages update by travis after $TRAVIS_COMMIT"
    git push -q origin master
else
    exit ${build_result}  # return doc build result
fi
