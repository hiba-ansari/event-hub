<?php
session_start();
$dsn = 'mysql:host=talsprddb02.int.its.rmit.edu.au;dbname=COSC3046_2402_UGRD_1479_G12';
$user = 'COSC3046_2402_UGRD_1479_G12';
$pass = 'LtEXbUiTF7Fm';
$conn = new PDO($dsn, $user, $pass);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Discussion Threads</title>
    <link rel="stylesheet" href="discussion.css">
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

    <div class="content">
        <h1>Threads</h1>
        <?php
        if (isset($_SESSION['userID'])){
            echo "<a href=\"createThread.php\" class=\"create-thread-button\">NEW THREAD</a>";
        }
        
        $sql = "
            SELECT ThreadID, SubjectName, CreatedAt, UserName, Link, InitialPost
            FROM DiscussionThreads 
            NATURAL JOIN Accounts 
            ORDER BY CreatedAt DESC";
        $result = $conn->query($sql);
        $threads = $result->fetchAll(PDO::FETCH_ASSOC);
        ?>

        <ul>
            <?php
                if (!empty($threads)) {  
                    foreach ($threads as $thread) {
                        $threadID = $thread['ThreadID'];
                        $subjectName = $thread['SubjectName'];
                        $initialPost = $thread['InitialPost'];
                        $username = $thread['UserName'];
                        $createdAt = $thread['CreatedAt'];
                        $link = $thread['Link'];
                        $initialPost = $thread['InitialPost'];
                        $pageContent = '
                        <?php
                            $subject = "' . addslashes($subjectName) . '";
                            $initialPost = "' . addslashes($initialPost) . '";
                            $link ="' .addslashes($link) . '";
                        ?>
                        ' . file_get_contents('../discussion/template.php');

                        file_put_contents($link, $pageContent);
                        echo "<li><a href='$link'>$subjectName</a>$createdAt<br>Created by $username<p id='initial-post'>$initialPost</p></li>";
                    }
                }
            ?>
        </ul>
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
