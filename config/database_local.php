<?php
// Local database configuration
// NOTE: use 127.0.0.1 (TCP), NOT 'localhost' — PHP treats 'localhost' as a
// Unix socket, which does not exist for a Docker-published MySQL port.
define('DB_HOST', '127.0.0.1');
define('DB_NAME', 'local_event_hub');
define('DB_USER', 'root');
define('DB_PASS', 'pass'); // matches MYSQL_ROOT_PASSWORD in docker-compose.yml
?>