<?php
    ob_start();

    // Load local database configuration
    require_once '../config/database_local.php';
    require_once __DIR__ . '/../config/features.php';

    // Shared connection for the events carousel on this page
    $dsn = "mysql:host=" . DB_HOST . ";dbname=" . DB_NAME;
    $conn = new PDO($dsn, DB_USER, DB_PASS);
    $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
?>
<!DOCTYPE html>
    <html lang="en">
    <head>
        <meta charset="UTF-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <title>Local Event Hub</title>
        <link rel="stylesheet" href="homepage.css">
        <link href="https://fonts.googleapis.com/css2?family=Bebas+Neue&family=Oswald:wght@600&family=Poppins:wght@300;400;600&family=Roboto:wght@300;400;500&display=swap" rel="stylesheet">
    </head>
    <body>
        <style>
            :root {
                --h3-size: 3.2vmin;
                --section-bottom: 7.5vh;
            }

            footer {
                margin: none;
            }

            h3 {
                color: #414c7a;
                /* font-size: min(2.7vmax); */
            }
            .nav > a {
                color: #4E598C;
            }
        </style>

    <div class='topnav'>
        <div class='column left'>
            <a href='../homePage/homepage.php' class="logo" style='font-size:40px;font-weight:600'><img src='../images/logo.png' style='width:50px;vertical-align:middle'> Local Event Hub</a>
        </div>
        <div class='column right'>
            <?php
                if (isset($_SESSION['userID'])){
                    $cartNav = feature_enabled('shopping_cart')
                        ? "<p class='nav'><a href=\"../shoppingCart/shopping-cart.php\">Cart</a></p>"
                        : '';
                    echo "
                    <p class='nav'><a href=\"../profilePage/view_profile.php\">Account</a></p>
                    $cartNav
                    <p class='nav'><a href=\"../calendar/events.php\">My Events</a></p>";
                } else {
                    echo "
                    <p class='nav'><a href=\"../profilePage/account.php\">Account</a></p>";
                }
            ?>
            <p class='nav'><a href="../discussion/discussion.php">Discussions</a></p>
            <p class='nav'><a href="../searchPage/search.php">Search</a></p>
        </div>
    </div>

    <div id="home-banner">
        <div id="img-filter"></div> 
        <img src="../images/banner.png" alt="banner" class="banner-image">
        <p style="filter: opacity(40%); z-index: 1; position:absolute; top: 0; left: 1%; font-size: 1vh;">Source: <a href="https://www.youworkforthem.com/photo/139312/group-of-happy-friends-having-fun-on-mountain-top">https://www.youworkforthem.com/photo/139312/group-of-happy-friends-having-fun-on-mountain-top</a></p>
        <h1 class="cool-header">FIND YOUR NEXT DAY OUT</h1>
        <button class="glowing-button"><a href="../searchPage/search.php">SEARCH EVENTS</a></button>
    </div>

    <div id="home-content" style="margin-left: 3vmin; margin-right: 3vmin; position: relative;">
        <h3 style="font-size: var(--h3-size);">UPCOMING EVENTS</h3>
        <div class="trending-carousel-container" style="margin-bottom: 12vmin; position: relative;">
            <?php
                // --- UPCOMING EVENTS: the current user's saved events that have not happened yet ---
                $upcomingEvents = [];
                if (!empty($_SESSION['userID'])) {
                    try {
                        $stmt = $conn->prepare("
                            SELECT Events.EventID, EventName, EventDate, EventWhen, EventAddress, Link, EventImage
                            FROM SavedEvents
                            JOIN Events ON SavedEvents.EventID = Events.EventID
                            WHERE SavedEvents.UserID = :uid AND Events.EventDate >= CURDATE()
                            ORDER BY Events.EventDate ASC
                        ");
                        $stmt->bindParam(':uid', $_SESSION['userID'], PDO::PARAM_INT);
                        $stmt->execute();
                        $upcomingEvents = $stmt->fetchAll(PDO::FETCH_ASSOC);
                    } catch (PDOException $e) {
                        error_log("Database error fetching upcoming events: " . $e->getMessage());
                        $upcomingEvents = [];
                    }
                }


                error_reporting(E_ALL ^ E_NOTICE);
                if (!empty($upcomingEvents)) {
                    foreach ($upcomingEvents as $event) {
                        // Map database columns to the variables expected by the UI
                        $title = $event['EventName'];
                        $date = $event['EventWhen']; // Use EventWhen for display
                        $address = $event['EventAddress'];
                        $image = $event['EventImage'];

                        echo "
                            <div class='trending-carousel-slide'>
                                <div class='home-trending-event'>
                                    <div class='home-trending-event-container'>
                                        <div><img src='$image' alt='$title'></div> <!-- Changed from <a> to <div> -->
                                        <h3 id='trending-heading'><div id='event-link'>$title</div></h3> <!-- Changed from <a> to <div> -->
                                        <p id='description'><strong>Date:</strong> $date<br><strong>Address:</strong> $address</p>
                                    </div>
                                </div>
                            </div>
                        ";
                    }
                } else {
                    echo "<p class='no-upcoming-events'>You have no upcoming events. Save events from Search or My Events to see them here.</p>";
                }
                ?>

            <?php if (!empty($upcomingEvents)): ?>
            <button id="prev" onclick="prevSlide()">&#10094;</button>
            <button id="next" onclick="nextSlide()">&#10095;</button>
            <?php endif; ?>
        </div>
    </div>
    <script src="homepage-script.js?v=<?php echo filemtime('homepage-script.js'); ?>"></script>
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
                <?php if (isset($_SESSION['userID'])): ?>
                <a href="../profilePage/view_profile.php">Account</a>
                <?php else: ?>
                <a href="../profilePage/account.php">Account</a>
                <?php endif; ?>
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
    <?php ob_end_flush(); ?>
</html>