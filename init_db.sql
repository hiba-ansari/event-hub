-- =====================================================================
-- SQL script to initialize the local database for the EventHub project
-- This schema is a code-grounded superset: it includes every column and
-- table referenced by the PHP pages (homePage, eventDetails, discussion,
-- calendar, profilePage). Safe to run repeatedly (IF NOT EXISTS / IGNORE).
-- =====================================================================

CREATE DATABASE IF NOT EXISTS local_event_hub;
USE local_event_hub;

-- ---------------------------------------------------------------------
-- Accounts
--   login.php reads: Password, isArchived, isLocked, failed_attempts,
--                    lockout_time, IsAdmin, UserID, Email
--   admin.php updates: isLocked, and reads archived_at
--   profile pages insert/update: UserName, PhoneNo, Location, Hobbies,
--                    ProfileImage, Theme
-- ---------------------------------------------------------------------
CREATE TABLE IF NOT EXISTS `Accounts` (
  `UserID` int NOT NULL AUTO_INCREMENT,
  `UserName` varchar(255) DEFAULT NULL,
  `Email` varchar(255) NOT NULL,
  `Password` varchar(255) NOT NULL,
  `PhoneNo` varchar(20) DEFAULT NULL,
  `Location` varchar(255) DEFAULT NULL,
  `Hobbies` text,
  `IsAdmin` tinyint(1) DEFAULT 0,
  `ProfileImage` varchar(255) DEFAULT NULL,
  `Theme` varchar(50) DEFAULT 'default',
  `IsLocked` tinyint(1) DEFAULT 0,
  `isArchived` tinyint(1) DEFAULT 0,
  `archived_at` date DEFAULT NULL,
  `failed_attempts` int DEFAULT 0,
  `lockout_time` datetime DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`UserID`),
  UNIQUE KEY `Email` (`Email`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- ---------------------------------------------------------------------
-- Events
--   Superset of BOTH write paths in the code:
--     logged-in.php  INSERT (EventName, EventDate, EventWhen, EventAddress, Link, EventImage)
--     logged-out.php INSERT (Title, Description, StartDate, EndDate, Address, Link)
--   Reads: details.php -> Price, EventName; calendar -> EventName/EventDate/...
--   All non-key columns are nullable so either insert path succeeds.
-- ---------------------------------------------------------------------
CREATE TABLE IF NOT EXISTS `Events` (
  `EventID` int NOT NULL AUTO_INCREMENT,
  `EventName` varchar(255) DEFAULT NULL,
  `EventDate` date DEFAULT NULL,
  `EventWhen` varchar(255) DEFAULT NULL,
  `EventAddress` varchar(255) DEFAULT NULL,
  `Link` varchar(500) DEFAULT NULL,
  `EventImage` varchar(255) DEFAULT NULL,
  `Title` varchar(255) DEFAULT NULL,
  `Description` text,
  `StartDate` date DEFAULT NULL,
  `EndDate` date DEFAULT NULL,
  `Address` varchar(255) DEFAULT NULL,
  `Price` decimal(10,2) DEFAULT NULL,
  PRIMARY KEY (`EventID`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- ---------------------------------------------------------------------
-- Prices  (logged-out.php weekend query JOINs Prices on EventID)
-- ---------------------------------------------------------------------
CREATE TABLE IF NOT EXISTS `Prices` (
  `PriceID` int NOT NULL AUTO_INCREMENT,
  `EventID` int NOT NULL,
  `PriceValue` decimal(10,2) NOT NULL,
  PRIMARY KEY (`PriceID`),
  KEY `Prices_Event_idx` (`EventID`),
  FOREIGN KEY (`EventID`) REFERENCES `Events` (`EventID`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- ---------------------------------------------------------------------
-- SavedEvents  (calendar / details save-for-later)
-- ---------------------------------------------------------------------
CREATE TABLE IF NOT EXISTS `SavedEvents` (
  `EventID` int NOT NULL,
  `UserID` int NOT NULL,
  PRIMARY KEY (`EventID`, `UserID`),
  FOREIGN KEY (`EventID`) REFERENCES `Events` (`EventID`) ON DELETE CASCADE,
  FOREIGN KEY (`UserID`) REFERENCES `Accounts` (`UserID`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- ---------------------------------------------------------------------
-- ShoppingCart  (shopping-cart.php JOINs Events for Price/EventName/EventImage)
-- ---------------------------------------------------------------------
CREATE TABLE IF NOT EXISTS `ShoppingCart` (
  `CartID` int NOT NULL AUTO_INCREMENT,
  `EventID` int NOT NULL,
  `UserID` int NOT NULL,
  `NumTickets` int DEFAULT 1,
  PRIMARY KEY (`CartID`),
  FOREIGN KEY (`EventID`) REFERENCES `Events` (`EventID`) ON DELETE CASCADE,
  FOREIGN KEY (`UserID`) REFERENCES `Accounts` (`UserID`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- ---------------------------------------------------------------------
-- DiscussionThreads  (discussion.php reads CreatedAt)
-- ---------------------------------------------------------------------
CREATE TABLE IF NOT EXISTS `DiscussionThreads` (
  `ThreadID` int NOT NULL AUTO_INCREMENT,
  `SubjectName` varchar(255) NOT NULL,
  `InitialPost` text NOT NULL,
  `UserID` int NOT NULL,
  `Link` varchar(500) NOT NULL,
  `CreatedDate` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `CreatedAt` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`ThreadID`),
  FOREIGN KEY (`UserID`) REFERENCES `Accounts` (`UserID`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- ---------------------------------------------------------------------
-- DiscussionPosts  (template.php reads Img, CreatedAt)
-- ---------------------------------------------------------------------
CREATE TABLE IF NOT EXISTS `DiscussionPosts` (
  `PostID` int NOT NULL AUTO_INCREMENT,
  `ThreadID` int NOT NULL,
  `UserID` int NOT NULL,
  `ParentPostID` int DEFAULT NULL,
  `Reply` text NOT NULL,
  `Img` varchar(255) DEFAULT NULL,
  `CreatedDate` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `CreatedAt` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `IsDeleted` tinyint(1) DEFAULT 0,
  PRIMARY KEY (`PostID`),
  FOREIGN KEY (`ThreadID`) REFERENCES `DiscussionThreads` (`ThreadID`) ON DELETE CASCADE,
  FOREIGN KEY (`UserID`) REFERENCES `Accounts` (`UserID`) ON DELETE CASCADE,
  FOREIGN KEY (`ParentPostID`) REFERENCES `DiscussionPosts` (`PostID`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- ---------------------------------------------------------------------
-- EventPosts  (details.php reads Image, CreatedAt)
-- ---------------------------------------------------------------------
CREATE TABLE IF NOT EXISTS `EventPosts` (
  `PostID` int NOT NULL AUTO_INCREMENT,
  `EventID` int NOT NULL,
  `UserID` int NOT NULL,
  `Reply` text NOT NULL,
  `Image` varchar(255) DEFAULT NULL,
  `CreatedAt` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`PostID`),
  FOREIGN KEY (`EventID`) REFERENCES `Events` (`EventID`) ON DELETE CASCADE,
  FOREIGN KEY (`UserID`) REFERENCES `Accounts` (`UserID`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- ---------------------------------------------------------------------
-- Logins  (admin.php activity chart)
-- ---------------------------------------------------------------------
CREATE TABLE IF NOT EXISTS `Logins` (
  `LoginID` int NOT NULL AUTO_INCREMENT,
  `UserID` int NOT NULL,
  `login_date` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `ip_address` varchar(45) DEFAULT NULL,
  PRIMARY KEY (`LoginID`),
  FOREIGN KEY (`UserID`) REFERENCES `Accounts` (`UserID`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- =====================================================================
-- Seed data
-- =====================================================================

-- Two demo accounts from the README (passwords are bcrypt hashes produced
-- by PHP password_hash(); verify with password_verify()).
--   Admin1@gmail.com  / admin1
--   robert@email.com  / qwerty12345
INSERT IGNORE INTO `Accounts`
  (`UserID`, `UserName`, `Email`, `Password`, `PhoneNo`, `Location`, `Hobbies`, `IsAdmin`)
VALUES
  (1, 'Admin', 'Admin1@gmail.com', '$2y$12$zXUV33WJ4NMUVFLvMo6qh.yIud4e3t44Wr/chHiieBC1lRbQ9L5mm', '0000000000', 'Melbourne', 'music, food, travel', 1),
  (2, 'Robert', 'robert@email.com', '$2y$12$qqONjqsgdO7GqXRtDZAEhev9SRwaCeiCTDAEX8QRS.B8sYIDJaGfm', '0123456789', 'Sydney', 'sports, movies', 0);

-- A few events populated with BOTH column sets so every read page shows data.
-- Dates are relative to NOW() so the "this weekend" queries can match.
INSERT IGNORE INTO `Events`
  (`EventID`, `EventName`, `EventDate`, `EventWhen`, `EventAddress`, `Link`, `EventImage`,
   `Title`, `Description`, `StartDate`, `EndDate`, `Address`, `Price`)
VALUES
  (1, 'City Music Festival', DATE_ADD(CURDATE(), INTERVAL 1 DAY), 'Sat 2:00 PM', 'Federation Square, Melbourne', 'https://example.com/festival', 'images/event1.jpg',
      'City Music Festival', 'Live music across the CBD.', DATE_ADD(CURDATE(), INTERVAL 1 DAY), DATE_ADD(CURDATE(), INTERVAL 1 DAY), 'Federation Square, Melbourne', 0.00),
  (2, 'Weekend Art Market', DATE_ADD(CURDATE(), INTERVAL 2 DAY), 'Sun 10:00 AM', 'Southbank, Melbourne', 'https://example.com/artmarket', 'images/event2.jpg',
      'Weekend Art Market', 'Local artists and handmade goods.', DATE_ADD(CURDATE(), INTERVAL 2 DAY), DATE_ADD(CURDATE(), INTERVAL 2 DAY), 'Southbank, Melbourne', 0.00),
  (3, 'Tech Meetup', DATE_ADD(CURDATE(), INTERVAL 3 DAY), 'Mon 6:30 PM', 'Melbourne Central', 'https://example.com/meetup', 'images/event3.jpg',
      'Tech Meetup', 'Monthly developer networking night.', DATE_ADD(CURDATE(), INTERVAL 3 DAY), DATE_ADD(CURDATE(), INTERVAL 3 DAY), 'Melbourne Central', 15.00);

-- Matching price rows (weekend free-events query JOINs Prices and filters PriceValue <= 0)
INSERT IGNORE INTO `Prices` (`PriceID`, `EventID`, `PriceValue`) VALUES
  (1, 1, 0.00),
  (2, 2, 0.00),
  (3, 3, 15.00);
