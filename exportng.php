<TITLE>
Extract and Download program
</TITLE>
<HEAD>
Welcome to the Enhanced Teleservices Extract Program<BR>
<A HREF="/manager/">Return to Manager page</A>
</HEAD>
<BODY>

<?php

$book = $_POST['book'];
$subcode = $_POST['subcode'];
$sttdate = $_POST['sttdate'];
$enddate = $_POST['enddate'];
$reptype = $_POST['reptype'];

$link = mysql_connect('192.168.100.9', 'ets1', 'ets1pass');

mysql_select_db('telesarchive')
    or die('Could not select database');

print "<FORM METHOD='POST' ACTION='" . $_SERVER['PHP_SELF'] . "'>";

if (strlen($book) < 1) {
    $link = mysql_connect('192.168.100.2', 'ets1', 'ets1pass');
    mysql_select_db('teleservices')
        or die('Could not select book database');
    $btop = "Select Campaign";
    print "<B>Select Book for extract:</B><BR>";
    print "<SELECT name='book' SIZE=19>";

    $query = "SELECT DISTINCT book FROM report,book WHERE book != 'Test' AND book.abbr = report.abbr ORDER BY book";
    $result = mysql_query($query)
	or die('SELECT book Query failed: ' .  mysql_error());
    while ($booklist = mysql_fetch_assoc($result)) {
	$book = $booklist['book'];
        print "<OPTION value='$book'>$book</OPTION>";
    }
    print "<INPUT TYPE='HIDDEN' NAME='subcode' VALUE=''>";
    print "<INPUT TYPE='HIDDEN' NAME='reptype' VALUE=''>";
    print "<INPUT TYPE='HIDDEN' NAME='sttdate' VALUE=''>";
    print "<INPUT TYPE='HIDDEN' NAME='enddate' VALUE=''>";
    print "</SELECT><BR>\n";
} else if (strlen($subcode) == 0) {
    $btop = 'Select Effort Code';
    print "<B>Select Effort Code for $book:</B><BR>";
    print "<SELECT name='subcode' SIZE=13>";
    $query = "SELECT DISTINCT subcode FROM contact WHERE book = '$book' AND reason = 'complete' ORDER BY subcode";
    $result = mysql_query($query)
	or die('SELECT code Query failed: ' .  mysql_error());
    while ($codelist = mysql_fetch_assoc($result)) {
	$subcode = $codelist['subcode'];
        print "<OPTION value='$subcode'>$subcode</OPTION>";
    }
    print "<OPTION value='ALL'>ALL</OPTION>";
    print "</SELECT><BR>\n";
    print "<INPUT TYPE='HIDDEN' NAME='book' VALUE='$book'>";
    print "<INPUT TYPE='HIDDEN' NAME='reptype' VALUE=''>";
    print "<INPUT TYPE='HIDDEN' NAME='sttdate' VALUE=''>";
    print "<INPUT TYPE='HIDDEN' NAME='enddate' VALUE=''>";
} else if (strlen($reptype) == 0) {
    $btop = 'Select Report Type';
    print "<B>Select Report Type for $book - $subcode:</B><BR>";
    print "<INPUT TYPE='RADIO' NAME='reptype' VALUE='comp'>";
    print " Completes<BR>";
    print "<INPUT TYPE='RADIO' NAME='reptype' VALUE='errs'>";
    print " All Errors<BR>";
    print "<INPUT TYPE='RADIO' NAME='reptype' VALUE='bads'>";
    print " Bad List Entries<BR>";
    print "<INPUT TYPE='RADIO' NAME='reptype' VALUE='call'>";
    print " Uncalled Entries<BR>";
    print "<INPUT TYPE='HIDDEN' NAME='book' VALUE='$book'>";
    print "<INPUT TYPE='HIDDEN' NAME='subcode' VALUE='$subcode'>";
    print "<INPUT TYPE='HIDDEN' NAME='sttdate' VALUE=''>";
    print "<INPUT TYPE='HIDDEN' NAME='enddate' VALUE=''>";
} else if (strlen($sttdate) == 0) {
    $btop = 'Select Start Date';
    print "<B>Select Extract Start Date for $book $subcode:</B><BR>";
    print "<SELECT name='sttdate' SIZE=13>";
    $query = "SELECT DISTINCT SUBSTRING(completed,1,11) AS sttdate FROM contact WHERE book = '$book' and subcode = '$subcode'  AND reason = 'complete' ORDER BY sttdate";
    $result = mysql_query($query)
	or die('SELECT date Query failed: ' .  mysql_error());
    while ($datelist = mysql_fetch_assoc($result)) {
	$sttdate = $datelist['sttdate'];
        print "<OPTION value='$sttdate'>$sttdate</OPTION>";
    }
    print "<OPTION value='ALL'>ALL</OPTION>";
    print "</SELECT><BR>\n";
    print "<INPUT TYPE='HIDDEN' NAME='book' VALUE='$book'>";
    print "<INPUT TYPE='HIDDEN' NAME='subcode' VALUE='$subcode'>";
    print "<INPUT TYPE='HIDDEN' NAME='reptype' VALUE='$reptype'>";
    print "<INPUT TYPE='HIDDEN' NAME='enddate' VALUE=''>";
    print "<BR><FONT COLOR='ORANGE'>Please note that if you choose 'ALL' for this option, the extract ";
    print "program will execute immediately.  This can take quite a bit of time, depending on the complexity ";
    print "of the extract and how many records you've chosen to include.  By 'quite a bit of time', ";
    print "we mean up to an hour.  You will be able to find your extract on the list of old etracts on the manager page.";
} else if ($sttdate != 'ALL' && strlen($enddate) == 0) {
    $btop = 'Select End Date';
    print "<B>Select Extract End Date for $book $subcode:</B><BR>";
    print "<SELECT name='enddate' SIZE=13>";
    $query = "SELECT DISTINCT SUBSTRING(completed,1,11) AS enddate FROM contact WHERE book = '$book' and subcode = '$subcode' AND completed >= '$sttdate' AND reason = 'complete' ORDER BY enddate";
    $result = mysql_query($query)
	or die('SELECT date Query failed: ' .  mysql_error());
    while ($datelist = mysql_fetch_assoc($result)) {
	$enddate = $datelist['enddate'];
        print "<OPTION value='$enddate'>$enddate</OPTION>";
    }
    print "<OPTION value='ALL'>ALL</OPTION>";
    print "</SELECT><BR>\n";
    print "<INPUT TYPE='HIDDEN' NAME='book' VALUE='$book'>";
    print "<INPUT TYPE='HIDDEN' NAME='subcode' VALUE='$subcode'>";
    print "<INPUT TYPE='HIDDEN' NAME='reptype' VALUE='$reptype'>";
    print "<INPUT TYPE='HIDDEN' NAME='sttdate' VALUE='$sttdate'>";
    print "<BR><FONT COLOR='ORANGE'>Please note that once you choose this option, the extract ";
    print "program will execute.  This can take quite a bit of time, depending on the complexity ";
    print "of the extract and how many records you've chosen to include.  By 'quite a bit of time', ";
    print "we mean up to an hour. You will be able to find your extract on the manager page.";
} else {
    $btop = 'Return to Campaign Selector';
    $query = "SELECT DISTINCT abbr FROM book WHERE book = '$book'";
    $result = mysql_query($query)
	or die('SELECT date Query failed: ' .  mysql_error());
    if ($abbrlist = mysql_fetch_assoc($result)) {
	$abbr = $abbrlist['abbr'];
    } else {
	$abbr = 'CSV';
    }
#    print "Book = $book<BR>";
#    print "Subcode = $subcode<BR>";
#    print "Extract date = $sttdate<BR>";

    $filename = "export$abbr-$subcode";
    if ($reptype != 'comp') {
	$filename .= "-" . $reptype;
    }
    if ($sttdate != 'ALL') {
	$filename .= "-" . substr($sttdate,2,2) . substr($sttdate,5,2) . substr($sttdate,8,2);
    }

    $fullpath = "/usr/share/httpd/ets/exportfiles/$filename";
    $program = "/usr/share/httpd/ets/export/export $abbr '$book' $fullpath";
    if ($subcode != 'ALL') {
        $program .= " '$subcode'";
    } else {
	$program .= " ''";
    }
    if ($sttdate != 'ALL') {
	$program .= " $sttdate";
    } else {
	$program .= " ''";
	$enddate = 'ALL';
    }
    if ($enddate != 'ALL') {
	$program .= " $enddate $reptype";
    } else {
	$program .= " '' $reptype";
    }

#    print "File = $fullpath<BR>";
    print "<HR>";
    print "$program<BR>";
    $status = exec ($program);
    print "$status<BR>";
    print "<HR>";
    print "Download extract:<BR>";
    if (is_file($fullpath . '.dat')) {
        print "<A HREF='/exportfiles/$filename.dat'>$filename Data File</A><BR>";
    }
    if (is_file($fullpath . '.csv')) {
        print "<A HREF='/exportfiles/$filename.csv'>$filename CSV  File</A><BR>";
    }
    if (is_file($fullpath . '.xml')) {
        print "<A HREF='/exportfiles/$filename.xml'>$filename XML  File</A><BR>";
    }
    if (is_file($fullpath . '.zip')) {
        print "<A HREF='/exportfiles/$filename-".$enddate.".zip'>$filename ZIP  File</A><BR>";
    }
    if (is_file($fullpath . '-add.txt')) {
        print "<A HREF='/exportfiles/$filename-add.txt'>$filename Add File</A><BR>";
    }
    if (is_file($fullpath . '-inf.txt')) {
        print "<A HREF='/exportfiles/$filename-inf.txt'>$filename Error File</A><BR>";
    }
    if (is_file($fullpath . '-req.txt')) {
        print "<A HREF='/exportfiles/$filename-req.txt'>$filename Requal File</A><BR>";
    }
}
if (strlen($btop) == 0) {
    $btop = 'Option Selected';
}
print "<BR><INPUT TYPE='SUBMIT' VALUE='$btop'>";
print "</FORM>";

?>

</BODY>
