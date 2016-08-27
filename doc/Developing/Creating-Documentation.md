source: Developing/Creating-Documentation.md
# Creating Documentation

### Writing documentation
One of the goals of the LibreNMS project is to enable users to get all of the help they need from our documentation.

When you are adding a new feature or extension, we need to have full documentation to go along with it. It's quite 
simple to do this:

  - Find the relevant directory to store your new document in, General, Support and Extensions are the most likely choices.
  - Think of a descriptive name that's not too long, it should match what they may be looking for or describes the feature.
  - In the body of the document, be descriptive but keep things simple. Some tips:
    - If the document could cover different distros like CentOS and Ubuntu please try and include the information for them all.
      If that's not possible then at least put a placeholder in asking for contributions.
    - Ensure you use the correct formating for `commands` and `code blocks` by wrapping one liners in backticks or blocks in ```.
  - If you rename a file, please add a redirect in for the old file by using `<meta http-equiv="refresh" content="0; url=/NewLocation/" />` within the old file name.

Please ensure you add the document to the relevant section within `pages` of `mkdocs.yml` so that it's in the correct menu and is built.
Forgetting this step will result in your document never seeing the light of day :)

### Building documentation
Our build process in GitHub automatically builds http://docs.librenms.org from everything in the `doc/` folder. You can simulate this 
process to test what the docs will look like before you submit your PR.

We use [mkdocs](http://www.mkdocs.org/) to build the documentation so you need to install that first (we assume you have `pip` installed):

```bash
pip install --user mkdocs
pip install --user pymdown-extensions
```

Now you will need to install the LibreNMS theme:

```bash
git clone https://github.com/librenms-docs/librenms_theme.git
```

Now you are ready to build your docs and check them, you can do this with mkdocs:

```bash
mkdocs serve
```

This will launch a web service on localhost port 8000. You can change the port or bind the web server to a different IP by adding 
` -a 0.0.0.0:8080`

