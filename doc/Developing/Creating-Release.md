source: Developing/Creating-Release.md
# Creating a release

### GitHub
You can create a new release on [GitHub](https://github.com/librenms/librenms/releases/new).

Enter the tag version that month, i.e for September 2016 you would enter `201609`.

Enter a title, we usually use `August 2016 Release`

Enter a placeholder for the body, we will edit this later.

### Create changelog
We utilise [Readmegen](https://github.com/fojuth/readmegen) to automatically populate the Changelog.

Install `readmegen` using `composer`:

```bash
./composer.phar update
```

You can now create the update change log by running (201608 was our last release):

```bash
./vendor/bin/readmegen --from 201608 --release 201609
```

Now commit and push the change that has been made to `doc\General\Changelog.md`
