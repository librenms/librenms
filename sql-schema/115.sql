update config set config_value='((%ports.ifInOctets_rate*8) / %ports.ifSpeed)*100' where config_id=460;
update config set config_default='((%ports.ifInOctets_rate*8) / %ports.ifSpeed)*100' where config_id=460;
