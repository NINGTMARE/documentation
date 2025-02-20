-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: Feb 14, 2025 at 07:41 AM
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
-- Database: `admin_dashboard`
--

-- --------------------------------------------------------

--
-- Table structure for table `attendance`
--

CREATE TABLE `attendance` (
  `id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `department_id` int(11) NOT NULL,
  `date` date NOT NULL,
  `status` enum('present','absent','leave') NOT NULL,
  `arrival_time` time DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `attendance`
--

INSERT INTO `attendance` (`id`, `user_id`, `department_id`, `date`, `status`, `arrival_time`) VALUES
(2, 27, 9, '2024-12-30', 'present', NULL),
(3, 3, 6, '2024-12-31', 'present', NULL),
(4, 3, 6, '2025-01-03', 'present', NULL),
(5, 3, 6, '2025-01-11', 'present', '05:42:13'),
(6, 3, 6, '2025-02-03', 'present', '09:17:39'),
(7, 50, 6, '2025-02-04', 'present', '08:48:02');

-- --------------------------------------------------------

--
-- Table structure for table `departments`
--

CREATE TABLE `departments` (
  `id` int(11) NOT NULL,
  `name` varchar(255) NOT NULL,
  `description` text DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `manager_id` int(11) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `departments`
--

INSERT INTO `departments` (`id`, `name`, `description`, `created_at`, `manager_id`) VALUES
(6, 'CELLULE INFORMATIQUE', 'we are doing in the ITC management', '2024-12-04 14:32:27', 1),
(9, 'CELLULE DE TRADICTION', '', '2024-12-27 14:48:59', 48),
(10, 'mich', 'mich', '2025-01-31 13:44:33', NULL);

-- --------------------------------------------------------

--
-- Table structure for table `department_members`
--

CREATE TABLE `department_members` (
  `id` int(11) NOT NULL,
  `user_id` int(11) DEFAULT NULL,
  `department_id` int(11) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `department_members`
--

INSERT INTO `department_members` (`id`, `user_id`, `department_id`) VALUES
(1, 3, 6),
(2, 27, 9),
(3, 46, 9),
(4, 50, 6);

-- --------------------------------------------------------

--
-- Table structure for table `leave_requests`
--

CREATE TABLE `leave_requests` (
  `id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `start_date` date NOT NULL,
  `end_date` date NOT NULL,
  `reason` text DEFAULT NULL,
  `status` enum('pending','approved','denied') DEFAULT 'pending',
  `manager_id` int(11) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `managers`
--

CREATE TABLE `managers` (
  `id` int(11) NOT NULL,
  `name` varchar(255) NOT NULL,
  `email` varchar(255) NOT NULL,
  `user_id` int(11) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `managers`
--

INSERT INTO `managers` (`id`, `name`, `email`, `user_id`) VALUES
(1, '', '', 4),
(48, 'RYAN', 'ebookevin@gmail.com', 48),
(56, 'KEVIN RN', 'ebookevinryan@gmail.com', 56);

-- --------------------------------------------------------

--
-- Table structure for table `settings`
--

CREATE TABLE `settings` (
  `id` int(11) NOT NULL,
  `site_name` varchar(255) NOT NULL,
  `timezone` varchar(50) NOT NULL,
  `language` varchar(10) NOT NULL,
  `theme` varchar(10) NOT NULL,
  `min_password_length` int(11) NOT NULL,
  `account_lockout` int(11) NOT NULL,
  `smtp_host` varchar(255) NOT NULL,
  `smtp_port` int(11) NOT NULL,
  `email_from` varchar(255) NOT NULL,
  `category` varchar(50) NOT NULL,
  `setting_key` varchar(50) NOT NULL,
  `setting_value` text NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `settings`
--

INSERT INTO `settings` (`id`, `site_name`, `timezone`, `language`, `theme`, `min_password_length`, `account_lockout`, `smtp_host`, `smtp_port`, `email_from`, `category`, `setting_key`, `setting_value`) VALUES
(1, '', '', '', '', 0, 0, '', 0, '', '', '', ''),
(2, '', '', '', '', 0, 0, '', 0, '', 'General', 'site_name', 'My Website'),
(3, '', '', '', '', 0, 0, '', 0, '', 'General', 'timezone', 'UTC'),
(4, '', '', '', '', 0, 0, '', 0, '', 'Appearance', 'theme', 'light'),
(5, '', '', '', '', 0, 0, '', 0, '', 'Security', 'min_password_length', '8');

-- --------------------------------------------------------

--
-- Table structure for table `tasks`
--

CREATE TABLE `tasks` (
  `id` int(11) NOT NULL,
  `employee_id` int(11) NOT NULL,
  `department_id` int(11) NOT NULL,
  `task_title` varchar(255) NOT NULL,
  `task_description` text DEFAULT NULL,
  `deadline` date NOT NULL,
  `status` enum('pending','in progress','completed') DEFAULT 'pending',
  `assigned_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `tasks`
--

INSERT INTO `tasks` (`id`, `employee_id`, `department_id`, `task_title`, `task_description`, `deadline`, `status`, `assigned_at`) VALUES
(1, 3, 6, 'COMPUTER', 'verify the computer of the office', '2024-12-20', 'completed', '2024-12-18 09:48:16'),
(2, 3, 6, 'COMPUTER', 'to be verify in the ministry ', '2024-12-25', 'completed', '2024-12-20 13:17:45'),
(3, 3, 6, 'COMPUTER', 'arrange computer ', '2025-02-12', 'pending', '2025-02-08 18:20:07');

-- --------------------------------------------------------

--
-- Table structure for table `users`
--

CREATE TABLE `users` (
  `id` int(11) NOT NULL,
  `name` varchar(255) DEFAULT NULL,
  `email` varchar(255) DEFAULT NULL,
  `password` varchar(255) DEFAULT NULL,
  `status` enum('approved','pending','banned') DEFAULT 'pending',
  `role` enum('admin','manager','user') DEFAULT 'user',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `department_id` int(11) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `users`
--

INSERT INTO `users` (`id`, `name`, `email`, `password`, `status`, `role`, `created_at`, `department_id`) VALUES
(3, 'kevin', 'eboo@gmail.com', '$2y$10$UQjOfeNgKM5IDQM7ZUf5XO/aWC0Nip3hui0CYzrTcQgaDgb608Qvq', 'approved', 'user', '2024-12-02 16:05:36', NULL),
(4, 'ryan', 'kevin@gmail.com', '$2y$10$E0dc9zLFnufN0gF2GuNoSeA4wtUcfuHw5H1GWWkreUAovB/inv8n6', 'approved', 'manager', '2024-12-03 09:20:53', NULL),
(5, 'KEVIN', 'ryan@gmail.com', '$2y$10$rjb3E2QHzNr18nyTArxLqOf0ABm0niT/Qp.aXW2rqTmzXO/XVoKZK', '', 'admin', '2024-12-05 12:43:07', NULL),
(27, 'dany', 'dany@gmail.com', '$2y$10$XYts3BczeObLzzLaJHeD3OwYJHilyMjiD1NcTh4LD6bCEl3upmx2i', 'approved', 'user', '2024-12-26 11:13:41', NULL),
(46, 'EBOOKEVIN', 'ebooryan@gmail.com', '$2y$10$AzD2/Yct1GY86plrnDKsKu618Vh0I13bOs5NjFJb1ZoccJ5bX9I2.', 'approved', 'user', '2024-12-27 15:17:16', NULL),
(48, 'RYAN', 'ebookevin@gmail.com', '$2y$10$GpkQj/ZscMB2BJ4koEJpkuQCSaIts0Cf0D4A86fCiauU9.FK7.SxC', 'approved', 'manager', '2024-12-29 17:30:48', NULL),
(50, 'NEBA', 'neba@gmail.com', '$2y$10$2kj5gxR6qdZ0aDSNv9xmJ.EC1JKjXI5MHOHm2.SSKys/msV9lMNw.', 'approved', 'user', '2025-01-01 15:08:32', NULL),
(52, 'Michelle', 'stanis@gmail.com', '$2y$10$r9TPH8xMleEnBxv6/FpkqOE9Y4efGQyvDplzpNXS8trYFEcnIB3Ca', '', 'admin', '2025-01-31 12:57:54', NULL),
(56, 'KEVIN RN', 'ebookevinryan@gmail.com', '$2y$10$ShBUZGt6.h5Q0QUnlZbrguVaaFxNZXq5zdWaiSoojv1p3h66Dh5Zu', 'pending', 'manager', '2025-02-13 12:48:00', NULL);

--
-- Triggers `users`
--
DELIMITER $$
CREATE TRIGGER `after_user_insert` AFTER INSERT ON `users` FOR EACH ROW BEGIN
    IF NEW.role = 'manager' THEN
        INSERT INTO managers (id,  name, email, user_id)
        VALUES (NEW.id,  NEW.name, NEW.email, NEW.id);
    END IF;
END
$$
DELIMITER ;

-- --------------------------------------------------------

--
-- Table structure for table `work_reports`
--

CREATE TABLE `work_reports` (
  `id` int(11) NOT NULL,
  `employee_id` int(11) NOT NULL,
  `report_title` varchar(255) NOT NULL,
  `report_description` text DEFAULT NULL,
  `submission_date` date NOT NULL DEFAULT current_timestamp(),
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  `department_id` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `work_reports`
--

INSERT INTO `work_reports` (`id`, `employee_id`, `report_title`, `report_description`, `submission_date`, `created_at`, `updated_at`, `department_id`) VALUES
(7, 3, 'COMPUTER', 'well done', '2025-01-07', '2025-01-07 19:21:16', '2025-01-07 19:21:16', 6),
(8, 3, 'COMPUTER', 'hello', '2025-02-03', '2025-02-03 08:36:07', '2025-02-03 08:36:07', 6);

--
-- Indexes for dumped tables
--

--
-- Indexes for table `attendance`
--
ALTER TABLE `attendance`
  ADD PRIMARY KEY (`id`),
  ADD KEY `user_id` (`user_id`),
  ADD KEY `department_id` (`department_id`);

--
-- Indexes for table `departments`
--
ALTER TABLE `departments`
  ADD PRIMARY KEY (`id`),
  ADD KEY `manager_id` (`manager_id`);

--
-- Indexes for table `department_members`
--
ALTER TABLE `department_members`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `unique_user_department` (`user_id`),
  ADD KEY `department_id` (`department_id`);

--
-- Indexes for table `leave_requests`
--
ALTER TABLE `leave_requests`
  ADD PRIMARY KEY (`id`),
  ADD KEY `user_id` (`user_id`),
  ADD KEY `manager_id` (`manager_id`);

--
-- Indexes for table `managers`
--
ALTER TABLE `managers`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `email` (`email`),
  ADD UNIQUE KEY `user_id` (`user_id`);

--
-- Indexes for table `settings`
--
ALTER TABLE `settings`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `tasks`
--
ALTER TABLE `tasks`
  ADD PRIMARY KEY (`id`),
  ADD KEY `employee_id` (`employee_id`),
  ADD KEY `department_id` (`department_id`);

--
-- Indexes for table `users`
--
ALTER TABLE `users`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `unique_email` (`email`),
  ADD KEY `department_id` (`department_id`);

--
-- Indexes for table `work_reports`
--
ALTER TABLE `work_reports`
  ADD PRIMARY KEY (`id`),
  ADD KEY `employee_id` (`employee_id`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `attendance`
--
ALTER TABLE `attendance`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=8;

--
-- AUTO_INCREMENT for table `departments`
--
ALTER TABLE `departments`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=11;

--
-- AUTO_INCREMENT for table `department_members`
--
ALTER TABLE `department_members`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- AUTO_INCREMENT for table `leave_requests`
--
ALTER TABLE `leave_requests`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `managers`
--
ALTER TABLE `managers`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=57;

--
-- AUTO_INCREMENT for table `settings`
--
ALTER TABLE `settings`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;

--
-- AUTO_INCREMENT for table `tasks`
--
ALTER TABLE `tasks`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT for table `users`
--
ALTER TABLE `users`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=57;

--
-- AUTO_INCREMENT for table `work_reports`
--
ALTER TABLE `work_reports`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=9;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `attendance`
--
ALTER TABLE `attendance`
  ADD CONSTRAINT `attendance_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `attendance_ibfk_2` FOREIGN KEY (`department_id`) REFERENCES `departments` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `departments`
--
ALTER TABLE `departments`
  ADD CONSTRAINT `departments_ibfk_1` FOREIGN KEY (`manager_id`) REFERENCES `managers` (`id`) ON DELETE SET NULL;

--
-- Constraints for table `department_members`
--
ALTER TABLE `department_members`
  ADD CONSTRAINT `department_members_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`),
  ADD CONSTRAINT `department_members_ibfk_2` FOREIGN KEY (`department_id`) REFERENCES `departments` (`id`);

--
-- Constraints for table `leave_requests`
--
ALTER TABLE `leave_requests`
  ADD CONSTRAINT `leave_requests_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`),
  ADD CONSTRAINT `leave_requests_ibfk_2` FOREIGN KEY (`manager_id`) REFERENCES `users` (`id`);

--
-- Constraints for table `managers`
--
ALTER TABLE `managers`
  ADD CONSTRAINT `managers_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `tasks`
--
ALTER TABLE `tasks`
  ADD CONSTRAINT `tasks_ibfk_1` FOREIGN KEY (`employee_id`) REFERENCES `users` (`id`),
  ADD CONSTRAINT `tasks_ibfk_2` FOREIGN KEY (`department_id`) REFERENCES `departments` (`id`);

--
-- Constraints for table `users`
--
ALTER TABLE `users`
  ADD CONSTRAINT `users_ibfk_1` FOREIGN KEY (`department_id`) REFERENCES `departments` (`id`) ON DELETE SET NULL;

--
-- Constraints for table `work_reports`
--
ALTER TABLE `work_reports`
  ADD CONSTRAINT `work_reports_ibfk_1` FOREIGN KEY (`employee_id`) REFERENCES `users` (`id`) ON DELETE CASCADE;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
