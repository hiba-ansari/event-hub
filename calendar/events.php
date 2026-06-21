<?php
    session_start();
    $dsn = 'mysql:host=talsprddb02.int.its.rmit.edu.au;dbname=COSC3046_2402_UGRD_1479_G12';
    $user = 'COSC3046_2402_UGRD_1479_G12';
    $pass = 'LtEXbUiTF7Fm';
    $conn = new PDO($dsn, $user, $pass);
    $currentPageUrl = 'http://'.$_SERVER["HTTP_HOST"].$_SERVER["REQUEST_URI"];
    $values = parse_url($currentPageUrl);
   
    if (!empty($_SESSION['userID'])){
        $userID = $_SESSION['userID'];
        $sql = "SELECT UserID, EventName, EventDate, EventAddress, EventWhen, EventImage, Link FROM SavedEvents NATURAL JOIN Events WHERE UserID = $userID";
        $result = $conn->query($sql);
        $rows = $result->fetchAll(PDO::FETCH_ASSOC);
        $json = json_encode($rows);
        $eventData = 'const eventsData =' . $json . ';';
    }
    else {
        $eventData = 'const eventsData = []';
    }

    $currentjs = file_get_contents('calendarthingy.js');
    $newjs = $eventData . $currentjs;
    file_put_contents('calendar.js', $newjs);
    
?>


<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Calendar</title>
    <link rel="stylesheet" href="calendar.css">
    <link href="https://fonts.googleapis.com/css2?family=Bebas+Neue&family=Oswald:wght@600&family=Poppins:wght@300;400;600&family=Roboto:wght@300;400;500&display=swap" rel="stylesheet">
    
</head>
<body style="background-color: var(--OURwhite);">
    <div class="layer" id="layer1">
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
        <div style="min-height: 38vmin; margin-left: 5vmax; margin-right: 5vmax; margin-top: 4vmin; margin-bottom: 10vmin;">
            <div class="header">
                <div style="margin-right: auto; margin-left: 30vmax;">
                    <button id="prevMonth">&lt;</button>
                </div>
                <div id="monthYear" style="font-size: 24px; font-weight: bold;"></div>
                <div style="margin-right: 30vmax; margin-left: auto;">
                    <button id="nextMonth">&gt;</button>
                </div>
            </div>
  
            <div class="weekdays">
                <span>Mon</span>
                <span>Tue</span>
                <span>Wed</span>
                <span>Thu</span>
                <span>Fri</span>
                <span>Sat</span>
                <span>Sun</span>
            </div>
            <div id="daysContainer" class="days-container">

            </div>
        </div>
    </div>

    <div class="layer" id="layer2" style="min-height: 80vmin;">
        <h1 style="margin-left: 5vmax;">Events this month</h1>
        <div class="what-groups-are-organising-c">
            <div id="listed-events-container" class="listed-events-container">
            
            </div>
        </div>
    </div>
    
    <script src="calendar.js"></script>
    
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