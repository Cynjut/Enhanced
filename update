#!/usr/pkg/bin/perl
#

use DBI;
use DBD::mysql;
use Text::CSV;
use textfix;

open (TEXT,">/usr/share/httpd/ets/import/output.file");
if ($#ARGV == -1) {
    print TEXT "This is the Update Script for ETS.\n\n";
    print TEXT "Usage: ./update 'Name of Campaign' [update file name]\n\n";
    print TEXT "The campaign ID (IN QUOATATION MARKS) is required and will be used for all of the \n";
    print TEXT "records updated into this campaign set.\n";
    print TEXT "The update file name is optional and defaults to 'update.csv'.\n";
    print TEXT "Exiting.\n";
    exit(1);
}

$debug = 0;

if (length($cid = $ARGV[0]) == 0) {
	$cid = '';
}

if (length($impfile = $ARGV[1]) == 0) {
	$impfile = 'update.csv';
}


$data_source = "DBI:mysql:database=teleservices;host=192.168.100.2";
$username = 'ets1';
$password = 'ets1pass';
$dbh = DBI->connect($data_source, $username, $password);

$statement = "SELECT * FROM import_headers";
$sth = $dbh->prepare($statement);
$retvals = $sth->execute;
while ($fieldhash = $sth->fetchrow_hashref()) {
    $label = $fieldhash->{'label'};
    $field = $fieldhash->{'field'};
    $dblabel{$label} = $field;
}


#
# The book table on 100.2 is always right, even when its wrong.  
#
$statement = "SELECT server FROM book WHERE book = '$cid'";
$sth = $dbh->prepare($statement);
if (!$sth->execute()) {
    $dbserver = '192.168.100.2';
} else {
    @server = $sth->fetchrow_array();
    $dbserver = $server[0];
}

#
# If we have to change servers, let's do it now and get it over with.
#
if ($dbserver ne '192.168.100.2') {
    $dbh->disconnect();
    $data_source = "DBI:mysql:database=telesarchive;host=$dbserver";
    $dbh = DBI->connect($data_source, $username, $password);
}

#
# Let's get the question responses for the book we are working with.
#
$statement = "SELECT qid,rlabel FROM resp2 WHERE cid = '$cid' AND rother = 'N' ORDER BY rlabel";
print TEXT "Query: $statement\n";
$sth = $dbh->prepare($statement);
$retvals = $sth->execute;
while ($resphash = $sth->fetchrow_hashref()) {
    $quest = "q_" . $resphash->{qid};
    $resp = $resphash->{rlabel};
    $valid_resp{$quest} .= "$resp,";
#    print TEXT "Valid Responses for $quest: ".$valid_resp{$quest}."\n";
}

print TEXT "Filename: $impfile\n";
open (INFILE,"<$impfile");
my $csv = Text::CSV->new;

$rec = 0;
$reccount = 0;
while (<INFILE>) {
#
# We need to remove apostrophes and backslashes from all data
#
    $inline = fixfield($_);

    if ($csv->parse($inline)) {
	my @field = $csv->fields;
	my $count = 0;
#
# First, we process the line with the Field names on it (we know this is the 
# first record since the fname array is empty).
#
# If we have a "Q_..." field, we need to preload that field with a 
# questionnaire response.
#
# We use the field positions to set the outrec positions.  These arent 
# really positions, so much as they are a hash based on the output fields.
#
	for $column (@field) {
	    if (length($fldname[$count]) == 0) {
		$column = lc(fixfield($column));
		$fldname[$count] = $column;
                if (substr($column,0,2) eq 'q_') {
                    $qfld_list{$column} = $count;
                    $redir[$count] = $column;
#		    print TEXT "Redir $count = ".$redir[$count]."\n";
                } else {
                    $redir[$count] = $dblabel{$column};
                    if ($redir[$count] eq 'clientnum') {
                        $haveclnt = 'Y';
                    }
                }
#		print TEXT "Field: $redir[$count]\n" if ($debug);
	    } else {
#
# Other post process special cases
#
# No subcode was supplied on the program call, so we have to use the one out of
# the original records.  
#
		if (length($subcode) == 0  && $redir[$count] eq 'promo') {
		    $subcode = $column;
		}
#
# Set the outrec field to the column data.  The only exception is that the 
# subcode is overridden by whatever we put in the subcode field.
#
		$outrec{$redir[$count]} = $column;
	    }
	    $count++;
	}
	if (length($outrec{'promo'}) == 0) {
	    $outrec{'promo'} = $subcode;
	}
#
#  All client numbers in the system are at least nine digits long, zero
#  filled.
#
	while (length($outrec{'clientnum'}) < 9) {
	    $outrec{'clientnum'} = '0' . $outrec{'clientnum'};
	}
	$clientnum = $outrec{'clientnum'};
#
# Let's clean up the ZIP codes (US ones are are either 5 or 9 digits).
#
	$country = fixfield($outrec{'country'});
	$zip = $outrec{'zip'};
        if (length($country) < 1 && length($zip) > 1) {  
            $zip = numfield($zip);
            while (length($zip) > 5 && length($zip) < 9) {
                $zip = '0' . $zip;
            }
            while (length($zip) < 5) {
                $zip = '0' . $zip;
            }
	}

#
# Let's process presets so that they match up with responses from the
# questionnaire manager.
#
        print TEXT "Processing Q_ Codes\n" if ($debug);
        while (($key,$value) = each %qfld_list) {
            my @values = split(',', $valid_resp{$key});
            $field_ok = 0;
            foreach $val (@values) {
#               print "Checking " . $outrec{$key} . " and $val\n";
                if (index($outrec{$key},$val) == 0) {
#                   print "Transformed " . $outrec{$key} . " to $val\n";
                    $outrec{$key} = $val;
                    $field_ok = 1;
                    last;
                }
            }
            if ($field_ok == 0) {
#               print TEXT "Blanked " . $outrec{$key} . "\n";
                $outrec{$key} = '';
            }
        }

#
# Always import a fullname
#

	if (length($outrec{'fullname'}) == 0) {
	    $outrec{'fullname'} = $outrec{'fname'} . ' ' . $outrec{'lname'};
	}
	if (length($outrec{'fullname'}) == 1) {
	    $outrec{'fullname'} = '';
	}
	
	$phone = numfield($outrec{'phone'});
	$fax   = numfield($outrec{'fax'});
	$fldcount = 0;
	$statement = "UPDATE contact SET";
	if (length($phone) != 0) {
	    $statement .= " phone = '". fixfield($phone) . "',";
	    $fldcount += 1;
	}
	if (length($fax) != 0) {
	    $statement .= " fax = '". fixfield($fax) . "',";
	    $fldcount += 1;
	}
	if (length($outrec{'promo'}) != 0) {
	    $statement .= " promo = '" . fixfield($outrec{'promo'}) . "',";
	    $fldcount += 1;
	}
	if (length($outrec{'fname'}) != 0) {
	    $statement .= " fname = '" . fixfield($outrec{'fname'}) . "',";
	    $fldcount += 1;
	}
	if (length($outrec{'lname'}) != 0) {
	    $statement .= " lname = '" . fixfield($outrec{'lname'}) . "',";
	    $fldcount += 1;
	}
	if (length($outrec{'fullname'}) > 2 ) {
	    $statement .= " fullname = '" . fixfield($outrec{'fullname'}) . "',";
	    $fldcount += 1;
	}
	if (length($outrec{'title'}) != 0) {
	    $statement .= " title = '" . fixfield($outrec{'title'}) . "',";
	    $fldcount += 1;
	}
	if (length($outrec{'orgname'}) != 0) {
	    $statement .= " orgname = '" . fixfield($outrec{'orgname'}) . "',";
	    $fldcount += 1;
	}
	if (length($outrec{'department'}) != 0) {
	    $statement .= " department = '" . fixfield($outrec{'department'}) . "',";
	    $fldcount += 1;
	}
	if (length($outrec{'address'}) != 0) {
	    $statement .= " address = '" . fixfield($outrec{'address'}) . "',";
	    $fldcount += 1;
	}
	if (length($outrec{'city'}) != 0) {
	    $statement .= " city = '" . fixfield($outrec{'city'}) . "',";
	    $fldcount += 1;
	}
	if (length($outrec{'email'}) != 0) {
	    $statement .= " email = '" . $outrec{'email'} . "',";
	    $fldcount += 1;
	}
	if (length($outrec{'st'}) != 0) {
	    $statement .= " st = '" . fixfield($outrec{'st'}) . "',";
	    $fldcount += 1;
	}
	if (length($country) != 0) {
	    $statement .= " country = '" . $country . "',";
	    $fldcount += 1;
	}
	if (length($zip) != 0) {
	    $statement .= " zip = '" . $zip . "',";
	    $fldcount += 1;
	}
	if (length($reason) != 0) {
	    $statement .= " reason = '". fixfield($reason) . "',";
	    $fldcount += 1;
	}
	if (length($completed) != 0) {
	    $statement .= " completed = '". fixfield($completed) . "',";
	    $fldcount += 1;
	}
	if (length($uniqueid) != 0) {
	    $statement .= " uniqueid = '". fixfield($uniqueid) . "',";
	    $fldcount += 1;
	}
	if (length($csr) != 0) {
	    $statement .= " csr = '". fixfield($csr) . "',";
	    $fldcount += 1;
	}
	if (length($extension) != 0) {
	    $statement .= " extension = '". fixfield($extension) . "',";
	    $fldcount += 1;
	}
	if (length($subcode) != 0) {
	    $statement .= " subcode = '". fixfield($subcode) . "',";
	    $fldcount += 1;
	}
	$stlen = length($statement) - 1;
	$statement = substr($statement,0,$stlen);
	$statement .= " WHERE book = '" . $cid . "'";
	$statement .= " AND clientnum like '$clientnum%'";
	$statement .= " AND reason != 'COMPLETE'";
	if ($fldcount > 0) {
	    $sth = $dbh->prepare($statement);
	    $retvals = $sth->execute;
	    $reccount += 1;
	    print TEXT "$statement\n";
	} else {
	    print TEXT "$clientnum has no new data\n";
	}
	if (keys(%qfld_list) >  0) {
            print TEXT "\nUploading new questrep data \n" if ($debug);
            while (($key,$value) = each %qfld_list) {
		print TEXT "Client $clientnum Q_ $key\n" if ($debug);
                $qid = substr($key,2);
                if ($outrec{$key} ne '') {
                    $statement = "REPLACE INTO questrep SET ";
                    $statement .= "cid  = '$cid', ";
                    $statement .= "qid  = '$qid', ";
                    $statement .= "sid  = '', ";
                    $statement .= "qresp  = '$outrec{$key}', ";
                    $statement .= "qother  = '', ";
                    $statement .= "phone  = '$phone', ";
                    $statement .= "clientnum  = '$clientnum' ";
                    $sth = $dbh->prepare($statement);
                    $retvals = $sth->execute;
                    print COMMAND "$statement\n" if ($debug);
                    print TEXT "$statement\n" if ($debug);
                }
            }
        }
#
# Clear all of the variables for the next record
#
        $outrec{'clientnum'} = '';
        $clientnum = '';
        $outrec{'promo'} = '';
        $outrec{'fname'} = '';
        $outrec{'lname'} = '';
        $outrec{'fullname'} = '';
        $fname = '';
        $lname = '';
        $fullname = '';
        $outrec{'title'} = '';
        $outrec{'orgname'} = '';
        $outrec{'department'} = '';
        $outrec{'address'} = '';
        $outrec{'city'} = '';
        $outrec{'st'} = '';
        $outrec{'country'} = '';
        $outrec{'email'} = '';
        $outrec{'completed'} = '';
        $outrec{'reason'} = '';
        $phone = '';
        $fax = '';
        $zip = '';
        if (keys(%qfld_list) >  0) {
            while (($key,$value) = each %qfld_list) {
                $outrec{$key} = '';
            }
        }
    } else {
	my $err = $csv->error_input;
	print TEXT "parse() failed on argument: ", $err, "\n";
    }
}
print TEXT "$reccount records updated\n";
print "$reccount records updated\n";

close(INFILE);
$dbh->disconnect();

