CREATE TABLE tmp_table LIKE config;
ALTER IGNORE TABLE tmp_table ADD UNIQUE INDEX uniqueindex_configname (config_name);
INSERT IGNORE INTO tmp_table SELECT * FROM config;
DROP TABLE config;
RENAME TABLE tmp_table TO config;
