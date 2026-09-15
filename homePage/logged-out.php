<?php
    ob_start();
    $userHobbies = $_SESSION['hobbies'] ?? '';
    $userLocation = $_SESSION['location'] ?? '';
    $key = getenv('SERP_API_KEY') ?: ''; // Use environment variable, fallback to empty string

    $directory = '../pages/';

    $templatePath = '../eventDetails/details.php';

    $pageContent = file_get_contents($templatePath);
    $pageContent = str_replace('src="../', 'src="../../', $pageContent);
    $pageContent = str_replace('href="../', 'href="../../', $pageContent);

    // --- NEW LOGIC FOR EVENTS THIS WEEKEND ---
    try {
        // Load local database configuration
        require_once '../config/database_local.php';

        // Database connection using local config
        $dsn = "mysql:host=" . DB_HOST . ";dbname=" . DB_NAME;
        $user = DB_USER;
        $pass = DB_PASS;
        $conn = new PDO($dsn, $user, $pass);
        $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

        $stmt = $conn->prepare("
            SELECT e.*, p.PriceValue AS Price
            FROM Events e
            JOIN Prices p ON e.EventID = p.EventID
            WHERE e.StartDate BETWEEN :start_date AND :end_date
            AND p.PriceValue <= 0
            ORDER BY e.StartDate ASC
            LIMIT 3
        ");
        $stmt->bindParam(':start_date', $weekendStartStr, PDO::PARAM_STR);
        $stmt->bindParam(':end_date', $weekendEndStr, PDO::PARAM_STR);
        $stmt->execute();

        // Fetch the results
        $weekendEvents = $stmt->fetchAll(PDO::FETCH_ASSOC);
    } catch (PDOException $e) {
        error_log("Database error fetching weekend events: " . $e->getMessage());
        $weekendEvents = []; // Return empty array on error
    }
    // --- END NEW LOGIC FOR EVENTS THIS WEEKEND ---
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
            <p class='nav'><a href="../profilePage/login.php">Log in</a></p>
            <p class='nav'><a href="../calendar/events.php">My Events</a></p>
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
        <h3 style="font-size: var(--h3-size);">EVENTS THIS WEEKEND</h3>
        <div class="trending-carousel-container" style="margin-bottom: 12vmin; position: relative;">
        <?php
                
                
                error_reporting(E_ALL ^ E_NOTICE);
                // Use $weekendEvents instead of $interestsEvents
                if (!empty($weekendEvents)) {
                    foreach ($weekendEvents as $event) {
                        // Map database columns to the variables expected by the UI
                        $title = $event['EventName'];
                        $date = $event['EventWhen']; // Use EventWhen for display
                        $address = $event['EventAddress'];
                        $image = $event['EventImage'];
                        // $filename = $event['Link']; // No longer used for linking

                        echo "
                            <div class='trending-carousel-slide'>
                                <div class='home-trending-event'>
                                    <div class='home-trending-event-container'>
                                        <div><img src='$image' alt='$title'></div> <!-- Changed from <a> to <div> -->
                                        <h3 id='title'><div id='event-link'>$title</div></h3> <!-- Changed from <a> to <div> -->
                                        <p id='description'><strong>Date:</strong> $date<br><strong>Address:</strong> $address</p>
                                        <!-- Share button removed as there's no specific link to share -->
                                    </div>
                                </div>
                            </div>
                        ";
                    }
                } else {
                    echo "
                            <div class='trending-carousel-slide active'>
                                <div class='home-trending-event'>
                                    <div class='home-trending-event-container'>
                                        <img src='../images/canyon.png' alt='canyon'>
                                        <button class='trending-share-event-btn'>Share</button>
                                        <h3 id='trending-heading'>#1 EVENT NAME</h3>
                                    </div>
                                </div>
                            </div>
                            <div class='trending-carousel-slide'>
                                <div class='home-trending-event'>
                                    <div class='home-trending-event-container'>
                                        <img src='../images/canyon.png' alt='canyon'>
                                        <button class='trending-share-event-btn'>Share</button>
                                        <h3 id='trending-heading'>#1 EVENT NAME</h3>
                                    </div>
                                </div>
                            </div>
                            <div class='trending-carousel-slide'>
                                <div class='home-trending-event'>
                                    <div class='home-trending-event-container'>
                                        <img src='../images/canyon.png' alt='canyon'>
                                        <button class='trending-share-event-btn'>Share</button>
                                        <h3 id='trending-heading'>#1 EVENT NAME</h3>
                                    </div>
                                </div>
                            </div>
                        ";
                }
                ?>
            <button id="prev" onclick="prevSlide()">&#10094;</button>
            <button id="next" onclick="nextSlide()">&#10095;</button>
        </div>

        <div class="section" style="margin-bottom: var(--section-bottom);">
            <h3 style="font-size: var(--h3-size);">FREE EVENTS IN AUSTRALIA</h3>
            <div class="home-listed-events-container">
            <?php
                    // Load local database configuration for this section
                    require_once '../config/database_local.php';
                    $dsn_free = "mysql:host=" . DB_HOST . ";dbname=" . DB_NAME;
                    $user_free = DB_USER;
                    $pass_free = DB_PASS;
                    $conn_free = new PDO($dsn_free, $user_free, $pass_free);
                    $conn_free->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

                    $freeEvents = []; // Initialize as empty array
                    $events = []; // Initialize $events as an empty array here
                    if (!empty($key)) { // Only proceed if the API key is set
                        $api_url = 'https://serpapi.com/search.json?engine=google_events&q=australia%20&hl=en&api_key='.$key;
                        $response = @file_get_contents($api_url); // Suppress warnings with @
                        if ($response !== false) {
                            $data = json_decode($response, true);
                            $events = $data['events_results'] ?? [];
                        }
                    }
                    foreach ($events as $event) {
                        $title = $event['title'] ?? 'N/A';
                        $date = $event['date'] ?? 'N/A';
                        $address = $event['address'] ?? 'N/A';
                        $description = $event['description'] ?? 'N/A';
                        $price = $event['price'] ?? 'Free';
                        $link = $event['link'] ?? '#';

                        // Prepare statement to check if event title already exists
                        $stmt_check = $conn_free->prepare("SELECT COUNT(*) FROM Events WHERE Title = :title");
                        $stmt_check->bindParam(':title', $title, PDO::PARAM_STR);
                        $stmt_check->execute();
                        $count = $stmt_check->fetchColumn();

                        // If event title doesn't exist, insert it
                        if ($count == 0) {
                            $stmt_insert = $conn_free->prepare("INSERT INTO Events (Title, Description, StartDate, EndDate, Address, Link) VALUES (:title, :description, :date, :date, :address, :link)");
                            $stmt_insert->bindParam(':title', $title, PDO::PARAM_STR);
                            $stmt_insert->bindParam(':description', $description, PDO::PARAM_STR);
                            $stmt_insert->bindParam(':date', $date, PDO::PARAM_STR);
                            $stmt_insert->bindParam(':address', $address, PDO::PARAM_STR);
                            $stmt_insert->bindParam(':link', $link, PDO::PARAM_STR);
                            $stmt_insert->execute();
                        }
                    }
                    error_reporting(E_ALL ^ E_NOTICE);
                    if (!empty($events)) {
                        foreach ($events as $index => $event) {
                            $title = is_array($event['title']) ? implode(', ', $event['title']) : ($event['title']);
                            $date = is_array($event['date']['when']) ? implode(', ', $event['date']['when']) : ($event['date']['when']);
                            $address = is_array($event['address']) ? implode(', ', $event['address']) : ($event['address']);
                            $image = $event['thumbnail'];
                            $start_date = is_array($event['date']['start_date']) ? implode(', ', $event['date']['start_date']) : ($event['date']['start_date']);

                            $file = preg_replace('/[^a-zA-Z0-9]/', '', $title . '_' . $date);

                            $filename = $directory . $file . '.php';

                            
                            $eventsString = implode(', ', $event);

                            //get free events
                            $getFreeEvents = "SELECT * from Events where Price < 1";
                            $freeEvents = $conn->query($getFreeEvents);

                            if (!empty($freeEvents)) {
                                foreach ($freeEvents as $index => $freeEvent) {
                                    echo "
                                            <div class='event-container'>
                                                <a href='$filename'><img src='$image' alt='$title'></a>
                                                <h3 id='title'><a href='$filename' id='event-link'>$title</a></h3>
                                                <p id='description'><strong>Date:</strong> $date<br><strong>Address:</strong> $address</p>
                                                <button onclick='copyEventLink(\"$filename\"); changeButtonText(this)' class='share-event-btn' style='cursor: pointer;'>Share</button>
                                            </div>
                                        ";
                                }
                            }

                        }
                    } else {
                        
                    }
                ?>
            </div>
        </div>

        <div class="section" style="margin-bottom var(--section-bottom);">
            <h3 style="font-size: var(--h3-size);">Please Log In or Create an Account to Continue</h3>
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
