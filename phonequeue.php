<html>
<head>
<noscript>
<!--
    We have the "refresh" meta-tag in case the user's browser does
    not correctly support JavaScript or has JavaScript disabled.

    Notice that this is nested within a "noscript" block.
-->
<meta http-equiv="refresh" content="8">

</noscript>

<script language="JavaScript">
<!--

var sURL = unescape(window.location.pathname);

function doLoad()
{
    // the timeout value should be the same as in the "refresh" meta-tag
    setTimeout( "refresh()", 8*1000 );
}

function refresh()
{
    //  This version of the refresh function will cause a new
    //  entry in the visitor's history.  It is provided for
    //  those browsers that only support JavaScript 1.0.
    //
    window.location.href = sURL;
}
//-->
</script>

<script language="JavaScript1.1">
<!--
function refresh()
{
    //  This version does NOT cause an entry in the browser's
    //  page view history.  Most browsers will always retrieve
    //  the document from the web-server whether it is already
    //  in the browsers page-cache or not.
    //  
    window.location.replace( sURL );
}
//-->
</script>

<script language="JavaScript1.2">
<!--
function refresh()
{
    //  This version of the refresh function will be invoked
    //  for browsers that support JavaScript version 1.2
    //
    
    //  The argument to the location.reload function determines
    //  if the browser should retrieve the document from the
    //  web-server.  In our example all we need to do is cause
    //  the JavaScript block in the document body to be
    //  re-evaluated.  If we needed to pull the document from
    //  the web-server again (such as where the document contents
    //  change dynamically) we would pass the argument as 'true'.
    //  
    window.location.reload( false );
}
//-->
</script>
</head>

<!--
    Use the "onload" event to start the refresh process.
-->
<body onload="doLoad()">

<script language="JavaScript">
<!--
    // we put this here so we can see something change
    document.write('');
    //document.write('<BR>');
    //document.write('<b>' + (new Date).toLocaleString() + '</b><BR>');
//-->
</script>

<?php

global $sid, $link;

function setstart($x,$zone) {
    $x = (float)$x;      
    if ($x == 0) {
        return 8.5 + (float)$zone;
    } else {
        return $x;
    }
}

function setlstrt($x,$zone) {
    $x = (float)$x;
    if ($x == 0) {
        return 11.0 + (float)$zone;
    } else {
        return $x;
    }
}

function setlnend($x,$zone) {
    $x = (float)$x;      
    if ($x == 0) {
        return 12.5 + (float)$zone;
    } else {
        return $x;
    }
}

function setstop($x,$zone) {
    $x = (float)$x;
    if ($x == 0) {
        return 17.0 + (float)$zone;
    } else {
        return $x;
    }
}


include "dbselect.php";

echo "\n<!-- phoneq SID = $sid -->\n";
session_id($sid);
session_start();

#
# We get the webserver's date, rather than
# something from the client or the DB.  That 
# way, if one clock is wrong, they are all
# wrong together.
#
$date = getdate();
$hrs = $date['hours'];
$utctime = $hrs + 6;
$min = $date['minutes'];
$sec = $date['seconds'];
$mon = $date['mon'];
$day = $date['mday'];
$yr = $date['year'];
$callingtime = $hrs + ($min/60.0);

$link = choosedatabase('192.168.100.2','teleservices');

$csrname = $_SERVER['REMOTE_USER']; 

$query = "SELECT * FROM mysql_auth WHERE username = '$csrname'";
echo "<!-- query = $query -->\n";
$result = mysql_query($query)
    or die('Query failed: ' .  mysql_error());
$authuser = mysql_fetch_assoc($result);
    
$cid = $authuser['book'];
$code = $authuser['subcode'];
$extension = $authuser['extension'];
$chatmessage = $authuser['chatmessage'];
if ($chatmessage != $_SESSION['chatmessage']) {
    $_SESSION['chatmessage'] = $chatmessage ;
    $_SESSION['chatcount'] = 25;
}
$_SESSION['book'] = $cid ;
$_SESSION['subcode'] = $code ;
$_SESSION['extension'] = $extension ;
#
# I changed my mind, but let me explain why.
#
# We need to be able to pick up book/subcode/extension/chatmessage 
# changes in the database, and this seems like a perfectly reasonable
# place to do it.  
#
#} else {
#    $cid = $_SESSION['book'];
#    $code = $_SESSION['subcode'];
#    $extension = $_SESSION['extension'];
#    $chatmessage = $_SESSION['chatmessage'];
#}

print "\n<!-- $cid | $code | $extension | $chatmessage -->\n";

$query = "SELECT * FROM book WHERE book = '$cid' LIMIT 1";
echo "<!-- query = $query -->\n";
$result = mysql_query($query)
    or die('Query failed: ' .  mysql_error());
$booklist = mysql_fetch_assoc($result);

$server = $booklist['server'];
$abbr = $booklist['abbr'];
$lower48 = $booklist['lower48'];
$estart = setstart($booklist['estart'],-1);
$elunchb = setlstrt($booklist['elunchb'],-1);
$elunche = setlnend($booklist['elunche'],-1);
$estop = setstop($booklist['estop'],-1);
$cstart = setstart($booklist['cstart'],0);
$clunchb = setlstrt($booklist['clunchb'],0);
$clunche = setlnend($booklist['clunche'],0);
$cstop = setstop($booklist['cstop'],0);
$mstart = setstart($booklist['mstart'],1);
$mlunchb = setlstrt($booklist['mlunchb'],1);
$mlunche = setlnend($booklist['mlunche'],1);
$mstop = setstop($booklist['mstop'],1);
$pstart = setstart($booklist['pstart'],2);
$plunchb = setlstrt($booklist['plunchb'],2);
$plunche = setlnend($booklist['plunche'],2);
$pstop = setstop($booklist['pstop'],2);

#
# We now use the current local wallclock to decide which timezones to allow.
#
if ($lower48 == 'U') {
    $zones = "'6','1','0','-1','-2','-3','-4','-5','-6','-7','-8'";
} else {
    $zones = "'6'";
    #Eastern
    if ($callingtime >= $estart && $callingtime <= $elunchb) {
        $zones .= ",'1'";
    } else {
        if ($callingtime >= $elunche && $callingtime <= $estop) {
    	    $zones .= ",'1'";
        }
    }
    #Central
    if ($callingtime >= $cstart && $callingtime <= $clunchb) {
        $zones .= ",'0'";
    } else {
        if ($callingtime >= $clunche && $callingtime <= $cstop) {
            $zones .= ",'0'";
        }
    }
    #Mountain
    if ($callingtime >= $mstart && $callingtime <= $mlunchb) {
        $zones .= ",'-1'";
    } else {
        if ($callingtime >= $mlunche && $callingtime <= $mstop) {
            $zones .= ",'-1'";
        }
    }
    #Pacific
    if ($callingtime >= $pstart && $callingtime <= $plunchb) {
        $zones .= ",'-2'";
    } else {
        if ($callingtime >= $plunche && $callingtime <= $pstop) {
            $zones .= ",'-2'";
        }
    }
}

if ($csrname == 'burgess') {
    $callinfo = 'callinfo.php';
    $zonelimit = '';
} else {
    $callinfo = 'callinfo.php';
    $zonelimit = "AND ltime IN (" . $zones . ")";
}

print "\n<!-- Zones = $zones -->\n";
print "<!-- z = $zonelimit -->\n";
$link = getbookserver($cid);
print "<!-- DBServer = $dbserver -->\n";

$query = "SELECT count(distinct phone) AS tcount FROM phonequeue WHERE book = '$cid' AND subcode = '$code' $zonelimit";
echo "<!-- query = $query -->\n";
$result = mysql_query($query)
    or die('Query failed: ' .  mysql_error());
while ($countlist = mysql_fetch_assoc($result)) {  
    $eligible = $countlist['tcount'];
}

$query = "SELECT * FROM phonequeue WHERE book = '$cid' AND subcode = '$code' $zonelimit ORDER BY reqcnt,ltime DESC LIMIT 350";
echo "<!-- query = $query -->\n";
$result = mysql_query($query)
    or die('Query failed: ' .  mysql_error());

$count = 1;
$dispphone = "4025551212";
while (($contact = mysql_fetch_assoc($result)) && $count < 31) {  

    if (strlen($contact['fullname']) > 0) {
	$name = $contact['fullname'];
    } else {
        $name = $contact['fname'] .' '. $contact['lname'];
    }
    $clientnum = (string)$contact['clientnum'];
    $cid = $contact['book'];
    $code = $contact['subcode'];
    $phone = $contact['phone'];
    $ltime = $contact['ltime'];
    $rtime = $hrs + $ltime;
#
    if ($phone != $dispphone) {
	if ($count < 10) {
	    print "&nbsp;&nbsp;";
	}
	print "&nbsp;$count:&nbsp;";
	print "<A HREF=\"javascript:void(parent.right_middle_frame.location.href='/$callinfo?convreason=inprogress&phone=$phone');\">";
	print "$phone</A>";
	print "<BR>";
	$count += 1;
    }
    $dispphone = $phone;
}
//$phone = "4026991226";
//print "&nbsp;$count:&nbsp;";
//print "<A HREF=\"javascript:void(parent.right_middle_frame.location.href='/$callinfo?convreason=inprogress&phone=$phone');\">";
//print "$phone</A>";
//print "<BR>";

print $count-1 . " of $eligible.";
print "<BR>";

session_write_close();
?>
</body>
</html>
