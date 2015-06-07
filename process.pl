#!/usr/bin/perl

sub getfiles {

opendir(DIR,".");

while($file = readdir(DIR)) {

if($file !~ /^\./) {

foreach $all ($file) {

system("snmpttconvertmib --in=$all --out=/etc/snmp/snmptt-d.conf");
print "$all\n";
}
}
}
closedir(DIR);
}

&getfiles; 
