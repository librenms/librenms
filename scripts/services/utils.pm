# Utility drawer for Nagios plugins.
# $Id: utils.pm.in,v 1.7 2003/04/13 04:25:36 sghosh Exp $
#
# $Log: utils.pm.in,v $
# Revision 1.7  2003/04/13 04:25:36  sghosh
# update for check_mailq - qmail support
#
# Revision 1.6  2003/02/03 20:29:55  sghosh
# change ntpdc to ntpq (Jonathan Rozes,Thomas Schimpke, bug-656237 )
#
# Revision 1.5  2002/10/30 05:07:29  sghosh
# monitor mailq
#
# Revision 1.4  2002/05/27 02:01:09  sghosh
#  new var - smbclient
#
# Revision 1.3  2002/05/10 03:49:22  sghosh
# added programs to autoconf
#
# Revision 1.2  2002/05/08 05:10:35  sghosh
#  is_hostname added, update CODES to POSIX
#
# 
package utils;

require Exporter;
@ISA = qw(Exporter);
@EXPORT_OK = qw($TIMEOUT %ERRORS &print_revision &support &usage);

#use strict;
#use vars($TIMEOUT %ERRORS);
sub print_revision ($$);
sub usage;
sub support();
sub is_hostname;

## updated by autoconf
$PATH_TO_RPCINFO = "/usr/bin/rpcinfo";
$PATH_TO_NTPDATE = "/usr/sbin/ntpdate";
$PATH_TO_NTPDC   = "/usr/bin/ntpdc";
$PATH_TO_NTPQ    = "/usr/bin/ntpq";
$PATH_TO_LMSTAT  = "" ;
$PATH_TO_SMBCLIENT = "/usr/bin/smbclient";
$PATH_TO_MAILQ   = "/usr/bin/mailq";
$PATH_TO_QMAIL_QSTAT = "";

## common variables
$TIMEOUT = 15;
%ERRORS=('OK'=>0,'WARNING'=>1,'CRITICAL'=>2,'UNKNOWN'=>3,'DEPENDENT'=>4);

## utility subroutines
sub print_revision ($$) {
	my $commandName = shift;
	my $pluginRevision = shift;
	$pluginRevision =~ s/^\$Revision: //;
	$pluginRevision =~ s/ \$\s*$//;
	print "$commandName (nagios-plugins 1.4.2) $pluginRevision\n";
	print "The nagios plugins come with ABSOLUTELY NO WARRANTY. You may redistribute\ncopies of the plugins under the terms of the GNU General Public License.\nFor more information about these matters, see the file named COPYING.\n";
}

sub support () {
	my $support='Send email to nagios-users@lists.sourceforge.net if you have questions\nregarding use of this software. To submit patches or suggest improvements,\nsend email to nagiosplug-devel@lists.sourceforge.net.\nPlease include version information with all correspondence (when possible,\nuse output from the --version option of the plugin itself).\n';
	$support =~ s/@/\@/g;
	$support =~ s/\\n/\n/g;
	print $support;
}

sub usage {
	my $format=shift;
	printf($format,@_);
	exit $ERRORS{'UNKNOWN'};
}

sub is_hostname {
	my $host1 = shift;
	if ($host1 && $host1 =~ m/^([0-9]+\.[0-9]+\.[0-9]+\.[0-9]+|[a-zA-Z][-a-zA-Z0-9]+(\.[a-zA-Z][-a-zA-Z0-9]+)*)$/) {
		return 1;
	}else{
		return 0;
	}
}

1;
