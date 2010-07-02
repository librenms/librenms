#!/usr/bin/perl
# --------------------------------------------------------------------
# Copyright (C) 2010 Tom Laermans <tom.laermans@powersource.cx>
# Based on quagga-snmp-bgpd (C) 2004 Oliver Hitz <oliver@net-track.ch>
#
# This program is free software; you can redistribute it and/or modify
# it under the terms of the GNU General Public License as published by
# the Free Software Foundation; either version 2 of the License, or
# (at your option) any later version.
#
# This program is distributed in the hope that it will be useful, but
# WITHOUT ANY WARRANTY; without even the implied warranty of
# MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the GNU
# General Public License for more details.
#
# You should have received a copy of the GNU General Public License
# along with this program; if not, write to the Free Software
# Foundation, Inc., 59 Temple Place - Suite 330, Boston,
# MA 02111-1307, USA.
# --------------------------------------------------------------------

use strict;

# The base OID of this extension. Has to match the OID in snmpd.conf:
my $baseoid = ".1.3.6.1.4.1.21337.15";

# Path to ipmitool
my $ipmitool="/usr/bin/ipmitool";
chomp($ipmitool);

# Results from the initscript are cached for some seconds so that an
# SNMP walk doesn't result in the script being called hundreds of times:
my $cache_secs = 60;

# --------------------------------------------------------------------

my $stats;
my $infotime;

# Switch on autoflush
$| = 1;

while (my $cmd = <STDIN>) {
  chomp $cmd;

  if ($cmd eq "PING") {
    print "PONG\n";
  } elsif ($cmd eq "get") {
    my $oid_in = <STDIN>;

    my $oid = get_oid($oid_in);
    my $stats = get_ipmi_values();

    if ($oid != 0 && defined($stats->{$oid})) {
      print "$baseoid.$oid\n";
      print $stats->{$oid}[0]."\n";
      print $stats->{$oid}[1]."\n";
    } else {
      print "NONE\n";
    }
  } elsif ($cmd eq "getnext") {
    my $oid_in = <STDIN>;

    my $oid = get_oid($oid_in);
    my $found = 0;

    my $stats = get_ipmi_values();
    my @s = sort { oidcmp($a, $b) } keys %{ $stats };
    for (my $i = 0; $i < @s; $i++) {
      if (oidcmp($oid, $s[$i]) == -1) {
	print "$baseoid.".$s[$i]."\n";
	print $stats->{$s[$i]}[0]."\n";
	print $stats->{$s[$i]}[1]."\n";
	$found = 1;
	last;
      }
    }
    if (!$found) {
      print "NONE\n";
    }
  } else {
    # Unknown command
  }
}

exit 0;

sub trim 
{
  my $string = shift;
  for ($string) 
  {
    s/^\s+//;
    s/\s+$//;
  }
  return $string;
}

sub get_oid
{

  my ($oid) = @_;
  chomp $oid;

  my $base = $baseoid;
  $base =~ s/\./\\./g;

  if ($oid !~ /^$base(\.|$)/) {
    # Requested oid doesn't match base oid
    return 0;
  }

  $oid =~ s/^$base\.?//;
  return $oid;
}

sub oidcmp {
  my ($x, $y) = @_;

  my @a = split /\./, $x;
  my @b = split /\./, $y;

  my $i = 0;

  while (1) {

    if ($i > $#a) {
      if ($i > $#b) {
	return 0;
      } else {
	return -1;
      }
    } elsif ($i > $#b) {
      return 1;
    }

    if ($a[$i] < $b[$i]) {
      return -1;
    } elsif ($a[$i] > $b[$i]) {
      return 1;
    }

    $i++;
  }
}

sub get_ipmi_values
{
  # We cache the results for $cache_secs seconds
  if (time - $infotime < $cache_secs) {
    return $stats;
  }
  my %info = ();

  my $index = 1;
  open Q, "$ipmitool sensor 2>/dev/null|";
  while (my $l = <Q>) {
    if ($l =~ /^(.*)\|(.*)\|(.*)\|(.*)\|(.*)\|(.*)\|(.*)\|(.*)\|(.*)\|(.*)/) {
      $info{"1." . $index } = [ "integer", $index ];
      $info{"2." . $index } = [ "string", trim($1) ];
      $info{"3." . $index } = [ "integer", trim($2)*1000 ]; # value
      $info{"4." . $index } = [ "string", trim($3) ]; # unit
      $info{"5." . $index } = [ "string" , trim($4) ]; # state
      $info{"6." . $index } = [ "integer", trim($5) eq "na" ? -1 : trim($5)*1000 ]; # low nonrecoverable
      $info{"7." . $index } = [ "integer", trim($6) eq "na" ? -1 : trim($6)*1000 ]; # low critical
      $info{"8." . $index } = [ "integer", trim($7) eq "na" ? -1 : trim($7)*1000 ]; # low warning
      $info{"9." . $index } = [ "integer", trim($8) eq "na" ? -1 : trim($8)*1000 ]; # high warning
      $info{"10." . $index } = [ "integer", trim($9) eq "na" ? -1 : trim($9)*1000 ]; # high critical
      $info{"11." . $index } = [ "integer", trim($10) eq "na" ? -1 : trim($10)*1000 ]; # high nonrecoverable
      $index++;
    }
  }
  close Q;


  $stats = \%info;
  $infotime = time;
  return $stats;
}
