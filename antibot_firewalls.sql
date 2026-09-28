-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: Sep 28, 2026 at 05:43 PM
-- Server version: 10.4.32-MariaDB
-- PHP Version: 8.0.30

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Database: `antibot_firewalls`
--

-- --------------------------------------------------------

--
-- Table structure for table `admin_users`
--

CREATE TABLE `admin_users` (
  `id` int(11) NOT NULL,
  `username` varchar(100) NOT NULL,
  `password` varchar(255) NOT NULL,
  `full_name` varchar(150) DEFAULT NULL,
  `role` varchar(50) DEFAULT 'Security Administrator',
  `status` enum('Active','Blocked') DEFAULT 'Active',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `admin_users`
--

INSERT INTO `admin_users` (`id`, `username`, `password`, `full_name`, `role`, `status`, `created_at`) VALUES
(1, 'admin', '0e7517141fb53f21ee439b355b5a1d0a', 'Security Administrator', 'Security Administrator', 'Active', '2026-09-26 10:01:19');

-- --------------------------------------------------------

--
-- Table structure for table `blocked_ips`
--

CREATE TABLE `blocked_ips` (
  `id` int(11) NOT NULL,
  `ip_address` varchar(45) NOT NULL,
  `reason` varchar(255) DEFAULT NULL,
  `status` enum('Active','Released') DEFAULT 'Active',
  `blocked_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `blocked_ips`
--

INSERT INTO `blocked_ips` (`id`, `ip_address`, `reason`, `status`, `blocked_at`) VALUES
(1, '192.168.1.20', 'Automated bot activity detected', 'Active', '2026-09-26 10:11:46'),
(2, '192.168.1.44', 'Honeypot trigger detected', 'Active', '2026-09-26 10:11:46'),
(3, '192.168.1.63', 'Repeated suspicious requests', 'Active', '2026-09-26 10:11:46');

-- --------------------------------------------------------

--
-- Table structure for table `security_logs`
--

CREATE TABLE `security_logs` (
  `id` int(11) NOT NULL,
  `event_type` varchar(100) NOT NULL,
  `ip_address` varchar(45) NOT NULL,
  `severity` enum('LOW','MEDIUM','HIGH','CRITICAL') DEFAULT 'LOW',
  `action_taken` varchar(150) DEFAULT NULL,
  `user_agent` text DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `security_logs`
--

INSERT INTO `security_logs` (`id`, `event_type`, `ip_address`, `severity`, `action_taken`, `user_agent`, `created_at`) VALUES
(1, 'BOT DETECTED', '192.168.1.20', 'HIGH', 'Request Blocked', 'Automated Scanner', '2026-09-26 10:10:48'),
(2, 'RATE LIMIT VIOLATION', '192.168.1.35', 'MEDIUM', 'Request Throttled', 'Mozilla/5.0', '2026-09-26 10:10:48'),
(3, 'HONEYPOT TRIGGERED', '192.168.1.44', 'HIGH', 'Threat Neutralized', 'Bot Crawler', '2026-09-26 10:10:48'),
(4, 'CAPTCHA VERIFICATION', '192.168.1.51', 'LOW', 'Request Verified', 'Mozilla/5.0', '2026-09-26 10:10:48'),
(5, 'SUSPICIOUS IP DETECTED', '192.168.1.63', 'HIGH', 'IP Restricted', 'Suspicious Client', '2026-09-26 10:10:48'),
(6, 'LEGITIMATE REQUEST', '192.168.1.72', 'LOW', 'Request Allowed', 'Mozilla/5.0', '2026-09-26 10:10:48'),
(7, 'FORM VALIDATION PASSED', '192.168.1.81', 'LOW', 'Request Allowed', 'Mozilla/5.0', '2026-09-26 10:10:48'),
(8, 'VALID REQUEST VERIFIED', '::1', 'LOW', 'Request Allowed', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/154.0.0.0 Safari/537.36 Edg/154.0.0.0', '2026-09-26 10:50:47');

-- --------------------------------------------------------

--
-- Table structure for table `security_settings`
--

CREATE TABLE `security_settings` (
  `id` int(11) NOT NULL,
  `setting_name` varchar(100) NOT NULL,
  `setting_value` varchar(255) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `security_settings`
--

INSERT INTO `security_settings` (`id`, `setting_name`, `setting_value`) VALUES
(1, 'captcha_protection', 'Enabled'),
(2, 'honeypot_protection', 'Enabled'),
(3, 'rate_limiting', 'Enabled'),
(4, 'ip_monitoring', 'Enabled'),
(5, 'suspicious_activity_detection', 'Enabled'),
(6, 'form_validation', 'Enabled');

-- --------------------------------------------------------

--
-- Table structure for table `submissions`
--

CREATE TABLE `submissions` (
  `id` int(11) NOT NULL,
  `ip_address` varchar(45) NOT NULL,
  `form_type` varchar(100) DEFAULT 'Web Form',
  `verification_status` enum('Verified','Suspicious','Blocked') DEFAULT 'Verified',
  `threat_score` int(11) DEFAULT 0,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `submissions`
--

INSERT INTO `submissions` (`id`, `ip_address`, `form_type`, `verification_status`, `threat_score`, `created_at`) VALUES
(1, '192.168.1.20', 'Contact Form', 'Blocked', 95, '2026-09-26 10:11:19'),
(2, '192.168.1.35', 'Registration Form', 'Suspicious', 72, '2026-09-26 10:11:19'),
(3, '192.168.1.44', 'Feedback Form', 'Blocked', 91, '2026-09-26 10:11:19'),
(4, '192.168.1.51', 'Contact Form', 'Verified', 5, '2026-09-26 10:11:19'),
(5, '192.168.1.63', 'Login Form', 'Blocked', 88, '2026-09-26 10:11:19'),
(6, '192.168.1.72', 'Registration Form', 'Verified', 8, '2026-09-26 10:11:19'),
(7, '192.168.1.81', 'Feedback Form', 'Verified', 3, '2026-09-26 10:11:19'),
(8, '192.168.1.92', 'Contact Form', 'Verified', 6, '2026-09-26 10:11:19'),
(9, '192.168.1.105', 'Login Form', 'Suspicious', 65, '2026-09-26 10:11:19'),
(10, '192.168.1.118', 'Contact Form', 'Verified', 4, '2026-09-26 10:11:19'),
(11, '::1', 'Contact Form', 'Verified', 0, '2026-09-26 10:50:47');

-- --------------------------------------------------------

--
-- Table structure for table `users`
--

CREATE TABLE `users` (
  `id` int(11) NOT NULL,
  `username` varchar(100) NOT NULL,
  `full_name` varchar(150) NOT NULL,
  `email` varchar(150) NOT NULL,
  `password` varchar(255) NOT NULL,
  `status` enum('Active','Blocked') DEFAULT 'Active',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `users`
--

INSERT INTO `users` (`id`, `username`, `full_name`, `email`, `password`, `status`, `created_at`) VALUES
(1, 'vaishu', 'Vaishnavi', 'vaishu@gmail.com', '25f9e794323b453885f5181f1b624d0b', 'Active', '2026-09-26 11:01:58'),
(2, 'testuser', 'Test User', 'testuser@gmail.com', '448ddd517d3abb70045aea6929f02367', 'Active', '2026-09-26 11:04:42'),
(3, 'pv', 'pv', 'pv@gmail.com', '25f9e794323b453885f5181f1b624d0b', 'Active', '2026-09-26 11:09:20');

--
-- Indexes for dumped tables
--

--
-- Indexes for table `admin_users`
--
ALTER TABLE `admin_users`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `username` (`username`);

--
-- Indexes for table `blocked_ips`
--
ALTER TABLE `blocked_ips`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `ip_address` (`ip_address`);

--
-- Indexes for table `security_logs`
--
ALTER TABLE `security_logs`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `security_settings`
--
ALTER TABLE `security_settings`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `setting_name` (`setting_name`);

--
-- Indexes for table `submissions`
--
ALTER TABLE `submissions`
  ADD PRIMARY KEY (`id`);

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
-- AUTO_INCREMENT for table `admin_users`
--
ALTER TABLE `admin_users`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT for table `blocked_ips`
--
ALTER TABLE `blocked_ips`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- AUTO_INCREMENT for table `security_logs`
--
ALTER TABLE `security_logs`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=9;

--
-- AUTO_INCREMENT for table `security_settings`
--
ALTER TABLE `security_settings`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=7;

--
-- AUTO_INCREMENT for table `submissions`
--
ALTER TABLE `submissions`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=12;

--
-- AUTO_INCREMENT for table `users`
--
ALTER TABLE `users`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
