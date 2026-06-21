<?php
session_start();

// Check if the user is logged in and is an admin
if (!isset($_SESSION['email']) || !isset($_SESSION['isAdmin']) || $_SESSION['isAdmin'] != 1) {
    header("Location: login.php"); // Redirect non-admins to login
    exit();
}

// Database connection using PDO
$dsn = 'mysql:host=talsprddb02.int.its.rmit.edu.au;dbname=COSC3046_2402_UGRD_1479_G12';
$user = 'COSC3046_2402_UGRD_1479_G12';
$pass = 'LtEXbUiTF7Fm';

try {
    $conn = new PDO($dsn, $user, $pass);
    $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (PDOException $e) {
    die("Database connection failed: " . $e->getMessage());
}

$error_message = '';
$success_message = '';

// Lock or unlock account
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['userId'], $_POST['action'])) {
    $userId = $_POST['userId'];
    $action = $_POST['action'] === 'lock' ? 1 : 0;

    $stmt = $conn->prepare("UPDATE Accounts SET isLocked = :action WHERE UserID = :userId");
    $stmt->bindParam(':action', $action);
    $stmt->bindParam(':userId', $userId);

    if ($stmt->execute()) {
        $success_message = "Account " . ($action ? "locked" : "unlocked") . " successfully.";
    } else {
        $error_message = "Error updating account status.";
    }
}

// Retrieve all user accounts
$stmt = $conn->prepare("SELECT UserID, UserName, Email, isLocked FROM Accounts");
$stmt->execute();
$users = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Retrieve statistics for charts
// Daily logins (assuming a 'logins' table with 'login_date' column)
$loginData = $conn->query("SELECT DATE(login_date) AS login_date, COUNT(*) AS login_count FROM Logins GROUP BY DATE(login_date) ORDER BY login_date DESC LIMIT 7")->fetchAll(PDO::FETCH_ASSOC);

// Account creation data
$creationData = $conn->query("SELECT DATE(created_at) AS creation_date, COUNT(*) AS creation_count FROM Accounts GROUP BY DATE(created_at) ORDER BY creation_date DESC LIMIT 7")->fetchAll(PDO::FETCH_ASSOC);

// Archived accounts data
$archivedData = $conn->query("SELECT DATE(archived_at) AS archived_date, COUNT(*) AS archived_count FROM Accounts WHERE isArchived = 1 GROUP BY DATE(archived_at) ORDER BY archived_date DESC LIMIT 7")->fetchAll(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Admin - Manage Accounts</title>
    <link rel="stylesheet" href="admin.css">
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
</head>
<body>
    <h2>Admin Panel - Manage User Accounts</h2>

    <?php if ($error_message): ?>
        <p class="error"><?php echo htmlspecialchars($error_message); ?></p>
    <?php endif; ?>
    <?php if ($success_message): ?>
        <p class="success"><?php echo htmlspecialchars($success_message); ?></p>
    <?php endif; ?>

    <table>
        <thead>
            <tr>
                <th>User ID</th>
                <th>Username</th>
                <th>Email</th>
                <th>Status</th>
                <th>Action</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($users as $user): ?>
                <tr>
                    <td><?php echo htmlspecialchars($user['UserID']); ?></td>
                    <td><?php echo htmlspecialchars($user['UserName']); ?></td>
                    <td><?php echo htmlspecialchars($user['Email']); ?></td>
                    <td><?php echo $user['isLocked'] ? 'Locked' : 'Active'; ?></td>
                    <td>
                        <form action="admin.php" method="post" style="display:inline;">
                            <input type="hidden" name="userId" value="<?php echo $user['UserID']; ?>">
                            <?php if ($user['isLocked']): ?>
                                <button type="submit" name="action" value="unlock">Unlock</button>
                            <?php else: ?>
                                <button type="submit" name="action" value="lock">Lock</button>
                            <?php endif; ?>
                        </form>
                    </td>
                </tr>
            <?php endforeach; ?>
        </tbody>
    </table>

    <h2>Admin Dashboard - User Activity Statistics</h2>

    <!-- Daily Logins Chart -->
    <canvas id="loginChart" width="400" height="200"></canvas>

    <!-- Account Creations Chart -->
    <canvas id="creationChart" width="400" height="200"></canvas>

    <!-- Archived Accounts Chart -->
    <canvas id="archivedChart" width="400" height="200"></canvas>

    <script>
        // Prepare data for Daily Logins Chart
        const loginLabels = <?php echo json_encode(array_column($loginData, 'login_date')); ?>;
        const loginCounts = <?php echo json_encode(array_column($loginData, 'login_count')); ?>;

        const loginChartCtx = document.getElementById('loginChart').getContext('2d');
        new Chart(loginChartCtx, {
            type: 'line',
            data: {
                labels: loginLabels,
                datasets: [{
                    label: 'Daily Logins',
                    data: loginCounts,
                    fill: true,
                    borderColor: 'blue',
                    backgroundColor: 'rgba(0, 0, 255, 0.1)',
                    tension: 0.3,
                    pointBackgroundColor: 'blue',
                    pointRadius: 4,
                }]
            },
            options: {
                responsive: true,
                plugins: {
                    title: {
                        display: true,
                        text: 'Daily Logins Over the Last 7 Days',
                        font: { size: 20 }
                    },
                    legend: { display: true, position: 'top' },
                },
                scales: {
                    x: { title: { display: true, text: 'Date', font: { size: 14 } } },
                    y: { title: { display: true, text: 'Logins', font: { size: 14 } }, beginAtZero: true }
                }
            }
        });

        // Account Creations Chart
        const creationLabels = <?php echo json_encode(array_column($creationData, 'creation_date')); ?>;
        const creationCounts = <?php echo json_encode(array_column($creationData, 'creation_count')); ?>;

        const creationChartCtx = document.getElementById('creationChart').getContext('2d');
        new Chart(creationChartCtx, {
            type: 'bar',
            data: {
                labels: creationLabels,
                datasets: [{
                    label: 'Account Creations',
                    data: creationCounts,
                    backgroundColor: 'rgba(34, 139, 34, 0.8)',
                    borderColor: 'green',
                    borderWidth: 1
                }]
            },
            options: {
                responsive: true,
                plugins: {
                    title: {
                        display: true,
                        text: 'Account Creations Over the Last 7 Days',
                        font: { size: 20 }
                    },
                    legend: { display: true, position: 'top' },
                },
                scales: {
                    x: { title: { display: true, text: 'Date', font: { size: 14 } } },
                    y: { title: { display: true, text: 'Number of Accounts Created', font: { size: 14 } }, beginAtZero: true }
                }
            }
        });

        // Archived Accounts Chart
        const archivedLabels = <?php echo json_encode(array_column($archivedData, 'archived_date')); ?>;
        const archivedCounts = <?php echo json_encode(array_column($archivedData, 'archived_count')); ?>;

        const archivedChartCtx = document.getElementById('archivedChart').getContext('2d');
        new Chart(archivedChartCtx, {
            type: 'bar',
            data: {
                labels: archivedLabels,
                datasets: [{
                    label: 'Archived Accounts',
                    data: archivedCounts,
                    backgroundColor: 'rgba(255, 0, 0, 0.8)',
                    borderColor: 'darkred',
                    borderWidth: 1
                }]
            },
            options: {
                responsive: true,
                plugins: {
                    title: {
                        display: true,
                        text: 'Archived Accounts Over the Last 7 Days',
                        font: { size: 20 }
                    },
                    legend: { display: true, position: 'top' },
                },
                scales: {
                    x: { title: { display: true, text: 'Date', font: { size: 14 } } },
                    y: { title: { display: true, text: 'Number of Archived Accounts', font: { size: 14 } }, beginAtZero: true }
                }
            }
        });
    </script>
</body>
</html>
