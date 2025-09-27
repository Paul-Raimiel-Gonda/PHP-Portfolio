<?php
session_start();
session_unset();   // clear session variables
session_destroy(); // destroy session
header("Location: index.php");
exit;
