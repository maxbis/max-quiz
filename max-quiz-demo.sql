-- phpMyAdmin SQL Dump
-- version 5.2.0
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: Jan 05, 2024 at 03:18 PM
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
(24, 26, 4, 49, 2, 1, '2024-01-02 14:18:40'),
(25, 28, 4, 10, 3, 1, '2024-01-03 10:31:48'),
(26, 28, 4, 30, 4, 1, '2024-01-03 10:32:10'),
(27, 29, 3, 34, 1, 1, '2024-01-03 14:13:02'),
(28, 30, 27, 105, 2, 0, '2024-01-03 16:33:16'),
(29, 30, 27, 111, 1, 1, '2024-01-03 16:33:19'),
(30, 31, 27, 101, 1, 1, '2024-01-03 16:36:16'),
(31, 31, 27, 112, 3, 0, '2024-01-03 16:36:50'),
(32, 32, 28, 137, 1, 1, '2024-01-03 19:09:54'),
(33, 32, 28, 136, 1, 1, '2024-01-03 19:10:03'),
(34, 32, 28, 135, 2, 0, '2024-01-03 19:10:12'),
(35, 32, 28, 130, 4, 0, '2024-01-03 19:10:22'),
(36, 34, 1, 2, 4, 1, '2024-01-04 22:02:10'),
(37, 34, 1, 5, 2, 1, '2024-01-04 22:02:20'),
(38, 34, 1, 6, 2, 1, '2024-01-04 22:05:32'),
(39, 34, 1, 2, 4, 1, '2024-01-04 22:23:52'),
(40, 34, 1, 5, 4, 0, '2024-01-04 22:37:50'),
(41, 34, 1, 5, 4, 0, '2024-01-04 22:38:14'),
(42, 34, 1, 6, 2, 1, '2024-01-04 22:38:18'),
(43, 35, 1, 2, 4, 1, '2024-01-04 22:44:31'),
(44, 35, 1, 6, 2, 1, '2024-01-04 22:44:35'),
(45, 35, 1, 5, 2, 1, '2024-01-04 22:44:44');

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
(202, 'Which city is known as \"The Eternal City\"?\r', 'Rome', 'Paris', 'Athens', 'London', NULL, NULL, 1, 'Demo'),
(203, 'What is the official language of Brazil?\r', 'Portuguese', 'Spanish', 'English', 'French', NULL, NULL, 1, 'Demo'),
(204, 'In which country can you visit the ancient Inca city of Machu Picchu?\r', 'Peru', 'Bolivia', 'Chile', 'Ecuador', NULL, NULL, 1, 'Demo'),
(205, 'What currency is used in Japan?\r', 'Yen', 'Won', 'Yuan', 'Rupee', NULL, NULL, 1, 'Demo'),
(206, 'Which country is known for having a city that spans two continents, Europe and Asia?\r', 'Turkey', 'Russia', 'Egypt', 'Kazakhstan', NULL, NULL, 1, 'Demo');

-- --------------------------------------------------------

--
-- Table structure for table `quiz`
--

CREATE TABLE `quiz` (
  `id` int(11) NOT NULL,
  `name` varchar(40) NOT NULL,
  `password` varchar(20) NOT NULL,
  `active` tinyint(1) NOT NULL,
  `no_questions` int(11) DEFAULT NULL,
  `review` int(11) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf32 COLLATE=utf32_general_ci;

--
-- Dumping data for table `quiz`
--

INSERT INTO `quiz` (`id`, `name`, `password`, `active`, `no_questions`, `review`) VALUES
(31, 'Demo', 'Demo', 1, 3, 1);

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
(351, 31, 202, 1),
(352, 31, 203, 1),
(353, 31, 204, 1),
(354, 31, 205, 1),
(355, 31, 206, 1);

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
  `answer_order` varchar(600) DEFAULT '''''',
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
(17, '7351a289dd2bab38e21f3e7b6445d3f824a7f7ef7bae8f54494d19ae6d818fa9', 'Max', 'Bisschop', '3C', '2023-12-29 13:34:43', NULL, '::1', '2 5 1', ' 4 1', 3, 2, 1, NULL, 1, '2023-12-29 14:03:17'),
(18, '396e1c0aa3e290ae27c74bd67ba7f73b865eb7edeaae264c7ac6c4e8b2bb3507', 'Tjeerd', 'Grootshuizen', '3A', '2023-12-29 14:12:33', NULL, '::1', '5 1 2', ' 2', 3, 1, 1, 0, 1, '2024-01-04 22:21:31'),
(19, 'bcf9dd4fcfba64de280f45662257d2e447efbf0d86c26a69cc506bced8dc5bb7', 'André', 'Grootveld', '3A', '2023-12-30 09:20:50', NULL, '::1', '6 7 1 2 5', ' 2 4 2', 5, 3, 2, 0, 1, '2024-01-04 08:44:37'),
(20, 'bd0ec3ea756a173776255ab15793f91e62be17c8792f4ab52a27863b0284ee50', 'Pipo', 'De Clown', '2C', '2023-12-30 09:30:06', '2023-12-30 09:30:50', '::1', '6 7 5 1 2', ' 2 4 2 2 4', 5, 5, 4, 1, 1, '2023-12-30 09:30:50'),
(24, '61620d9fba67655e6a22edf1d2e485ff5364f9bd0e9a1c27a5ef269148288b6b', 'Anja', 'Visser', '2B', '2024-01-01 13:29:24', NULL, '::1', '12 13 14 11', ' 1 1', 4, 2, 2, NULL, 3, '2024-01-01 13:29:35'),
(25, 'fc4583cd0f4f39eed1f4badb24009a6b3f6a35fcb610da438346d8704f8ffa68', 'Piet', 'Janssen', '2B', '2024-01-01 13:39:37', NULL, '::1', '14 11 13 12', '', 4, 0, 0, NULL, 3, '2024-01-01 13:39:37'),
(26, 'c4ca2317e51c70f0db11603f89678878977acbbe492a02b37ea007990f0e0b9e', 'Chris', 'Blauwdruk', '2F', '2024-01-02 14:17:16', NULL, '::1', '49 5 33 50 31 32 6 1 7 10 51 41 30 34 2', ' 2', 15, 1, 1, NULL, 4, '2024-01-02 14:18:40'),
(27, '759b52713586edf199e1b5318249fa2ff2c793b1eeb83afd84bb7afbdc3a42e6', 'Anne', 'Drijver', '2F', '2024-01-02 16:39:07', NULL, '::1', '5 41 10 2 30 32 49 51 33 6 34 7 1 50 31', '', 15, 0, 0, NULL, 4, '2024-01-02 16:39:07'),
(28, '0fc9e0287becec3d5a9b3971e9cf28d24614316105e9841479ae8e8e7a172a90', 'Edo', 'de Jager', '1A', '2024-01-03 10:31:29', NULL, '::1', '10 30 34 50 51 31 6 33 41 32 2 7 5 49 1', ' 3 4', 15, 2, 2, NULL, 4, '2024-01-03 10:32:10'),
(29, '8b43ca7cd76bc224b7b3de6b14ef9db3120f774652f863e3ec751c5933a9752c', 'Gio', 'Charmelle', '2F', '2024-01-03 14:12:58', '2024-01-03 14:13:02', '::1', '34', ' 1', 1, 1, 1, 1, 3, '2024-01-03 14:13:02'),
(30, 'ddfff4f36d0a4c117ca315ddfe6163d7100d95dbaff07ccca04a272b0e90da7f', 'Mark', 'de Lieve', '2D', '2024-01-03 16:33:08', NULL, '::1', '105 111', ' 2 1', 25, 2, 1, NULL, 27, '2024-01-03 16:33:19'),
(31, '3c94569d207ff636edf039e5d37ec2c6136505c8cbadb31aeaeb553eadee1b1d', 'Anja', 'De Kwaaie', '2D', '2024-01-03 16:35:44', '2024-01-03 16:36:50', '::1', '101 112', ' 1 3', 2, 2, 1, 1, 27, '2024-01-03 16:36:50'),
(32, 'db8a6627b452f0789ccf4dc5ebf8f183a3e6709ac6d0f1ff8e51385f91c905a3', 'Anne', 'de Ruiter', '2A', '2024-01-03 19:03:51', '2024-01-03 19:10:22', '::1', '137 136 135 130', ' 1 1 2 4', 4, 4, 2, 1, 28, '2024-01-03 19:10:22'),
(33, 'ac739708c4f68fc7412f3baf9df2d90ebd9d0482df12b108bc86d7377eb1111a', 'Moon', 'Pool', '2A', '2024-01-04 08:34:56', NULL, '::1', '128 153 124 120 121 152 163 140 122 156', '', 10, 0, 0, NULL, 28, '2024-01-04 08:34:56'),
(35, '908c4b7318ee61f17b4a90ef77a2cb9fa65918da232f58ca0e1910b282e196d1', 'Karel', 'Nedervoel', '1A', '2024-01-04 22:44:23', '2024-01-04 22:44:44', '::1', '2 6 5', ' 4 2 2', 3, 3, 3, 1, 1, '2024-01-04 22:44:44');

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
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=46;

--
-- AUTO_INCREMENT for table `question`
--
ALTER TABLE `question`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=207;

--
-- AUTO_INCREMENT for table `quiz`
--
ALTER TABLE `quiz`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=32;

--
-- AUTO_INCREMENT for table `quizquestion`
--
ALTER TABLE `quizquestion`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=356;

--
-- AUTO_INCREMENT for table `submission`
--
ALTER TABLE `submission`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=36;

--
-- AUTO_INCREMENT for table `tbl_user`
--
ALTER TABLE `tbl_user`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=8;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
