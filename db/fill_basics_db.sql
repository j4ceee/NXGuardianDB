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

INSERT INTO `platforms` (`platformID`, `platformName`)
VALUES (1, 'Steam'),
       (2, 'Epic Games'),
       (3, 'GOG'),
       (4, 'Playstation 5'),
       (5, 'Playstation 4'),
       (6, 'Playstation 3'),
       (7, 'Playstation 2'),
       (8, 'Xbox Series X/S'),
       (9, 'Xbox One X/S'),
       (10, 'Xbox 360'),
       (11, 'Nintendo Switch'),
       (12, 'Nintendo Wii U'),
       (13, 'Nintendo Wii'),
       (14, 'Nintendo 3DS/2DS'),
       (15, 'Nintendo DS/DSi');

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
