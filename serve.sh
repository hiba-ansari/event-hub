#!/bin/zsh
# ─────────────────────────────────────────────────────────────
# serve.sh — start/stop the local Event Hub dev environment
#
#   ./serve.sh start     Start Apache (httpd) + MySQL (Docker)
#   ./serve.sh stop      Stop both (database data is KEPT)
#   ./serve.sh restart   Stop then start
#   ./serve.sh status    Show what's currently running
#
# Note: stop never removes the MySQL data volume. To WIPE the
# database on purpose, run: docker compose down -v
# ─────────────────────────────────────────────────────────────

set -e
cd "$(dirname "$0")"

CONTAINER="event-hub-mysql"

do_start() {
    echo "▶ Starting Apache (httpd)…"
    brew services start httpd

    echo "▶ Starting MySQL container…"
    if docker ps -a --format '{{.Names}}' | grep -qx "$CONTAINER"; then
        docker start "$CONTAINER"
    else
        docker compose up -d
    fi

    echo ""
    echo "✔ Event Hub is up → http://localhost:8080/homePage/homepage.php"
}

do_stop() {
    echo "■ Stopping Apache (httpd)…"
    brew services stop httpd || true

    echo "■ Stopping MySQL container (data is kept)…"
    docker stop "$CONTAINER" >/dev/null 2>&1 && echo "  $CONTAINER stopped" || echo "  $CONTAINER was not running"

    echo ""
    echo "✔ Everything stopped."
}

do_status() {
    echo "── Apache (brew services) ──"
    brew services list | grep -E 'httpd|^Name' || true
    echo ""
    echo "── MySQL (docker) ──"
    docker ps -a --filter "name=$CONTAINER" --format '{{.Names}}\t{{.Status}}'
}

case "${1:-status}" in
    start)   do_start ;;
    stop)    do_stop ;;
    restart) do_stop; echo ""; do_start ;;
    status)  do_status ;;
    *)
        echo "Usage: ./serve.sh {start|stop|restart|status}"
        exit 1
        ;;
esac
