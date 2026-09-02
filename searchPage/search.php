<?php
    error_log("*************** Inside search.php (XAMPP htdocs version)");
    session_start();
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
        
        $api_url = 'https://serpapi.com/search.json?engine=google&q=australia%20'.$userQuery.'&hl=en&api_key='.$key."&start=". $offset; //1 --> 0*10 = 0 = page 1
        error_log("DEBUG: Attempting to call API URL: " . $api_url);
        $response = file_get_contents($api_url);
        $data = json_decode($response, true);
        // Updated to use 'organic_results' for 'google' engine
        $events = $data['organic_results'] ?? [];

        $directory = '../pages/';

        // Ensure the directory exists
        if (!is_dir($directory)) {
            mkdir($directory, 0755, true); // Create directory recursively with permissions
            error_log("DEBUG: Created directory $directory");
        }

        $templatePath = '../eventDetails/details.php';

        $pageContent = file_get_contents($templatePath);

        if (!empty($events)) {
            foreach ($events as $index => $event) {
                // Extract data from organic_results structure
                $title = $event['title'] ?? 'No Title Found';
                // Date and address are not available in organic results, use placeholder or query term
                $date = 'See Link'; // Placeholder as date is not in organic results
                $address = 'See Link'; // Placeholder as address is not in organic results
                $description = $event['snippet'] ?? 'No description available.';
                $image = $event['thumbnail'] ?? 'https://via.placeholder.com/150'; // Use thumbnail if available, otherwise a placeholder
                // Use the 'link' field from organic results as the event link
                $link_from_api = $event['link'] ?? '#';

                // Generate a safe filename based on title and query, avoiding filesystem issues
                $safe_title_part = preg_replace('/[^a-zA-Z0-9]/', '_', $title);
                $safe_query_part = preg_replace('/[^a-zA-Z0-9]/', '_', $userQuery);
                $file = $safe_title_part . '_' . $safe_query_part . '_' . $index;

                $filename = $directory . $file . '.php';

                $pageContent = '
                <?php
                    $eventTitle = "' . addslashes($title) . '";
                    $eventDate = "' . addslashes($date) . '"; 
                    $eventAddress = "' . addslashes($address) . '";
                    $eventImage = "' . addslashes($image) . '";
                    $description = "' .addslashes($description) . '";
                    $link ="' .addslashes($filename) . '";
                    // Store the actual external link from the API
                    $externalLink = "' . addslashes($link_from_api) . '";
                ?>
                ' . file_get_contents($templatePath);

                file_put_contents($filename, $pageContent);
                // Load local database configuration
                require_once '../config/database_local.php';

                // Database connection using local config
                $dsn = "mysql:host=" . DB_HOST . ";dbname=" . DB_NAME;
                $user = DB_USER;
                $pass = DB_PASS;
                $conn = new PDO($dsn, $user, $pass);
                $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION); // Enable exception mode for PDO

                $currentPageUrl = 'http://'.$_SERVER["HTTP_HOST"].$_SERVER["REQUEST_URI"];
                $values = parse_url($currentPageUrl);
                // For events without a specific date, use a default or current date, or skip date manipulation
                $start_date = date("Y-m-d"); // Using current date as a fallback

                // Use prepared statements to avoid SQL injection
                $sql = "INSERT INTO Events (EventName, EventDate, EventWhen, EventAddress, Link, EventImage) VALUES (:eventName, :eventDate, :eventWhen, :eventAddress, :link, :eventImage)";
                $stmt = $conn->prepare($sql);
                $stmt->bindParam(':eventName', $title, PDO::PARAM_STR);
                $stmt->bindParam(':eventDate', $start_date, PDO::PARAM_STR);
                $stmt->bindParam(':eventWhen', $date, PDO::PARAM_STR);
                $stmt->bindParam(':eventAddress', $address, PDO::PARAM_STR);
                $stmt->bindParam(':link', $filename, PDO::PARAM_STR);
                $stmt->bindParam(':eventImage', $image, PDO::PARAM_STR);

                $stmt->execute();
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

                            $sql = "Select EventID from Events where link = '$filename'";
                            $result = $conn->query($sql);
                            $row = $result->fetch(PDO::FETCH_ASSOC);
                            $eventID = $row['EventID'];
                            $sql = "SELECT * FROM SavedEvents WHERE EventID = '$eventID' AND UserID = '$userID'";
                            $result = $conn->query($sql);
                            $rows = $result->fetchAll(PDO::FETCH_ASSOC);
                            

                            if (isset($_SESSION['userID'])){
                                $userID = $_SESSION['userID'];
                                $sql = "SELECT * FROM SavedEvents WHERE EventID = '$eventID' AND UserID = '$userID'";
                                $result = $conn->query($sql);
                                $rows = $result->fetchAll(PDO::FETCH_ASSOC);
                                if (isset($_POST['add-to-calendar'])) {
                                    $eventID = $_POST['eventID'];
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
                                    
                                    header("Location: " . $_SERVER['PHP_SELF'] . "?query=" . $userQuery);
                                    exit();
                                }
                            }
                            
                            if (empty($_SESSION['userID']) || !isset($_SESSION['userID'])) {
                                echo "
                                    <div class='search-event-container'>
                                        <a href='$filename'><img src='$image' alt='$title'></a>
                                        <h3 id='title'><a href='$filename' id='event-link'>$title</a></h3>
                                        <p id='description'><strong>Date:</strong> $date<br><strong>Address:</strong> $address</p>
                                        <button onclick='copyEventLink(\"$filename\"); changeButtonText(this)' class='share-event-btn' style='cursor: pointer;'>Share</button>
                                        <div class='search-event-tag-container'>
                                                <p>$category</p>
                                        </div>
                                    </div>
                                    ";
                            }
                            else {
                                if (isset($_SESSION['userID'])) {
                                    echo "
                                    <div class='search-event-container'>
                                        <a href='$filename'><img src='$image' alt='$title'></a>
                                        <h3 id='title'><a href='$filename' id='event-link'>$title</a></h3>
                                        <p id='description'><strong>Date:</strong> $date<br><strong>Address:</strong> $address</p>
                                        <button onclick='copyEventLink(\"$filename\"); changeButtonText(this)' class='share-event-btn' style='cursor: pointer;'>Share</button>
                                        <div ";
                            if (empty($_SESSION['userID'])){echo "style=\"display:none\"";}
                            echo ">
                                <form method=\"POST\">
                                    <input type='hidden' name='eventID' value='$eventID'>
                            ";
                            if (empty($rows)) {
                                echo "<button name=\"add-to-calendar\" class='save-event-btn' style='cursor: pointer;'>+</button>";
                            }
                            else {
                                echo "<button name=\"add-to-calendar\" class='saved-event-btn' style='cursor: pointer;'>&#10003;</button>";
                            }    
                            echo "
                                </form>
                                </div>
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

                    $api_url_paginated = 'https://serpapi.com/search.json?engine=google&q=australia%20'.$userQuery.'&hl=en&api_key='.$key."&start=". (($pageNo + 1 - 1) * 10); // Use the next page offset
                    error_log("DEBUG: Attempting to call paginated API URL: " . $api_url_paginated);
                    $response = file_get_contents($api_url_paginated);
                    $data = json_decode($response, true);
                    // Updated to use 'organic_results' for 'google' engine
                    $events = $data['organic_results'] ?? [];

                    $directory = '../pages/';

                    // Ensure the directory exists (same check as above)
                    if (!is_dir($directory)) {
                        mkdir($directory, 0755, true); // Create directory recursively with permissions
                        error_log("DEBUG: Created directory $directory (in pagination block)");
                    }

                    $templatePath = '../eventDetails/details.php';

                    $pageContent = file_get_contents($templatePath);

                    if (!empty($events)) {
                        foreach ($events as $index => $event) {
                            // Extract data from organic_results structure
                            $title = $event['title'] ?? 'No Title Found';
                            // Date and address are not available in organic results, use placeholder or query term
                            $date = 'See Link'; // Placeholder as date is not in organic results
                            $address = 'See Link'; // Placeholder as address is not in organic results
                            $description = $event['snippet'] ?? 'No description available.';
                            $image = $event['thumbnail'] ?? 'https://via.placeholder.com/150'; // Use thumbnail if available, otherwise a placeholder
                            // Use the 'link' field from organic results as the event link
                            $link_from_api = $event['link'] ?? '#';

                            // Generate a safe filename based on title and query, avoiding filesystem issues
                            $safe_title_part = preg_replace('/[^a-zA-Z0-9]/', '_', $title);
                            $safe_query_part = preg_replace('/[^a-zA-Z0-9]/', '_', $userQuery);
                            $file = $safe_title_part . '_' . $safe_query_part . '_' . $index;

                            $filename = $directory . $file . '.php';

                            $pageContent = '
                            <?php
                                $eventTitle = "' . addslashes($title) . '";
                                $eventDate = "' . addslashes($date) . '"; 
                                $eventAddress = "' . addslashes($address) . '";
                                $eventImage = "' . addslashes($image) . '";
                                $description = "' .addslashes($description) . '";
                                $link ="' .addslashes($filename) . '";
                                // Store the actual external link from the API
                                $externalLink = "' . addslashes($link_from_api) . '";
                            ?>
                            ' . file_get_contents($templatePath);

                            file_put_contents($filename, $pageContent);
                            // Load local database configuration
                            require_once '../config/database_local.php';

                            // Database connection using local config
                            $dsn = "mysql:host=" . DB_HOST . ";dbname=" . DB_NAME;
                            $user = DB_USER;
                            $pass = DB_PASS;
                            $conn = new PDO($dsn, $user, $pass);
                            $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION); // Enable exception mode for PDO

                            $currentPageUrl = 'http://'.$_SERVER["HTTP_HOST"].$_SERVER["REQUEST_URI"];
                            $values = parse_url($currentPageUrl);
                            // For events without a specific date, use a default or current date, or skip date manipulation
                            $start_date = date("Y-m-d"); // Using current date as a fallback

                            // Use prepared statements to avoid SQL injection
                            $sql = "INSERT INTO Events (EventName, EventDate, EventWhen, EventAddress, Link, EventImage) VALUES (:eventName, :eventDate, :eventWhen, :eventAddress, :link, :eventImage)";
                            $stmt = $conn->prepare($sql);
                            $stmt->bindParam(':eventName', $title, PDO::PARAM_STR);
                            $stmt->bindParam(':eventDate', $start_date, PDO::PARAM_STR);
                            $stmt->bindParam(':eventWhen', $date, PDO::PARAM_STR);
                            $stmt->bindParam(':eventAddress', $address, PDO::PARAM_STR);
                            $stmt->bindParam(':link', $filename, PDO::PARAM_STR);
                            $stmt->bindParam(':eventImage', $image, PDO::PARAM_STR);

                            $stmt->execute();
                        }
                    }
                }
            ?>
        </div>
    </div>
    <script src="../searchPage/search-script.js"></script>
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
</html>
