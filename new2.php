<?php
    ob_start();
    session_start();
    error_reporting(E_ALL ^ E_NOTICE);

    //echo session details
    // $sessionDetails = implode(",", $_SESSION);
    // echo "\$sessionDetails = " . $sessionDetails . "<br>"; 
    // echo "\$_SESSION = " . json_encode($_SESSION) . "<br>";
    echo "\$_SESSION['email'] = " . $_SESSION['email'] . "<br>";
    echo "\$_SESSION['userID'] = " . $_SESSION['userID'] . "<br>";

    //database connection and initialising variables
    $dsn = 'mysql:host=talsprddb02.int.its.rmit.edu.au;dbname=COSC3046_2402_UGRD_1479_G12';
    $user = 'COSC3046_2402_UGRD_1479_G12';
    $pass = 'LtEXbUiTF7Fm';
    $conn = new PDO($dsn, $user, $pass);
    $email_error = '';
    $pass_error = '';
    $pass2_error = '';

    //CHECK IF LOGGED IN
    if (isset($_SESSION['userID']) || !empty($_SESSION['userID'])) {
        $isLoggedIn = TRUE;
    }
    else {
        $isLoggedIn = FALSE;
    }
    echo "logged in? " . json_encode($isLoggedIn);

    ?>

<!DOCTYPE html>
<html lang="en">
    <head>
        <meta charset="UTF-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <title>User Profile Form</title>
        <link rel="stylesheet" href="profilePage/userp2.css">
        <link rel="stylesheet" href="profilePage/account-details.css">
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
        <?php
            if ($isLoggedIn == TRUE) {
                //DISPLAY DETAILS (pfp, username, email, phone, hobbies, location)
                echo "<div class='account-details'><h2>Your Account:</h2>";
            
                //pfp
                echo "<div class='pfp-container'>";
            
                // ! ERRORS WITH SQL QUERIES ($_POST ARE EMPTY) !
                $getPfp = "SELECT ProfileImage from Accounts WHERE UserID = '" . $_SESSION["userID"] . "'";
                $pfp = $conn->query($getPfp);
                $userPfp = $pfp->fetch(PDO::FETCH_ASSOC);
                $_SESSION['pfp'] = $userPfp['ProfileImage'];
                echo "<p>" . $_SESSION['pfp'] . "</p>";
                echo "</div>";
            
                //username
                echo "<h3>Username</h3>";
                $getUsername = "SELECT UserName from Accounts WHERE UserID = '" . $_SESSION["userID"] . "'";
                $name = $conn->query($getUsername);
                $userName = $name->fetch(PDO::FETCH_ASSOC);
                $_SESSION['username'] = $userName['UserName'];
                echo "<p class='user-info'>" . $_SESSION['username'] . "</p>";
            
                //email
                echo "<h3>Email</h3>";
                $getEmail = "SELECT Email from Accounts WHERE UserID = '" . $_SESSION["userID"] . "'";
                $email = $conn->query($getEmail);
                $userEmail = $email->fetch(PDO::FETCH_ASSOC);
                $_SESSION['email'] = $userEmail['Email'];
                echo "<p class='user-info'>" . $_SESSION['email'] . "</p>";
            
                //phone
                echo "<h3>Phone Number</h3>";
                $getPhone = "SELECT PhoneNo from Accounts WHERE UserID = '" . $_SESSION["userID"] . "'";
                $phone = $conn->query($getPhone);
                $userPhone = $phone->fetch(PDO::FETCH_ASSOC);
                $_SESSION['phone'] = $userPhone['PhoneNo'];
                echo "<p class='user-info'>" . $_SESSION['phone'] . "</p>";
            
                //hobbies
                echo "<h3>Your Hobbies</h3>";
                $getHobbies = "SELECT Hobbies from Accounts WHERE UserID = '" . $_SESSION["userID"] . "'";
                $hobbies = $conn->query($getHobbies);
                $userHobbies = $hobbies->fetch(PDO::FETCH_ASSOC);
                $_SESSION['hobbies'] = $userHobbies['Hobbies'];
                echo "<p class='user-info'>" . $_SESSION['hobbies'] . "</p>";
            
                //location
                echo "<h3>Your Location</h3>";
                $getLocation = "SELECT Location from Accounts WHERE UserID = '" . $_SESSION["userID"] . "'";
                $location = $conn->query($getLocation);
                $userLocation = $location->fetch(PDO::FETCH_ASSOC);
                $_SESSION['location'] = $userLocation['Location'];
                echo "<p class='user-info'>" . $_SESSION['location'] . "</p>";
            
                echo "</div>"; //account-details <div>
            }
        ?>

        <!-- All forms -->
        <div class='forms'>
            <!-- Create account -->
            <div class='create-acc' style='position: relative; display: block; justify-content: center;'>
                <h3>Create Account</h3>
                <form action='new2.php' method='POST'>
                    <label for='create-email'>Your Email:</label><br>
                    <input type='email' id='create-email' name='create-email' required><br>
                    <?php 
                    if (!preg_match("/^[a-zA-Z0-9._-]+@[a-zA-Z0-9-]+\.[a-zA-Z.]{2,5}$/", $_POST["create-email"])) {
                        $email_error = "Invalid email format.";
                        echo "<p>" . $email_error . "</p>"; 
                    }
                    ?>

                <label for='create-pass'>Create Password:</label><br>
                <input type='password' id='create-pass' name='create-pass' required><br>
                    <?php 
                        if (strlen($_POST["create-pass"]) < 2) {
                            $pass_error = "Password must be more than 2 characters.";
                            echo "<p>" . $pass_error . "</p>"; 
                        }   
                    ?>

                <label for='create-pass2'>Retype Password:</label><br>
                <input type='password' id='create-pass2' name='create-pass2' required><br>
                    <?php 
                        if ($_POST["create-pass2"] !== $_POST["create-pass"]) {
                            $pass2_error = "Password does not match.";
                            echo "<p>" . $pass2_error . "</p>"; 
                        }
                    ?>

                <button type='submit' name='create-account' onclick="changeForm('user-details')">Create Account!</button>
                </form>
                <p><a href="" onclick="changeForm('login')">I Already Have An Account</a></p>
                <?php 
                    if (isset($_POST["create-account"]) && empty($email_error) && empty($pass_error) && empty($pass2_error)) {
                        $sql = "INSERT INTO Accounts (Email, Password) 
                                VALUES ('" . $_POST["create-email"] . "', '" . $_POST["create-pass"] . "')";
                        $result = $conn->query($sql);
                    }
                    $createdEmail = $_POST["create-email"];
                    $createdPass = $_POST["create-pass"];
                ?>
        </div>

        <!-- User adds more details -->
        <div class='user-details' style='display: block; justify-content: center;'>
            <h3>Tell Us About You!</h3>    
            <form action='new2.php' method='post'>
                <label for="img">Select a Profile Image:</label>
                <input type="file" id="pfp" name="pfp" accept="image/*"><br>

                <label for='username'>Your new username:</label><br>

                <input type='text' id='username' name='username'><br>

                <label for='hobbies'>What are your hobbies?</label><br>
                <input type='text' id='hobbies' name='hobbies'><br>

                <label for='location'>Input a Location:</label><br>
                <input type='text' id='location' name='location'><br>

                <button type='submit' name='user-details'>Submit!</button>
            </form>
            <?php 
                if (isset($_POST["user-details"])) {
                    $sql = "UPDATE Accounts 
                            SET ProfileImage = :profileImage, 
                                UserName = :username, 
                                Hobbies = :hobbies, 
                                Location = :location 
                            WHERE Email = :email 
                            AND Password = :password";

                    $stmt = $conn->prepare($sql);
                    $stmt->bindParam(':profileImage', $_POST['pfp'], PDO::PARAM_STR);
                    $stmt->bindParam(':username', $_POST['username'], PDO::PARAM_STR);
                    $stmt->bindParam(':hobbies', $_POST['hobbies'], PDO::PARAM_STR);
                    $stmt->bindParam(':location', $_POST['location'], PDO::PARAM_STR);
                    $stmt->bindParam(':email', $createdEmail, PDO::PARAM_STR);
                    $stmt->bindParam(':password', $createdPass, PDO::PARAM_STR);
                    $result = $stmt->execute();
                }
            ?>
        </div>

        <!-- Log back in -->
        <div class='login' style='display: block; justify-content: center;'>
            <h3>Login</h3>
            <form action='new2.php' method='post'>
                <label for='email'>Email:</label><br>
                <input type='text' id='email' name='email'><br>

                <label for='pass'>Password:</label><br>
                <input type='text' id='pass' name='pass'><br>
                
                <button type='submit' name='login'>Login!</button>
            </form>
            <p><a href="" onclick="changeForm('create')">Create New Account</a></p>
            <?php 
                if (isset($_POST["login"])) {
                    $isLoggedIn = TRUE;
                    $sql = "SELECT * FROM Accounts WHERE Email = '" . $_POST["email"] . "' and Password = '" . $_POST["pass"] . "'";
                    $result = $conn->query($sql);
                    $userAcc = $result->fetchAll(PDO::FETCH_ASSOC);

                    if (empty($userAcc)) {
                        echo "<p>Invalid email or password</p>";
                    }
                    else {
                        echo "<p>Successful login!</p>";
                        $getUserID = "SELECT UserID from Accounts WHERE Email = '" . $_POST["email"] . "' and Password = 
                        '" . $_POST["pass"] . "'";
                        $userID = $conn->query($getUserID);
                        $userRow = $userID->fetch(PDO::FETCH_ASSOC);
                        $_SESSION['userID'] = $userRow['UserID'];
                        $getAllUserDetails = "SELECT UserID, Email, UserName, Location, Hobbies, ProfileImage, PhoneNo
                                              from Accounts WHERE Email = '" . $_POST["email"] . "' and Password = '" . $_POST["pass"] . "'";
                        $results = $conn->query($getAllUserDetails);
                        $sessionDetails = $results->fetch(PDO::FETCH_ASSOC);
                        $_SESSION['email'] = $sessionDetails['Email'];
                        $_SESSION['username'] = $sessionDetails['UserName'];
                        $_SESSION['location'] = $sessionDetails['Location'];
                        $_SESSION['hobbies'] = $sessionDetails['Hobbies'];
                        $_SESSION['pfp'] = $sessionDetails['ProfileImage'];
                        $_SESSION['phone'] = $sessionDetails['PhoneNo'];
                        header("Location: new2.php");
                        exit();
                    }
                }

                //logout button
                if (!isset($_SESSION) || !empty($_SESSION)) {
                    echo "<form action='new2.php' method='post'><button type='submit' name='logout' id='logout'>Logout</button></form>";
                }
                if (isset($_POST["logout"])) {
                    echo "\$sessionDetails = " . $sessionDetails . "<br>"; 
                    echo "\$_SESSION = " . json_encode($_SESSION) . "<br>";
                    session_unset();
                    session_destroy();
                    header("Location: new2.php");
                    exit();
                }
            ?>
        </div>
    </div>
    <script>
        function changeForm(to) {
            if (to == 'create') {
                document.getElementById('create-account').style.display = "block";
            }
            else if (to == 'user-details') {
                document.getElementById('user-details').style.display = "block";
            }
            else {
                document.getElementById('login').style.display = "block";
            }
        }
    </script>
</body>

<footer class="footer" style='display: none;'>
        <div class="footer-content">
            <div class="footer-column footer-logo">
                <!-- <img src="../images/logo.png" alt="Logo"> -->
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
            <a href="mailto:s4100892@student.rmit.edu.au" style="color: inherit; text-decoration: underline;">Hiba Ansari (s4100892)</a>
            ,
            <a href="mailto:s3842127@student.rmit.edu.au" style="color: inherit; text-decoration: underline;">Robert Vo-Ho (s3842127)</a>
            ,
            <a href="mailto:s4038608@student.rmit.edu.au" style="color: inherit; text-decoration: underline;">Mahita Jain (s4038608)</a>
            ,
            <a href="mailto:s4026932@student.rmit.edu.au" style="color: inherit; text-decoration: underline;">Hami Faizal (s4026932)</a>
        </p>
    </footer>
</html>
<?php ob_end_flush(); ?>