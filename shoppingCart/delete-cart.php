<?php
session_start();
$dsn = 'mysql:host=talsprddb02.int.its.rmit.edu.au;dbname=COSC3046_2402_UGRD_1479_G12';
$user = 'COSC3046_2402_UGRD_1479_G12';
$pass = 'LtEXbUiTF7Fm';
$conn = new PDO($dsn, $user, $pass);

$eventID = $_POST['eventID'];
$userID = $_SESSION['userID'];
$sql = "DELETE FROM ShoppingCart WHERE EventID = '$eventID' AND UserID = '$userID'";
$conn->exec($sql);

?>
