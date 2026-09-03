<?php
session_start();

// Handle logout
if (isset($_POST['logout'])) {
    session_unset();
    session_destroy();
    header("Location: ../profilePage/login.php"); // Redirect to login page after logout
    exit();
}

// Redirect to login if not logged in
if (!isset($_SESSION['userID'])) {
    header("Location: login.php");
    exit();
}

// Load local database configuration
require_once '../config/database_local.php';

// Database connection using local config
$dsn = "mysql:host=" . DB_HOST . ";dbname=" . DB_NAME;
$user = DB_USER;
$pass = DB_PASS;

try {
    $conn = new PDO($dsn, $user, $pass);
    $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    // Fetch user details based on session userID
    $stmt = $conn->prepare("SELECT UserName, Email, PhoneNo, Location, Hobbies, ProfileImage FROM Accounts WHERE UserID = :userID");
    $stmt->bindParam(':userID', $_SESSION['userID'], PDO::PARAM_INT);
    $stmt->execute();
    $user_details = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$user_details) {
        // Handle case where user ID doesn't exist in DB
        session_destroy();
        header("Location: login.php");
        exit();
    }

} catch (PDOException $e) {
    die("Database error fetching profile: " . $e->getMessage());
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>My Profile</title>
    <link rel="stylesheet" href="userp.css">
    <style>
        .profile-container {
            display: flex;
            flex-direction: column;
            align-items: center;
            max-width: 600px;
            margin: 2em auto;
            padding: 2em;
            border: 1px solid #ccc;
            border-radius: 8px;
            background-color: #f9f9f9;
        }
        .profile-detail {
            margin-bottom: 1em;
            width: 100%;
            text-align: left;
        }
        .profile-label {
            font-weight: bold;
            margin-right: 10px;
        }
        .profile-value {
            margin-left: 10px;
        }
        .profile-image {
            max-width: 200px;
            max-height: 200px;
            border-radius: 50%;
            object-fit: cover;
            margin-bottom: 1em;
        }
        .edit-profile-btn {
            margin-top: 1em;
            padding: 0.5em 1em;
            background-color: #4E598C;
            color: white;
            border: none;
            border-radius: 4px;
            cursor: pointer;
        }
        .edit-profile-btn:hover {
            background-color: #3a436d;
        }
    </style>
</head>
<body>

<div class='topnav'>
    <div class='column left'>
        <a href='../homePage/homepage.php' class="logo" style='font-size:40px;font-weight:600'>
            <img src='../images/logo.png' style='width:50px;vertical-align:middle'> Local Event Hub
        </a>
    </div>
    <div class='column right'>
        <p class='nav'><a href="view_profile.php">Account</a></p>
        <p class='nav'><a href="../shoppingCart/shopping-cart.php">Cart</a></p>
        <p class='nav'><a href="../calendar/events.php">My Events</a></p>
        <p class='nav'><a href="../discussion/discussion.php">Discussions</a></p>
        <p class='nav'><a href="../searchPage/search.php">Search</a></p>
    </div>
</div>

<div class="profile-container">
    <h2>My Profile</h2>
    <?php if ($user_details['ProfileImage']): ?>
        <img src="../profilePage/<?php echo htmlspecialchars($user_details['ProfileImage']); ?>" alt="Profile Image" class="profile-image">
    <?php else: ?>
        <p>No profile image set.</p>
    <?php endif; ?>
    
    <div class="profile-detail">
        <span class="profile-label">Username:</span>
        <span class="profile-value"><?php echo htmlspecialchars($user_details['UserName']); ?></span>
    </div>
    <div class="profile-detail">
        <span class="profile-label">Email:</span>
        <span class="profile-value"><?php echo htmlspecialchars($user_details['Email']); ?></span>
    </div>
    <div class="profile-detail">
        <span class="profile-label">Phone Number:</span>
        <span class="profile-value"><?php echo htmlspecialchars($user_details['PhoneNo'] ?: 'Not provided'); ?></span>
    </div>
    <div class="profile-detail">
        <span class="profile-label">Location:</span>
        <span class="profile-value"><?php echo htmlspecialchars($user_details['Location'] ?: 'Not provided'); ?></span>
    </div>
    <div class="profile-detail">
        <span class="profile-label">Hobbies:</span>
        <span class="profile-value"><?php echo htmlspecialchars($user_details['Hobbies'] ?: 'Not provided'); ?></span>
    </div>
    <a href="login.php"><button class="edit-profile-btn">Edit Profile / Change Password</button></a>
    
    <!-- Logout Button -->
    <form method="post" style="display:inline; margin-top: 1em;">
        <button type="submit" name="logout" class="edit-profile-btn" style="background-color: #d9534f;">Logout</button>
    </form>
</div>

</body>
</html>