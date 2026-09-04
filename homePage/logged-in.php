<?php
    ob_start();
    $userHobbies = $_SESSION['hobbies'] ?? '';
    $userLocation = $_SESSION['location'] ?? '';
    $key = getenv('SERP_API_KEY') ?: ''; // Use environment variable, fallback to empty string

    $directory = '../pages/';

    $templatePath = '../eventDetails/details.php';

    $pageContent = file_get_contents($templatePath);

    // Load local database configuration
    require_once '../config/database_local.php';
    require_once __DIR__ . '/../config/features.php';

    // Shared connection for persisting API events on this page
    $dsn = "mysql:host=" . DB_HOST . ";dbname=" . DB_NAME;
    $conn = new PDO($dsn, DB_USER, DB_PASS);
    $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    // Events the current user has already saved (for rendering + button state)
    $savedIds = [];
    if (!empty($_SESSION['userID'])) {
        $savedStmt = $conn->prepare("SELECT EventID FROM SavedEvents WHERE UserID = :uid");
        $savedStmt->execute([':uid' => $_SESSION['userID']]);
        $savedIds = $savedStmt->fetchAll(PDO::FETCH_COLUMN);
    }

    /**
     * Persist a SERP API event into the Events table and generate its
     * details page under ../pages/. Deduplicates on Events.Link.
     * Returns ['id' => EventID|null, 'filename' => generated page path].
     */
    function persistApiEvent($conn, array $event, string $directory, string $templatePath): ?array
    {
        $title = is_array($event['title'] ?? '') ? implode(', ', $event['title']) : ($event['title'] ?? '');
        $when = is_array($event['date']['when'] ?? '') ? implode(', ', $event['date']['when']) : ($event['date']['when'] ?? '');
        $address = is_array($event['address'] ?? '') ? implode(', ', $event['address']) : ($event['address'] ?? '');
        $description = $event['description'] ?? '';
        if (is_array($description)) {
            $description = implode(', ', $description);
        }
        $image = $event['thumbnail'] ?? '';
        if (is_array($image)) {
            $image = implode(', ', $image);
        }
        $start_date = is_array($event['date']['start_date'] ?? '') ? implode(', ', $event['date']['start_date']) : ($event['date']['start_date'] ?? '');

        if ($title === '') {
            return null;
        }

        $file = preg_replace('/[^a-zA-Z0-9]/', '', $title . '_' . $when);
        $filename = $directory . $file . '.php';

        // Generate this event's details page from the template
        $generated = '
        <?php
            $eventTitle = "' . addslashes($title) . '";
            $eventDate = "' . addslashes($when) . '"; 
            $eventAddress = "' . addslashes($address) . '";
            $eventImage = "' . addslashes($image) . '";
            $description = "' . addslashes($description) . '";
            $link ="' . addslashes($filename) . '";
        ?>
        ' . file_get_contents($templatePath);

        if (!is_dir($directory)) {
            @mkdir($directory, 0777, true);
        }
        file_put_contents($filename, $generated);

        // Normalise the start date; push past events forward a year so they stay visible
        $ts = strtotime($start_date);
        $start_date = $ts ? date('Y-m-d', $ts) : date('Y-m-d');
        if (date('Y-m-d') > $start_date) {
            $start_date = date('Y-m-d', strtotime('+1 year', strtotime($start_date)));
        }

        $stmt = $conn->prepare("INSERT INTO Events (EventName, EventDate, EventWhen, EventAddress, Link, EventImage)
                                VALUES (:name, :date, :when, :address, :link, :image)
                                ON DUPLICATE KEY UPDATE EventDate = VALUES(EventDate), EventWhen = VALUES(EventWhen),
                                    EventAddress = VALUES(EventAddress), EventImage = VALUES(EventImage)");
        $stmt->execute([
            ':name' => $title,
            ':date' => $start_date,
            ':when' => $when,
            ':address' => $address,
            ':link' => $filename,
            ':image' => $image,
        ]);

        $idStmt = $conn->prepare("SELECT EventID FROM Events WHERE Link = :link");
        $idStmt->execute([':link' => $filename]);
        $id = $idStmt->fetchColumn();

        return ['id' => $id ? (int)$id : null, 'filename' => $filename];
    }

    $interestsEvents = []; // Initialize as empty array
    if (!empty($key)) { // Only proceed if the API key is set
        $api_url_interests = 'https://serpapi.com/search.json?engine=google_events&q=australia%20'.$userHobbies.'&hl=en&api_key='.$key;
        $response = @file_get_contents($api_url_interests); // Suppress warnings with @
        if ($response !== false) {
            $interestsData = json_decode($response, true);
            $interestsEvents = $interestsData['events_results'] ?? [];
        }
    }
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
        <h3 style="font-size: var(--h3-size);">TRENDING NEAR YOU</h3>
        <div class="trending-carousel-container" style="margin-bottom: 12vmin; position: relative;">
            <?php
                // --- BEGIN NEW LOGIC FOR TRENDING EVENTS (based on Weekend) ---
                // Calculate the upcoming weekend (Saturday and Sunday)
                $today = new DateTime();
                $daysUntilSat = 6 - $today->format('w'); // w is 0 for Sunday, 1 for Monday, ..., 6 for Saturday
                if ($daysUntilSat <= 0) {
                    $daysUntilSat += 7; // If today is Sat/Sun, get next weekend
                }
                $weekendDateStart = clone $today;
                $weekendDateStart->modify("+$daysUntilSat days");
                $weekendDateEnd = clone $weekendDateStart;
                $weekendDateEnd->modify('+1 day'); // Sunday

                $weekendStartStr = $weekendDateStart->format('Y-m-d');
                $weekendEndStr = $weekendDateEnd->format('Y-m-d');

                // Local DB config + $conn were loaded at the top of this page

                try {
                    // Prepare and execute the query to get events for the weekend
                    $stmt = $conn->prepare("
                        SELECT EventID, EventName, EventDate, EventWhen, EventAddress, Link, EventImage
                        FROM Events
                        WHERE EventDate BETWEEN :start_date AND :end_date
                        ORDER BY EventDate ASC
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
                // --- END NEW LOGIC FOR TRENDING EVENTS (based on Weekend) ---


                error_reporting(E_ALL ^ E_NOTICE);
                // Use $weekendEvents instead of $interestsEvents
                if (!empty($weekendEvents)) {
                    foreach ($weekendEvents as $event) {
                        // Map database columns to the variables expected by the UI
                        $eventId = $event['EventID'];
                        $title = $event['EventName'];
                        $date = $event['EventWhen']; // Use EventWhen for display
                        $address = $event['EventAddress'];
                        $image = $event['EventImage'];
                        // $filename = $event['Link']; // No longer used for linking

                        // Whether this event is already in the user's calendar
                        $isSaved = in_array($eventId, $savedIds);
                        $saveLabel = $isSaved ? "&#10003;" : "+";
                        $saveStyle = $isSaved
                            ? "cursor: pointer; background-color:#0D99FF; color:white;"
                            : "cursor: pointer;";

                        // Determine category based on the event title (similar to existing logic)
                        $eventsString = $title . ' ' . $address . ' ' . $date;
                        if (stripos($eventsString, 'gaming') || stripos($eventsString, 'game')) {
                            $category = "🎮 GAMING";
                        }
                        else if (stripos($eventsString, 'art') ||
                        stripos($eventsString, 'arts') ||
                        stripos($eventsString, 'gallery')) {
                            $category = "🎨 ART";
                        }
                        else if (stripos($eventsString, 'sport')) {
                            $category = "🏀 SPORT";
                        }
                        else if (stripos($eventsString, 'music') || stripos($eventsString, 'Music') || stripos($eventsString, 'band')){
                            $category = "🎵 MUSIC";
                        }
                        else {
                            $category = "";
                        }

                        echo "
                            <div class='trending-carousel-slide'>
                                <div class='home-trending-event'>
                                    <div class='home-trending-event-container'>
                                        <div><img src='$image' alt='$title'></div> <!-- Changed from <a> to <div> -->
                                        <h3 id='trending-heading'><div id='event-link'>$title</div></h3> <!-- Changed from <a> to <div> -->
                                        <p id='description'><strong>Date:</strong> $date<br><strong>Address:</strong> $address</p>
                                        <!-- Share button removed as there's no specific link to share -->
                                        <button onclick='saveEvent(this)' class='trending-save-event-btn' data-event-id='$eventId' style='$saveStyle'>$saveLabel</button>
                                        <div class='trending-event-tag-container'>
                                            <p>$category</p>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        ";
                    }
                } else {
                    echo "<p>No events found.</p>";
                }
                ?>

            <button id="prev" onclick="prevSlide()">&#10094;</button>
            <button id="next" onclick="nextSlide()">&#10095;</button>
        </div>

        <div class="section" style="margin-bottom: var(--section-bottom);">
            <h3 style="font-size: var(--h3-size);">BASED ON YOUR INTERESTS</h3>
            <div class="home-listed-events-container">
                <?php
                    

                    error_reporting(E_ALL ^ E_NOTICE);
                    if (!empty($interestsEvents)) {
                        foreach ($interestsEvents as $index => $event) {
                            $title = is_array($event['title']) ? implode(', ', $event['title']) : ($event['title']);
                            $date = is_array($event['date']['when']) ? implode(', ', $event['date']['when']) : ($event['date']['when']);
                            $address = is_array($event['address']) ? implode(', ', $event['address']) : ($event['address']);
                            $image = $event['thumbnail'];

                            // Persist the event so it can be saved to a calendar
                            $persisted = persistApiEvent($conn, $event, $directory, $templatePath);
                            $filename = $persisted['filename'];
                            $eventId = $persisted['id'];

                            $isSaved = $eventId && in_array($eventId, $savedIds);
                            $saveLabel = $isSaved ? "&#10003;" : "+";
                            $saveStyle = $isSaved
                                ? "cursor: pointer; background-color:#0D99FF; color:white;"
                                : "cursor: pointer;";

                            $eventsString = implode(', ', $event);
                            //echo "$eventsString";
                            //reformatting
                            if (stripos($eventsString, 'gaming') || stripos($eventsString, 'game')) {
                                $category = "🎮 GAMING";
                            }
                            else if (stripos($eventsString, 'art') || 
                            stripos($eventsString, 'arts') || 
                            stripos($eventsString, 'gallery')) {
                                $category = "🎨 ART";
                            }
                            else if (stripos($eventsString, 'sport')) {
                                $category = "🏀 SPORT";
                            }
                            else if (stripos($eventsString, 'music') || stripos($eventsString, 'Music') || stripos($eventsString, 'band')){
                                $category = "🎵 MUSIC";
                            }
                            else {
                                $category = "";
                            }

                            echo "
                                    <div class='event-container'>
                                        <a href='$filename'><img src='$image' alt='$title'></a>
                                        <h3 id='trending-heading'><a href='$filename' id='event-link'>$title</a></h3>
                                        <p id='description'><strong>Date:</strong> $date<br><strong>Address:</strong> $address</p>
                                        <button onclick='copyEventLink(\"$filename\"); changeButtonText(this)' class='share-event-btn' style='cursor: pointer;'>Share</button>
                                        <button onclick='saveEvent(this)' class='save-event-btn' data-event-id='$eventId' style='$saveStyle'>$saveLabel</button>
                                        <div class='search-event-tag-container'>
                                                <p>$category</p>
                                        </div>
                                    </div>
                                    ";

                        }
                    } else {
                        echo "<p>No events found.</p>";
                    }
                ?>
            </div>
        </div>

        <div class="section" style="margin-bottom: var(--section-bottom);">
            <h3 style="font-size: var(--h3-size);">BASED ON YOUR RECENT EVENTS</h3>
            <div class="home-listed-events-container">
                <?php
                    // --- API fetch for "recent events" (based on user location) ---
                    $recentEvents = []; // Initialize as empty array
                    if (!empty($key)) { // Only proceed if the API key is set
                        $api_url_recents = 'https://serpapi.com/search.json?engine=google_events&q=australia%20'.$userLocation.'&hl=en&api_key='.$key;
                        $response_recents = @file_get_contents($api_url_recents); // Suppress warnings with @
                        if ($response_recents !== false) {
                            $recentsData = json_decode($response_recents, true);
                            $recentEvents = $recentsData['events_results'] ?? [];
                        }
                    }
                    // --- END recent events fetch ---

                    error_reporting(E_ALL ^ E_NOTICE);
                    if (!empty($recentEvents)) {
                        foreach ($recentEvents as $index => $event) {
                            $title = is_array($event['title']) ? implode(', ', $event['title']) : ($event['title']);
                            $date = is_array($event['date']['when']) ? implode(', ', $event['date']['when']) : ($event['date']['when']);
                            $address = is_array($event['address']) ? implode(', ', $event['address']) : ($event['address']);
                            $image = $event['thumbnail'];

                            // Persist the event so it can be saved to a calendar
                            $persisted = persistApiEvent($conn, $event, $directory, $templatePath);
                            $filename = $persisted['filename'];
                            $eventId = $persisted['id'];

                            $isSaved = $eventId && in_array($eventId, $savedIds);
                            $saveLabel = $isSaved ? "&#10003;" : "+";
                            $saveStyle = $isSaved
                                ? "cursor: pointer; background-color:#0D99FF; color:white;"
                                : "cursor: pointer;";

                            $eventsString = implode(', ', $event);
                            //reformatting
                            if (stripos($eventsString, 'gaming') || stripos($eventsString, 'game')) {
                                $category = "🎮 GAMING";
                            }
                            else if (stripos($eventsString, 'art') || 
                            stripos($eventsString, 'arts') || 
                            stripos($eventsString, 'gallery')) {
                                $category = "🎨 ART";
                            }
                            else if (stripos($eventsString, 'sport')) {
                                $category = "🏀 SPORT";
                            }
                            else if (stripos($eventsString, 'music') || stripos($eventsString, 'Music') || stripos($eventsString, 'band')){
                                $category = "🎵 MUSIC";
                            }
                            else {
                                $category = "";
                            }

                            echo "
                                    <div class='event-container'>
                                        <a href='$filename'><img src='$image' alt='$title'></a>
                                        <h3 id='title'><a href='$filename' id='event-link'>$title</a></h3>
                                        <p id='description'><strong>Date:</strong> $date<br><strong>Address:</strong> $address</p>
                                        <button onclick='copyEventLink(\"$filename\"); changeButtonText(this)' class='share-event-btn' style='cursor: pointer;'>Share</button>
                                        <button onclick='saveEvent(this)' class='save-event-btn' data-event-id='$eventId' style='$saveStyle'>$saveLabel</button>
                                        <div class='search-event-tag-container'>
                                                <p>$category</p>
                                        </div>
                                    </div>
                                    ";

                        }
                    } else {
                        echo "<p>No events found.</p>";
                    }
                ?>
            </div>
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
    <?php ob_end_flush(); ?>
</html>