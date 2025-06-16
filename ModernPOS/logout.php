<?php
// 1. Initialize the session
// To access session variables, we must always start the session.
session_start();
 
// 2. Unset all of the session variables
// This effectively logs the user out by clearing their data.
$_SESSION = array();
 
// 3. Destroy the session from the server
// This removes the session file and finalizes the logout.
session_destroy();
 
// 4. Redirect the user to the login page
// After logging out, send them back to the login screen.
header("location: login.php");
exit;
?>