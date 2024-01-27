SET
SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
SET
time_zone = "+00:00";

/*!40101 SET @OLD_CHARACTER_SET_CLIENT = @@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS = @@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION = @@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

-- create database if not exists
USE
`jaceedb`;

INSERT INTO `platforms` (`platformID`, `platformName`, `platformCategory`)
VALUES (1, 'Steam', 'PC'),
       (2, 'Epic Games', 'PC'),
       (3, 'GOG', 'PC'),
       (4, 'Origin', 'PC'),
       (5, 'Uplay', 'PC'),
       (6, 'Battle.net', 'PC'),
       (7, 'Playstation 5', 'Playstation'),
       (8, 'Playstation 4', 'Playstation'),
       (9, 'Playstation 3', 'Playstation'),
       (10, 'Playstation 2', 'Playstation'),
       (11, 'Xbox Series X/S', 'Xbox'),
       (12, 'Xbox One X/S', 'Xbox'),
       (13, 'Xbox 360', 'Xbox'),
       (14, 'Nintendo Switch', 'Nintendo'),
       (15, 'Nintendo Wii U', 'Nintendo'),
       (16, 'Nintendo Wii', 'Nintendo'),
       (17, 'Nintendo 3DS/2DS', 'Nintendo'),
       (18, 'Nintendo DS/DSi', 'Nintendo');

INSERT INTO `playermodes` (`modeID`, `modeName`)
VALUES (1, 'Singleplayer'), -- Singleplayer
       (2, 'Local Multiplayer'), -- Splitscreen / Shared Screen
       (3, 'Local LAN Play'), -- local multiplayer in the same network
       (4, 'Local Wireless Play'), -- nintendo specific multiplayer over direct wireless connection between devices
       (5, 'Online Multiplayer'), -- Online Multiplayer
       (6, 'Massively Multiplayer (MMO)'); -- MMO


/*!40101 SET CHARACTER_SET_CLIENT = @OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS = @OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION = @OLD_COLLATION_CONNECTION */;
