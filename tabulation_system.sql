-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: Mar 02, 2025 at 01:32 PM
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
-- Database: `tabulation_system`
--

-- --------------------------------------------------------

--
-- Table structure for table `admin_logs`
--

CREATE TABLE `admin_logs` (
  `id` int(11) NOT NULL,
  `username` varchar(255) NOT NULL,
  `attempted_password` varchar(255) NOT NULL,
  `attempt_time` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `admin_logs`
--

INSERT INTO `admin_logs` (`id`, `username`, `attempted_password`, `attempt_time`) VALUES
(1, 'admin', 'admin1qwewq', '2025-02-22 17:53:18'),
(2, 'judge1', 'qeqwe', '2025-02-22 17:53:40'),
(3, 'judge2', '1234', '2025-02-23 15:16:52'),
(4, 'judge1', 'judge1', '2025-02-23 15:55:07'),
(5, 'judge1', '12345', '2025-02-26 13:30:36');

-- --------------------------------------------------------

--
-- Table structure for table `evaluation_criteria`
--

CREATE TABLE `evaluation_criteria` (
  `id` int(11) NOT NULL,
  `category` varchar(255) DEFAULT NULL,
  `criteria` text DEFAULT NULL,
  `percentage` decimal(5,2) NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `evaluation_criteria`
--

INSERT INTO `evaluation_criteria` (`id`, `category`, `criteria`, `percentage`, `created_at`) VALUES
(4, 'Q&#38;A', 'Stage Presence', 15.00, '2025-02-26 13:27:40'),
(5, 'Q&#38;A', 'Creativity', 25.00, '2025-02-26 13:27:55'),
(7, 'Costume', 'Creativity', 25.00, '2025-02-26 13:28:20');

-- --------------------------------------------------------

--
-- Table structure for table `events`
--

CREATE TABLE `events` (
  `id` int(11) NOT NULL,
  `event_name` varchar(255) NOT NULL,
  `status` enum('Active','Inactive') NOT NULL DEFAULT 'Active',
  `event_logo` varchar(255) DEFAULT NULL,
  `event_banner` varchar(255) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `events`
--

INSERT INTO `events` (`id`, `event_name`, `status`, `event_logo`, `event_banner`) VALUES
(5, 'Example Event 2025', 'Active', '../eventbanner/Barangay.png', '../eventbanner/banner1.png');

-- --------------------------------------------------------

--
-- Table structure for table `officials_tbl`
--

CREATE TABLE `officials_tbl` (
  `id` int(11) NOT NULL,
  `fullname` varchar(255) NOT NULL,
  `position` varchar(255) NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `officials_tbl`
--

INSERT INTO `officials_tbl` (`id`, `fullname`, `position`, `created_at`) VALUES
(9, 'Juan Dela Cruz', 'Barangay Captain', '2025-02-26 13:29:24');

-- --------------------------------------------------------

--
-- Table structure for table `overall_scores_tbl`
--

CREATE TABLE `overall_scores_tbl` (
  `id` int(11) NOT NULL,
  `participant_name` varchar(255) NOT NULL,
  `total_score` decimal(10,2) NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `overall_scores_tbl`
--

INSERT INTO `overall_scores_tbl` (`id`, `participant_name`, `total_score`, `created_at`) VALUES
(1, '5. Ana Marie Chan', 117.00, '2025-02-25 15:22:18'),
(2, '4. Juan Dela Cruzs', 112.00, '2025-02-25 15:22:18'),
(3, '6. Kitty Mae Doe', 107.00, '2025-02-25 15:22:18'),
(4, 'Juan Dela Cruzs', 60.00, '2025-02-25 15:22:18'),
(5, 'Ana Marie Chan', 49.00, '2025-02-25 15:22:18');

-- --------------------------------------------------------

--
-- Table structure for table `participants`
--

CREATE TABLE `participants` (
  `id` int(11) NOT NULL,
  `fullname` varchar(255) NOT NULL,
  `status` enum('Active','Inactive') NOT NULL DEFAULT 'Active',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `participants`
--

INSERT INTO `participants` (`id`, `fullname`, `status`, `created_at`) VALUES
(9, 'Jane Doe Smith', 'Active', '2025-02-26 13:27:14'),
(10, 'Maxine Smith', 'Active', '2025-02-26 13:27:19');

-- --------------------------------------------------------

--
-- Table structure for table `score_results`
--

CREATE TABLE `score_results` (
  `id` int(11) NOT NULL,
  `judge_id` int(11) NOT NULL,
  `judge_name` varchar(255) NOT NULL,
  `participant_name` varchar(255) NOT NULL,
  `category` varchar(100) NOT NULL,
  `criteria` varchar(255) NOT NULL,
  `percentage` float DEFAULT NULL,
  `score` float DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `users`
--

CREATE TABLE `users` (
  `id` int(11) NOT NULL,
  `fullname` varchar(255) NOT NULL,
  `username` varchar(255) NOT NULL,
  `password` varchar(255) NOT NULL,
  `role` enum('Admin','Judge') NOT NULL,
  `status` enum('Active','Inactive') NOT NULL DEFAULT 'Active',
  `passcode` varchar(10) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_general_ci;

--
-- Dumping data for table `users`
--

INSERT INTO `users` (`id`, `fullname`, `username`, `password`, `role`, `status`, `passcode`) VALUES
(1, '', 'admin', '$2y$10$iHMOjjQ1BXwsam02wQx6cuoB0m4sLbGQosCWK7bM1xtumh83L491W', 'Admin', 'Active', '1234'),
(8, 'admin1', 'admin1', '$2y$10$iWHJwDifLqqyMOCfeeqgf.HO0fR88rZ1g90i0PFH3qYeKzdKPyP32', 'Admin', 'Active', ''),
(11, 'Judge 1', 'judge1', '$2y$10$VdXhgPHlzeuGic8Oes31a.UzC06LQHM6f4v3KrR8hm4V80Df3eDu.', 'Judge', 'Active', ''),
(13, 'Judge2', 'judge2', '$2y$10$L1v/YjP7Lt6EFv0hztK1XunxnimSIhMPsJnkyS2INJfJRd/sg0kpq', 'Judge', 'Active', '');

--
-- Indexes for dumped tables
--

--
-- Indexes for table `admin_logs`
--
ALTER TABLE `admin_logs`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `evaluation_criteria`
--
ALTER TABLE `evaluation_criteria`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `events`
--
ALTER TABLE `events`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `officials_tbl`
--
ALTER TABLE `officials_tbl`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `overall_scores_tbl`
--
ALTER TABLE `overall_scores_tbl`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `participants`
--
ALTER TABLE `participants`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `score_results`
--
ALTER TABLE `score_results`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `unique_score` (`judge_id`,`participant_name`,`category`,`criteria`);

--
-- Indexes for table `users`
--
ALTER TABLE `users`
  ADD PRIMARY KEY (`id`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `admin_logs`
--
ALTER TABLE `admin_logs`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;

--
-- AUTO_INCREMENT for table `evaluation_criteria`
--
ALTER TABLE `evaluation_criteria`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=8;

--
-- AUTO_INCREMENT for table `events`
--
ALTER TABLE `events`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;

--
-- AUTO_INCREMENT for table `officials_tbl`
--
ALTER TABLE `officials_tbl`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=10;

--
-- AUTO_INCREMENT for table `overall_scores_tbl`
--
ALTER TABLE `overall_scores_tbl`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;

--
-- AUTO_INCREMENT for table `participants`
--
ALTER TABLE `participants`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=11;

--
-- AUTO_INCREMENT for table `score_results`
--
ALTER TABLE `score_results`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `users`
--
ALTER TABLE `users`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=14;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
