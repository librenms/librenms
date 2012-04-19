#!/usr/bin/perl

my $doPlugin = 0;
my $x = shift(@ARGV);
if ($x !~ /^-p/) { unshift(@ARGV, $x); }
else { $doPlugin=1; }
my $injs = shift(@ARGV);
my $outjs = shift(@ARGV);

if ($injs eq '' or $outjs eq '') {
	print "Please use this script like this: makemini.pl [-p] in.js out.js\n";	
	exit(0);
}


open(INJS, $injs);
open(OUTJS, ">$outjs");

my $output = '';

while (<INJS>) {
	my $line = $_;
	
	if ($line =~ /^\/\//) {
		# Remove lines that aren't important: //\
		$line = "" if ($line !~ /^\/\/\\/);
		$line = "\n//\\  THIS IS A VERY MODIFIED VERSION. DO NOT EDIT OR PUBLISH. GET THE ORIGINAL!\n\n" if ($line =~ /\/\/\\mini/);
	} else {
		chop $line;

		$line =~ s/, /,/g unless ($line =~ /'\], '/);           # ,{sp} -> ,
		$line =~ s/; /;/g;           # ;{sp} -> ;
		$line =~ s/ = /=/g;          # {sp}={sp} -> =
		$line =~ s/ == /==/g;        # {sp}=={sp} -> ==
		$line =~ s/ < /</g;          # {sp}<{sp} -> <
		$line =~ s/ > />/g;          # {sp}>{sp} -> >
		$line =~ s/ & /&/g;          # {sp}&{sp} -> &
		$line =~ s/ \| /\|/g;        # {sp}|{sp} -> |
		$line =~ s/ <= /<=/g;        # {sp}<={sp} -> <=
		$line =~ s/ >= />=/g;        # {sp}>={sp} -> >=
		$line =~ s/ \+ /\+/g;        # {sp}+{sp} -> +
		$line =~ s/ - /-/g;          # {sp}-{sp} -> -
		$line =~ s/ \/ /\//g;
		$line =~ s/ \|\| /\|\|/g;    # {sp}||{sp} -> ||
		$line =~ s/ && /&&/g;        # {sp}&&{sp} -> &&
		$line =~ s/ \? /\?/g;        # {sp?{sp} -> ?
		$line =~ s/ \: /\:/g;        # {sp}:{sp} -> :
		$line =~ s/ != /!=/g;        # {sp}!={sp} -> !=
		$line =~ s/ += /+=/g;        # {sp}+={sp} -> +=
		$line =~ s/ -= /-=/g;        # {sp}-={sp} -> -=
		$line =~ s/ \*= /\*=/g;      # {sp}*={sp} -> *=
		$line =~ s/ \|= /\|=/g;       # {sp}|={sp} -> |=
		$line =~ s/ \^= /\^=/g;       # {sp}^={sp} -> ^=
		$line =~ s/= /=/g;           # ={sp} -> =
		$line =~ s/ =/=/g;           # {sp}= -> =
		$line =~ s/\+ /\+/g;
		$line =~ s/ \+/\+/g;
		$line =~ s/- /-/g;
		$line =~ s/ -/-/g;

		$line =~ s/\/\/(.*)$//g if ($line !~ /\/\/-->(.*)$/ && $line !~ /http:\/\/(.*)$/); # remove trailing comments unless its part of a javascript insert or web address
		$line = '' if $line =~ /^[\n|\/\/]/; # skip blank lines or any line starting with //

		$line =~ s/^\s+//g;
		$line =~ s/\s+$//g;
		$line =~ s/(.+)\s+(.+)/$1 $2/g;
		$line =~ s/\{ (\w)/\{$1/g;
		$line =~ s/\) (\w)/\)$1/g;
		$line =~ s/\) var/\)var/g;
		$line =~ s/[ ]+\(/\(/g;
		$line =~ s/\) \{/\)\{/g;
		$line =~ s/\} else/\}else/g;
		$line =~ s/else \{/else\{/g;
		if ($line =~ /^\}$/) {
			if ($output =~ /;$/) {
				$output .= "}";
			} else {
				$output = substr($output,0,length($output)-1) . "}";
			}
			$line = '';
		}
	}

	$output .= $line if ($line ne '');
	$output .= "\n" unless ($line =~ /;\n*$/ or $line =~ /{\n*$/);
}

$output =~ s/\n+/\n/g;
$output .= "}\n" if ($doPlugin && $output !~ /\}\s+$/);
# replace multiple ;var xx to ,xx if the line contains var
@lines = split(/^/,$output);
foreach $line (@lines) {
	$line =~ s/;var /,/g if ($line =~ /^\s*var / && $line !~ /(turn|ment|Capture\(\)|Div'\)|1000\));var /);
	print OUTJS $line;
}
