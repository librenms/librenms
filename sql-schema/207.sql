ALTER TABLE `devices` ADD `parent_id` int(11) unsigned;
CREATE INDEX parent_id_idx ON devices (parent_id);
