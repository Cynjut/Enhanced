<TITLE>
Call List Uploader
</TITLE>
<BODY>

<?php

set_time_limit(0);

$book = '';
$subcode = '';

$username = $_SERVER['REMOTE_USER']; 
$book = $_POST['book'];
$subcode = $_POST['subcode'];
$keepold = $_POST['keepold'];
$clearold = $_POST['clearold'];
$preproc = $_POST['preproc'];
$refused = $_POST['refused'];

print "<!-- preproc = $preproc -->\n";
print "<!-- refused = $refused -->\n";

$myfile = trim($_FILES['myfiles']['name']);
$tfile =  trim($_FILES['myfiles']['tmp_name']);

$link = mysql_connect('192.168.100.2', 'ets1', 'ets1pass')
    or die('Could not connect: ' .  mysql_error());

mysql_select_db('teleservices')
    or die('Could not select database');

if (strlen($book) == 0) {
    print "<FORM METHOD='POST' ACTION='/import/import.php'>";
    print "<B>Select Book for new data:</B><BR>";
    print "<SELECT name='book' SIZE=13>";

    $query = "SELECT book,abbr FROM book WHERE length(book) > 3 ";
    if ($username != 'burgess') { 
	$query .= "AND book != 'Test' ";
    }
    $query .= "ORDER BY book";
    $result = mysql_query($query)
	or die('SELECT book Query failed: ' .  mysql_error());
    while ($booklist = mysql_fetch_assoc($result)) {
	$book = $booklist['book'];
	$abbr = $booklist['abbr'];
	print "<OPTION value='$book'>$book ($abbr)</OPTION>";
    }
    print "<INPUT TYPE='HIDDEN' NAME='subcode' VALUE=''>";
    print "</SELECT><BR>\n";
    print "<BR><INPUT TYPE='SUBMIT' VALUE='Book Selected'>";
    print "</FORM>";
    print "<A HREF='/manager/' TARGET='_top'>Click here to return to Manager Page</A>";

} else if (strlen($subcode) == 0) {
    print "<FORM METHOD='POST' ACTION='/import/import.php'>";
    print "By default, the questionnaire response data from the last time ";
    print "you processed lists for this book are not deleted and can be used ";
    print "in this campaign.  If you are importing new data or want to ";
    print "to make sure your questrep data is always new, leave the ";
    print "'Keep Old Data' box below unchecked:";
    print "<BR><BR>";
    print "<input type='checkbox' name='keepold'>Keep Old Data<BR><BR>";
    print "If you want to delete ALL old data (and over-ride the Keep Old ";
    print "Data checkbox) that might exist for ";
    print "the records you are importing select the 'Clear Old Data' ";
    print "checkbox below:<BR><BR>";
    print "<input type='checkbox' name='clearold'>Clear Old Data<BR><BR><HR>";
    print "You can preprocess the list to not add records which you ";
    print "know are out-of-business, wrong numbers, etc. by selecting the ";
    print "'Preprocess' ";
    print "checkbox below:<BR><BR>";
    print "<input type='checkbox' name='preproc' checked>Preprocess<BR><BR>";
    print "(use to preprocess both new and requal lists)<HR>";
    print "You can preprocess the list to not include records for which ";
    print "someone from this number has refused this magazine in the past ";
    print "by selecting the ";
    print "'Preprocess Against REFUSALS.' ";
    print "checkbox below:<BR><BR>";
    print "<input type='checkbox' name='refused'>Preprocess Against REFUSALS<BR><BR>";
    print "(Useful for NEW lists only.)<HR>";
    print "Subcode is optional, but errors can occur if there is no ";
    print "subcode in the upload file.  If you enter a code here it will ";
    print "override the one in the file.  To use the one in the file, type ";
    print "<B>FILE</B> in the space below.</P>";
    print "<B>Enter Effort Code for $book:</B><BR>";
    print "<INPUT TYPE='TEXT' NAME='subcode' VALUE=''>";
    print "<INPUT TYPE='HIDDEN' NAME='book' VALUE='$book'>";
    print "<BR><INPUT TYPE='SUBMIT' VALUE='Effort Code Entered'>";
    print "</FORM>";
    print "<A HREF='/manager/' TARGET='_top'>Click here to return to Manager Page</A>";
} else {
    echo "<div align='center'>";
    echo "<center>";
    echo "<form method='post' enctype='multipart/form-data'>";
    echo "<table border='0' width='640'>";
    echo "<tr>"; 
    echo "<td width='100%' bgcolor='#EEEEEE'>";
    echo "<table border='1' width='100%' bordercolor='#000000' style='font-family: Verdana; font-size: 8pt'>";
    echo "<tr>";
    echo "<td width='100%' align='center' colspan=3>";
    echo "<b>Enhanced Teleservices Campaign Upload Program</b>";
    echo "</td>";
    echo "</tr>";

    echo "<tr>";
    echo "<td width='100%' align='center' colspan=3><b>Instructions: </b>";
    echo "This is the file upload component of the program.  ";
    echo "Use the 'Browse' button to find the Comma Seperated Values (CSV) file that has ";
    echo "your new records in it.  Fill out the rest of the form and click 'Upload'.  ";
    echo "The file you've selected will be uploaded to the server and them copied into the ";
    echo "database.<BR>";
#   print "Book = $book<BR>";
#   print "Subcode = $subcode<BR>";
#   print "Upload = $myfile<BR>";
#   print "TmpFile = $tfile<BR>";
    echo "</td>";
    echo "</tr>";

    // Fix path of your file to be uploaded
    // don't forget to CHMOD 777 to this folder
    $file_dir = "/usr/share/httpd/ets/import/";
    echo "<tr>";
    if (strlen($myfile) > 0) {
	$filename = "/usr/share/httpd/ets/import/import.csv";
	move_uploaded_file($tfile, $filename);
	$program = "/usr/share/httpd/ets/import/import '$book' '$filename'" ;
	if ($subcode != 'FILE') {
	    $program .= " '" . $subcode . "'";
	}
	if ($clearold == 'on') {
	    $program .= " 'CLEAROLD'";
	} else {
	    if ($keepold == 'on') {
	        $program .= " 'KEEPOLD'";
	    }
        }
	if ($preproc == 'on') {
	    $program .= " 'PREPROC'";
	}
	if ($refused == 'on') {
	    $program .= " 'REFUSED'";
	}
	print "\n<!-- File = $filename -->\n";
	print "<!-- Program = $program -->\n";
	$status = exec ($program);
	$book = '';
	$subcode = '';
	$keepold = '';
	$clearold = '';
	$preproc = '';
	$refused = '';
    }

    echo "<td width='100%' bgcolor='#EEEEEE' colspan=3>";
    print "<INPUT TYPE='HIDDEN' NAME='book' VALUE='$book'>";
    print "<INPUT TYPE='HIDDEN' NAME='subcode' VALUE='$subcode'>";
    print "<INPUT TYPE='HIDDEN' NAME='keepold' VALUE='$keepold'>";
    print "<INPUT TYPE='HIDDEN' NAME='clearold' VALUE='$clearold'>";
    print "<INPUT TYPE='HIDDEN' NAME='preproc' VALUE='$preproc'>";
    print "<INPUT TYPE='HIDDEN' NAME='refused' VALUE='$refused'>";
    if (strlen($book) != 0) {
	echo "Upload <br>";
    	print "<input type='file' name='myfiles' size='30'>";
	print "&nbsp;&nbsp;";
	print "<input type='submit' name='action' value='Upload'>";
    } else {
	print "$status<BR>";
#	print "$filename<BR>";
	print "Import Complete<BR>";
	print "<input type='submit' name='action' value='Continue'>";
#	unlink ($filename);
    }
    echo "</td>";
    echo "</tr>";
    print "</form>";
    print "</table>";
    print "</div>";
}

?>

</BODY>
