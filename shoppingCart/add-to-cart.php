<?php
session_start();
$dsn = 'mysql:host=talsprddb02.int.its.rmit.edu.au;dbname=COSC3046_2402_UGRD_1479_G12';
$user = 'COSC3046_2402_UGRD_1479_G12';
$pass = 'LtEXbUiTF7Fm';
$conn = new PDO($dsn, $user, $pass);

$eventID = $_POST['eventID'];
$quantity = $_POST['quantity'];
$userID = $_SESSION['userID'];
$sql = "INSERT INTO ShoppingCart (EventID, NumTickets, UserID) VALUES ('$eventID', '$quantity', '$userID')";
$conn->exec($sql);
?>
