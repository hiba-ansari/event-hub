<?php
session_start();
error_reporting(E_ALL);
ini_set('display_errors', 1);

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
$is_login_successful = false;

// Generate a random CAPTCHA code if not set
if (empty($_SESSION['captcha_code'])) {
    $_SESSION['captcha_code'] = substr(str_shuffle("0123456789abcdefghijklmnopqrstuvwxyz"), 0, 6);
}
$captcha_code = $_SESSION['captcha_code'];

// Logout handling
if (isset($_POST['logout'])) {
    session_unset();
    session_destroy();
    header("Location: login.php");
    exit();
}

// Login handling with CAPTCHA verification and lockout check
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['login'])) {
    $email = trim($_POST['email']);
    $password = $_POST['password'];
    $input_captcha = trim(strtolower($_POST['captcha'])); // Convert input to lowercase and trim

    // Check if the account exists and retrieve lockout status
    $stmt = $conn->prepare("SELECT failed_attempts, lockout_time, isLocked FROM Accounts WHERE Email = :email");
    $stmt->bindParam(':email', $email);
    $stmt->execute();
    $user = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($user) {
        // Check if the account is locked
        if ($user['isLocked']) {
            $error_message = "Your account is locked. Please contact the administrator.";
        } elseif ($user['lockout_time'] && new DateTime() < new DateTime($user['lockout_time'])) {
            // Check lockout time
            $error_message = "Account locked due to multiple failed login attempts. Please try again after an hour.";
        } elseif ($input_captcha !== strtolower($_SESSION['captcha_code'])) {
            // CAPTCHA verification
            $error_message = "Invalid CAPTCHA. Please try again.";
            $_SESSION['captcha_code'] = substr(str_shuffle("0123456789abcdefghijklmnopqrstuvwxyz"), 0, 6);
        } else {
            // Process login attempt
            $stmt = $conn->prepare("SELECT * FROM Accounts WHERE Email = :email");
            $stmt->bindParam(':email', $email);
            $stmt->execute();
            $user = $stmt->fetch(PDO::FETCH_ASSOC);

            if ($user && password_verify($password, $user['Password'])) {
                if ($user['isArchived']) {
                    $error_message = "Your account is archived and cannot be accessed.";
                } else {
                    // Reset failed attempts on successful login
                    $reset_attempts_stmt = $conn->prepare("UPDATE Accounts SET failed_attempts = 0, lockout_time = NULL WHERE Email = :email");
                    $reset_attempts_stmt->bindParam(':email', $email);
                    $reset_attempts_stmt->execute();

                    $_SESSION['email'] = $user['Email'];
                    $_SESSION['isAdmin'] = $user['IsAdmin'];
                    $_SESSION['userID'] = $user['UserID'];
                    unset($_SESSION['captcha_code']);
                    $success_message = "Login successful!";
                    $is_login_successful = true;
                }
            } else {
                // Increment failed attempts on failed login
                $failed_attempts = $user['failed_attempts'] + 1;
                if ($failed_attempts >= 3) {
                    // Lock account for 1 hour
                    $lockout_time = (new DateTime())->add(new DateInterval('PT1H'))->format('Y-m-d H:i:s');
                    $update_stmt = $conn->prepare("UPDATE Accounts SET failed_attempts = :failed_attempts, lockout_time = :lockout_time WHERE Email = :email");
                    $update_stmt->bindParam(':failed_attempts', $failed_attempts);
                    $update_stmt->bindParam(':lockout_time', $lockout_time);
                    $update_stmt->bindParam(':email', $email);
                    $update_stmt->execute();

                    $error_message = "Account locked due to multiple failed login attempts. Please try again after an hour.";
                } else {
                    // Update failed attempts without locking the account
                    $update_stmt = $conn->prepare("UPDATE Accounts SET failed_attempts = :failed_attempts WHERE Email = :email");
                    $update_stmt->bindParam(':failed_attempts', $failed_attempts);
                    $update_stmt->bindParam(':email', $email);
                    $update_stmt->execute();

                    $error_message = "Invalid email or password. Attempt $failed_attempts of 3.";
                }
                $_SESSION['captcha_code'] = substr(str_shuffle("0123456789abcdefghijklmnopqrstuvwxyz"), 0, 6);
            }
        }
    } else {
        $error_message = "Invalid email or password.";
    }
}

// Password reset logic
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['change_password']) && isset($_SESSION['email'])) {
    $current_password = $_POST['current_password'];
    $new_password = $_POST['new_password'];
    $confirm_password = $_POST['confirm_password'];

    // Verify current password
    $stmt = $conn->prepare("SELECT * FROM Accounts WHERE Email = :email");
    $stmt->bindParam(':email', $_SESSION['email']);
    $stmt->execute();
    $user = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($user && password_verify($current_password, $user['Password'])) {
        if ($new_password === $confirm_password) {
            $hashed_password = password_hash($new_password, PASSWORD_DEFAULT);
            $update_stmt = $conn->prepare("UPDATE Accounts SET Password = :password WHERE Email = :email");
            $update_stmt->bindParam(':password', $hashed_password);
            $update_stmt->bindParam(':email', $_SESSION['email']);

            if ($update_stmt->execute()) {
                $success_message = "Password updated successfully!";
            } else {
                $error_message = "Error updating password. Please try again.";
            }
        } else {
            $error_message = "New passwords do not match.";
        }
    } else {
        $error_message = "Current password is incorrect.";
    }
}

// Archive account logic
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['archive_account']) && isset($_SESSION['email'])) {
    $stmt = $conn->prepare("UPDATE Accounts SET isArchived = 1 WHERE Email = :email");
    $stmt->bindParam(':email', $_SESSION['email']);
    if ($stmt->execute()) {
        $success_message = "Account archived successfully.";
        session_destroy();
        header("Location: login.php");
        exit();
    } else {
        $error_message = "Error archiving account. Please try again.";
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login</title>
    <link rel="stylesheet" href="userp.css">
</head>
<body>

<div class='topnav'>
    <div class='column left'>
        <a href='../homePage/homepage.php' class="logo" style='font-size:40px;font-weight:600'>
            <img src='../images/logo.png' style='width:50px;vertical-align:middle'> Local Event Hub
        </a>
    </div>
    <div class='column right'>
        <p class='nav'><a href="../profilePage/account.php">Account</a></p>
        <?php if (isset($_SESSION['userID'])): ?>
            <p class='nav'><a href="../shoppingCart/shopping-cart.php">Cart</a></p>
            <p class='nav'><a href="../calendar/events.php">My Events</a></p>
        <?php endif; ?>
        <p class='nav'><a href="../discussion/discussion.php">Discussions</a></p>
        <p class='nav'><a href="../searchPage/search.php">Search</a></p>
    </div>
</div>

<div class="container">
    <h2>Login</h2>
    <?php if ($error_message): ?>
        <p class="error"><?php echo htmlspecialchars($error_message); ?></p>
    <?php endif; ?>
    <?php if ($success_message): ?>
        <p class="success"><?php echo htmlspecialchars($success_message); ?></p>
    <?php endif; ?>

    <?php if (!$is_login_successful): ?>
        <!-- Login Form -->
        <form action="login.php" method="post">
            <label for="email">Email:</label>
            <input type="email" id="email" name="email" required>

            <label for="password">Password:</label>
            <input type="password" id="password" name="password" required>

            <label for="captcha">CAPTCHA: <?php echo $_SESSION['captcha_code']; ?></label>
            <input type="text" id="captcha" name="captcha" required placeholder="Enter CAPTCHA">

            <button type="submit" name="login">Login</button>
        </form>
        <p><a href="account.php">Not a user? Create an account</a></p>
    <?php else: ?>
        <!-- Post-login Options -->
        <div id="postLoginOptions">
            <form action="login.php" method="post" style="display: inline;">
                <button type="submit" name="logout">Logout</button>
            </form>
            <button id="changePasswordButton" style="display: inline; margin-left: 10px;">Change Password</button>
            <button id="archiveAccountButton" style="display: inline; margin-left: 10px;">Archive Account</button>
            <button id="updateProfileButton" style="display: inline; margin-left: 10px;">Update Profile</button>

            <?php if ($_SESSION['isAdmin']): ?>
                <button id="adminPanelButton" style="display: inline; margin-left: 10px;" onclick="window.location.href='admin.php';">Admin Panel</button>
            <?php endif; ?>
        </div>

        <div id="redirectMessage" style="display: none;">
            <p>Navigating you to the main website, you have successfully logged in.</p>
        </div>

        <script>
            document.getElementById('changePasswordButton').addEventListener('click', function () {
                toggleFormDisplay('changePasswordForm');
            });

            document.getElementById('archiveAccountButton').addEventListener('click', function () {
                toggleFormDisplay('archiveAccountForm');
            });

            document.getElementById('updateProfileButton').addEventListener('click', function () {
                toggleFormDisplay('updateProfileForm');
            });

            function toggleFormDisplay(formId) {
                var form = document.getElementById(formId);
                form.style.display = (form.style.display === 'none' || form.style.display === '') ? 'block' : 'none';
            }

            setTimeout(function() {
                document.getElementById('postLoginOptions').style.display = 'none';
                document.getElementById('redirectMessage').style.display = 'block';
                
                setTimeout(function() {
                    window.location.href = "../homePage/homepage.php";
                }, 2000);
            }, 5000);
        </script>
    <?php endif; ?>
</div>

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
        <a href="mailto:s4100892@student.rmit.edu.au" style="color: inherit;">Hiba Ansari (s4100892)</a>,
        <a href="mailto:s3842127@student.rmit.edu.au" style="color: inherit;">Robert Vo-Ho (s3842127)</a>,
        <a href="mailto:s4038608@student.rmit.edu.au" style="color: inherit;">Mahita Jain (s4038608)</a>,
        <a href="mailto:s4026932@student.rmit.edu.au" style="color: inherit;">Hami Faizal (s4026932)</a>
    </p>
</footer>

</body>
</html>
