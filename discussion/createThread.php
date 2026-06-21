<?php
session_start();
$dsn = 'mysql:host=talsprddb02.int.its.rmit.edu.au;dbname=COSC3046_2402_UGRD_1479_G12';
$user = 'COSC3046_2402_UGRD_1479_G12';
$pass = 'LtEXbUiTF7Fm';
$conn = new PDO($dsn, $user, $pass);

if (isset($_POST['subject']) && isset($_POST['initialPost'])) {
    $subject = addslashes($_POST['subject']);
    $initialPost = addslashes($_POST['initialPost']);
    $userID = $_SESSION['userID'];
    
    $directory = '../discussionThreads/';
    $templatePath = '../discussion/template.php';
    $pageContent = file_get_contents($templatePath);
    $file = preg_replace('/[^a-zA-Z0-9]/', '_', $subject);
    $filename = $directory . $file . '.php';

    $suffix = 1;
    while (file_exists($filename)) {
        $filename = $directory . $file . '_' . $suffix . '.php';
        $suffix++;
    }

    $pageContent = '
    <?php
        $subject = "' . addslashes($subject) . '";
        $initialPost = "' . addslashes($initialPost) . '";
        $link ="' .addslashes($filename) . '";
    ?>
    ' . file_get_contents($templatePath);

    file_put_contents($filename, $pageContent);
    
    $sql = "INSERT INTO DiscussionThreads (SubjectName, InitialPost, UserID, Link) VALUES ('$subject', '$initialPost', '$userID', '$filename')";    
    if ($conn->query($sql)) {
        header("Location: discussion.php");
        exit();
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Create New Thread</title>
    <link rel="stylesheet" href="discussion.css">
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

    <div class="create-thread-content">
        <h1>Create a New Discussion Thread</h1>
        <form method="POST">
            <input type="text" id="subject" name="subject" placeholder="Subject..."required>
            <textarea id="initialPost" name="initialPost" placeholder="Initial Post..."required></textarea>
            <button type="submit" name="submit" id="create-thread-button">Create Thread</button>
        </form>
    </div>

    <footer class="footer" style="position:absolute">
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
</body>
</html>
