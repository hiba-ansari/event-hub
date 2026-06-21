<?php
ob_start();
session_start();

    //echo session details
    // $sessionDetails = implode(",", $_SESSION);
    // echo "\$sessionDetails = " . $sessionDetails . "<br>"; 
    // echo "\$_SESSION = " . json_encode($_SESSION) . "<br>";
    // echo "\$_SESSION['email'] = " . $_SESSION['email'] . "<br>";
    // echo "\$_SESSION['userID'] = " . $_SESSION['userID'] . "<br>";

    if (!isset($_SESSION['userID']) || empty($_SESSION['userID'])) {
        include_once 'logged-out.php';
    }
    else {
        if (isset($_SESSION['userID'])) {
            include_once 'logged-in.php';
        }
    }
    ob_end_flush();
?>