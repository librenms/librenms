#!/usr/bin/env perl

=pod

=head1 NAME

find_missing_config_defs - Locates config defs missing from misc/config_definitions.json

=head1 SYNOPSIS

./scripts/find_missing_config_defs
./find_missing_config_defs

=head1 DESCRIPTION

Checks .config under misc/config_definitions.json for missing defs via checking to see
if a that item is defined.

This requires rg and a few Perl modules to work.

On FreeBSD those may be installed via...

    pkg install ripgrep p5-JSON p5-String-ShellQuote p5-File-Slurp

On Debian based systems those may be installed via...

    apt-get install libjson-perl libstring-shellquote-perl libfile-slurp ripgrep

=cut

use strict;
use warnings;
use JSON;
use String::ShellQuote;
use File::Slurp;

if ( -f 'find_missing_config_defs' && -d '../LibreNMS' && -f '../misc/config_definitions.json' ) {
	chdir('..');
}
elsif ( ! -d './LibreNMS' && ! -f './misc/config_definitions.json' ) {
	die('Does not appear to be the LibreNMS base directory. Please CD to there and rerun this script');
}


my $to_run = 'rg ' . shell_quote('Config::get\(') . ' | grep -v ' . shell_quote('^tests');
my @found = grep( !/^tests/, split( /\n/, `$to_run` ) );

my $int = 0;
my %found; # used for dedup
while ( defined( $found[$int] ) ) {
	$found[$int] =~ s/^.*Config\:\:get\(\'//;
	$found[$int] =~ s/\'.*$//;

	if (
		# anything not maching this can't be processed as it contains $
		# which means it is autogened
		$found[$int] =~ /^[.0-9A-Za-z_\-]+$/
		&&

		# was part of a generated item fetch
		$found[$int] !~ /\.$/ &&

		# sort of out of the scope for this
		$found[$int] !~ /^graph_colours/
		)
	{
		$found{ $found[$int] } = 1;
	}

	$int++;
}
@found = sort( keys(%found) );

# read the conf def json and then look for undefined items
my $json = decode_json( read_file('misc/config_definitions.json') );
$int = 0;
while ( defined( $found[$int] ) ) {
	if ( !defined( $json->{config}{ $found[$int] } ) ) {
		print $found[$int] . "\n";
	}

	$int++;
}
