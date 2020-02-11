#!/usr/bin/env bash
GH_REPO="@github.com/librenms-docs/librenms-docs.github.io.git"
FULL_REPO="https://${GH_TOKEN}$GH_REPO"

if [ "$EXECUTE_BUILD_DOCS" != "true" ]; then
    echo "Doc build skipped"
    exit 0
fi

pip3 install --upgrade pip
pip3 install --user mkdocs mkdocs-material pymdown-extensions
pip3 install --user git+git://github.com/aleray/mdx_del_ins.git

mkdir -p out
cd out

git init
git remote add origin $FULL_REPO
git fetch
git config user.name "librenms-docs"
git config user.email "travis@librenms.org"
git checkout master

cd ../
mkdocs build --clean
build_result=$?

# Only deploy after merging to master
if [ "$build_result" == "0" -a "$TRAVIS_PULL_REQUEST" == "false" -a "$TRAVIS_BRANCH" == "master" ]; then
    cd out/
    touch .
    git add -A .
    git commit -m "GH-Pages update by travis after $TRAVIS_COMMIT"
    git push -q origin master
else
    exit ${build_result}  # return doc build result
fi
