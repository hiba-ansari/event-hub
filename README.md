# web-studio-project-group_09_wps_2024

# Local Event Hub

Local Event Hub is a platform designed to help people discover nearby events and connect with others who share similar interests. The platform provides a personalized experience, making it easier to find activities, make friends, and build community connections.

## Features
- Event Discovery: Easily find events tailored to your interests.
- Discussion Forums: Join in or create topics based on your hobbies and interests.
- Shopping Cart: Add and manage events or tickets you are interested in purchasing.
- Calendar Integration: View and manage all your upcoming events in a calendar format.
- Event Suggestions: Based on user interests, past activities.

## Goals
- Eliminate difficulties in finding things to do.
- Help users find a community to belong to.
- Encourage people to engage in local activities and meet new people.

## Target Market
- Audience: Students, socially active individuals (18+).
- Lifestyle: Enjoys discovering new activities and engaging with the community.

## Login Details
- Email:Admin1@gmail.com    Password:admin1
- Email:robert@email.com    Password:qwerty12345

## Local Development Setup

This project runs with **Apache + PHP via Homebrew** and **MySQL via Docker** (no XAMPP).

### Prerequisites
- Homebrew (`/opt/homebrew`)
- Docker Desktop

### One-time setup
```bash
# Web server + PHP (PHP is compiled as an Apache module)
brew install httpd php

# Start Apache (serves this repo directly on http://localhost:8080)
brew services start httpd
```

Apache is configured in `/opt/homebrew/etc/httpd/httpd.conf`:
- `DocumentRoot "/Users/hibiscus/event-hub"` (edits are live, no deploy step)
- `Listen 8080`
- `LoadModule php_module .../libphp.so` and a `<FilesMatch \.php$>` handler

### Database (Docker)
```bash
cd event-hub
docker compose up -d      # starts MySQL 8.4 on localhost:3306, auto-imports init_db.sql on first run
```
The `root` password and DB name match `config/database_local.php`
(`DB_HOST=127.0.0.1`, `DB_NAME=local_event_hub`, `DB_USER=root`, `DB_PASS=pass`).

> Note: `DB_HOST` must be `127.0.0.1`, not `localhost` — PHP resolves `localhost`
> to a Unix socket, which doesn't exist for Docker's published port.

### Reset the database
```bash
docker compose down -v    # removes the data volume
docker compose up -d      # recreates it and re-imports init_db.sql
```

### Open the app
- http://localhost:8080/homePage/homepage.php
