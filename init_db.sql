-- SQL script to initialize the local database for the EventHub project

-- Create the database
CREATE DATABASE IF NOT EXISTS local_event_hub;
USE local_event_hub;

-- Table structure for table `Accounts`
CREATE TABLE IF NOT EXISTS `Accounts` (
  `UserID` int(11) NOT NULL AUTO_INCREMENT,
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
  `failed_attempts` int(11) DEFAULT 0,
  `lockout_time` datetime DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`UserID`),
  UNIQUE KEY `Email` (`Email`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Table structure for table `Events`
CREATE TABLE IF NOT EXISTS `Events` (
  `EventID` int(11) NOT NULL AUTO_INCREMENT,
  `EventName` varchar(255) NOT NULL,
  `EventDate` date NOT NULL,
  `EventWhen` varchar(255) DEFAULT NULL,
  `EventAddress` varchar(255) DEFAULT NULL,
  `Link` varchar(255) NOT NULL,
  `EventImage` varchar(255) DEFAULT NULL,
  PRIMARY KEY (`EventID`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Table structure for table `SavedEvents`
CREATE TABLE IF NOT EXISTS `SavedEvents` (
  `EventID` int(11) NOT NULL,
  `UserID` int(11) NOT NULL,
  PRIMARY KEY (`EventID`,`UserID`),
  FOREIGN KEY (`EventID`) REFERENCES `Events` (`EventID`) ON DELETE CASCADE,
  FOREIGN KEY (`UserID`) REFERENCES `Accounts` (`UserID`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Table structure for table `DiscussionThreads`
CREATE TABLE IF NOT EXISTS `DiscussionThreads` (
  `ThreadID` int(11) NOT NULL AUTO_INCREMENT,
  `SubjectName` varchar(255) NOT NULL,
  `InitialPost` text NOT NULL,
  `UserID` int(11) NOT NULL,
  `Link` varchar(255) NOT NULL,
  `CreatedDate` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`ThreadID`),
  FOREIGN KEY (`UserID`) REFERENCES `Accounts` (`UserID`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Table structure for table `DiscussionPosts`
CREATE TABLE IF NOT EXISTS `DiscussionPosts` (
  `PostID` int(11) NOT NULL AUTO_INCREMENT,
  `ThreadID` int(11) NOT NULL,
  `UserID` int(11) NOT NULL,
  `ParentPostID` int(11) DEFAULT NULL,
  `Reply` text NOT NULL,
  `CreatedDate` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `IsDeleted` tinyint(1) DEFAULT 0,
  PRIMARY KEY (`PostID`),
  FOREIGN KEY (`ThreadID`) REFERENCES `DiscussionThreads` (`ThreadID`) ON DELETE CASCADE,
  FOREIGN KEY (`UserID`) REFERENCES `Accounts` (`UserID`) ON DELETE CASCADE,
  FOREIGN KEY (`ParentPostID`) REFERENCES `DiscussionPosts` (`PostID`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Table structure for table `EventPosts`
CREATE TABLE IF NOT EXISTS `EventPosts` (
  `PostID` int(11) NOT NULL AUTO_INCREMENT,
  `EventID` int(11) NOT NULL,
  `UserID` int(11) NOT NULL,
  `Reply` text NOT NULL,
  `CreatedDate` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`PostID`),
  FOREIGN KEY (`EventID`) REFERENCES `Events` (`EventID`) ON DELETE CASCADE,
  FOREIGN KEY (`UserID`) REFERENCES `Accounts` (`UserID`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Table structure for table `Logins`
CREATE TABLE IF NOT EXISTS `Logins` (
  `LoginID` int(11) NOT NULL AUTO_INCREMENT,
  `UserID` int(11) NOT NULL,
  `login_date` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `ip_address` varchar(45) DEFAULT NULL,
  PRIMARY KEY (`LoginID`),
  FOREIGN KEY (`UserID`) REFERENCES `Accounts` (`UserID`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;