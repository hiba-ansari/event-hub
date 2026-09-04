<?php
/**
 * Shared helper for persisting SERP API events into the Events table and
 * generating their details page (from the eventDetails template) under a
 * given directory. Deduplicates on Events.Link.
 *
 * Handles both API event shapes:
 *  - google_events engine:  $event['date'] is an array with 'when' / 'start_date'
 *  - google engine (search): $event['date'] is a scalar string, with a separate
 *                            $event['time'] string; $event['type'] acts as the
 *                            description.
 *
 * Returns ['id' => EventID|null, 'filename' => generated page path] or null
 * when the event has no usable title.
 */
if (!function_exists('persistApiEvent')) {
    function persistApiEvent($conn, array $event, string $directory, string $templatePath): ?array
    {
        $title = is_array($event['title'] ?? '') ? implode(', ', $event['title']) : ($event['title'] ?? '');
        $address = is_array($event['address'] ?? '') ? implode(', ', $event['address']) : ($event['address'] ?? '');

        $image = $event['thumbnail'] ?? '';
        if (is_array($image)) {
            $image = implode(', ', $image);
        }

        // Date handling: support both the array (google_events) and scalar (search) shapes.
        if (isset($event['date']) && is_array($event['date'])) {
            $when = is_array($event['date']['when'] ?? '') ? implode(', ', $event['date']['when']) : ($event['date']['when'] ?? '');
            $start_date_raw = is_array($event['date']['start_date'] ?? '') ? implode(', ', $event['date']['start_date']) : ($event['date']['start_date'] ?? '');
        } else {
            $date = is_array($event['date'] ?? '') ? implode(', ', $event['date']) : ($event['date'] ?? '');
            $date = is_array($date) ? implode(', ', $date) : $date;
            $time = $event['time'] ?? '';
            $time = is_array($time) ? implode(', ', $time) : $time;
            if ($date && $time) {
                $when = $date . ' at ' . $time;
            } else {
                $when = $date;
            }
            $start_date_raw = $date; // best-effort: parse the scalar date string
        }

        $description = $event['description'] ?? $event['type'] ?? '';
        if (is_array($description)) {
            $description = implode(', ', $description);
        }

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
        $ts = strtotime($start_date_raw);
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
}
