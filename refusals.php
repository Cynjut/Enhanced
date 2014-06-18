<BODY>
<?php
    $csrname = $_SERVER['REMOTE_USER']; 
    $book = $_GET['book'];
    $clientnum = $_GET['clientnum'];
    $phone=$_GET['phone'];
    if ($phone == "") {
        $phone = "4025551212";
    }
    $convreason = $_GET['convreason'];
    $contact = $_GET['name'];
    $fs = $_GET['fs'];

include "dbselect.php";
$dbserver = getbookserver($book);

if (strlen($clientnum) == 0) {
    $query = "UPDATE contact SET completed = now(), reason = 'refused', csr = '$csrname', requeue = NULL WHERE phone='$phone' AND book='$book' and reason = 'inprogress'";
} else {
    $query = "UPDATE contact SET completed = now(), reason = 'refused', csr = '$csrname', requeue = NULL WHERE clientnum='$clientnum' AND phone='$phone' AND book='$book'";
}
$result = mysql_query($query)
    or die('Query failed: ' .  mysql_error());

mysql_close($link);
$link = 0;

print "Select one of the following reasons for refusal:<br>";

if ($convreason == "") {
    print "<A HREF='/callinfo.php?fs=1&book=$book&phone=$phone&clientnum=$clientnum&convreason=notqual' TARGET='_top'>Do not qualify for the magazine</A><BR><BR>";
    print "<A HREF='/callinfo.php?fs=1&book=$book&phone=$phone&clientnum=$clientnum&convreason=notwant' TARGET='_top'>Do Not Want the magazine</A><BR><BR>";
    print "<A HREF='/callinfo.php?fs=1&book=$book&phone=$phone&clientnum=$clientnum&convreason=seccan' TARGET='_top'>Authorized person (other than label person) wants to cancel</A><BR><BR>";
    print "<A HREF='/callinfo.php?fs=1&book=$book&phone=$phone&clientnum=$clientnum&convreason=nothere' TARGET='_top'>No longer with the company - no replacement</A><BR><BR>";
    print "<A HREF='/callinfo.php?fs=1&book=$book&phone=$phone&clientnum=$clientnum&convreason=noneed' TARGET='_top'>No longer needed - new job responsibilities</A><BR><BR>";
    print "<A HREF='/callinfo.php?fs=1&book=$book&phone=$phone&clientnum=$clientnum&convreason=hungup' TARGET='_top'>Hung Up or No Reason Given</A><BR><BR>";
    print "<A HREF='/callinfo.php?fs=1&book=$book&phone=$phone&clientnum=$clientnum&convreason=langprob' TARGET='_top'>Language problems</A><BR><BR>";
    print "<A HREF='/callinfo.php?fs=1&book=$book&phone=$phone&clientnum=$clientnum&convreason=goingout' TARGET='_top'>Company going out of business</A><BR><BR>";
    print "<A HREF='/callinfo.php?fs=1&book=$book&phone=$phone&clientnum=$clientnum&convreason=toomany' TARGET='_top'>Too many magazines</A><BR><BR>";
    print "<A HREF='/callinfo.php?fs=1&book=$book&phone=$phone&clientnum=$clientnum&convreason=calllim' TARGET='_top'>Too many calls to update information</A><BR><BR>";
    print "<A HREF='/callinfo.php?fs=1&book=$book&phone=$phone&clientnum=$clientnum&convreason=deceased' TARGET='_top'>Deceased</A><BR><BR>";
    print "<A HREF='/callinfo.php?fs=1&book=$book&phone=$phone&convreason=donotcall' TARGET='_top'>Please put me on your Do Not Call list</A><BR>";
}

?>

</BODY>
