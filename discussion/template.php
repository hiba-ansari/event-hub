<?php
    session_start();
    $dsn = 'mysql:host=talsprddb02.int.its.rmit.edu.au;dbname=COSC3046_2402_UGRD_1479_G12';
    $user = 'COSC3046_2402_UGRD_1479_G12';
    $pass = 'LtEXbUiTF7Fm';
    $conn = new PDO($dsn, $user, $pass);

    $sql = "SELECT ThreadID, UserName FROM DiscussionThreads NATURAL JOIN Accounts WHERE link = '$link'";
    $result = $conn->query($sql);
    $row = $result->fetch(PDO::FETCH_ASSOC);
    $threadID = $row['ThreadID'];
    $username = $row['UserName'];

    if (isset($_POST['discussion-input'])) {
        $replyPOST = addslashes($_POST['discussion-input']);
        $userID = $_SESSION['userID'];
        $sql = "INSERT INTO DiscussionPosts (ThreadID, UserID, Reply) VALUES ('$threadID', '$userID', '$replyPOST')";
        $result = $conn->query($sql);
        header("Location: " . $_SERVER['PHP_SELF']);
        exit();
    }

    if (isset($_POST['submit-reply'])) {
        $parentPostID = $_POST['parent-post-id'];
        $replyContent = addslashes($_POST['reply-input']);
        $userID = $_SESSION['userID'];

        $sql = "INSERT INTO DiscussionPosts (ThreadID, UserID, ParentPostID, Reply) 
                VALUES ('$threadID', '$userID', '$parentPostID', '$replyContent')";
        $conn->query($sql);

        header("Location: " . $_SERVER['PHP_SELF']);
        exit();
    }

    if (isset($_POST['update-reply'])) {
        $newReply = addslashes($_POST['reply-input']);
        $postID = $_POST['post-id'];

        $sql = "UPDATE DiscussionPosts SET Reply = '$newReply' WHERE PostID = '$postID' AND UserID = '".$_SESSION['userID']."'";
        $conn->query($sql);

        header("Location: " . $_SERVER['PHP_SELF']);
        exit();
    }
    if (isset($_POST['delete-reply'])) {
        $postID = $_POST['post-id'];
        $sql = "UPDATE DiscussionPosts SET IsDeleted = 1 WHERE PostID = '$postID' AND UserID = '".$_SESSION['userID']."'";
        $conn->query($sql);
        header("Location: " . $_SERVER['PHP_SELF']);
        exit();
    
    }

?>


<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo $subject;?></title>
    <link rel="stylesheet" href="../discussion/discussion.css">
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

    <div style="margin-left:20vmax; margin-right:20vmax; min-height: 68.9vmin">
        <?php
            echo "
            <div class=\"discussion-container main-post\">
                <p class=\"username\">$username</p>
                <h1 style='margin:0;' class=\"subject\">$subject</h1>
                <h3 style='margin:0;'>$initialPost</h3>
            </div>
            ";

            $sql = "SELECT PostID, ParentPostID, Reply, Img, CreatedAt, UserName, UserID, IsDeleted
                    FROM DiscussionPosts 
                    NATURAL JOIN Accounts 
                    WHERE ThreadID = '$threadID'
                    ORDER BY 
                        CASE 
                            WHEN ParentPostID IS NULL THEN PostID 
                            ELSE ParentPostID 
                        END ASC, 
                        CreatedAt ASC;
                    ";
            $result = $conn->query($sql);
            $posts = $result->fetchAll(PDO::FETCH_ASSOC);

            if (isset($_SESSION['userID'])) {
                echo "
                    <form name=\"discussionForm\" id=\"discussionForm\" class=\"discussion-form\" method=\"POST\" onSubmit>
                        <input id=\"discussion-input\" name=\"discussion-input\" class=\"discussion-input\" placeholder=\"Type your discussion here...\">
                        <button type=\"submit\" class=\"submit-btn\">Submit Discussion</button>
                    </form>
                ";
            }

            if (!empty($posts)) {
                foreach ($posts as $post) {
                    $username = $post['UserName'];
                    $reply = $post['Reply'];
                    $date = $post['CreatedAt'];
                    $postID = $post['PostID'];
                    $parentPostID = $post['ParentPostID'];
                    $postUserID = $post['UserID'];
                    $isDeleted = $post['IsDeleted'];
        
                    if ($parentPostID) {
                        $postClass = "reply";
                    } else {
                        $postClass = "original-post";
                    }
        
                    echo "
                        <div class='discussion-container $postClass'>
                            <p class='username'>$username</p>
                            <p class='date'>$date</p>";

                    if ($isDeleted == 1){
                        echo "
                            <p class='reply-text'>(Deleted Message)</p>";
                    }
                    else {
                        echo "
                            <p class='reply-text'>$reply</p>";
                        
                        if (isset($_SESSION['userID'])) {
                            echo "
                                <button class='reply-button' name='reply-post' class='reply-btn' onclick='toggleReplyForm($postID)'>Reply</button>";
                            if ($postUserID == $_SESSION['userID']){
                                echo "
                                    <button class='reply-button' name='edit-post' class='edit-btn' onclick='toggleEditForm($postID)'>Edit</button>
                                    <button class='reply-button' name='delete-post' class='delete-btn' onclick='toggleDeleteForm($postID)'>Delete</button>";
                            }
                            echo "
                                    <div id='replyForm$postID' class='reply-form' style='display:none;'>
                                        <form method='POST' action=''>
                                            <input name='reply-input' class='reply-input' placeholder='Type your reply...'>
                                            <input type='hidden' name='parent-post-id' value='$postID'>
                                            <button type='submit' name='submit-reply' class='submit-reply-btn' style='margin-top:10px';>Submit Reply</button>
                                        </form>
                                    </div>";
                            if ($postUserID == $_SESSION['userID']){
                                echo "
                                    <div id='editForm$postID' class='edit-form' style='display:none;'>
                                        <form method='POST' action=''>
                                            <input name='reply-input' class='reply-input' value='$reply'>
                                            <input type='hidden' name='post-id' value='$postID'>
                                            <button type='submit' name='update-reply' class='submit-reply-btn' style='margin-top:10px';>Update Reply</button>
                                        </form>
                                    </div>
                                    <div id='deleteForm$postID' class='delete-form' style='display:none;'>
                                        <form method='POST' action=''>
                                            <input type='hidden' name='post-id' value='$postID'>
                                            <img src='https://media.tenor.com/6RoRpT3SvBQAAAAj/are-you.gif' style='width:160px;height:150px;'> <br>
                                            <button type='submit' name='delete-reply' class='submit-reply-btn' style='margin-top:10px';>Yes</button>
                                            <button class='reply-button' name='delete-post-cancel' class='delete-btn' onclick='toggleDeleteForm($postID)'>No</button>
                                        </form>
                                    </div>";
                            }
                            echo "</div>";
                        }
                        
                    }
                }
            }
        ?>  
    </div>
    <script>
    function toggleReplyForm(postID) {
        const form = document.getElementById(`replyForm${postID}`);
        let form2 = null;
        let form3 = null;
        if (document.getElementById(`editForm${postID}`)) {
            form2 = document.getElementById(`editForm${postID}`);
        }
        if (document.getElementById(`deleteForm${postID}`)) {
            form3 = document.getElementById(`deleteForm${postID}`);
        }
        if (form.style.display === "none") {
            if (form2 && form2.style.display === "block") {
                form2.style.display = "none";
            }
            if (form3 && form3.style.display === "block") {
                form3.style.display = "none";
            }
            form.style.display = "block";
        } else {
            form.style.display = "none";
        }
    }
    function toggleEditForm(postID) {
        const form = document.getElementById(`editForm${postID}`);
        const form2 = document.getElementById(`replyForm${postID}`);
        const form3 = document.getElementById(`deleteForm${postID}`);
        if (form.style.display === "none") {
            if (form2.style.display === "block") {
                form2.style.display = "none";
            }
            if (form3.style.display === "block") {
                form3.style.display = "none";
            }
            form.style.display = "block";
        } else {
            form.style.display = "none";
        }
    }
    function toggleDeleteForm(postID) {
        const form = document.getElementById(`deleteForm${postID}`);
        const form2 = document.getElementById(`editForm${postID}`);
        const form3 = document.getElementById(`replyForm${postID}`);
        if (form.style.display === "none") {
            if (form2.style.display === "block") {
                form2.style.display = "none";
            }
            if (form3.style.display === "block") {
                form3.style.display = "none";
            }
            form.style.display = "block";
        } else {
            form.style.display = "none";
        }
    }
    </script>


    
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
</body>
</html>