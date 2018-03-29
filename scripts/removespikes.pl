#!/usr/bin/perl -w
#
# matapicos v2.1 - Vins Vilaplana <vins at terra dot es)
#
# Translated by Humberto Rossetti Baptista <humberto at baptista dot name)
# slight adjustments and code cleanup too :-)
#

require "getopts.pl";

my (@dump,%exp,@cols,@dbak,%tot,%por);
my ($linea,$linbak,$lino,$cdo,$tresto,$tstamp,$a,$b,$cont);
my $DEBUG = 0;

# Limit % for cutting. Any peak representing less than this % will be cut
my $LIMIT=0.6; # obs this is really %, so 0.6 means 0.6% (and not 0.006%!)

&Getopts('l:');

if ($#ARGV < 0) {
   print "REMOVESPIKES: Remove spikes from RRDtool databases.\n\n";
   print "Usage:\n";
   print "$0 [-l number]  name_of_database\n\n";
   print "Where: number is the % limit of spikes to chop (default: $LIMIT)\n";
   print "       and name_of_database ir the rrd file to be treated.\n";
   exit;
}

if ($opt_l) { 
   $LIMIT=$opt_l; 
   print "Limit set to $LIMIT\n" if $DEBUG;
}

# temporary filename:
# safer this way, so many users can run this script simultaneusly
my $tempfile="/tmp/matapicos.dump.$$"; 

###########################################################################
# Dump the rrd database to the temporary file (as XML)
`rrdtool dump $ARGV[0] > $tempfile`;

# Scan the XML dump checking the variations and exponent deviations
open(FICH,"<$tempfile") 
   || die "$0: Cannot open file $tempfile:\n $! - $@";

while (<FICH>) {
  chomp;
  $linea=$_;
  $cdo=0;
  if ($linea=~/^(.*)<row>/) { $tstamp=$1; }
  if ($linea=~/(<row>.*)$/) { $tresto=$1; }
  if (/<v>\s\d\.\d+e.(\d+)\s<\/v>/) {
    @dump = split(/<\/v>/, $tresto);
    for ($lino=0; $lino<=$#dump-1; $lino++) {   # scans DS's within each row 
      if ( $dump[$lino]=~/\d\.\d+e.(\d+)\s/ ) { # make sure it is a number (and not NaN)
        $a=substr("0$lino",-2).":".$1;
        $exp{$a}++;                             # store exponents
        $tot{substr("0$lino",-2)}++;            # and keep a per DS total
      }
    }
  }
}

close FICH;

###########################################################################
# Scan the hash to get the percentage variation of each value
foreach $lino (sort keys %exp) {
  ($a)=$lino=~/^(\d+)\:/;                      
  $por{$lino}=(100*$exp{$lino})/$tot{$a};
}

if ($DEBUG) { 
   # Dumps percentages for debugging purposes
   print "--percentages--\n";
   foreach $lino (sort keys %exp) {
     print $lino."--".$exp{$lino}."/";
     ($a)=$lino=~/^(\d+)\:/;
     print $tot{$a}." = ".$por{$lino}."%\n";
   }
   print "\n\n\n";
}


###########################################################################
# Open the XML dump, and create a new one removing the spikes:
open(FICH,"<$tempfile") || 
   die "$0: Cannot open $tempfile for reading: $!-$@";
open(FSAL,">$tempfile.xml")  || 
   die "$0: Cannot open $tempfile.xml for writing: $!-$@";

$linbak='';
$cont=0;
while (<FICH>) {
  chomp;
  $linea=$_;
  $cdo=0;
  if ($linea=~/^(.*)<row>/) { $tstamp=$1; }     # Grab timestamp
  if ($linea=~/(<row>.*)$/) { $tresto=$1; }     # grab rest-of-line :-)
  if (/<v>\s\d\.\d+e.(\d+)\s<\/v>/) {           # are there DS's?
    @dump=split(/<\/v>/, $tresto);              # split them
    if ($linbak ne '') {
      for ($lino=0;$lino<=$#dump-1;$lino++) {   # for each DS:
        if ($dump[$lino]=~/\d\.\d+e.(\d+)\s/) { # grab number (and not a NaN)
          $a=$1*1;                              # and exponent
          $b=substr("0$lino",-2).":$1";         # calculate the max percentage of this DS
          if ($por{$b}< $LIMIT) {               # if this line represents less than $LIMIT
            $linea=$tstamp.$linbak;             # we dump it.
            $cdo=1;
            $tresto=$linbak;
          }
        }
      }
    }
    $linbak=$tresto;
    if ($cdo==1) { 
      print "Chopping peak at $tstamp\n";
      $cont++; }
  }
  
  print FSAL "$linea\n";
}
close FICH;
close FSAL;

###########################################################################
# Cleanup and move new file to the place of original one
# and original one gets backed up.
if ($cont == 0) { print "No peaks found.!\n"; }
else {
  rename($ARGV[0],"$ARGV[0].old");
  $lino="rrdtool restore $tempfile.xml $ARGV[0]";
  system($lino);
  die "$0: Unable to execute the rrdtool restore on $ARGV[0] - $! - $@\n"  if $!;
}

# cleans up the files created
unlink("$tempfile");
unlink("$tempfile.xml");

