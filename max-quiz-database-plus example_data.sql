-- phpMyAdmin SQL Dump
-- version 5.2.0
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: Jan 02, 2024 at 04:13 PM
-- Server version: 10.4.28-MariaDB
-- PHP Version: 8.0.25

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Database: `max-quiz`
--
CREATE DATABASE IF NOT EXISTS `max-quiz` DEFAULT CHARACTER SET utf32 COLLATE utf32_general_ci;
USE `max-quiz`;

-- --------------------------------------------------------

--
-- Table structure for table `log`
--

CREATE TABLE `log` (
  `id` int(11) NOT NULL,
  `submission_id` int(11) NOT NULL,
  `quiz_id` int(11) DEFAULT NULL,
  `question_id` int(11) NOT NULL,
  `answer_no` int(11) NOT NULL,
  `correct` int(11) DEFAULT NULL,
  `timestamp` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf32 COLLATE=utf32_general_ci;

--
-- Dumping data for table `log`
--

INSERT INTO `log` (`id`, `submission_id`, `quiz_id`, `question_id`, `answer_no`, `correct`, `timestamp`) VALUES
(1, 15, NULL, 2, 4, NULL, '2023-12-28 22:59:13'),
(2, 16, NULL, 1, 4, NULL, '2023-12-29 13:33:05'),
(3, 16, NULL, 5, 2, NULL, '2023-12-29 13:33:13'),
(4, 16, NULL, 2, 4, NULL, '2023-12-29 13:33:17'),
(5, 17, NULL, 2, 4, NULL, '2023-12-29 13:34:51'),
(6, 17, NULL, 5, 1, NULL, '2023-12-29 14:03:17'),
(7, 19, NULL, 6, 2, NULL, '2023-12-30 09:21:39'),
(8, 19, NULL, 7, 4, NULL, '2023-12-30 09:21:50'),
(9, 19, NULL, 1, 2, NULL, '2023-12-30 09:22:45'),
(10, 20, NULL, 6, 2, NULL, '2023-12-30 09:30:14'),
(11, 20, NULL, 7, 4, NULL, '2023-12-30 09:30:19'),
(12, 20, NULL, 5, 2, NULL, '2023-12-30 09:30:23'),
(13, 20, NULL, 1, 2, NULL, '2023-12-30 09:30:27'),
(14, 20, NULL, 2, 4, NULL, '2023-12-30 09:30:50'),
(15, 21, NULL, 1, 4, NULL, '2023-12-30 09:34:13'),
(16, 18, NULL, 5, 2, NULL, '2023-12-31 17:21:55'),
(17, 22, 1, 6, 2, 1, '2023-12-31 20:00:22'),
(18, 22, 1, 5, 2, 1, '2023-12-31 20:00:26'),
(19, 22, 1, 2, 4, 1, '2023-12-31 20:00:53'),
(20, 22, 1, 7, 4, 1, '2023-12-31 20:00:58'),
(21, 22, 1, 1, 4, 1, '2023-12-31 20:01:17'),
(22, 24, 3, 12, 1, 1, '2024-01-01 13:29:32'),
(23, 24, 3, 13, 1, 1, '2024-01-01 13:29:35'),
(24, 26, 4, 49, 2, 1, '2024-01-02 14:18:40');

-- --------------------------------------------------------

--
-- Table structure for table `question`
--

CREATE TABLE `question` (
  `id` int(11) NOT NULL,
  `question` varchar(800) NOT NULL,
  `a1` varchar(300) NOT NULL,
  `a2` varchar(300) NOT NULL,
  `a3` varchar(300) DEFAULT NULL,
  `a4` varchar(300) DEFAULT NULL,
  `a5` varchar(300) DEFAULT NULL,
  `a6` varchar(300) DEFAULT NULL,
  `correct` int(11) NOT NULL,
  `label` varchar(100) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf32 COLLATE=utf32_general_ci;

--
-- Dumping data for table `question`
--

INSERT INTO `question` (`id`, `question`, `a1`, `a2`, `a3`, `a4`, `a5`, `a6`, `correct`, `label`) VALUES
(2, 'Deze vraag gaat over PHP.\r\n\r\n<pre>\r\nfunction bereken($a, $b) {\r\n  $c = $a - $b;\r\n  if ($c < 0) {\r\n    $c = 0;\r\n  }\r\n  return $c\r\n}\r\n\r\necho bereken(10,8);\r\n</pre>\r\nWat is de output van de code?', '-1', '0', '1', '2', '', '', 4, 'PHP'),
(5, '\r\nDeze vraag gaat over PHP.\r\n\r\n<pre>\r\n$a = 12;\r\n$b = $a - 2;\r\n$a = $a + $b;\r\n$b = 2;\r\n$a = $b + 2;\r\necho $a;\r\n</pre>\r\nWat is de output van de code?', '2', '4', '12', '10', '', '', 2, 'PHP'),
(6, 'Als je in PHP door een associatieve array heen wilt lopen en de waarden wilt afdrukken, wat heb je dan in ieder geval nodig?', 'Een functie', 'Een loop', 'Een if-then constructie', 'Een return', '', '', 2, 'PHP'),
(7, 'Als je programmeert in OOP, wat is dan een class?', 'Een beschrijving van een property (variabele).', 'Een beschrijving van een method (function).', 'Een object, bijvoorbeeld een \'hond\' met de naam \'Max\'', 'Een beschrijving van een object.', '', '', 4, 'PHP OOP'),
(10, 'Deze vraag gaat over PHP.\r\n\r\n<pre>\r\n$leeftijd = 12;\r\n$leerling = \"Alice\";\r\n$groep = 6;\r\n$leeftijd++;\r\n</pre>\r\nHoeveel variabelen worden in deze code gebruikt?\r\n\r\n', '4\r\n', '6\r\n', '3\r\n', '1\r\n', '', '', 3, 'PHP B4P1'),
(30, 'Deze vraag gaat over PHP\r\n\r\n<pre>\r\nfunction myFunction($a, $b) {\r\n  $c=$a+b;\r\n  return $c\r\n}\r\n</pre>\r\nWat is de input van deze functie?\r\n', '$c\r\n', '$a\r\n', '$b\r\n', '$a en $b\r\n', '$a, $b en $c\r\n', '', 4, 'PHP B4P1'),
(31, 'Deze vraag gaat over PHP\r\n\r\n<pre>\r\nfunction optellen($a) {\r\n  $som=0;\r\n  foreach($a as $element) {\r\n    $som=$som+1;\r\n  }\r\n  return $som;\r\n}\r\n</pre>\r\n\r\nWat doet deze functie?\r\n', 'Telt het aantal elementen in array $a\r\n', 'Telt alle getallen van array $a bij elkaar op\r\n', 'Berekent de som van het getal $a\r\n', 'Bepaalt het grootste getal van array $a\r\n', '', '', 2, 'PHP B4P1'),
(32, 'Deze vraag gaat over PHP\r\r<pre>\rforeach($a as $item) {\r  echo $item[2];\r}\r</pre>\r\rWat is $a voor een type variabele?\r\r', 'Integer\r', 'String\r', 'Array\r', 'Array van arrays\r', 'Associatieve array\r', 'Array van associatieve arrays\r', 4, 'PHP B4P1\r'),
(33, 'Deze vraag gaat over PHP\r\r<pre>\r$uitslagen=[\r [\'thuis\' => \'Feyenoord\', \'uit\' => \'FC Twente\', \'uitslag\'=> [1,2] ],\r [\'thuis\' => \'AZ\', \'uit\' => \'RKC Waalwijk\', \'uitslag\'=> [1,3] ],\r [\'thuis\' => \'PEC Zwolle\', \'uit\' => \'PSV\', \'uitslag\'=> [1,2] ],\r];\r</pre>\r\rMet welke code druk je af hoeveel doelpunten PSV tegen PEC Zwolle heeft gescoord?\r\r', 'echo $uitslagen[\'uitslag\'][1];\r', 'echo $uitslagen[2][\'uitslag\'][1];\r', 'echo $uitslagen[\'uit\'][\'uitslag\'][1];\r', 'echo $uitslagen->score->uit;\r', 'echo $uitslag[2][\'uit\'];\r', 'echo $uitslag[\'uit\'][1];\r', 2, 'PHP B4P1\r'),
(34, 'Wat is het antwoord op deze <b>vraag</b>?\r\n', '1\r\n', '2\r\n', '3\r\n', '4\r\n', '', '', 1, 'Test'),
(40, 'Wat is het antwoord op deze vraag?\r', '1\r', '2\r', '3\r', '4\r', NULL, NULL, 1, 'Test\r'),
(41, 'Wat is het antwoord op deze vraag?\r', '1\r', '2\r', '3\r', '4\r', NULL, NULL, 1, 'Test\r'),
(49, 'Deze vraag gaat over SQL.\r\n\r\nIn een query staat:\r\n\r\n<pre>\r\nWHERE naam like \'%a%\'\r\n</pre>\r\nWelke namen worden door deze query geselecteerd?\r\n', 'Benoit, Anton, Charlie\r\n', 'Jappie, Elisia, Daria\r\n', 'Daan, Cees, Ayoub\r\n', 'Mohammed, Elise, Abdel\r\n', '', '', 2, 'B4P1 SQL'),
(50, 'Deze vraag gaat over SQL.\r\n\r\n<pre>\r\nSELECT *\r\nFROM gebruiker\r\nJOIN klas on ..... = .......\r\n</pre>\r\nWat moet er op de ..... komen?', 'De naam van de (twee) tabellen', 'De PK van klas en FK van gebruiker', 'De PK van klas en PK van gebruiker', 'De FK van klas en FK van gebruiker', '', '', 2, 'B4P1 SQL'),
(51, 'Stel je hebt 4 queries:\r\n\r\n<pre>\r\nselect sum(cijfer) from resultaten\r\nselect count(cijfer) from resultaten\r\nselect min(cijfer) from resultaten\r\nselect max(cijfer) from resultaten\r\n</pre>\r\nIn de antwoorden staan telkens vier uitkomsten. Dit zijn uitkomsten van de queries 1, 2, 3 en 4.\r\n\r\nWelke combinatie van uitkomsten is <i>mogelijk</i>?\r\n\r\n', '1, 100, 1000, 10\r\n', '150, 10, 10, 20\r\n', '0, 1, 10, 10\r\n', '100, 5, 1, 2\r\n', '', '', 2, 'B4P1 SQL');

-- --------------------------------------------------------

--
-- Table structure for table `quiz`
--

CREATE TABLE `quiz` (
  `id` int(11) NOT NULL,
  `name` varchar(40) NOT NULL,
  `password` varchar(20) NOT NULL,
  `active` tinyint(1) NOT NULL,
  `no_questions` int(11) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf32 COLLATE=utf32_general_ci;

--
-- Dumping data for table `quiz`
--

INSERT INTO `quiz` (`id`, `name`, `password`, `active`, `no_questions`) VALUES
(1, 'PHP Level 2 -- poging 1', 'paars23', 0, NULL),
(2, 'PHP Level 2 - poging 2', 'paars24', 0, NULL),
(3, 'test', 'test', 0, NULL),
(4, 'Blok 4 Poging 1 (B1P4)', 'KAAS121', 1, 2);

-- --------------------------------------------------------

--
-- Table structure for table `quizquestion`
--

CREATE TABLE `quizquestion` (
  `id` int(11) NOT NULL,
  `quiz_id` int(11) NOT NULL,
  `question_id` int(11) NOT NULL,
  `active` tinyint(1) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf32 COLLATE=utf32_general_ci;

--
-- Dumping data for table `quizquestion`
--

INSERT INTO `quizquestion` (`id`, `quiz_id`, `question_id`, `active`) VALUES
(6, 1, 1, 1),
(7, 1, 2, 1),
(8, 1, 5, 1),
(9, 1, 6, 0),
(10, 1, 7, 0),
(22, 4, 1, 0),
(39, 2, 1, 1),
(40, 2, 2, 1),
(41, 2, 5, 1),
(46, 3, 1, 0),
(47, 3, 2, 0),
(48, 3, 5, 0),
(49, 3, 6, 0),
(50, 3, 7, 0),
(51, 3, 10, 0),
(59, 2, 7, 0),
(60, 2, 6, 0),
(61, 2, 10, 0),
(71, 4, 2, 1),
(77, 4, 5, 1),
(78, 4, 6, 1),
(79, 4, 7, 0),
(80, 4, 10, 1),
(87, 4, 30, 1),
(88, 4, 31, 1),
(89, 4, 32, 1),
(90, 4, 33, 1),
(99, 3, 34, 0),
(107, 4, 34, 0),
(108, 4, 41, 0),
(109, 4, 50, 1),
(110, 4, 49, 1),
(111, 4, 51, 1);

-- --------------------------------------------------------

--
-- Table structure for table `submission`
--

CREATE TABLE `submission` (
  `id` int(11) NOT NULL,
  `token` varchar(64) DEFAULT NULL,
  `first_name` varchar(40) NOT NULL,
  `last_name` varchar(40) NOT NULL,
  `class` varchar(8) NOT NULL,
  `start_time` timestamp NOT NULL DEFAULT current_timestamp(),
  `end_time` timestamp NULL DEFAULT NULL,
  `ip_address` varchar(32) NOT NULL,
  `question_order` varchar(600) NOT NULL,
  `answer_order` varchar(600) NOT NULL,
  `no_questions` int(11) NOT NULL,
  `no_answered` int(11) DEFAULT NULL,
  `no_correct` int(11) DEFAULT NULL,
  `finished` tinyint(1) DEFAULT NULL,
  `quiz_id` int(11) NOT NULL,
  `last_updated` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf32 COLLATE=utf32_general_ci;

--
-- Dumping data for table `submission`
--

INSERT INTO `submission` (`id`, `token`, `first_name`, `last_name`, `class`, `start_time`, `end_time`, `ip_address`, `question_order`, `answer_order`, `no_questions`, `no_answered`, `no_correct`, `finished`, `quiz_id`, `last_updated`) VALUES
(16, '0c0e8058d671a4b0c5028b779fa14e544d67123d14e8ca8efffdcf5cf93bd935', 'Max', 'De Wielen', '3C', '2023-12-29 13:32:45', '2023-12-29 13:33:17', '::1', '1 5 2', ' 4 2 4', 3, 3, 3, 1, 1, '2023-12-31 20:02:24'),
(17, '7351a289dd2bab38e21f3e7b6445d3f824a7f7ef7bae8f54494d19ae6d818fa9', 'Max', 'Bisschop', '3C', '2023-12-29 13:34:43', NULL, '::1', '2 5 1', ' 4 1', 3, 2, 1, NULL, 1, '2023-12-29 14:03:17'),
(18, '396e1c0aa3e290ae27c74bd67ba7f73b865eb7edeaae264c7ac6c4e8b2bb3507', 'Tjeerd', 'Groothuizen', '3A', '2023-12-29 14:12:33', NULL, '::1', '5 1 2', ' 2', 3, 1, 1, 0, 1, '2023-12-31 17:21:55'),
(19, 'bcf9dd4fcfba64de280f45662257d2e447efbf0d86c26a69cc506bced8dc5bb7', 'André', 'Grootveld', '3A', '2023-12-30 09:20:50', NULL, '::1', '6 7 1 2 5', ' 2 4 2', 5, 3, 2, 1, 1, '2023-12-30 09:22:45'),
(20, 'bd0ec3ea756a173776255ab15793f91e62be17c8792f4ab52a27863b0284ee50', 'Pipo', 'De Clown', '2C', '2023-12-30 09:30:06', '2023-12-30 09:30:50', '::1', '6 7 5 1 2', ' 2 4 2 2 4', 5, 5, 4, 1, 1, '2023-12-30 09:30:50'),
(21, '676590da0e2f3e9d461fd645b4f7d673d9b70fb28dd39b39ee71152f263c5113', 'Tel', 'de Lama', '3C', '2023-12-30 09:34:04', NULL, '::1', '1 6 7 5 2', ' 4', 5, 1, 1, 0, 1, '2023-12-30 09:55:24'),
(22, '6ad0997b9cc1af855242ab4a58f9fac9d8e6d9bf733a95bc3933306820b9352f', 'Mark', 'de Willenschap-Groothuizen-Wessel', '3C', '2023-12-31 19:59:38', '2023-12-31 20:01:17', '::1', '6 5 2 7 1', ' 2 2 4 4 4', 5, 5, 5, 1, 1, '2023-12-31 20:08:35'),
(23, 'b8d8d542ae1e93b76fa6c32a81e510b0d11ccabdb178e1ab9e49d3cdbdf2e467', 'Roos', 'Meijer', '2C', '2023-12-31 23:38:44', NULL, '::1', '6 7 2 5 1', '', 5, 0, 0, NULL, 1, '2023-12-31 23:38:44'),
(24, '61620d9fba67655e6a22edf1d2e485ff5364f9bd0e9a1c27a5ef269148288b6b', 'Anja', 'Visser', '2B', '2024-01-01 13:29:24', NULL, '::1', '12 13 14 11', ' 1 1', 4, 2, 2, NULL, 3, '2024-01-01 13:29:35'),
(25, 'fc4583cd0f4f39eed1f4badb24009a6b3f6a35fcb610da438346d8704f8ffa68', 'Piet', 'Janssen', '2B', '2024-01-01 13:39:37', NULL, '::1', '14 11 13 12', '', 4, 0, 0, NULL, 3, '2024-01-01 13:39:37'),
(26, 'c4ca2317e51c70f0db11603f89678878977acbbe492a02b37ea007990f0e0b9e', 'Chris', 'Blauwdruk', '2F', '2024-01-02 14:17:16', NULL, '::1', '49 5 33 50 31 32 6 1 7 10 51 41 30 34 2', ' 2', 15, 1, 1, NULL, 4, '2024-01-02 14:18:40');

-- --------------------------------------------------------

--
-- Table structure for table `tbl_user`
--

CREATE TABLE `tbl_user` (
  `id` int(11) NOT NULL,
  `username` varchar(128) NOT NULL,
  `password` varchar(128) NOT NULL,
  `authKey` varchar(200) DEFAULT NULL,
  `role` varchar(20) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `tbl_user`
--

INSERT INTO `tbl_user` (`id`, `username`, `password`, `authKey`, `role`) VALUES
(5, 'admin', 'd033e22ae348aeb5660fc2140aec35850c4da997', '195f646de713fe51073524410c2201d2', '');

--
-- Indexes for dumped tables
--

--
-- Indexes for table `log`
--
ALTER TABLE `log`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `question`
--
ALTER TABLE `question`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `quiz`
--
ALTER TABLE `quiz`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `password` (`password`);

--
-- Indexes for table `quizquestion`
--
ALTER TABLE `quizquestion`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `quizquestion_two_fks` (`quiz_id`,`question_id`);

--
-- Indexes for table `submission`
--
ALTER TABLE `submission`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `token` (`token`);

--
-- Indexes for table `tbl_user`
--
ALTER TABLE `tbl_user`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `username` (`username`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `log`
--
ALTER TABLE `log`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=25;

--
-- AUTO_INCREMENT for table `question`
--
ALTER TABLE `question`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=52;

--
-- AUTO_INCREMENT for table `quiz`
--
ALTER TABLE `quiz`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=22;

--
-- AUTO_INCREMENT for table `quizquestion`
--
ALTER TABLE `quizquestion`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=112;

--
-- AUTO_INCREMENT for table `submission`
--
ALTER TABLE `submission`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=27;

--
-- AUTO_INCREMENT for table `tbl_user`
--
ALTER TABLE `tbl_user`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
