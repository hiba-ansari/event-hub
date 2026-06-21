<?php
    session_start();
    $dsn = 'mysql:host=talsprddb02.int.its.rmit.edu.au;dbname=COSC3046_2402_UGRD_1479_G12';
    $user = 'COSC3046_2402_UGRD_1479_G12';
    $pass = 'LtEXbUiTF7Fm';
    $conn = new PDO($dsn, $user, $pass);
    $sql = "Select EventID from Events where link = '$link'";
    $result = $conn->query($sql);
    $row = $result->fetch(PDO::FETCH_ASSOC);
    $eventID = $row['EventID'];
    if (isset($_SESSION['userID'])){
        $userID = $_SESSION['userID'];
        $sql = "SELECT * FROM SavedEvents WHERE EventID = '$eventID' AND UserID = '$userID'";
        $result = $conn->query($sql);
        $rows = $result->fetchAll(PDO::FETCH_ASSOC);
        if (isset($_POST['add-to-calendar'])) {
            $sql = "SELECT * FROM SavedEvents WHERE EventID = '$eventID' AND UserID = '$userID'";
            $result = $conn->query($sql);
            $rows = $result->fetchAll(PDO::FETCH_ASSOC);
            if (empty($rows)) {
                $sql = "INSERT INTO SavedEvents (EventID, UserID) VALUES ('$eventID', '$userID')";
                $insert = $conn->query($sql);
            }
            else {
                $sql = "DELETE FROM SavedEvents Where EventID = '$eventID' AND UserID = '$userID'";
                $insert = $conn->query($sql);
            }
            header("Location: " . $_SERVER['PHP_SELF']);
            exit();
        }
        if (isset($_POST['discussion-input'])) {
            $replyPOST = addslashes($_POST['discussion-input']);
            $sql = "INSERT INTO EventPosts (EventID, UserID, Reply) Values ('$eventID', '$userID', '$replyPOST')";
            $result = $conn->query($sql);
            header("Location: " . $_SERVER['PHP_SELF']);
            exit();
        }
    }
    $getEventPrice = "SELECT Price from Events where EventName = :title and EventID = :ID";
    $result = $conn->prepare($getEventPrice);
    $result->execute(['title' => $eventTitle, 'ID' => $eventID]);
    $fetchedPrice = $result->fetch(PDO::FETCH_ASSOC);
    $eventPrice = '';
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Details Page</title>
    <link rel="stylesheet" href="../eventDetails/details.css">
    <link href="https://fonts.googleapis.com/css2?family=Bebas+Neue&family=Oswald:wght@600&family=Poppins:wght@300;400;600&family=Roboto:wght@300;400;500&display=swap" rel="stylesheet">
</head>
<body>
    <div class="layer" id="layer1" style="padding-top:8px">
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
        <div class="image">
            <img src="<?php echo $eventImage; ?>" width="100%" id="banner" alt="event-thumbnail">
        </div>
        <div class="section">
            <div class="details">
                <p class="event-date"><?php echo $eventDate; ?></p>
                <p class="event-title"><?php echo $eventTitle; ?></p>
                <p class="event-address"><?php echo $eventAddress; ?></p>
                <p id="event-price" style="margin: 5px 0;">
                    <?php 
                        $eventPrice = $fetchedPrice['Price'];
                        if (is_null($fetchedPrice['Price']) || $fetchedPrice['Price'] == NULL || $fetchedPrice['Price'] === NULL || $fetchedPrice['Price'] == 'null' || empty($fetchedPrice['Price']) || !isset($fetchedPrice['Price'])) { //if free
                            $eventPrice = "Free Event";
                            echo $eventPrice;
                        }
                        else { //if has cost
                            if (strpos($fetchedPrice['Price'], '.') !== false) { //if has a decimal place, append a 0
                                echo "$" . $eventPrice . '0 per person';
                            }
                            else {
                                if (strpos($fetchedPrice['Price'], '.') === false) {
                                    echo "$" . $eventPrice . " per person"; 

                                }
                            }
                        }
                    ?>
                </p>
            </div>
            <div class="buttons" <?php if (empty($_SESSION['userID'])){echo "style=\"display:none\"";}?>>
                <button class="btn-action glowing-button" name="add-to-cart" onclick="showPopup()">Add To Cart</button>
                <form method="POST">
                    <?php
                        $sql = "SELECT * FROM SavedEvents WHERE EventID = '$eventID' AND UserID = '$userID'";
                        $result = $conn->query($sql);
                        $rows = $result->fetchAll(PDO::FETCH_ASSOC);
                        if (empty($rows)) {
                            echo "<button name=\"add-to-calendar\" class=\"btn-action glowing-button\">Add to Calendar</button>";
                        }
                        else {
                            echo "<button name=\"add-to-calendar\" class=\"btn-action glowing-button\" style=\"background-color:#58b8fd\">Added to Calendar</button>";
                        }
                    ?>
                </form>
            </div>

            <div id="bg-overlay" style="display: none;"></div>
            
            <div id="popup-container" style="display: none;">
                <button id="close-popup" onclick="closePopup()">X</button>
                <?php echo "<h3><span>Purchasing Tickets for:</span><br>" . $eventTitle . "</h3>"; ?>
                <div class="ticket-buttons">
                    <button id="decrement">-</button>
                    <div id="num-tickets">1</div>
                    <button id="increment">+</button>
                </div>
                <p class="price">Total: $<span id="popup-price"><?php 
                        if ($eventPrice == "Free Event") {
                            echo "00.00";
                        }
                        else {
                            echo number_format($eventPrice, 2);
                        }
                    ?></span>
                    <span class="event-id" style="display:none;"><?php echo $eventID ?></span>
                </p>
                <button class="submit-tickets">Confirm</button>
            </div>
        </div>
    </div>
    
    <div>
        <hr class="section-divider">
    </div>
    
    <div class="layer" id="layer2">
        <div class="section">
            <div class="text" style="width:57vmax">
                <p style="font-size: 4vmin">Discussion</button>
            </div>
            <div class="text">
                <p style="font-size: 4vmin">About</p>
            </div>            
        </div>

        <!-- <div>
            <hr style="margin-left: 5vmax; margin-right: auto; border:0.1vmin solid #0D99FF; border-radius: 5px; width: 22.5vmin;">
        </div> -->
        


        <div class="section" id="discussion" style="margin-top: 2vmin; margin-bottom: 2vmin;">
            <div class="discussion">
                <div style="overflow:overlay; height: 92%;">
                <?php
                    $sql = "Select PostID, Reply, Image, CreatedAt, UserName From EventPosts NATURAL JOIN Accounts WHERE EventID = '$eventID'";
                    $result = $conn->query($sql);
                    $posts = $result->fetchAll(PDO::FETCH_ASSOC);
                    if (!empty($posts)) {  
                        foreach ($posts as $post) {
                        $username = $post['UserName'];
                        $reply = $post['Reply'];
                        $imageReply = $post['Image'];
                        $date = $post['CreatedAt'];
                        echo "
                            <div style=\"border: 2px solid black\">
                            <p>$username</p>
                            <p>$date</p>
                            <p>$reply</p>
                            </div>
                            ";
                        }
                       
                    }
                ?>
                </div>
                <?php
                    if (isset($_SESSION['userID'])){
                        echo "
                            <form name=\"discussionForm\" id=\"discussionForm\" method=\"POST\">
                            <div style=\"display:flex; flex-direction:row; align-items:center; margin-top:1vmin\">
                                <div style=\"margin-right:1vmin\">
                                    <input id=\"discussion-input\" name=\"discussion-input\" rows=\"3\" style=\"width:42vmax; height:2vmax\" placeholder=\"Type your discussion here...\"></input>
                                </div>
                                <div>
                                    <button type=\"submit\" class=\"submit-btn glowing-button\" style = \" align-items:center\">Submit Discussion</button>
                                </div>
                            </div>
                            </form>
                        ";
                    }
                ?>
            </div>
            
            <div class="all3">
                <div class="about">
                    <p style="font-size: 2.5vh;line-height: 1.45"><?php echo $description; ?></p>
                </div>
                <div class="api">
                    <?php
                        $apiKey = 'AIzaSyC_vLZJKvQiFSA_k02z3tvkyg_M_R928bM';
                        $location = $eventAddress;
                        $mapUrl = "https://maps.googleapis.com/maps/api/staticmap?center=" . urlencode($location) . "&zoom=14&size=600x300&maptype=roadmap&markers=color:red%7Clabel:S%7C" . urlencode($location) . "&key=" . $apiKey;
                        echo "<h2>Location: " . $location . "</h2>";
                        echo "<img src='" . $mapUrl . "' alt='Static map of " . $location . "' style=\"width:100%;\">";
                    ?>
                </div>
            </div>
        </div>
    </div>

    <div class="layer" id="layer3">
    </div>

    <script src="../eventDetails/details-scripts.js"></script>
    
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

