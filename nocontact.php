<?php

$csrname = $_SERVER['REMOTE_USER']; 
$self = $_SERVER['SCRIPT_NAME'];
$contact = $_REQUEST['name'];
$clientnum = $_REQUEST['clientnum'];
$book = $_REQUEST['book'];
$phone=$_REQUEST['phone'];
$reqdate = $_REQUEST['reqdate'];

if ($phone == "") {
    $phone = "4025551212";
}
$link = 0;
include "dbselect.php";
echo "<!-- DBServer = $dbserver -->\n";
$dbserver = getbookserver($book);
echo "<!-- DBServer = $dbserver -->\n";
$link = choosedatabase($dbserver,'teleservices');

if (strlen($clientnum) > 0) {
    $clientclause = "clientnum = '$clientnum' AND";
} else {
    $clientclause = "";
}

#
# Here, we check to see if there are any records In Progress.  If there are 
# not, then we don't need to requeue anything.  If there are, we need to pick 
# why.  We may be able to simplify this code down considerably now that we 
# can do Javascript Calendars.
#
echo "<!-- phone = $phone | book = $book | DBServer = $dbserver -->\n";
$query = "SELECT requeue FROM contact WHERE $clientclause phone = '$phone' AND book = '$book' AND reason = 'inprogress'";
$result = mysql_query($query)
    or die('Query failed: ' .  mysql_error());
if (mysql_num_rows($result) == 0) {
    $clientnum = '';
    $phone = '';
    $book = '';
    $reason = '';
    mysql_close($link);
    header("HTTP/1.1 301 Moved Permanently");
    header("Location: /index.php");
    exit();
}

if (strlen($reqdate) > 0) {
    $query = "UPDATE contact set requeue = '$reqdate' WHERE $clientclause phone = '$phone' AND book = '$book' AND reason = 'inprogress'";
    $result = mysql_query($query)
	or die('Query failed: ' .  mysql_error());
    if (strlen($clientnum) < 3) {
        header("Location: /dispupdate.php?book=$book&phone=$phone&convreason=requeue&reqdate=$reqdate");
    } else {
        header("Location: /dispupdate.php?book=$book&clientnum=$clientnum&phone=$phone&convreason=requeue&reqdate=$reqdate");
    }
    exit();
}
mysql_close($link);
?>

<HTML>
<HEAD>
<!-- mySQL format yyyy-mm-dd -->
<script language="JavaScript" src="/calendar/calendar3.js"></script>
<!-- Date only with year scrolling -->

</HEAD>
<BODY>

<?php
#
#  In order to get here, we need to have set the reason code on the records 
# we want to requeue to 'requeue'.  That way, we don't requeue records that 
# are complete or 'donotcall'.
#

$currenttime = time();
$firstthing = time() % 86400 + 21600;
$today = date("Y-m-d+H:", strtotime("+6 hours")) . "00:00";
$today2 = date("Y-m-d+H:", strtotime("+12 hours")) . "00:00";
$tomorrow = date("Y-m-d",strtotime("+27 hours")) . " 06:00:00";
$oneweek = date("Y-m-d", strtotime("+1 week")) . " 06:00:00";
$twoweek = date("Y-m-d", strtotime("+2 week")) . " 06:00:00";
$nextyear = date("Y", strtotime("+1 year")) . "-01-01 06:00:00";

if (strlen($clientnum) < 3) {
    print "<B>PER PHONE NUMBER DISPOSITION<BR></B>";
} else {
    print "<B>PER CLIENT NUMBER DISPOSITION<BR></B>";
}
print "Select one of the following <B>No Contact</B> reasons:<br><br>\n";

print "<A HREF=\"javascript:void(self.location.href='/dispupdate.php?book=$book&phone=$phone&convreason=requeue&reqdate=$today');\">No one able to help us - call back after $today (four hours)</A><BR><BR>\n";
print "<A HREF=\"javascript:void(self.location.href='/dispupdate.php?book=$book&phone=$phone&convreason=requeue&reqdate=$today2');\">No one able to help us - call back after $today2 (eight hours)</A><BR><BR>\n";
print "<A HREF=\"javascript:void(self.location.href='/dispupdate.php?book=$book&clientnum=$clientnum&phone=$phone&convreason=requeue&reqdate=$oneweek');\">Out of town - call back after $oneweek (one week)</A><BR><BR>\n";
print "<A HREF=\"javascript:void(self.location.href='/dispupdate.php?book=$book&clientnum=$clientnum&phone=$phone&convreason=requeue&reqdate=$twoweek');\">Out of town - call back after $twoweek (two weeks)</A><BR><BR>\n\n";

print "<!-- today   = $today   -->\n";
$reqdate = substr($tomorrow,0,10) . " 06:00:00";
print "<!-- reqdate = $reqdate -->\n";
?>

<FORM NAME='calendar' ACTION='<?php print $self; ?>'>
<input type="hidden" name='name' value='<?php print $contact;?>'>
<input type="hidden" name='book' value='<?php print $book;?>'>
<input type="hidden" name='clientnum' value='<?php print $clientnum;?>'>
<input type="hidden" name='phone' value='<?php print $phone;?>'>
<input type="text" name="reqdate" value='<?php print $reqdate;?>'>
<a href="javascript:cal.popup();document.calendar.submit();">No one available to help us/Out of town - Pick Date <img src="/calendar/img/cal.gif" width="16" height="16" border="0" alt="Pick the date"></a>
</FORM>

<SCRIPT language="JavaScript">
<!-- // create calendar object(s) just after form tag closed
     // specify form element as the only parameter (document.forms['formname'].elements['inputname']);
     // note: you can have as many calendar objects as you need for your application
        var cal = new calendar3(document.forms['calendar'].elements['reqdate']);
        cal.year_scroll = true;
        cal.time_comp = true;

//-->

</script>
<BR>
Important, and hopefully temporary, note:<BR><BR>
The Pick Date function isn't working as planned - if you want to enter an arbitrary date for a requeue, please type the date into the box provided.  The default is always tomorrow morning at 6:00 AM, but you can enter anything.  Once you enter the date you want to schedule the requeue for, click on the Calendar icon and click on the highlighted date and time.  The system will use the time you entered as the requeue time for this record.<BR><BR>
The pop-up calendar doesn't correctly allow you to change the date yet.  It will, I just haven't figured out exactly how yet.
</BODY>
</HTML>
