SET
SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
SET
time_zone = "+00:00";

/*!40101 SET @OLD_CHARACTER_SET_CLIENT = @@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS = @@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION = @@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

-- create database if not exists
CREATE
DATABASE IF NOT EXISTS `jaceedb` DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE
`jaceedb`;

-- create table developers
CREATE TABLE `developers`
(
    `devID`   int(11) NOT NULL AUTO_INCREMENT, -- primary key
    `devName` varchar(30) NOT NULL,            -- name of development studio

    PRIMARY KEY (`devID`),
    UNIQUE (`devName`) -- every developer should have a unique name (no duplicates)
) ENGINE = InnoDB
  DEFAULT CHARSET = utf8mb4
  COLLATE = utf8mb4_unicode_ci;

-- create table games
CREATE TABLE `games`
(
    `gameID`      int(11) NOT NULL AUTO_INCREMENT, -- primary key
    `gameName`    varchar(176) NOT NULL,           -- name of game
    `gameRelease` date         NOT NULL,           -- release date of game
    `devID`       int(11) NOT NULL,                -- foreign key to developers table
    `steamgridID` int(12) NOT NULL,                -- SteamGridDB ID --TODO: implement SteamGridDB feature
    `imageLink` varchar(150) NOT NULL,             -- Link to image of game

    PRIMARY KEY (`gameID`),
    -- UNIQUE (`gameName`), -- every game should have a unique name / some games have the same name (e.g. Need for Speed)
    FOREIGN KEY (`devID`) references `developers` (`devID`) ON DELETE CASCADE -- foreign key to developers table
) ENGINE = InnoDB
  DEFAULT CHARSET = utf8mb4
  COLLATE = utf8mb4_unicode_ci;

-- create table platforms
CREATE TABLE `platforms`
(
    `platformID`   int(11) NOT NULL AUTO_INCREMENT, -- primary key
    `platformName` varchar(20) NOT NULL,            -- name of platform (e.g. Steam, Xbox, Playstation)
    `platformCategory` varchar(20) NOT NULL,            -- name of platform category (e.g. PC, Xbox, Playstation)

    PRIMARY KEY (`platformID`),
    UNIQUE (`platformName`) -- every platform should have a unique name (no duplicates)
) ENGINE = InnoDB
  DEFAULT CHARSET = utf8mb4
  COLLATE = utf8mb4_unicode_ci;

-- create table playermodes
CREATE TABLE `playermodes`
(
    `modeID`   int(11) NOT NULL AUTO_INCREMENT, -- primary key
    `modeName` varchar(30) NOT NULL,            -- name of player mode (e.g. single player, splitscreen, online multiplayer)
    `modeShort` varchar(10) NOT NULL,           -- short name

    PRIMARY KEY (`modeID`),
    UNIQUE (`modeName`), -- every player mode should have a unique name (no duplicates)
    UNIQUE (`modeShort`) -- every player mode should have a unique name (no duplicates)
) ENGINE = InnoDB
  DEFAULT CHARSET = utf8mb4
  COLLATE = utf8mb4_unicode_ci;

-- create table game_platform_link
-- links games to platforms
CREATE TABLE game_platform_link
(
    `game_platformID` int(11) NOT NULL AUTO_INCREMENT, -- primary key
    `gameID`          int(11) NOT NULL,                -- foreign key to games table
    `platformID`      int(11) NOT NULL,                -- foreign key to platforms table
    `releaseDate`     date NULL,                       -- release date of game on platform, can be Null if released on platforms at same time
    `storeLink`       varchar(150) NULL,           -- link to store page for game on platform

    PRIMARY KEY (`game_platformID`),
    FOREIGN KEY (`gameID`) REFERENCES `games` (`gameID`) ON DELETE CASCADE,
    FOREIGN KEY (`platformID`) REFERENCES `platforms` (`platformID`)

) ENGINE = InnoDB
  DEFAULT CHARSET = utf8mb4
  COLLATE = utf8mb4_unicode_ci;

-- create table game_platform_player_link
-- links games to platforms to player modes
CREATE TABLE game_platform_player_link
(
    `game_platform_playerID` int(11) NOT NULL AUTO_INCREMENT, -- primary key
    `game_platformID` int(11) NOT NULL,                                                -- foreign key to game_platform_link table
    `modeID`          int(11) NOT NULL,                                                -- foreign key to playermodes table
    `minPlayers`      int(11) NOT NULL CHECK ( `minPlayers` >= 0 ),                    -- minimum number of players for mode
    `maxPlayers`      int(11) NOT NULL CHECK ( `maxPlayers` >= `minPlayers` ),         -- maximum number of players for mode

    PRIMARY KEY (`game_platform_playerID`),
    FOREIGN KEY (`game_platformID`) REFERENCES game_platform_link (`game_platformID`) ON DELETE CASCADE, -- foreign key to game_platform_link table
    FOREIGN KEY (`modeID`) REFERENCES `playermodes` (`modeID`)                         -- foreign key to playermodes table
) ENGINE = InnoDB
  DEFAULT CHARSET = utf8mb4
  COLLATE = utf8mb4_unicode_ci;


-- database optimization

ALTER TABLE games ADD FULLTEXT(gameName);
ALTER TABLE developers ADD FULLTEXT(devName);

CREATE INDEX idx_devID ON games(devID);
CREATE INDEX idx_platformID ON game_platform_link(platformID);
CREATE INDEX idx_gameID ON game_platform_link(gameID);
CREATE INDEX idx_game_platformID ON game_platform_player_link(game_platformID);
CREATE INDEX idx_modeID ON game_platform_player_link(modeID);

/*!40101 SET CHARACTER_SET_CLIENT = @OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS = @OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION = @OLD_COLLATION_CONNECTION */;
