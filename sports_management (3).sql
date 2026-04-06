-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: Apr 06, 2026 at 08:10 PM
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
-- Database: `sports_management`
--

-- --------------------------------------------------------

--
-- Table structure for table `events`
--

CREATE TABLE `events` (
  `id` int(11) NOT NULL,
  `title` varchar(100) NOT NULL,
  `description` text DEFAULT NULL,
  `event_date` date NOT NULL,
  `registration_deadline` date NOT NULL,
  `location` varchar(255) NOT NULL,
  `max_participants` int(11) DEFAULT NULL,
  `is_team_event` tinyint(1) DEFAULT 0,
  `status` enum('upcoming','ongoing','completed','cancelled') DEFAULT 'upcoming',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `events`
--

INSERT INTO `events` (`id`, `title`, `description`, `event_date`, `registration_deadline`, `location`, `max_participants`, `is_team_event`, `status`, `created_at`, `updated_at`) VALUES
(1, 'Running', '100mtr', '2025-04-20', '2025-04-20', 'RPD', NULL, 0, 'ongoing', '2025-04-20 21:18:30', '2025-04-20 21:46:43'),
(2, 'Team', 'team', '2025-04-20', '2025-04-20', 'RPD G', NULL, 1, '', '2025-04-20 23:17:28', '2025-04-20 21:02:47'),
(3, '1000 mtr Walking', 'Description', '2025-05-29', '2025-05-28', 'RPD GROUND', NULL, 0, 'upcoming', '2025-05-24 06:11:04', '2025-05-24 06:11:04'),
(4, 'Shotput', 'Shotput', '2025-05-29', '2025-05-28', 'RPD GROUND', NULL, 0, 'upcoming', '2025-05-25 04:08:53', '2025-05-25 04:08:53'),
(5, 'Discuss throw', 'Discuss throw', '2025-05-29', '2025-05-28', 'RPD GROUND', NULL, 0, 'upcoming', '2025-05-25 04:09:31', '2025-05-25 04:09:31'),
(6, 'Long Jump', 'Long jump', '2025-05-29', '2025-05-28', 'RPD GROUND', NULL, 0, 'upcoming', '2025-05-25 04:09:56', '2025-05-25 04:09:56'),
(7, 'Javlin Throw', 'Javlin Throw', '2025-05-24', '2025-05-22', 'RPD Ground', NULL, 0, 'completed', '2025-05-25 04:10:55', '2025-05-25 04:10:55'),
(8, 'Running 400mtr', 'Running', '2025-05-29', '2025-05-28', 'Rpd', 6, 0, 'upcoming', '2025-05-25 05:34:21', '2025-05-25 05:37:24');

-- --------------------------------------------------------

--
-- Table structure for table `live_streams`
--

CREATE TABLE `live_streams` (
  `id` int(11) NOT NULL,
  `title` varchar(255) NOT NULL,
  `description` text DEFAULT NULL,
  `video_url` varchar(255) NOT NULL,
  `event_id` int(11) DEFAULT NULL,
  `is_active` tinyint(1) DEFAULT 1,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `live_streams`
--

INSERT INTO `live_streams` (`id`, `title`, `description`, `video_url`, `event_id`, `is_active`, `created_at`, `updated_at`) VALUES
(1, 'Ind', 'kd', 'https://youtu.be/gL_LIAm22Mc', 1, 1, '2025-04-20 21:54:19', '2025-04-20 21:54:19'),
(2, 'India vs pak', 'pak', 'https://www.youtube.com/watch?v=pYRdi0f6v4o', 2, 0, '2025-04-20 21:55:54', '2025-04-21 04:42:23'),
(3, 'Football Match', 'Match 3', 'https://www.youtube.com/watch?v=DZIASl9q90s', 2, 1, '2025-05-25 05:43:42', '2025-05-25 05:43:42');

-- --------------------------------------------------------

--
-- Table structure for table `registrations`
--

CREATE TABLE `registrations` (
  `id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `event_id` int(11) NOT NULL,
  `registration_date` timestamp NOT NULL DEFAULT current_timestamp(),
  `status` enum('pending','approved','rejected') DEFAULT 'pending'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `registrations`
--

INSERT INTO `registrations` (`id`, `user_id`, `event_id`, `registration_date`, `status`) VALUES
(2, 3, 1, '2025-04-20 21:39:01', 'approved'),
(3, 7, 3, '2025-05-24 06:29:24', 'approved'),
(6, 2, 3, '2025-05-25 03:24:40', 'pending'),
(7, 17, 6, '2025-05-25 04:13:48', 'approved'),
(8, 2, 8, '2025-05-25 05:46:47', 'approved'),
(9, 19, 8, '2025-05-25 05:51:19', 'approved');

-- --------------------------------------------------------

--
-- Table structure for table `results`
--

CREATE TABLE `results` (
  `id` int(11) NOT NULL,
  `event_id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `position` int(11) DEFAULT NULL,
  `score` varchar(50) DEFAULT NULL,
  `remarks` text DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `results`
--

INSERT INTO `results` (`id`, `event_id`, `user_id`, `position`, `score`, `remarks`, `created_at`, `updated_at`) VALUES
(1, 1, 2, 3, '10', 'Running Winner', '2025-04-20 21:18:54', '2025-04-20 21:21:16'),
(2, 1, 3, 1, '', '100 mtrs', '2025-04-20 21:43:45', '2025-04-20 21:43:45'),
(3, 3, 7, 2, '', 'Runner-UP', '2025-05-24 06:30:52', '2025-05-24 06:30:52'),
(4, 7, 9, 1, '88.95', '', '2025-05-25 04:11:24', '2025-05-25 04:11:24'),
(5, 7, 15, 2, '84.33', '', '2025-05-25 04:11:40', '2025-05-25 04:11:40'),
(6, 7, 11, 3, '80.22', '', '2025-05-25 04:12:01', '2025-05-25 04:12:01'),
(7, 8, 19, 1, '10.23', 'winner', '2025-05-25 05:53:40', '2025-05-25 05:53:40'),
(8, 8, 2, 2, '11.1', '2nd Position', '2025-05-25 05:54:52', '2025-05-25 05:54:52');

-- --------------------------------------------------------

--
-- Table structure for table `teams`
--

CREATE TABLE `teams` (
  `id` int(11) NOT NULL,
  `name` varchar(100) NOT NULL,
  `description` text DEFAULT NULL,
  `created_by` int(11) NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `teams`
--

INSERT INTO `teams` (`id`, `name`, `description`, `created_by`, `created_at`, `updated_at`) VALUES
(1, 'Sumit Warriors', 'Sumit warriors', 2, '2025-04-20 23:27:30', '2025-04-20 23:27:30'),
(2, 'Football XI', 'Football team for GSS BCA', 2, '2025-05-24 06:34:08', '2025-05-24 06:34:08'),
(3, 'blues XI', 'team blue for football', 2, '2025-05-25 05:48:35', '2025-05-25 05:48:35');

-- --------------------------------------------------------

--
-- Table structure for table `team_members`
--

CREATE TABLE `team_members` (
  `id` int(11) NOT NULL,
  `team_id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `is_captain` tinyint(1) DEFAULT 0,
  `joined_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `team_members`
--

INSERT INTO `team_members` (`id`, `team_id`, `user_id`, `is_captain`, `joined_at`) VALUES
(1, 1, 2, 1, '2025-04-20 23:27:30'),
(3, 2, 2, 1, '2025-05-24 06:34:08'),
(4, 2, 17, 0, '2025-05-25 04:14:03'),
(5, 3, 2, 1, '2025-05-25 05:48:35');

-- --------------------------------------------------------

--
-- Table structure for table `team_registrations`
--

CREATE TABLE `team_registrations` (
  `id` int(11) NOT NULL,
  `team_id` int(11) NOT NULL,
  `event_id` int(11) NOT NULL,
  `registration_date` timestamp NOT NULL DEFAULT current_timestamp(),
  `status` enum('pending','approved','rejected') DEFAULT 'pending'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `team_registrations`
--

INSERT INTO `team_registrations` (`id`, `team_id`, `event_id`, `registration_date`, `status`) VALUES
(1, 1, 2, '2025-04-20 23:27:58', 'pending');

-- --------------------------------------------------------

--
-- Table structure for table `team_results`
--

CREATE TABLE `team_results` (
  `id` int(11) NOT NULL,
  `event_id` int(11) NOT NULL,
  `team_id` int(11) NOT NULL,
  `position` int(11) DEFAULT NULL,
  `score` varchar(50) DEFAULT NULL,
  `remarks` text DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `team_results`
--

INSERT INTO `team_results` (`id`, `event_id`, `team_id`, `position`, `score`, `remarks`, `created_at`, `updated_at`) VALUES
(1, 2, 1, 1, '', '', '2025-04-20 21:03:22', '2025-04-20 21:44:28');

-- --------------------------------------------------------

--
-- Table structure for table `users`
--

CREATE TABLE `users` (
  `id` int(11) NOT NULL,
  `username` varchar(50) NOT NULL,
  `password` varchar(255) NOT NULL,
  `email` varchar(100) NOT NULL,
  `full_name` varchar(100) NOT NULL,
  `role` enum('admin','player') NOT NULL DEFAULT 'player',
  `phone` varchar(20) DEFAULT NULL,
  `date_of_birth` date DEFAULT NULL,
  `gender` enum('male','female','other') DEFAULT NULL,
  `address` text DEFAULT NULL,
  `profile_image` varchar(255) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  `is_verified` tinyint(1) DEFAULT 0,
  `last_login` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `users`
--

INSERT INTO `users` (`id`, `username`, `password`, `email`, `full_name`, `role`, `phone`, `date_of_birth`, `gender`, `address`, `profile_image`, `created_at`, `updated_at`, `is_verified`, `last_login`) VALUES
(1, 'admin', '$2y$10$JNFama3Rft0jdnIAoDsphOHa.4SdQXVq2.rdO/xYGOfDyBFOcO3ri', 'admin@example.com', 'System Administrator', 'admin', NULL, NULL, NULL, NULL, '', '2025-04-20 21:06:59', '2026-04-06 18:03:26', 0, '2026-04-06 18:03:26'),
(2, 'sumit', '$2y$10$JNFama3Rft0jdnIAoDsphOHa.4SdQXVq2.rdO/xYGOfDyBFOcO3ri', 'sumit@gmail.com', 'Sumit Singh', 'player', '8884190237', '2025-04-20', 'male', '', '', '2025-04-20 21:09:15', '2025-05-25 05:45:44', 2, '2025-05-25 05:45:44'),
(3, 'sumit2', '$2y$10$8lCYwiVX3gNHDSWQfe.hoeybihkH/szvEdbBwbR9/k8YXYJVmgaLG', 'sumit2@gmail.com', 'sumit2', 'player', NULL, NULL, NULL, NULL, NULL, '2025-04-20 22:11:15', '2025-04-20 22:11:15', 0, NULL),
(4, 'sumit22', '$2y$10$SnPsFDzsbr4BeN2LE3NEweRbgc/qg5pz2ZAau3Gj5nmonYyQFzGUa', 'sumit22@gmail.com', 'Sumit Singh', 'player', '', '2025-04-21', '', '', NULL, '2025-04-21 05:12:54', '2025-04-21 05:12:54', 0, NULL),
(5, 'sumitsingh', '$2y$10$WScVw.eXVfL1xdeGFAvmgOSz.xxdbZGhNNflAMvKv.H8s6yyvfETi', 'spgamerofficial22@gmail.com', 'Sumit Singh', 'player', NULL, NULL, NULL, NULL, 'assets/images/avatars/male/avatar3.png', '2025-05-24 05:56:38', '2025-05-24 05:56:38', 0, '2025-05-24 05:56:38'),
(6, 'sumitsingh2', '$2y$10$QZt6GuLdp8NMh1IZ9svP0.JUVjpeSPtsvNi8MrgHQeLJdlbqdVrve', 'sumit20@gmail.com', 'sumit singh', 'player', '9008192385', '2003-11-24', 'male', 'Sumit 212 address', 'assets/images/avatars/female/avatar3.png', '2025-05-24 06:24:49', '2025-05-24 06:24:49', 0, NULL),
(7, 'akshay', '$2y$10$Hm60oa9q4N1TmbX1G9rA5uZYau7PU/9H2wfvfTLSyUeCmktA/OrHO', 'akshay@gmail.com', 'Akshay Kokitkar', 'player', '8526259658', '2022-05-24', 'male', 'Adress', 'assets/images/avatars/male/avatar2.png', '2025-05-24 06:26:09', '2025-05-24 06:29:08', 0, '2025-05-24 06:29:08'),
(8, 'rajesh', '$2y$10$ZXGg1blMf4KYa.Lu0eGI/OAMJUx0KdgLs0hCGb15tRj97EyneKw9y', 'rajesh@gmail.com', 'rajesh', 'player', '852659559', '2002-05-24', 'male', 'indes', 'assets/images/avatars/female/avatar5.png', '2025-05-24 17:58:15', '2025-05-24 17:58:15', 0, NULL),
(9, 'rajesh22', '$2y$10$fGCidKOKx7VMcrlaex88Fu4dGCaYFlR4KAglBpLa2Iuy71QjDDewi', 'rajesh5@gmail.com', 'rajesh', 'player', '8526569589', '2025-05-24', 'male', 'dd', 'assets/images/avatars/female/avatar5.png', '2025-05-24 17:59:40', '2025-05-24 17:59:40', 0, NULL),
(10, 'ramesh', '$2y$10$JJQ2C3sgo.wzNYt9z.YgpO/.wFLW6ujZxFYI0BrWwq9/6rGbzQ8xG', 'ramesh@gmail.com', 'ramesh', 'player', '9966336699', '2025-05-24', 'male', 'jhdfbefheh', 'assets/images/avatars/female/avatar1.png', '2025-05-24 18:00:39', '2025-05-24 18:00:39', 0, NULL),
(11, 'akash', '$2y$10$Je0goREpl00Z9CJuZyP4ieAaeOEzOikoIGJLBxBb/d5s2H2yksBnW', 'akash@gmail.com', 'akash', 'player', '8546985236', '2004-05-24', 'male', 'addrrss', 'assets/images/avatars/male/avatar1.png', '2025-05-24 18:03:26', '2025-05-24 18:03:26', 0, NULL),
(12, 'rahul', '$2y$10$DImasuZABHQU26yKMYKhoOMrFY3kbK8.EyGMsVOVp2fOvquritUqe', 'rahul@gmail.com', 'Rahul', '', '7878767678', '2000-05-25', 'male', 'Address', NULL, '2025-05-25 03:08:22', '2025-05-25 03:08:22', 0, NULL),
(13, 'sumitt33', '$2y$10$Y8VOFya/PL3SZc8y18L9zu4EN.xB5zWl8.eIY6OzALWj5QHvfTGCS', 'sumit.singh@gmail.com', 'sumit singh', '', '3656958587', '2007-05-25', 'male', 'address22', NULL, '2025-05-25 03:15:14', '2025-05-25 03:15:14', 0, NULL),
(14, 'sumit747', '$2y$10$Vdl05kNgGHFoqLk3eqmMHO5Y9lXz0nYgAC/yP/8vxepVWU7BWp6hq', 'sumit.singh.88@gmail.com', 'sumit', '', '7897897897', '2009-05-25', 'male', 'sfhgkjlafdsgjhb,kflhk', NULL, '2025-05-25 03:16:19', '2025-05-25 03:16:19', 0, NULL),
(15, 'ram', '$2y$10$x7JQUzRq3DwJRsFP4AWspO0fxoglkhn181N/gMyMyH4jd.1WoLM1y', 'ram@gmail.co', 'ram', 'player', '123456987', '2007-05-25', 'male', 'Address', 'assets/images/avatars/male/avatar2.png', '2025-05-25 03:18:43', '2025-05-25 03:18:43', 0, NULL),
(16, 'samarth', '$2y$10$ysLrnz2M61UOIZRJk83OK.01zOQHSa2MYOrlS0ZdlRVbwcbngrDFK', 'samarth@gmail.com', 'samarth', 'player', '9878987888', '2004-05-25', 'male', 'address demo', 'assets/images/avatars/male/avatar4.png', '2025-05-25 04:04:34', '2025-05-25 04:04:34', 0, NULL),
(17, 'Prasad', '$2y$10$ynXTEA0zsIM/MayRB/Hh/eLaCmslUoj8Y9bZmp9I99.5ztPLORS9.', 'prasadpatil@gmail.com', 'prasad patil', 'player', '8555669988', '2004-05-25', 'male', 'address demo3', 'assets/images/avatars/female/avatar5.png', '2025-05-25 04:05:56', '2025-05-25 04:13:31', 0, '2025-05-25 04:13:31'),
(18, 'suma', '$2y$10$4hwyzhLpSkRUuDPzTuwS/Oxp.WPnVejYjfCzc5ibSmQoSeKav0HfO', 'suma@gmail.com', 'suma patil', 'player', '8569858596', '2003-05-25', 'female', 'address demo 4', 'assets/images/avatars/female/avatar4.png', '2025-05-25 04:06:34', '2025-05-25 04:06:34', 0, NULL),
(19, 'sneha', '$2y$10$LAiVt6SStss.zUSrzK9UH.VYn3bbL3J5gCT3Cl/Kv5rW8hRTvpNFG', 'sneha73@gmail.com', 'sneha', 'player', '8596958585', '2004-05-25', 'female', 'address demo 5', 'assets/images/avatars/female/avatar4.png', '2025-05-25 04:08:03', '2025-05-25 05:51:02', 0, '2025-05-25 05:51:02'),
(20, 'Sneha patil', '$2y$10$y32ZXy3xalUp5xG1Tb6Pw.SpXdOduIFxU6xXiw9FQa9UVDbpBA9wK', 'Snehap@gmail.com', 'sneha patil', 'player', '5587469822', '2004-05-25', 'female', 'address', 'assets/images/avatars/male/avatar1.png', '2025-05-25 05:32:50', '2025-05-25 05:32:50', 0, NULL);

--
-- Indexes for dumped tables
--

--
-- Indexes for table `events`
--
ALTER TABLE `events`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `live_streams`
--
ALTER TABLE `live_streams`
  ADD PRIMARY KEY (`id`),
  ADD KEY `event_id` (`event_id`);

--
-- Indexes for table `registrations`
--
ALTER TABLE `registrations`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `user_id` (`user_id`,`event_id`),
  ADD KEY `event_id` (`event_id`);

--
-- Indexes for table `results`
--
ALTER TABLE `results`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `event_id` (`event_id`,`user_id`),
  ADD KEY `user_id` (`user_id`);

--
-- Indexes for table `teams`
--
ALTER TABLE `teams`
  ADD PRIMARY KEY (`id`),
  ADD KEY `created_by` (`created_by`);

--
-- Indexes for table `team_members`
--
ALTER TABLE `team_members`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `team_id` (`team_id`,`user_id`),
  ADD KEY `user_id` (`user_id`);

--
-- Indexes for table `team_registrations`
--
ALTER TABLE `team_registrations`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `team_id` (`team_id`,`event_id`),
  ADD KEY `event_id` (`event_id`);

--
-- Indexes for table `team_results`
--
ALTER TABLE `team_results`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `event_id` (`event_id`,`team_id`),
  ADD KEY `team_id` (`team_id`);

--
-- Indexes for table `users`
--
ALTER TABLE `users`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `username` (`username`),
  ADD UNIQUE KEY `email` (`email`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `events`
--
ALTER TABLE `events`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=9;

--
-- AUTO_INCREMENT for table `live_streams`
--
ALTER TABLE `live_streams`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT for table `registrations`
--
ALTER TABLE `registrations`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=10;

--
-- AUTO_INCREMENT for table `results`
--
ALTER TABLE `results`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=9;

--
-- AUTO_INCREMENT for table `teams`
--
ALTER TABLE `teams`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT for table `team_members`
--
ALTER TABLE `team_members`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;

--
-- AUTO_INCREMENT for table `team_registrations`
--
ALTER TABLE `team_registrations`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT for table `team_results`
--
ALTER TABLE `team_results`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT for table `users`
--
ALTER TABLE `users`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=21;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `live_streams`
--
ALTER TABLE `live_streams`
  ADD CONSTRAINT `live_streams_ibfk_1` FOREIGN KEY (`event_id`) REFERENCES `events` (`id`) ON DELETE SET NULL;

--
-- Constraints for table `registrations`
--
ALTER TABLE `registrations`
  ADD CONSTRAINT `registrations_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `registrations_ibfk_2` FOREIGN KEY (`event_id`) REFERENCES `events` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `results`
--
ALTER TABLE `results`
  ADD CONSTRAINT `results_ibfk_1` FOREIGN KEY (`event_id`) REFERENCES `events` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `results_ibfk_2` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `teams`
--
ALTER TABLE `teams`
  ADD CONSTRAINT `teams_ibfk_1` FOREIGN KEY (`created_by`) REFERENCES `users` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `team_members`
--
ALTER TABLE `team_members`
  ADD CONSTRAINT `team_members_ibfk_1` FOREIGN KEY (`team_id`) REFERENCES `teams` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `team_members_ibfk_2` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `team_registrations`
--
ALTER TABLE `team_registrations`
  ADD CONSTRAINT `team_registrations_ibfk_1` FOREIGN KEY (`team_id`) REFERENCES `teams` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `team_registrations_ibfk_2` FOREIGN KEY (`event_id`) REFERENCES `events` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `team_results`
--
ALTER TABLE `team_results`
  ADD CONSTRAINT `team_results_ibfk_1` FOREIGN KEY (`event_id`) REFERENCES `events` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `team_results_ibfk_2` FOREIGN KEY (`team_id`) REFERENCES `teams` (`id`) ON DELETE CASCADE;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
