#! /usr/bin/perl
# $Id: bgp-peers.pl,v 1.5 2001/08/06 14:45:55 lwa Exp $

# $Log: bgp-peers.pl,v $
# Revision 1.5  2001/08/06 14:45:55  lwa
# add help
#
# Revision 1.4  2001/08/03 19:03:22  lwa
# display hardware mac address (in Cisco format)
#
#

=pod

=head1 NAME

  bgp-peers.pl - generate MRTG configuration for BGP peering point

=head1 SYNOPSIS

  ./bgp-peers.pl <your IP on the exchange point> <its snmp comunity>

=head1 DESCRIPTION

This program generates a MRTG configuration file for graphing traffic
to and from all peers present on a Cisco NAP, using
mac-accounting. Tested with Cisco 720x and Cisco 750x.

You can then use indexmaker (from mrtg distribution) to generate a cool index
and run the whole stuff from a cronjob.


=head2 CISCO CONFIGURATION

Enable mac-accounting on NAP interface (see the example below)

=over 4

 Router# conf t
 Router(config)# interface FastEthernet2/0
 Router(config-if)# ip accounting mac-address input
 Router(config-if)# ip accounting mac-address output
 Router(config-if)#exit
 Router(config)#exit

=back

=head1 BUGS

The only problem I have noticed is when your are not getting any data
from a peer (IN or OUT) the variable wont be in the SNMP tree and
MRTG will complain. If its occurs during long time, this probably mean
you have a problem with your peer because you are expected to make
some traffic with it (at least, BGP traffic). 

If the peer change its hardware address, you have to re-run
this program to take the modification.

=head1 THANKS

Thanks to groups.google.com for the tricks : http://groups.google.com/groups?q=this_mac&hl=en&safe=off&rnum=1&ic=1&selm=91750329.28451%40news.Colorado.EDU

Thanks to Robert Kiessling from Easynet Germany to have irritated us.

=head1 AUTHORS

 Erwan Lerale <erwan@de.clara.net> 
 Arnaud le Taillanter <alt@fr.clara.net>
 Laurent Wacrenier <lwa@teaser.fr>

=cut

use SNMP_Session;
use SNMP_util;
use BER;
use Socket;

my $hostname = shift;
my $community = shift;
my %interface;
$interface{ip} = shift;

if ($hostname !~ /^\d+\.\d+\.\d+\.\d+$/) {
    print STDERR
	"usage: $0 router  community  interface_ip \n";
    exit 1;
}

$community = "public" unless defined $community;

my %OIDS = (
	    ipNetToMediaPhysAddress => "1.3.6.1.2.1.4.22.1.2",
	    bgpPeerState      => "1.3.6.1.2.1.15.3.1.2",
	    bgpPeerRemoteAs   => "1.3.6.1.2.1.15.3.1.9",
	    bgpPeerRemoteAddr => "1.3.6.1.2.1.15.3.1.7",
	    bgpPeerLocalAddr  => "1.3.6.1.2.1.15.3.1.5",
	    cipMacSwitchedBytes => "1.3.6.1.4.1.9.9.84.1.2.1.1.4",
	    );

my %peers; 

($interface{index}) = snmpget("$community\@$hostname",
			      "ipAdEntIfIndex.$interface{ip}");

($interface{speed}) = snmpget("$community\@$hostname",
			      "ifSpeed.$interface{index}");
$interface{speed}/=8;


my $session = SNMP_Session->open ($hostname, $community, 161)
    or die "Couldn't open SNMP session to $hostname";

$session->map_table([ [split /\./, $OIDS{bgpPeerRemoteAddr}], 
		      [split /\./, $OIDS{bgpPeerRemoteAs}],
		      [split /\./, $OIDS{bgpPeerState}],
		      [split /\./, $OIDS{bgpPeerLocalAddr}],
		      ], \&getpeers);

sub getpeers {
    my ($index, $RemoteAddr, $RemoteAs, $State, $LocalAddr) = @_;
    my $pLocalAddr = pretty_print($LocalAddr);
    return  if $pLocalAddr ne $interface{ip};

    my ($PhysAddress) = snmpget("$community\@$hostname",
				"ipNetToMediaPhysAddress.$interface{index}.$index");

    $peer{$index} = {
	RemoteAs => pretty_print($RemoteAs),
	PhysAddress => join(".", unpack("C*", $PhysAddress)),
    };
  
}

for my $peer (sort keys %peer) {
    my $name = gethostbyaddr(inet_aton($peer), AF_INET) || $peer;
    my $mac  = sprintf "%02x%02x.%02x%02x.%02x%02x",
    split(/\./, $peer{$peer}->{PhysAddress});

#    print <<EOF;
#Target[$peer]: $OIDS{cipMacSwitchedBytes}.$interface{index}.1.$peer{$peer}->{PhysAddress}\&$OIDS{cipMacSwitchedBytes}.$interface{index}.2.$peer{$peer}->{PhysAddress}:$community\@$interface{ip}
#MaxBytes[$peer]: $interface{speed}
#Options[$peer]: bits, growright
#ShortLegend[$peer]: bps
#Title[$peer]: MAC traffic for <tt>$name</tt> (AS$peer{$peer}->{RemoteAs})
#PageTop[$peer]: <H1>MAC traffic for <tt>$name</tt> (AS$peer{$peer}->{RemoteAs})
# </H1>
# <TABLE>
#   <TR><TD>Peer Name:</TD><TD><tt>$name</tt></TD></TR>
#   <TR><TD>IP address:</TD><TD><tt>$peer</tt></TD></TR>
#   <TR><TD>Mac address:</TD><TD><tt>$mac</tt></TD></TR>
#   <TR><TD>Peer AS:</TD><TD><tt>$peer{$peer}->{RemoteAs}</tt></TD></TR>
#  </TABLE>
#
#EOF

print <<EOF;
$interface{ip},$peer,$name,$peer{$peer}->{RemoteAs},$mac,$OIDS{cipMacSwitchedBytes}.$interface{index}.1.$peer{$peer}->{PhysAddress},$OIDS{cipMacSwitchedBytes}.$interface{index}.2.$peer{$peer}->{PhysAddress},$community
EOF



}

