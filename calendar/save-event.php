<?php
/**
 * AJAX endpoint to toggle whether an event is saved to the current user's calendar.
 * POST param: event_id
 * Returns JSON: { ok: bool, saved: bool }
 */
session_start();

// Load local database configuration
require_once '../config/database_local.php';

header('Content-Type: application/json');

// Must be logged in to save events
if (empty($_SESSION['userID'])) {
    http_response_code(401);
    echo json_encode(['ok' => false, 'error' => 'not_logged_in']);
    exit();
}

$eventID = $_POST['event_id'] ?? $_GET['event_id'] ?? null;
if (!$eventID) {
    http_response_code(400);
    echo json_encode(['ok' => false, 'error' => 'missing_event_id']);
    exit();
}

$dsn = "mysql:host=" . DB_HOST . ";dbname=" . DB_NAME;
$userID = $_SESSION['userID'];

try {
    $conn = new PDO($dsn, DB_USER, DB_PASS);
    $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    // Verify the event actually exists
    $chk = $conn->prepare("SELECT EventID FROM Events WHERE EventID = :id");
    $chk->execute([':id' => $eventID]);
    if (!$chk->fetch()) {
        http_response_code(404);
        echo json_encode(['ok' => false, 'error' => 'event_not_found']);
        exit();
    }

    // Toggle membership in SavedEvents
    $sel = $conn->prepare("SELECT 1 FROM SavedEvents WHERE EventID = :id AND UserID = :u");
    $sel->execute([':id' => $eventID, ':u' => $userID]);

    if ($sel->fetch()) {
        $del = $conn->prepare("DELETE FROM SavedEvents WHERE EventID = :id AND UserID = :u");
        $del->execute([':id' => $eventID, ':u' => $userID]);
        echo json_encode(['ok' => true, 'saved' => false]);
    } else {
        $ins = $conn->prepare("INSERT INTO SavedEvents (EventID, UserID) VALUES (:id, :u)");
        $ins->execute([':id' => $eventID, ':u' => $userID]);
        echo json_encode(['ok' => true, 'saved' => true]);
    }
} catch (PDOException $e) {
    http_response_code(500);
    echo json_encode(['ok' => false, 'error' => 'database_error']);
}
