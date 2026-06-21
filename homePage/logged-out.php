<?php
    ob_start();
    $key = "abf15d2639c68a5ede04f73af3abc209f92dca4139559c83d2eadadc378d8c1a";

    $directory = '../pages/';

    $templatePath = '../eventDetails/details.php';

    $pageContent = file_get_contents($templatePath);

    if (!empty($events)) {
        foreach ($events as $index => $event) {
            $title = is_array($event['title']) ? implode(', ', $event['title']) : ($event['title']);
            $date = is_array($event['date']['when']) ? implode(', ', $event['date']['when']) : ($event['date']['when']);
            $address = is_array($event['address']) ? implode(', ', $event['address']) : ($event['address']);
            if (isset($event['description'])) {
                $description = is_array($event['description']) ? implode(', ', $event['description']) : ($event['description']);
            }
            else {
                $description = '';
            }
            $image = $event['thumbnail'];
            $start_date = is_array($event['date']['start_date']) ? implode(', ', $event['date']['start_date']) : ($event['date']['start_date']);

            $file = preg_replace('/[^a-zA-Z0-9]/', '', $title . '_' . $date);

            $filename = $directory . $file . '.php';

            $pageContent = '
            <?php
                $eventTitle = "' . addslashes($title) . '";
                $eventDate = "' . addslashes($date) . '"; 
                $eventAddress = "' . addslashes($address) . '";
                $eventImage = "' . $image . '";
                $description = "' .addslashes($description) . '";
                $link ="' .addslashes($filename) . '";
            ?>
            ' . file_get_contents($templatePath);

            file_put_contents($filename, $pageContent);
            $dsn = 'mysql:host=talsprddb02.int.its.rmit.edu.au;dbname=COSC3046_2402_UGRD_1479_G12';
            $user = 'COSC3046_2402_UGRD_1479_G12';
            $pass = 'LtEXbUiTF7Fm';
            $conn = new PDO($dsn, $user, $pass);
            $currentPageUrl = 'http://'.$_SERVER["HTTP_HOST"].$_SERVER["REQUEST_URI"];
            $values = parse_url($currentPageUrl);
            $start_date = strtotime($start_date);
            $start_date = date("Y-m-d", $start_date);
            $current = date("Y-m-d");

            if ($current > $start_date) {
                $start_date = date("Y-m-d", strtotime("+1 year", strtotime($start_date)));
            }

            
            $sql = "INSERT INTO Events (EventName, EventDate, EventWhen, EventAddress, Link, EventImage)
                    VALUES ('$title', '$start_date', '$date', '$address', '$filename', '$image');";
            $result = $conn->query($sql);
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
            <p class='nav'><a href="../profilePage/account.php">Log in</a></p>
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
                if (!empty($interestsEvents)) {
                    for ($index = 0; $index < 3 && $index < count($interestsEvents); $index++) {
                        $event = $interestsEvents[$index];
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
                        
                        echo "
                            <div class='trending-carousel-slide'>
                                <div class='home-trending-event'>
                                    <div class='home-trending-event-container'>
                                        <a href='$filename'><img src='$image' alt='$title'></a>
                                        <h3 id='title'><a href='$filename' id='event-link'>$title</a></h3>
                                        <p id='description'><strong>Date:</strong> $date<br><strong>Address:</strong> $address</p>
                                        <button onclick='copyEventLink(\"$filename\"); changeButtonText(this)' class='trending-share-event-btn' style='cursor: pointer;'>Share</button>
                                        <div class='trending-event-tag-container'>
                                            <p>$category</p>
                                        </div>
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
                                        <button class='trending-save-event-btn'>+</button>
                                        <h3 id='trending-heading'>#1 EVENT NAME</h3>
                                        <div class='trending-event-tag-container'>
                                            <p>🏀 SPORT</p>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <div class='trending-carousel-slide'>
                                <div class='home-trending-event'>
                                    <div class='home-trending-event-container'>
                                        <img src='../images/canyon.png' alt='canyon'>
                                        <button class='trending-share-event-btn'>Share</button>
                                        <button class='trending-save-event-btn'>+</button>
                                        <h3 id='trending-heading'>#1 EVENT NAME</h3>
                                        <div class='trending-event-tag-container'>
                                            <p>🏀 SPORT</p>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <div class='trending-carousel-slide'>
                                <div class='home-trending-event'>
                                    <div class='home-trending-event-container'>
                                        <img src='../images/canyon.png' alt='canyon'>
                                        <button class='trending-share-event-btn'>Share</button>
                                        <button class='trending-save-event-btn'>+</button>
                                        <h3 id='trending-heading'>#1 EVENT NAME</h3>
                                        <div class='trending-event-tag-container'>
                                            <p>🏀 SPORT</p>
                                        </div>
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
                    $dsn = 'mysql:host=talsprddb02.int.its.rmit.edu.au;dbname=COSC3046_2402_UGRD_1479_G12';
                    $user = 'COSC3046_2402_UGRD_1479_G12';
                    $pass = 'LtEXbUiTF7Fm';
                    $conn = new PDO($dsn, $user, $pass);
                    $api_url = 'https://serpapi.com/search.json?engine=google_events&q=australia%20&hl=en&api_key='.$key;
                    $response = file_get_contents($api_url);
                    $data = json_decode($response, true);
                    $events = $data['events_results'] ?? [];

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
                                                <div class='search-event-tag-container'>
                                                        <p>$category</p>
                                                </div>
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
    <script src="homepage-script.js"></script>
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
