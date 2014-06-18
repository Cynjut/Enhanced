<html>
<head>
<meta http-equiv="content-type" content="text/html;charset=iso-8859-1">
<title>Enhanced TeleServices CSR Interface</Title>
<script language=JavaScript>
	function fullscreen(theURL){
	window.open(theURL, '', 'fullscreen=yes, scrollbars=auto');
	}
</script>

</head>

<script type="text/javascript">
<!--
if (top.frames.length!=0) {
    if (window.location.href.replace) {
        top.location.replace(self.location.href);
    } else {
        top.location.href=self.document.href;
    }
}
// -->
</script>

<?php
    global $link, $book, $username, $subcode, $groups, $longtitle, $dbserver;
    $currenthour = date("G");

    $csrname = $_SERVER['REMOTE_USER']; 
    $clientnum = $_GET['clientnum'];
    $phone=$_GET['phone'];
    $name = $_GET['name'];
    $convreason = $_GET['convreason'];
    $newphone = $_GET['newphone'];
 
#
#  We only make calls to locations that are in a reasonable calling area.
#
#  We can do that several ways, but the easiest is to try to match up the 
# timezone information based on the area code with our clock time.  We do 
# this by adding 6 hours to the current time, and then adding the UTC
# offset for the area code we are calling.  If the subsequent time is in 
# our calling window (between 8 and 20, with a gap from 12 to 13) we can
# call to that timezone.
#

include "dbselect.php";
$link = choosedatabase('192.168.100.2','teleservices');
    
$query = "SELECT utcoffset FROM timezone WHERE areacode = '$areacode'";  
$result = mysql_query($query)
    or die('Query failed: ' .  mysql_error());
$csrinfo = mysql_fetch_assoc($result);  
    
$utcoffset = $csrinfo['utcoffset'];
    
if (strlen($_SESSION['groups']) < 1) {
    $query = "SELECT groups,extension FROM mysql_auth WHERE username = '$csrname'";  
    $result = mysql_query($query)
        or die('Query failed: ' .  mysql_error());
    $csrinfo = mysql_fetch_assoc($result);  
    
    $groups = $csrinfo['groups'];
    $extension = $csrinfo['extension'];
    $_SESSION['groups'] = $groups;
    $_SESSION['extension'] = $extension ;
    session_write_close();
} else {
    $groups = $_SESSION['groups'];
    $extension = $_SESSION['extension'];
}
  
?>
   
    <frameset rows="100,700,*" border="0" frameborder="0" framespacing="0">
	<frame src="top.html" name="top_frame" scrolling="no" marginwidth="0" marginheight="0" frameborder="0">
	    <frameset cols="170,*" border="0" frameborder="0" framespacing="0">

<?php
echo "\n";
echo "<!-- info = $info -->\n";
if ($groups != "tmember") {
    echo "<frame src='/enterphone.php' name='middle_left_frame' scrolling='no' marginwidth='0' marginheight='0' frameborder='0'>";
} else if (strlen($extension) < 3) {
    echo "<frame src='/enterexten.php' name='middle_left_frame' scrolling='no' marginwidth='0' marginheight='0' frameborder='0'>";
} else {
	echo "<frame src='/phonequeue.php' name='middle_left_frame' scrolling='no' marginwidth='0' marginheight='0' frameborder='0'>";
}
    
#
# If the extension number is numeric, it's OK - but if it's text, then
# we don't want to display the option to dial.
#

echo "<frame src='/callinfo.php?phone=$phone&clientnum=$clientnum&convreason=$convreason' name='right_middle_frame' noresize marginwidth='0' marginheight='0' frameborder='0'>";
?>

	    </frameset>
	</frame>
    </frameset>
<?php
    foreach ($_SESSION as $data => $key) {
        echo "<!-- Session Data: $data / $key -->\n";
    }
?>
</html>

