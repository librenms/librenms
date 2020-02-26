#!/usr/bin/env bash
GH_REPO="@github.com/librenms-docs/librenms-docs.github.io.git"
FULL_REPO="https://${GH_TOKEN}$GH_REPO"

if [ "$EXECUTE_BUILD_DOCS" != "true" ]; then
    echo "Doc build skipped"
    exit 0
fi

pip3 install --upgrade pip
pip3 install --user --requirement <(cat <<EOF
Click==7.0
future==0.18.2
Jinja2==2.11.1
livereload==2.6.1
lunr==0.5.6
Markdown==3.2.1
MarkupSafe==1.1.1
mkdocs==1.1
mkdocs-material==4.6.3
nltk==3.4.5
Pygments==2.5.2
pymdown-extensions==6.3
PyYAML==5.3
six==1.14.0
tornado==6.0.3
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
