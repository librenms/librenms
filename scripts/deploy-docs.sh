#!/usr/bin/env bash
GH_REPO="@github.com/librenms-docs/librenms-docs.github.io.git"
FULL_REPO="https://${GH_TOKEN}$GH_REPO"

if [ "$EXECUTE_BUILD_DOCS" != "true" ]; then
    echo "Doc build skipped"
    exit 0
fi

pip3 install --upgrade pip
pip3 install --user --requirement <(cat <<EOF
future==0.18.2
Jinja2==2.11.2
livereload==2.6.1
lunr==0.5.8
Markdown==3.2.2
mkdocs==1.1.2
mkdocs-material==5.2.2
nltk==3.5
pymdown-extensions==7.1
PyYAML==5.3.1
six==1.15.0
tornado==6.0.4
MarkupSafe==1.1.1
EOF
)


mkdir -p out
cd out

git init
git remote add origin "$FULL_REPO"
git fetch
git config user.name "librenms-docs"
git config user.email "travis@librenms.org"
git checkout master

cd ../
mkdocs build --clean
build_result=$?

# Only deploy after merging to master
if [ "$build_result" == "0" ] && [ "$TRAVIS_PULL_REQUEST" == "false" ] && [ "$TRAVIS_BRANCH" == "master" ]; then
    cd out/
    touch .
    git add -A .
    git commit -m "GH-Pages update by travis after $TRAVIS_COMMIT"
    git push -q origin master
else
    exit ${build_result}  # return doc build result
fi
