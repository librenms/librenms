#!/usr/bin/perl

# depends on availability of status and extended status info from your
# Apache webserver -- your httpd.conf needs to include something like the
# following: (uncommented)
#<Location /server-status>
#    SetHandler server-status
#    Order allow,deny
#    Allow from localhost
#</Location>
#ExtendedStatus On

# can return hits or bytes (counters)

@res = `/usr/bin/lynx -dump http://$ARGV[0]:80/server-status`;

foreach $res (@res) {
        if ($res =~ /Server uptime: (.*)$/) { $up = $1; last } else { next }
        if ($res =~ /Server at/) { $server = $res; last } else { next }
}

@res = `/usr/bin/lynx -dump http://$ARGV[0]:80/server-status?auto`;

foreach $res (@res) {
        if ($res =~ /Total Accesses: (\d+)/) { $d1 = $1; next }
        if ($res =~ /Total kBytes: (\d+)/) { $d2 = $1 * 1024; next }
}

$d1 = int($d1);
$d2 = int($d2);

#if ($ARGV[1] eq "hits") {
        print "$d1\n";
#        print "$d1\n";
#} elsif ($ARGV[1] eq "bytes") {
        print "$d2\n";
#       print "$d2\n";
#}

print "$up\n";
print "$server";


