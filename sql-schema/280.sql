alter table eventlog modify device_id int unsigned null;
alter table eventlog modify reference varchar(64) null;
alter table eventlog modify severity tinyint default 2 not null;
drop index host on eventlog;
alter table eventlog drop column host;
