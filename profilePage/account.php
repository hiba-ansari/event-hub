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

$error_message = '';
$success_message = '';

// Generate a random CAPTCHA code if not already set
if (!isset($_SESSION['captcha_code'])) {
    $_SESSION['captcha_code'] = substr(str_shuffle("0123456789abcdefghijklmnopqrstuvwxyz"), 0, 6);
}
$captcha_code = $_SESSION['captcha_code'];

// Handle form submission
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $username = trim($_POST['username']);
    $email = trim($_POST['email']);
    $password = $_POST['password'];
    $phone = trim($_POST['phone']);
    $location = trim($_POST['location']);
    $hobbies = trim($_POST['hobbies']);
    $isAdmin = isset($_POST['isAdmin']) ? 1 : 0;
    $adminPassword = isset($_POST['admin_password']) ? $_POST['admin_password'] : '';
    $theme = isset($_POST['theme']) ? $_POST['theme'] : 'none';
    $captcha_response = $_POST['captcha_code'];

    // Check CAPTCHA
    if ($captcha_response !== $_SESSION['captcha_code']) {
        $error_message = "Incorrect CAPTCHA code. Please try again.";
    } elseif (empty($username) || empty($email) || empty($password)) {
        $error_message = "Please fill in all required fields.";
    } elseif ($isAdmin && $adminPassword !== 'rmit123') {
        $error_message = "Invalid admin password. Please enter the correct password to create an admin account.";
    } else {
        $checkEmail = $conn->prepare("SELECT * FROM Accounts WHERE Email = :email");
        $checkEmail->bindParam(':email', $email);
        $checkEmail->execute();

        if ($checkEmail->rowCount() > 0) {
            $error_message = "This email is already registered. Please use a different email.";
        } else {
            // Profile image upload handling
            $profileImagePath = "";
            if (isset($_FILES['profileImage']) && $_FILES['profileImage']['error'] === 0) {
                $allowedTypes = ['image/jpeg', 'image/png', 'image/gif'];
                $fileType = $_FILES['profileImage']['type'];

                if (in_array($fileType, $allowedTypes)) {
                    $targetDirectory = __DIR__ . "/uploads/";

                    // Check if the uploads directory exists, if not, create it with appropriate permissions
                    if (!file_exists($targetDirectory)) {
                        if (!mkdir($targetDirectory, 0777, true)) {
                            $error_message = "Failed to create uploads directory.";
                        }
                    }

                    // Check if the directory is writable
                    if (is_writable($targetDirectory)) {
                        // Sanitize file name by replacing spaces and special characters
                        $sanitizedFileName = uniqid() . "_" . preg_replace("/[^a-zA-Z0-9\._-]/", "_", basename($_FILES['profileImage']['name']));
                        $profileImagePath = "uploads/" . $sanitizedFileName;

                        // Move the uploaded file to the target directory
                        if (!move_uploaded_file($_FILES['profileImage']['tmp_name'], $targetDirectory . $sanitizedFileName)) {
                            $error_message = "There was an error moving the uploaded file.";
                            $profileImagePath = ""; // Reset the path if upload fails
                        }
                    } else {
                        $error_message = "Uploads directory is not writable. Please check permissions.";
                    }
                } else {
                    $error_message = "Invalid image format. Only JPEG, PNG, and GIF are allowed.";
                }
            }

            $hashed_password = password_hash($password, PASSWORD_DEFAULT);

            if (empty($error_message)) {
                $stmt = $conn->prepare("INSERT INTO Accounts (UserName, Email, Password, PhoneNo, Location, Hobbies, IsAdmin, ProfileImage, Theme) 
                                        VALUES (:username, :email, :password, :phone, :location, :hobbies, :isAdmin, :profileImage, :theme)");
                $stmt->bindParam(':username', $username);
                $stmt->bindParam(':email', $email);
                $stmt->bindParam(':password', $hashed_password);
                $stmt->bindParam(':phone', $phone);
                $stmt->bindParam(':location', $location);
                $stmt->bindParam(':hobbies', $hobbies);
                $stmt->bindParam(':isAdmin', $isAdmin);
                $stmt->bindParam(':profileImage', $profileImagePath);
                $stmt->bindParam(':theme', $theme);

                if ($stmt->execute()) {
                    $success_message = "Account created successfully!";
                    unset($_SESSION['captcha_code']); // Clear CAPTCHA after successful registration
                } else {
                    $error_message = "There was an error creating your account. Please try again.";
                }
            }
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Account</title>
    <link rel="stylesheet" href="userp.css">
    <link href="https://fonts.googleapis.com/css2?family=Bebas+Neue&family=Oswald:wght@600&family=Poppins:wght@300;400;600&family=Roboto:wght@300;400;500&display=swap" rel="stylesheet">
</head>
<body>
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
        <h2>Register</h2>
        <?php if ($error_message): ?>
            <p class="error"><?php echo htmlspecialchars($error_message); ?></p>
        <?php endif; ?>
        <?php if ($success_message): ?>
            <p class="success"><?php echo htmlspecialchars($success_message); ?></p>
            <script>
                // Redirect to homepage after account creation
                setTimeout(function() {
                    window.location.href = "../homePage/homepage.php";
                }, 2000); // 2-second delay
            </script>
        <?php endif; ?>

        <form action="account.php" method="post" enctype="multipart/form-data">
            <label for="username">Username:</label>
            <input type="text" id="username" name="username" required>

            <label for="email">Email:</label>
            <input type="email" id="email" name="email" required>

            <label for="password">Password:</label>
            <input type="password" id="password" name="password" required>

            <label for="phone">Phone Number:</label>
            <input type="text" id="phone" name="phone">

            <label for="location">Location:</label>
            <input type="text" id="location" name="location">

            <label for="hobbies">Hobbies:</label>
            <textarea id="hobbies" name="hobbies"></textarea>

            <label for="isAdmin">Is Admin:</label>
            <input type="checkbox" id="isAdmin" name="isAdmin">

            <div id="adminPasswordField" style="display: none;">
                <label for="admin_password">Enter Admin Password:</label>
                <input type="password" id="admin_password" name="admin_password" placeholder="Admin password">
            </div>

            <label for="profileImage">Profile Image:</label>
            <input type="file" id="profileImage" name="profileImage" accept="image/*">

            <!-- Theme Selection -->
            <label for="theme">Choose Theme:</label>
            <input type="radio" id="light" name="theme" value="light" <?php if(isset($theme) && $theme == 'light') echo 'checked'; ?>>
            <label for="light">Light</label>
            
            <input type="radio" id="dark" name="theme" value="dark" <?php if(isset($theme) && $theme == 'dark') echo 'checked'; ?>>
            <label for="dark">Dark</label>
            
            <input type="radio" id="none" name="theme" value="none" <?php if(!isset($theme) || $theme == 'none') echo 'checked'; ?>>
            <label for="none">None</label>

            <!-- CAPTCHA -->
            <div class="captcha-container">
                <p>Please enter the code below:</p>
                <p><strong><?php echo htmlspecialchars($captcha_code); ?></strong></p>
                <label for="captcha_code">Enter CAPTCHA code:</label>
                <input type="text" id="captcha_code" name="captcha_code" required>
            </div>

            <button type="submit">Register</button>
        </form>
        <p>Already a user? <a href="login.php">Login here</a></p>
    </div>

    <script>
        document.getElementById('isAdmin').addEventListener('change', function () {
            document.getElementById('adminPasswordField').style.display = this.checked ? 'block' : 'none';
        });
    </script>
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
            <a href="../profilePage/login.php"> Login </a>
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
