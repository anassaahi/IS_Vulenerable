-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: Apr 27, 2025 at 06:07 PM
-- Server version: 10.4.28-MariaDB
-- PHP Version: 8.2.4

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Database: `users`
--

-- --------------------------------------------------------

--
-- Table structure for table `users`
--

CREATE TABLE `users` (
  `user_id` int(11) NOT NULL,
  `username` varchar(50) NOT NULL,
  `password` varchar(255) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `users`
--

INSERT INTO `users` (`user_id`, `username`, `password`) VALUES
(6, 'hello', '12345678'),
(7, 'anas', '12345678'),
(8, 'talha', '123456778');

--
-- Triggers `users`
--
DELIMITER $$
CREATE TRIGGER `after_user_insert` AFTER INSERT ON `users` FOR EACH ROW BEGIN
    INSERT INTO user_task_progress (user_id) VALUES (NEW.user_id);
END
$$
DELIMITER ;

-- --------------------------------------------------------

--
-- Table structure for table `user_task_progress`
--

CREATE TABLE `user_task_progress` (
  `progress_id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `xss_complete` tinyint(1) DEFAULT 0,
  `xss_hintseen` tinyint(1) DEFAULT 0,
  `xss_solutionseen` tinyint(1) DEFAULT 0,
  `xss_totalmark` int(11) DEFAULT 0,
  `xss_completion_time` timestamp NULL DEFAULT NULL,
  `csrf_complete` tinyint(1) DEFAULT 0,
  `csrf_hintseen` tinyint(1) DEFAULT 0,
  `csrf_solutionseen` tinyint(1) DEFAULT 0,
  `csrf_totalmark` int(11) DEFAULT 0,
  `csrf_completion_time` timestamp NULL DEFAULT NULL,
  `fileupload_complete` tinyint(1) DEFAULT 0,
  `fileupload_hintseen` tinyint(1) DEFAULT 0,
  `fileupload_solutionseen` tinyint(1) DEFAULT 0,
  `fileupload_totalmark` int(11) DEFAULT 0,
  `fileupload_completion_time` timestamp NULL DEFAULT NULL,
  `sqlinjection_complete` tinyint(1) DEFAULT 0,
  `sqlinjection_hintseen` tinyint(1) DEFAULT 0,
  `sqlinjection_solutionseen` tinyint(1) DEFAULT 0,
  `sqlinjection_totalmark` int(11) DEFAULT 0,
  `sqlinjection_completion_time` timestamp NULL DEFAULT NULL,
  `openredirect_complete` tinyint(1) DEFAULT 0,
  `openredirect_hintseen` tinyint(1) DEFAULT 0,
  `openredirect_solutionseen` tinyint(1) DEFAULT 0,
  `openredirect_totalmark` int(11) DEFAULT 0,
  `openredirect_completion_time` timestamp NULL DEFAULT NULL,
  `ssrf_complete` tinyint(1) DEFAULT 0,
  `ssrf_hintseen` tinyint(1) DEFAULT 0,
  `ssrf_solutionseen` tinyint(1) DEFAULT 0,
  `ssrf_totalmark` int(11) DEFAULT 0,
  `ssrf_completion_time` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `user_task_progress`
--

INSERT INTO `user_task_progress` (`progress_id`, `user_id`, `xss_complete`, `xss_hintseen`, `xss_solutionseen`, `xss_totalmark`, `xss_completion_time`, `csrf_complete`, `csrf_hintseen`, `csrf_solutionseen`, `csrf_totalmark`, `csrf_completion_time`, `fileupload_complete`, `fileupload_hintseen`, `fileupload_solutionseen`, `fileupload_totalmark`, `fileupload_completion_time`, `sqlinjection_complete`, `sqlinjection_hintseen`, `sqlinjection_solutionseen`, `sqlinjection_totalmark`, `sqlinjection_completion_time`, `openredirect_complete`, `openredirect_hintseen`, `openredirect_solutionseen`, `openredirect_totalmark`, `openredirect_completion_time`, `ssrf_complete`, `ssrf_hintseen`, `ssrf_solutionseen`, `ssrf_totalmark`, `ssrf_completion_time`) VALUES
(1, 6, 0, 0, 0, 0, NULL, 0, 0, 0, 0, NULL, 0, 0, 0, 0, NULL, 0, 0, 0, 0, NULL, 0, 0, 0, 0, NULL, 0, 0, 0, 0, NULL),
(2, 7, 1, 1, 0, 35, NULL, 0, 0, 0, 0, NULL, 0, 0, 0, 0, NULL, 0, 0, 0, 0, NULL, 1, 1, 1, 0, NULL, 0, 0, 0, 0, NULL),
(3, 8, 1, 1, 0, 35, NULL, 0, 0, 0, 0, NULL, 0, 0, 0, 0, NULL, 0, 0, 0, 0, NULL, 1, 1, 1, 50, NULL, 0, 0, 0, 0, NULL);

--
-- Indexes for dumped tables
--

--
-- Indexes for table `users`
--
ALTER TABLE `users`
  ADD PRIMARY KEY (`user_id`),
  ADD UNIQUE KEY `username` (`username`);

--
-- Indexes for table `user_task_progress`
--
ALTER TABLE `user_task_progress`
  ADD PRIMARY KEY (`progress_id`),
  ADD UNIQUE KEY `user_id` (`user_id`),
  ADD KEY `user_id_2` (`user_id`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `users`
--
ALTER TABLE `users`
  MODIFY `user_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=9;

--
-- AUTO_INCREMENT for table `user_task_progress`
--
ALTER TABLE `user_task_progress`
  MODIFY `progress_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `user_task_progress`
--
ALTER TABLE `user_task_progress`
  ADD CONSTRAINT `user_task_progress_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`user_id`) ON DELETE CASCADE;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
