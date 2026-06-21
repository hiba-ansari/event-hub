<?php
session_start();

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

// Ensure the user is logged in
if (!isset($_SESSION['email'])) {
    header("Location: login.php");
    exit();
}

$error_message = '';
$success_message = '';

// Retrieve user information from the database
$email = $_SESSION['email'];
$stmt = $conn->prepare("SELECT * FROM Accounts WHERE Email = :email");
$stmt->bindParam(':email', $email);
$stmt->execute();
$user = $stmt->fetch(PDO::FETCH_ASSOC);

// Update user details and theme when form is submitted
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $username = trim($_POST['username']);
    $phone = trim($_POST['phone']);
    $location = trim($_POST['location']);
    $hobbies = trim($_POST['hobbies']);
    $theme = isset($_POST['theme']) ? $_POST['theme'] : 'none';

    // Update query
    $updateStmt = $conn->prepare("UPDATE Accounts SET UserName = :username, PhoneNo = :phone, Location = :location, Hobbies = :hobbies, Theme = :theme WHERE Email = :email");
    $updateStmt->bindParam(':username', $username);
    $updateStmt->bindParam(':phone', $phone);
    $updateStmt->bindParam(':location', $location);
    $updateStmt->bindParam(':hobbies', $hobbies);
    $updateStmt->bindParam(':theme', $theme);
    $updateStmt->bindParam(':email', $email);

    if ($updateStmt->execute()) {
        $success_message = "Profile updated successfully!";
        // Refresh user data
        $stmt->execute();
        $user = $stmt->fetch(PDO::FETCH_ASSOC);

        // Update session theme variable
        $_SESSION['theme'] = $user['Theme'];
    } else {
        $error_message = "There was an error updating your profile. Please try again.";
    }
}

// Determine which CSS file to load based on the user's theme preference
$themeCssFile = ($user['Theme'] == 'dark') ? 'css/dark-theme.css' : (($user['Theme'] == 'light') ? 'css/light-theme.css' : ''); 
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Profile</title>
    <link rel="stylesheet" href="userp.css"> <!-- Assuming this is for general styling -->
    <?php if ($themeCssFile): ?>
        <link rel="stylesheet" href="<?php echo htmlspecialchars($themeCssFile); ?>"> <!-- Load theme CSS if set -->
    <?php endif; ?>
</head>
<body class="<?php echo htmlspecialchars($user['Theme']) . '-theme'; ?>">
<div class='topnav'>
        <div class='column left'>
            <a href='../homePage/homepage.php' class="logo" style='font-size:40px;font-weight:600'><img src='../images/logo.png' style='width:50px;vertical-align:middle'> Local Event Hub</a>
        </div>
        <div class='column right'>
            <p class='nav'><a href="../profilePage/account.php">Account</a></p>
            <?php
                if (isset($_SESSION['userID'])){
                    echo "
                    <p class='nav'><a href=\"../shoppingCart/shopping-cart.php\">Cart</a></p>
                    <p class='nav'><a href=\"../calendar/events.php\">My Events</a></p>";
                }
            ?>
            <p class='nav'><a href="../discussion/discussion.php">Discussions</a></p>
            <p class='nav'><a href="../searchPage/search.php">Search</a></p>
        </div>
    </div>
    <div class="container">
        <h2>Your Profile</h2>
        <?php if ($error_message): ?>
            <p class="error"><?php echo htmlspecialchars($error_message); ?></p>
        <?php endif; ?>
        <?php if ($success_message): ?>
            <p class="success"><?php echo htmlspecialchars($success_message); ?></p>
        <?php endif; ?>

        <form action="profile.php" method="post">
            <label for="username">Username:</label>
            <input type="text" id="username" name="username" value="<?php echo htmlspecialchars($user['UserName']); ?>" required>

            <label for="email">Email:</label>
            <input type="email" id="email" name="email" value="<?php echo htmlspecialchars($user['Email']); ?>" disabled>

            <label for="phone">Phone Number:</label>
            <input type="text" id="phone" name="phone" value="<?php echo htmlspecialchars($user['PhoneNo']); ?>">

            <label for="location">Location:</label>
            <input type="text" id="location" name="location" value="<?php echo htmlspecialchars($user['Location']); ?>">

            <label for="hobbies">Hobbies:</label>
            <textarea id="hobbies" name="hobbies"><?php echo htmlspecialchars($user['Hobbies']); ?></textarea>

            <!-- Theme selection -->
            <label for="theme">Choose Theme:</label>
            <select id="theme" name="theme">
                <option value="light" <?php if ($user['Theme'] == 'light') echo 'selected'; ?>>Light</option>
                <option value="dark" <?php if ($user['Theme'] == 'dark') echo 'selected'; ?>>Dark</option>
                <option value="none" <?php if ($user['Theme'] == 'none' || empty($user['Theme'])) echo 'selected'; ?>>None</option>
            </select>

            <button type="submit">Update Profile</button>
        </form>
    </div>
</body>

<footer class="footer">
        <div class="footer-content">
            <div class="footer-column footer-logo">
                <img src="../images/logo.png" alt="Logo">
            </div>
            <div class="footer-column">
                <h3>Events</h3>
                <a href="../groupsPage/groups.html">Groups</a>
                <a href="../searchPage/search.php">Search</a>
            </div>
            <div class="footer-column">
                <h3>Account</h3>
                <a href="../profilePage/account.php">Account</a>
                <a href="../calendar/events.html">My Events</a>
            </div>
        </div>
        <p style="color: #202335; position: absolute; margin: 0; transform: translateX(50%); right: 50%; bottom: 5%;">Contact Group 9 - 
            <a href="mailto:s4100892@student.rmit.edu.au" style="color: inherit;">Hiba Ansari (s4100892)</a>
            ,
            <a href="mailto:s3842127@student.rmit.edu.au" style="color: inherit;">Robert Vo-Ho (s3842127)</a>
            ,
            <a href="mailto:s4038608@student.rmit.edu.au" style="color: inherit;">Mahita Jain (s4038608)</a>
            ,
            <a href="mailto:s4026932@student.rmit.edu.au" style="color: inherit;">Hami Faizal (s4026932)</a>
        </p>
    </footer>

</html>
