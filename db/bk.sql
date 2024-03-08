-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: Feb 09, 2024 at 05:22 PM
-- Server version: 10.4.32-MariaDB
-- PHP Version: 8.2.12

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Database: `jaceedb`
--
USE `jaceedb`;

--
-- Dumping data for table `developers`
--

INSERT INTO `developers` (`devID`, `devName`) VALUES
(12, 'Bethesda Game Studios'),
(3, 'Frontier Developments'),
(5, 'Game Freak'),
(7, 'Genius Sonority'),
(9, 'Ghost Games'),
(11, 'Hazelight Studios'),
(4, 'ILCA'),
(10, 'Mojang'),
(1, 'Nintendo'),
(2, 'Pocket Pair'),
(6, 'Toys for Bob'),
(8, 'Zeekerss');

--
-- Dumping data for table `games`
--

INSERT INTO `games` (`gameID`, `gameName`, `gameRelease`, `devID`, `steamgridID`, `imageLink`) VALUES
(1, 'Mario Kart 8 Deluxe', '2017-04-27', 1, 1234, 'https://img-eshop.cdn.nintendo.net/i/55e0720b1a064e28b5df173d30b343ecd91287dc86942f833ffbf979a7a66ec9.jpg'),
(2, 'Palworld', '2024-01-24', 2, 1234, 'https://cdn2.steamgriddb.com/thumb/c7442a22ca020c53aefeb2e1fbec4bc2.jpg'),
(3, 'Super Smash Bros. Ultimate', '2018-12-07', 1, 1234, 'https://cdn2.steamgriddb.com/thumb/8d9ab8e22d8536b143157bd811dd7232.jpg'),
(4, 'Splatoon 3', '2022-09-09', 1, 1234, 'https://cdn2.steamgriddb.com/thumb/1ed482c069ef23636e7bf137e14846e2.jpg'),
(5, 'Elite Dangerous', '2014-12-16', 3, 1234, 'https://cdn2.steamgriddb.com/thumb/26b48e46bf3f22ac4a843de3ef54bd78.jpg'),
(6, 'The Legend of Zelda: Breath of the Wild', '2017-03-03', 1, 1234, 'https://cdn2.steamgriddb.com/thumb/a34b4983ea71864d1c630b7b138444a2.jpg'),
(7, 'The Legend of Zelda: Tears of the Kingdom', '2023-05-12', 1, 1234, 'https://cdn2.steamgriddb.com/thumb/f1ae16b005517a574e5e359ac3b6fd8a.jpg'),
(8, 'Pokémon Brilliant Diamond', '2021-11-19', 4, 1234, 'https://cdn2.steamgriddb.com/thumb/7ae9b7ad43cf1d69b895041b2bb68d43.jpg'),
(9, 'Pokémon Scarlet', '2022-11-18', 5, 1234, 'https://sm.ign.com/ign_nordic/cover/p/pokemon-sc/pokemon-scarlet_fqfe.jpg'),
(10, 'Spyro Reignited Trilogy', '2018-11-13', 6, 1234, 'https://cdn2.steamgriddb.com/thumb/f036e60d7c7777393aa8713b501a3530.png'),
(11, 'Pokémon Battle Revolution', '2006-12-14', 7, 1234, 'https://cdn2.steamgriddb.com/thumb/af9d8b8c1db81c9fc16c6e899386cba5.png'),
(12, 'Lethal Company', '2023-10-23', 8, 1234, 'https://cdn2.steamgriddb.com/thumb/a8fba7232b85ea00244c2de3c29135a7.jpg'),
(13, 'Need for Speed Rivals', '2013-11-15', 9, 1234, 'https://cdn2.steamgriddb.com/thumb/370c95248c885439aba89c390b949c41.jpg'),
(14, 'Minecraft', '2011-08-16', 10, 1234, 'https://cdn2.steamgriddb.com/thumb/a488ceea8a1f5aed84cb4cda6aaffa89.jpg'),
(15, 'It Takes Two', '2021-03-26', 11, 1234, 'https://cdn2.steamgriddb.com/thumb/6d6ed2ea29b0d5ce5338b24ec32b871f.jpg'),
(16, 'Starfield', '2023-09-06', 12, 1234, 'https://store-images.s-microsoft.com/image/apps.1647.14419706211314168.b0f9d237-3727-4f27-a56e-60574c628757.ef6e6b86-ef7d-4357-9dbf-1d0766fdc93a');

--
-- Dumping data for table `game_platform_link`
--

INSERT INTO `game_platform_link` (`game_platformID`, `gameID`, `platformID`, `releaseDate`, `storeLink`) VALUES
(1, 1, 14, NULL, 'https://mariokart8.nintendo.com/'),
(2, 2, 1, NULL, 'https://store.steampowered.com/app/1623730/Palworld/'),
(3, 2, 11, NULL, 'https://www.xbox.com/en-US/games/store/palworld-game-preview/9NKV34XDW014'),
(4, 2, 12, NULL, 'https://www.xbox.com/en-US/games/store/palworld-game-preview/9NKV34XDW014'),
(5, 3, 14, NULL, 'https://www.smashbros.com/en_US/'),
(6, 4, 14, NULL, 'https://splatoon.nintendo.com/'),
(7, 5, 1, '2015-04-02', 'https://store.steampowered.com/app/359320/Elite_Dangerous/'),
(8, 5, 7, '2017-06-27', 'https://store.playstation.com/de-de/product/EP2377-CUSA05308_00-EDBASEGAME000000'),
(9, 5, 8, '2017-06-27', 'https://store.playstation.com/de-de/product/EP2377-CUSA05308_00-EDBASEGAME000000'),
(10, 5, 11, '2015-10-06', 'https://www.xbox.com/en-US/games/store/elite-dangerous-standard-edition/C3LW50BQJ878'),
(11, 5, 12, '2015-10-06', 'https://www.xbox.com/en-US/games/store/elite-dangerous-standard-edition/C3LW50BQJ878'),
(12, 6, 14, NULL, 'https://zelda.nintendo.com/breath-of-the-wild/'),
(13, 7, 14, NULL, 'https://zelda.nintendo.com/tears-of-the-kingdom/'),
(14, 8, 14, NULL, 'https://diamondpearl.pokemon.com/en-us/'),
(15, 9, 14, NULL, 'https://scarletviolet.pokemon.com/en-us/'),
(16, 10, 14, NULL, 'https://www.nintendo.com/us/store/products/spyro-reignited-trilogy-switch/'),
(17, 10, 1, NULL, 'https://store.steampowered.com/app/996580/Spyro_Reignited_Trilogy/'),
(18, 10, 7, NULL, 'https://store.playstation.com/en-us/product/UP0002-CUSA12125_00-SPYROTRILOGY0001'),
(19, 10, 8, NULL, 'https://store.playstation.com/en-us/product/UP0002-CUSA12125_00-SPYROTRILOGY0001'),
(20, 10, 11, NULL, 'https://www.xbox.com/en-US/games/store/spyro-reignited-trilogy/BWHFZNSL0PB5'),
(21, 10, 12, NULL, 'https://www.xbox.com/en-US/games/store/spyro-reignited-trilogy/BWHFZNSL0PB5'),
(22, 11, 16, NULL, ''),
(23, 12, 1, NULL, 'https://store.steampowered.com/app/1966720/Lethal_Company/'),
(24, 13, 1, '2013-11-19', 'https://store.steampowered.com/app/1262600/Need_for_Speed_Rivals/'),
(25, 13, 4, '2013-11-19', 'https://www.ea.com/en-gb/games/need-for-speed/need-for-speed-rivals'),
(26, 13, 7, '2013-11-15', 'https://store.playstation.com/en-us/product/UP0006-CUSA00113_00-NFS14000PS4000NA'),
(27, 13, 8, '2013-11-15', 'https://store.playstation.com/en-us/product/UP0006-CUSA00113_00-NFS14000PS4000NA'),
(28, 13, 9, '2013-11-19', NULL),
(29, 13, 11, '2013-11-22', 'https://www.xbox.com/en-US/games/store/need-for-speed-rivals/C3VM0TMWNZ5M'),
(30, 13, 12, '2013-11-22', 'https://www.xbox.com/en-US/games/store/need-for-speed-rivals/C3VM0TMWNZ5M'),
(31, 13, 13, '2013-11-19', 'https://marketplace.xbox.com/en-US/Product/Need-for-Speed-Rivals/66acd000-77fe-1000-9115-d802454109c6?cid=majornelson'),
(38, 5, 2, '2015-04-02', 'https://store.epicgames.com/en-US/p/elite-dangerous'),
(40, 14, 14, NULL, 'https://www.nintendo.com/us/store/products/minecraft-switch/'),
(41, 14, 3, NULL, 'https://www.xbox.com/en-us/games/store/minecraft-fur-windows/9nblggh2jhxj?rtc=2&activetab=pivot:overviewtab'),
(42, 14, 7, NULL, 'https://www.playstation.com/en-us/games/minecraft/'),
(43, 14, 8, NULL, 'https://www.playstation.com/en-us/games/minecraft/'),
(44, 14, 11, NULL, 'https://www.xbox.com/en-us/games/store/minecraft/9mvxmvt8zkwc'),
(45, 14, 12, NULL, 'https://www.xbox.com/en-us/games/store/minecraft/9mvxmvt8zkwc'),
(46, 15, 14, NULL, 'https://www.ea.com/en-gb/games/it-takes-two/buy/nintendo-switch'),
(47, 15, 1, NULL, 'https://store.steampowered.com/app/1426210/It_Takes_Two/'),
(48, 15, 4, NULL, 'https://www.ea.com/en-gb/games/it-takes-two'),
(49, 15, 7, NULL, 'https://lnk.to/it-takes-two-sony-ps5'),
(50, 15, 8, NULL, 'https://lnk.to/it-takes-two-sony-ps5'),
(51, 15, 11, NULL, 'https://www.microsoft.com/p/It-Takes-Two/9NKJ0VZQ4N0L'),
(52, 15, 12, NULL, 'https://www.microsoft.com/p/It-Takes-Two/9NKJ0VZQ4N0L'),
(53, 16, 1, NULL, NULL),
(54, 16, 3, NULL, NULL),
(55, 16, 11, NULL, NULL);

--
-- Dumping data for table `game_platform_player_link`
--

INSERT INTO `game_platform_player_link` (`game_platform_playerID`, `game_platformID`, `modeID`, `minPlayers`, `maxPlayers`) VALUES
(1, 1, 1, 0, 0),
(2, 1, 2, 2, 4),
(3, 1, 3, 2, 12),
(4, 1, 4, 2, 8),
(5, 1, 5, 2, 12),
(6, 2, 1, 0, 0),
(7, 2, 5, 1, 32),
(8, 3, 1, 0, 0),
(9, 3, 5, 1, 4),
(10, 4, 1, 0, 0),
(11, 4, 5, 1, 4),
(12, 5, 1, 0, 0),
(13, 5, 2, 2, 8),
(14, 5, 4, 2, 8),
(15, 5, 5, 2, 8),
(16, 6, 1, 0, 0),
(17, 6, 3, 2, 10),
(18, 6, 4, 2, 8),
(19, 6, 5, 2, 10),
(20, 7, 1, 0, 0),
(21, 7, 5, 1, 150),
(22, 7, 6, 1, 150),
(23, 8, 1, 0, 0),
(24, 8, 5, 1, 32),
(25, 8, 6, 1, 32),
(26, 9, 1, 0, 0),
(27, 9, 5, 1, 32),
(28, 9, 6, 1, 32),
(29, 10, 1, 0, 0),
(30, 10, 5, 1, 32),
(31, 10, 6, 1, 32),
(32, 11, 1, 0, 0),
(33, 11, 5, 1, 32),
(34, 11, 6, 1, 32),
(35, 12, 1, 0, 0),
(36, 13, 1, 0, 0),
(37, 14, 1, 0, 0),
(38, 14, 4, 1, 8),
(39, 14, 5, 1, 8),
(40, 15, 1, 0, 0),
(41, 15, 4, 2, 4),
(42, 15, 5, 2, 4),
(43, 16, 1, 0, 0),
(44, 17, 1, 0, 0),
(45, 18, 1, 0, 0),
(46, 19, 1, 0, 0),
(47, 20, 1, 0, 0),
(48, 21, 1, 0, 0),
(49, 22, 1, 0, 0),
(50, 22, 2, 1, 2),
(51, 23, 1, 0, 0),
(52, 23, 5, 1, 4),
(53, 24, 1, 0, 0),
(54, 24, 5, 2, 6),
(55, 25, 1, 0, 0),
(56, 25, 5, 2, 6),
(57, 26, 1, 0, 0),
(58, 26, 5, 2, 6),
(59, 27, 1, 0, 0),
(60, 27, 5, 2, 6),
(61, 28, 1, 0, 0),
(62, 28, 5, 2, 6),
(63, 29, 1, 0, 0),
(64, 29, 5, 2, 6),
(65, 30, 1, 0, 0),
(66, 30, 5, 2, 6),
(67, 31, 1, 0, 0),
(68, 31, 5, 2, 6),
(71, 38, 6, 1, 150),
(72, 38, 5, 1, 150),
(73, 38, 1, 0, 0),
(75, 40, 1, 0, 0),
(76, 40, 2, 2, 4),
(77, 40, 4, 2, 8),
(78, 40, 5, 2, 200),
(79, 41, 1, 0, 0),
(80, 41, 3, 2, 8),
(81, 41, 5, 2, 200),
(82, 42, 1, 0, 0),
(83, 42, 2, 2, 4),
(84, 42, 3, 2, 8),
(85, 42, 5, 2, 200),
(86, 43, 1, 0, 0),
(87, 43, 2, 2, 4),
(88, 43, 3, 2, 8),
(89, 43, 5, 2, 200),
(90, 44, 1, 0, 0),
(91, 44, 2, 2, 4),
(92, 44, 3, 2, 8),
(93, 44, 5, 2, 200),
(94, 45, 1, 0, 0),
(95, 45, 2, 2, 4),
(96, 45, 3, 2, 8),
(97, 45, 5, 2, 200),
(98, 46, 2, 2, 2),
(99, 46, 4, 2, 2),
(100, 46, 5, 2, 2),
(101, 47, 2, 2, 2),
(102, 47, 5, 2, 2),
(103, 48, 2, 2, 2),
(104, 48, 5, 2, 2),
(105, 49, 2, 2, 2),
(106, 49, 5, 2, 2),
(107, 50, 2, 2, 2),
(108, 50, 5, 2, 2),
(109, 51, 2, 2, 2),
(110, 51, 5, 2, 2),
(111, 52, 2, 2, 2),
(112, 52, 5, 2, 2),
(113, 53, 1, 0, 0),
(114, 54, 1, 0, 0),
(115, 55, 1, 0, 0);

COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
