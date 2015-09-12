# Database Schema Updates

Providing SQL Schema changes for LibreNMS is pretty simply, we have two ways to do this depending on what you are updating.

#### General schema changes

General schema changes such as adding columns to existing tables, creating indexes or fixing issues should be done within the
core schema change directory: `sql-schema/org.librenms.core` and should be named incremently. If the latest schema file is 073.sql
then you need to create 074.sql.

#### New component schema changes

If you are developing a new component for LibreNMS such as an alerting system or API then you would create a new folder listing
these changes. This is done so that you can work on the functionality you need without worrying about merge conflicts later.
These files should be created in a directory based on the Java Naming Convention using your email address and the component name
you are working on, as an example: `sql-schema/org.example.steve.api`.

An example .sql file is as follows:

```sql
ALTER TABLE  `munin_plugins_ds` CHANGE  `ds_cdef`  `ds_cdef` VARCHAR( 255 ) CHARACTER SET utf8 COLLATE utf8_bin NOT NULL;
ALTER TABLE  `applications` ADD  `app_state` TEXT CHARACTER SET utf8 COLLATE utf8_bin NOT NULL;
```

General schema changes still have the potential of merge conflicts if more than one person is working on a change, however these schema
changes should be relatively small PRs so we expect to be able to merge them quicker than larger components which may require more
extensive testing.

