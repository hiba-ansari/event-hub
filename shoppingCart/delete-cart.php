<?php
session_start();
require_once __DIR__ . '/../config/features.php';

// Shopping cart is disabled behind a feature flag – reject the request.
if (!feature_enabled('shopping_cart')) {
    http_response_code(404);
    exit();
}

$dsn = 'mysql:host=talsprddb02.int.its.rmit.edu.au;dbname=COSC3046_2402_UGRD_1479_G12';
$user = 'COSC3046_2402_UGRD_1479_G12';
$pass = 'LtEXbUiTF7Fm';
$conn = new PDO($dsn, $user, $pass);

$eventID = $_POST['eventID'];
$userID = $_SESSION['userID'];
$sql = "DELETE FROM ShoppingCart WHERE EventID = '$eventID' AND UserID = '$userID'";
$conn->exec($sql);

?>
