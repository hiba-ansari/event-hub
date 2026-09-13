<?php
    error_log("*************** Inside search.php (XAMPP htdocs version) - Using events_results - NO LINKS");
    session_start();
    require_once __DIR__ . '/../config/features.php';
    require_once __DIR__ . '/../config/database_local.php';
    require_once __DIR__ . '/../includes/persist-api-event.php';

    // Shared connection so search results can be persisted + saved like the homepage.
    // Kept optional: if the DB is unreachable the search page still renders (without saving).
    $conn = null;
    try {
        $dsn = "mysql:host=" . DB_HOST . ";dbname=" . DB_NAME;
        $conn = new PDO($dsn, DB_USER, DB_PASS);
        $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    } catch (PDOException $e) {
        error_log("search.php: DB connection failed - " . $e->getMessage());
        $conn = null;
    }

    // Events the current user has already saved (for rendering the button state)
    $savedIds = [];
    if ($conn && !empty($_SESSION['userID'])) {
        $savedStmt = $conn->prepare("SELECT EventID FROM SavedEvents WHERE UserID = :uid");
        $savedStmt->execute([':uid' => $_SESSION['userID']]);
        $savedIds = $savedStmt->fetchAll(PDO::FETCH_COLUMN);
    }

    if (isset($_GET["query"])) {
        $key = "c30be58e6984eafadc346b28a3422bd9638cbc88c9783243ad3b7310f81590e1";
        $userQuery = $_GET["query"];
        $urlStart = 10;
        if (isset($_GET["page-no"])) {
            $pageNo = (int)$_GET["page-no"];
        }
        else {
            $pageNo = 1;
        }
        $offset = ($pageNo - 1) * 10;

        // Remove 'australia%20' prefix and rely on location context or general search potentially yielding event results
        $encodedUserQuery = urlencode($userQuery);
        $api_url = 'https://serpapi.com/search.json?engine=google&q='.$encodedUserQuery.'&hl=en&api_key='.$key."&start=". $offset; //1 --> 0*10 = 0 = page 1
        error_log("DEBUG: Attempting to call API URL: " . $api_url);
        $response = file_get_contents($api_url);
        $data = json_decode($response, true);
        // Use 'events_results' which is present when searching for events like 'art events'
        $events = $data['events_results'] ?? [];

        if (!empty($events)) {
            foreach ($events as $index => $event) {
                // Extract data from events_results structure
                $title = $event['title'] ?? 'No Title Found';
                $date = $event['date'] ?? 'Date TBD';
                $time = $event['time'] ?? ''; // Time might be separate
                // Combine date and time if both are present
                if ($date && $time) {
                     $full_date_str = $date . ' at ' . $time;
                } elseif ($date) {
                     $full_date_str = $date;
                } else {
                     $full_date_str = 'Time TBD';
                }
                // Address is an array, join the parts
                $address = is_array($event['address']) ? implode(', ', $event['address']) : ($event['address'] ?? 'Address TBD');
                $description = $event['type'] ?? 'No description available.'; // Using 'type' as description
                $image = $event['thumbnail'] ?? 'https://via.placeholder.com/150'; // Use thumbnail if available

                // Persist the event (generate its details page + Events row) so logged-in
                // users can click through and save it, same as the homepage. Uses the raw
                // $event so both API date shapes are handled by the shared helper.
                $eventId = null;
                $eventLink = '';
                if (!empty($_SESSION['userID']) && $conn) {
                    $persisted = persistApiEvent($conn, $event, '../pages/', '../eventDetails/details.php');
                    if ($persisted) {
                        $eventId = $persisted['id'];
                        $eventLink = $persisted['filename'];
                    }
                }

                // Store event data for rendering (also persisted above for saving)
                $event_data[] = [
                    'title' => $title,
                    'date' => $full_date_str,
                    'address' => $address,
                    'description' => $description,
                    'image' => $image,
                    'original_index' => $index,
                    'event_id' => $eventId,
                    'link' => $eventLink
                ];
            }
        }
    } 
    else {
        $userQuery = "";
    }      
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Search</title>
    <link rel="stylesheet" href="search.css">
    <link rel="stylesheet" href="../homePage/homepage.css">
    <link href="https://fonts.googleapis.com/css2?family=Bebas+Neue&family=Oswald:wght@600&family=Poppins:wght@300;400;600&family=Roboto:wght@300;400;500&display=swap" rel="stylesheet">
</head>
<body>
    <style>
        :root {
            --h3-size: 3.2vh;
            --event-size: 430px;
        }
        .listed-events-container {
            min-height: 56.4vh;
        }
        footer {
            position: relative;
            top: 100%;
        }
    </style>
    <div class='topnav'>
        <div class='column left'>
            <a href='../homePage/homepage.php' class="logo" style='font-size:40px;font-weight:600'><img src='../images/logo.png' style='width:50px;vertical-align:middle'> Local Event Hub</a>
        </div>
        <div class='column right'>
            <?php if (isset($_SESSION['userID'])): ?>
            <p class='nav'><a href="../profilePage/view_profile.php">Account</a></p>
            <?php else: ?>
            <p class='nav'><a href="../profilePage/account.php">Account</a></p>
            <?php endif; ?>
            <?php
                if (isset($_SESSION['userID'])){
                    $cartNav = feature_enabled('shopping_cart')
                        ? "<p class='nav'><a href=\"../shoppingCart/shopping-cart.php\">Cart</a></p>"
                        : '';
                    echo "
                    $cartNav
                    <p class='nav'><a href=\"../calendar/events.php\">My Events</a></p>";
                }
            ?>
            <p class='nav'><a href="../discussion/discussion.php">Discussions</a></p>
            <p class='nav'><a href="../searchPage/search.php">Search</a></p>
        </div>
    </div>

    <div id="search-content" style="margin-left: 30px; margin-right: 30px;">
        <form method="GET">
            <div id="event-searchbox-container" style="margin-top: 15px;">
                <input type="text" id="events-searchbar" placeholder="Search for things to do..." name="query">
            </div>
        </form>

        <div class="what-groups-are-organising-c">
            <?php
                if (!$userQuery) {
                    echo "<h3 style='font-size: var(--h3-size);'>  </h3>";
                }
                else {
                    echo "<h3 style='font-size: var(--h3-size);'>SHOWING TOP 10 RESULTS FOR: '".$userQuery."'</h3>";
                }
            ?>
            <div class="listed-events-container">
                
                <?php
                    error_reporting(E_ALL ^ E_NOTICE);
                    // Iterate over the collected event data
                    if (!empty($event_data)) {
                        foreach ($event_data as $event_item) {
                            $title = $event_item['title'];
                            $date = $event_item['date'];
                            $address = $event_item['address'];
                            $image = $event_item['image'];
                            $description = $event_item['description'];

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

                            // Use a div/span instead of an anchor tag for the image and title
                            if (empty($_SESSION['userID']) || !isset($_SESSION['userID'])) {
                                echo "
                                    <div class='search-event-container'>
                                        <div><img src='$image' alt='$title'></div>
                                        <h3 id='title'><div id='event-link'>$title</div></h3>
                                        <p id='description'><strong>Date:</strong> $date<br><strong>Address:</strong> $address</p>
                                        <!-- Share button removed as there's no specific link to share -->
                                        <div class='search-event-tag-container'>
                                                <p>$category</p>
                                        </div>
                                    </div>
                                    ";
                            }
                            else {
                                if (isset($_SESSION['userID'])) {
                                    $eventId = $event_item['event_id'] ?? null;
                                    $link = $event_item['link'] ?? '';
                                    $isSaved = $eventId && in_array($eventId, $savedIds);
                                    $saveLabel = $isSaved ? "&#10003;" : "+";
                                    $saveStyle = $isSaved
                                        ? "cursor: pointer; background-color:#0D99FF; color:white;"
                                        : "cursor: pointer;";

                                    // Clickable once the event has been persisted (same as homepage)
                                    $imageBlock = $link
                                        ? "<a href='$link'><img src='$image' alt='$title'></a>"
                                        : "<div><img src='$image' alt='$title'></div>";
                                    $titleBlock = $link
                                        ? "<a href='$link' id='event-link'>$title</a>"
                                        : "<div id='event-link'>$title</div>";
                                    $saveButton = $eventId
                                        ? "<button onclick='saveEvent(this)' class='save-event-btn' data-event-id='$eventId' style='$saveStyle'>$saveLabel</button>"
                                        : '';

                                    echo "
                                    <div class='search-event-container'>
                                        $imageBlock
                                        <h3 id='title'>$titleBlock</h3>
                                        <p id='description'><strong>Date:</strong> $date<br><strong>Address:</strong> $address</p>
                                        $saveButton
                                        <div class='search-event-tag-container'>
                                                <p>$category</p>
                                        </div>
                                    </div>
                                    ";
                                }
                            }

                        }
                    } else {
                        echo "<p>No events.</p>";
                    }
                ?>
            </div>
            <?php

                //SHOWING MORE REULTS
                if (isset($_GET['query'])) {
                    echo "
                            
                            <div class='next-page-buttons-container'>
                                <form id='page-form' method='GET' style='display:contents;'>
                                <input type='hidden' name='query' value='" . htmlspecialchars($userQuery) . "'>
                                <button type='submit' name='page-no' value='" . max(1, $pageNo - 1) . "'><</button>
                                <p><span id='page-no' name='page'>$pageNo</span>/5</p>
                                <button type='submit' name='page-no' value='" . ($pageNo + 1) . "'>></button>
                                </form>
                            </div>
                    ";

                    // Remove 'australia%20' prefix for pagination too
                    $encodedUserQueryPag = urlencode($userQuery);
                    $api_url_paginated = 'https://serpapi.com/search.json?engine=google&q='.$encodedUserQueryPag.'&hl=en&api_key='.$key."&start=". (($pageNo + 1 - 1) * 10); // Use the next page offset
                    error_log("DEBUG: Attempting to call paginated API URL: " . $api_url_paginated);
                    $response = file_get_contents($api_url_paginated);
                    $data = json_decode($response, true);
                    // Use 'events_results' which is present when searching for events like 'art events'
                    $events = $data['events_results'] ?? [];

                    // Skip file generation and database insertion for pagination as well
                    if (!empty($events)) {
                        foreach ($events as $index => $event) {
                            // Extract data from events_results structure
                            $title = $event['title'] ?? 'No Title Found';
                            $date = $event['date'] ?? 'Date TBD';
                            $time = $event['time'] ?? ''; // Time might be separate
                            // Combine date and time if both are present
                            if ($date && $time) {
                                 $full_date_str = $date . ' at ' . $time;
                            } elseif ($date) {
                                 $full_date_str = $date;
                            } else {
                                 $full_date_str = 'Time TBD';
                            }
                            // Address is an array, join the parts
                            $address = is_array($event['address']) ? implode(', ', $event['address']) : ($event['address'] ?? 'Address TBD');
                            $description = $event['type'] ?? 'No description available.'; // Using 'type' as description
                            $image = $event['thumbnail'] ?? 'https://via.placeholder.com/150'; // Use thumbnail if available

                            // Append data to the main event_data array
                            $event_data[] = [
                                'title' => $title,
                                'date' => $full_date_str,
                                'address' => $address,
                                'description' => $description,
                                'image' => $image,
                                'original_index' => $index // Keep index for uniqueness if needed later
                            ];

                            // Skip file generation and database insertion steps
                            // $filename = $directory . $file . '.php';
                            // $pageContent = '...' . file_get_contents($templatePath);
                            // file_put_contents($filename, $pageContent);

                            // Database insertion is skipped as there's no file link to store
                            // require_once '../config/database_local.php';
                            // $dsn = "mysql:host=" . DB_HOST . ";dbname=" . DB_NAME;
                            // $user = DB_USER;
                            // $pass = DB_PASS;
                            // $conn = new PDO($dsn, $user, $pass);
                            // $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
                            // $start_date = date("Y-m-d");
                            // if ($date) {
                            //     $parsed_timestamp = strtotime($date);
                            //     if ($parsed_timestamp !== false) {
                            //         $start_date = date("Y-m-d", $parsed_timestamp);
                            //     }
                            // }
                            // $sql = "INSERT INTO Events (EventName, EventDate, EventWhen, EventAddress, Link, EventImage) VALUES (:eventName, :eventDate, :eventWhen, :eventAddress, :link, :eventImage)";
                            // $stmt = $conn->prepare($sql);
                            // $stmt->bindParam(':eventName', $title, PDO::PARAM_STR);
                            // $stmt->bindParam(':eventDate', $start_date, PDO::PARAM_STR);
                            // $stmt->bindParam(':eventWhen', $full_date_str, PDO::PARAM_STR);
                            // $stmt->bindParam(':eventAddress', $address, PDO::PARAM_STR);
                            // $stmt->bindParam(':link', $filename, PDO::PARAM_STR); // This $filename was problematic
                            // $stmt->bindParam(':eventImage', $image, PDO::PARAM_STR);
                            // $stmt->execute();
                        }
                    }
                }
            ?>
        </div>
    </div>
    <script src="../searchPage/search-script.js?v=<?php echo filemtime(__DIR__ . '/search-script.js'); ?>"></script>
</body>
<footer class="footer" style="z-index: 0; position: relative; bottom: 0;">
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
