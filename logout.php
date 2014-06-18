<?php

include "dbselect.php";
session_id($sid);
session_start();

$csrname = $_SERVER['REMOTE_USER']; 

if (strlen($csrname) != 0) {

    choosedatabase('192.168.100.2','teleservices');
    $query = "UPDATE mysql_auth SET book = '', subcode = '', extension = '' WHERE username = '$csrname'";
    $result = mysql_query($query)
        or die('Query failed: ' .  mysql_error());
    
    mysql_close($link);
    $link = 0;
    foreach ($_SESSION as $data => $key) {
        echo "<!-- Session Data: $data / $key -->\n";
	$_SESSION[$key] = '';
    }
    session_write_close();
    header("Location: /logout/index.php");
} else {
    header("HTTP/1.1 301 Moved Permanently");
    header("Location: /index.php");
}
?>
