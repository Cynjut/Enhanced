sub spaces(@_) {
    $count = $_[0];
    $out = ' ';
    for ($i = $count; $i > 1; $i--) {
	$out .= ' ';
    }
    return $out;
}

#
#  Get rid of leading spaces, apostrophes, and trailing spaces
#  After we've done that, fill the end with whitespace.
#
sub fillwhite(@_) {
    $instring = $_[0];
    $length = $_[1];
    if ($length == 0) {
        $length = 255;
    }
    $instring =~ s/\'//g;
    $instring =~ s/^\s+//;
    $instring =~ s/\s+$//;

#   while (substr($instring,0,1) eq ' ') {
#       $instring = substr($instring,1,$length);
#   }
    $instring .= spaces($length);
    
    return substr($instring,0,$length);
}

#
#  Get rid of leading spaces, apostrophes, and trailing spaces
#  After we've done that, fill the field so the text if justified.
#
sub justwhite(@_) {
    $instring = $_[0];
    $length = $_[1];
    if ($length == 0) {
        $length = 255;
    }
    $instring =~ s/\'//g;
    $instring =~ s/^\s+//;
    $instring =~ s/\s+$//;

    $instring = spaces($length - length($instring)) . $instring;
    
    print "Instring = '$instring'\n";
    return substr($instring,0,$length);
}

sub fillzero(@_) {
    $instring = $_[0];
    $length = $_[1];
    if ($length == 0) {
        $length = 255;
    }
#    $instring =~ tr/0-9//dc;

   while (length($instring) < $length)  {
       $instring = '0' . $instring;
   }
    
    return substr($instring,0,$length);
}

sub stripzero(@_) {
    $instring = $_[0];

    $instring =~ tr/0-9//dc;
    $instring =~ s/^[0]*//g;

    return $instring;
}

sub makecsv(@_) { 
    $instring = fixcsv($_[0]);
    $eol = $_[1];
    $instring =~ s/\"/\"\"/g;
    $outstring =  "\"$instring\"";
    if ($eol eq "Y") {
        $outstring .= "\n";
    } else {
        $outstring .= ",";
    }  
    return $outstring;
}  

#
#  Get rid of leading spaces, apostrophes, dashes, and trailing spaces
#
sub fixcsv(@_) {
    $instring = $_[0];
    $instring =~ s/\'//g;
    $instring =~ s/^\s+//;
    $instring =~ s/\s+$//;
    $instring =~ s/\s+/ /;

    return $instring;
}
#
#
#  Get rid of leading spaces, apostrophes, dashes, and trailing spaces
#
sub fixfield(@_) {
    $instring = $_[0];
    $instring =~ s/-//g;
    return fixcsv($instring);
}
#
# Keep the numbers, and only the numbers
#
sub numfield(@_) {
    $instring = $_[0];
    $instring =~ tr/0-9//dc;
    return $instring;
}

sub getabbr(@_) {
    $dbh = $_[0];
    $localcid = $_[1];

#
# Thanks to a recent change in the way Jim works with Books, the campaign ID 
# can now come in the form "book" or "'book','book'...".  In order to make
# sure the system continues to work, we need to check the cid for quotes.
#

    if (substr($cid,0,1) ne "'") {
	$localcid = "'$cid'";
    } else {
	$localcid = $cid;
    }
    $statement = "SELECT abbr FROM book WHERE book IN ($localcid)";
    $asth = $dbh->prepare($statement);
    if (!$asth) {
        die "dbh>asth Error: " . $dbh->errstr . "\n";
    }
    if (!$asth->execute()) {
        die "asth Error: " . $asth->errstr . "\n";
    }

    @abbrlist = $asth->fetchrow_array();
    $abbr = $abbrlist[0];
    $abbrlength = length($abbr);
    if ($abbrlength == 1) {
        $abbr = 'CSV';
    }
    $asth->finish();
    return $abbr;
}

sub getcallduration(@_) {
    $calldate = $_[0];
    $uniqueid = $_[1];

    $dir = "/usr/share/httpd/ets/recordings/";
    $filename = $dir . $calldate . '/*' .  $uniqueid;
    $filename = `ls $filename 2>&1 | head -1`;
#    print "Filename = $filename\n";
    $size = (stat($filename))[7]/1000;
    if ($size > 0) {
        $minutes = $size/60;
	$seconds = $size % 60;
    } else {
	$minutes = "N/A";
    }
    return ("$minutes"."$seconds");
}

1;
