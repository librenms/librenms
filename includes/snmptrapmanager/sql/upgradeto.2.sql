create table MIBUploaderTraps (
	`id` int(11) not null auto_increment primary key,
	`device_id` int(11) not null,
	`oid` varchar(255) not null,
	`values` varchar(255),
	`last_update` timestamp not null default CURRENT_TIMESTAMP
);