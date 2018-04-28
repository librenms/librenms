create table MIBUploaderPlugin (
    info varchar(255) NOT NULL UNIQUE,
    value varchar(255)
) CHARSET=utf8;

insert into MIBUploaderPlugin values ('dbschema', '1');
insert into MIBUploaderPlugin values ('snmptt_state', 'idle');

create table MIBUploaderMIBS (
    id INT(11) NOT NULL AUTO_INCREMENT PRIMARY KEY UNIQUE,
    name VARCHAR(255) UNIQUE,
    current_version INT(11)
) CHARSET=utf8;

create table MIBUploaderMIBContent (
    id_mib INT(11) NOT NULL,
    version INT(11) NOT NULL,
    date timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
    content MEDIUMTEXT,
    constraint fk_mibcontent_mib
        foreign key(`id_mib`)
        references MIBUploaderMIBS(`id`)
        on delete cascade
        on update cascade
) CHARSET=utf8;