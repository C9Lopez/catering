-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: Feb 25, 2025 at 07:20 PM
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
-- Database: `catering_db`
--

-- --------------------------------------------------------

--
-- Table structure for table `admin_user`
--

CREATE TABLE `admin_user` (
  `admin_id` int(11) NOT NULL,
  `first_name` varchar(50) NOT NULL,
  `middle_name` varchar(50) DEFAULT NULL,
  `last_name` varchar(50) NOT NULL,
  `birthdate` date NOT NULL,
  `gender` varchar(50) NOT NULL,
  `address` varchar(255) NOT NULL,
  `contact_no` varchar(50) DEFAULT NULL,
  `email` varchar(255) NOT NULL,
  `profile_picture` varchar(255) DEFAULT NULL,
  `password` varchar(255) NOT NULL,
  `role` varchar(50) DEFAULT 'admin',
  `status` varchar(50) DEFAULT 'active',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `admin_user`
--

INSERT INTO `admin_user` (`admin_id`, `first_name`, `middle_name`, `last_name`, `birthdate`, `gender`, `address`, `contact_no`, `email`, `profile_picture`, `password`, `role`, `status`, `created_at`, `updated_at`) VALUES
(6, '123', 'sqd', 'asd', '2003-02-10', 'Male', 'Philippines', '09452291123', 'test@111gmail.com', NULL, '$2y$10$CPGWDH5weqUoZLW9qrzpB.6U4yD8/qFrKKyCFXOp2MKmLKZlmEfQa', 'admin', 'active', '2025-02-21 17:40:25', '2025-02-21 17:40:25'),
(7, 'Don', 'Admin', 'Lopez', '2003-03-09', 'Male', 'Philippines', '092222222222', 'ljayson785@gmail.com', NULL, '$2y$10$Nn.fsxrsgB8oA1SAyDMw2.ukoDfEajJJUFobw7MmYHghxfs8KUas6', 'admin', 'active', '2025-02-21 17:40:44', '2025-02-21 17:40:44'),
(8, 'Don', 'Riel', 'Lopez', '2025-02-06', 'Male', 'Philippines', '123213', 'DonLopez@test.com', NULL, '$2y$10$TS9fr0x2FKsyHxjKHyze6ePlminXGUrPC84flW/DtObaS/usgORkq', 'admin', 'active', '2025-02-23 10:29:36', '2025-02-23 10:29:36'),
(9, 'Don', 'Rield', 'Lopez', '2025-02-06', 'Male', 'Philippines', '12312312', 'huncho@test.com', NULL, '$2y$10$a.lL5nCDjtb9UtDCPuZwfucWWHhWzdRw8/zhjUTeYlMIACaLGaGrC', 'admin', 'active', '2025-02-23 10:30:37', '2025-02-23 10:30:37');

-- --------------------------------------------------------

--
-- Table structure for table `announcements`
--

CREATE TABLE `announcements` (
  `id` int(11) NOT NULL,
  `title` varchar(255) NOT NULL,
  `description` text NOT NULL,
  `media_path` varchar(255) NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `status` enum('live','preview') DEFAULT 'preview'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `announcements`
--

INSERT INTO `announcements` (`id`, `title`, `description`, `media_path`, `created_at`, `status`) VALUES
(137, '   ', '   ', './uploads/1740346148_OPENING.png', '2025-02-23 21:29:08', 'preview'),
(138, '   ', '   ', './uploads/1740346329_OPENING.png', '2025-02-23 21:32:09', 'preview'),
(139, '   ', '   ', './uploads/1740346330_OPENING.png', '2025-02-23 21:32:10', 'preview'),
(140, 'CONFY', 'HAKDOG', './uploads/1740381112_John_Gokongwei.jpg', '2025-02-24 07:11:52', 'live');

-- --------------------------------------------------------

--
-- Table structure for table `catering_packages`
--

CREATE TABLE `catering_packages` (
  `package_id` int(11) NOT NULL,
  `name` varchar(255) NOT NULL,
  `description` text DEFAULT NULL,
  `price` decimal(10,2) NOT NULL,
  `min_guests` int(11) NOT NULL,
  `max_guests` int(11) NOT NULL,
  `includes` text DEFAULT NULL,
  `image_url` varchar(255) DEFAULT NULL,
  `category` varchar(50) NOT NULL,
  `is_available` tinyint(1) DEFAULT 1,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `catering_packages`
--

INSERT INTO `catering_packages` (`package_id`, `name`, `description`, `price`, `min_guests`, `max_guests`, `includes`, `image_url`, `category`, `is_available`, `created_at`, `updated_at`) VALUES
(17, 'premium PACKAGE 1', 'PREMIUM IPSUM\r\nPREMIUM IPSUM PREMIUM IPSUM\r\nPREMIUM IPSUM PREMIUM IPSUM\r\nPREMIUM IPSUM PREMIUM IPSUM\r\nPREMIUM IPSUM PREMIUM IPSUM PREMIUM IPSUM', 898921.00, 0, 0, NULL, NULL, 'Wedding Catering', 1, '2025-02-21 17:42:35', '2025-02-21 17:42:35'),
(18, 'Pancit 1', 'ADA\r\nAdadsd\r\nasdasd\r\nasdasd\r\nadasda', 123131.00, 0, 0, NULL, NULL, 'Debut Catering', 1, '2025-02-21 17:43:05', '2025-02-21 17:43:05'),
(19, 'PCKAGE1', 'SADASDASDA', 12312312.00, 0, 0, NULL, NULL, 'Corporate Catering', 1, '2025-02-21 17:43:30', '2025-02-21 17:43:30'),
(20, 'asd', 'ASDASDA', 123123.00, 0, 0, NULL, NULL, 'Childrens Party Catering', 1, '2025-02-21 17:43:54', '2025-02-21 17:43:54'),
(21, 'TEST1', 'TEST', 1341212.00, 0, 0, NULL, NULL, 'Private Catering', 1, '2025-02-21 17:44:18', '2025-02-21 17:44:18'),
(22, 'Jersey_CDF_Shirt_Girl', 'asd', 123441.00, 0, 0, NULL, NULL, 'Wedding Catering', 1, '2025-02-22 17:14:47', '2025-02-22 17:14:47');

-- --------------------------------------------------------

--
-- Table structure for table `chat_messages`
--

CREATE TABLE `chat_messages` (
  `id` int(11) NOT NULL,
  `order_id` int(11) DEFAULT NULL,
  `sender` varchar(255) DEFAULT NULL,
  `message` text DEFAULT NULL,
  `created_at` datetime DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `event_bookings`
--

CREATE TABLE `event_bookings` (
  `booking_id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `package_id` int(11) NOT NULL,
  `location` varchar(255) NOT NULL,
  `event_type` varchar(100) NOT NULL,
  `event_date` date NOT NULL,
  `event_time` time NOT NULL,
  `setup_time` time NOT NULL,
  `number_of_guests` int(11) NOT NULL,
  `total_amount` decimal(10,2) NOT NULL,
  `payment_status` varchar(50) DEFAULT 'pending',
  `booking_status` varchar(50) DEFAULT 'pending',
  `additional_requests` text DEFAULT NULL,
  `special_requirements` text DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `event_bookings`
--

INSERT INTO `event_bookings` (`booking_id`, `user_id`, `package_id`, `location`, `event_type`, `event_date`, `event_time`, `setup_time`, `number_of_guests`, `total_amount`, `payment_status`, `booking_status`, `additional_requests`, `special_requirements`, `created_at`, `updated_at`) VALUES
(1, 6, 17, 'PHILIPPINES', 'Wedding Catering', '2025-02-20', '14:09:00', '04:10:00', 1232, 898.00, 'pending', 'pending', 'NONE', 'NONE', '2025-02-25 18:11:26', '2025-02-25 18:11:26');

-- --------------------------------------------------------

--
-- Table structure for table `menus`
--

CREATE TABLE `menus` (
  `id` int(11) NOT NULL,
  `service_type` varchar(50) NOT NULL,
  `category` varchar(50) NOT NULL,
  `title` varchar(255) NOT NULL,
  `description` text DEFAULT NULL,
  `image_path` varchar(255) NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `menus`
--

INSERT INTO `menus` (`id`, `service_type`, `category`, `title`, `description`, `image_path`, `created_at`) VALUES
(44, 'private', 'Appetizers', 'testiong', 'testing', './uploads/1740493675_Modern_room.png', '2025-02-25 14:27:55'),
(45, 'private', 'Appetizers', 'testiong', 'testing', './uploads/1740493729_Modern_room.png', '2025-02-25 14:28:49'),
(46, 'private', 'Appetizers', 'testiong', 'testing', './uploads/1740493768_Modern_room.png', '2025-02-25 14:29:28'),
(47, 'wedding', 'Soups', 'werw', 'sdfsdf', './uploads/1740493778_image_(4).png', '2025-02-25 14:29:38'),
(48, 'wedding', 'Soups', 'WED', 'WED', './uploads/1740493971_image_(3).png', '2025-02-25 14:32:51'),
(49, 'private', 'Appetizers', 'PRIVATE', 'PRIVATE', './uploads/1740493991_image_(3).png', '2025-02-25 14:33:11'),
(50, 'debut', 'Appetizers', 'dbeut', 'debut', './uploads/1740494137_image_(2).png', '2025-02-25 14:35:37'),
(51, 'debut', 'Appetizers', 'dbeut', 'debut', './uploads/1740494163_image_(2).png', '2025-02-25 14:36:03'),
(52, 'debut', 'Appetizers', 'dbeut', 'debut', './uploads/1740494183_image_(2).png', '2025-02-25 14:36:23'),
(53, 'debut', 'Appetizers', 'dbeut', 'debut', './uploads/1740494238_image_(2).png', '2025-02-25 14:37:18'),
(54, 'debut', 'Appetizers', 'dbeut', 'debut', './uploads/1740494243_image_(2).png', '2025-02-25 14:37:23'),
(55, 'debut', 'Appetizers', 'dbeut', 'debut', './uploads/1740494265_image_(2).png', '2025-02-25 14:37:45'),
(56, 'debut', 'Appetizers', 'dbeut', 'debut', './uploads/1740494301_image_(2).png', '2025-02-25 14:38:21'),
(57, 'debut', 'Appetizers', 'dbeut', 'debut', './uploads/1740494310_image_(2).png', '2025-02-25 14:38:30'),
(58, 'debut', 'Appetizers', 'dbeut', 'debut', './uploads/1740494318_image_(2).png', '2025-02-25 14:38:38'),
(59, 'debut', 'Appetizers', 'dbeut', 'debut', './uploads/1740494328_image_(2).png', '2025-02-25 14:38:48'),
(60, 'debut', 'Appetizers', 'dbeut', 'debut', './uploads/1740494332_image_(2).png', '2025-02-25 14:38:52'),
(61, 'debut', 'Appetizers', 'dbeut', 'debut', './uploads/1740494332_image_(2).png', '2025-02-25 14:38:52'),
(62, 'debut', 'Appetizers', 'dbeut', 'debut', './uploads/1740494346_image_(2).png', '2025-02-25 14:39:06'),
(63, 'debut', 'Appetizers', 'dbeut', 'debut', './uploads/1740494347_image_(2).png', '2025-02-25 14:39:07'),
(64, 'debut', 'Appetizers', 'dbeut', 'debut', './uploads/1740494356_image_(2).png', '2025-02-25 14:39:16'),
(65, 'debut', 'Appetizers', 'dbeut', 'debut', './uploads/1740494358_image_(2).png', '2025-02-25 14:39:18'),
(66, 'debut', 'Appetizers', 'dbeut', 'debut', './uploads/1740494379_image_(2).png', '2025-02-25 14:39:39'),
(67, 'debut', 'Appetizers', 'dbeut', 'debut', './uploads/1740494411_image_(2).png', '2025-02-25 14:40:11'),
(68, 'debut', 'Appetizers', 'dbeut', 'debut', './uploads/1740494426_image_(2).png', '2025-02-25 14:40:26'),
(69, 'debut', 'Appetizers', 'dbeut', 'debut', './uploads/1740494427_image_(2).png', '2025-02-25 14:40:27'),
(70, 'debut', 'Appetizers', 'dbeut', 'debut', './uploads/1740494440_image_(2).png', '2025-02-25 14:40:40'),
(71, 'debut', 'Appetizers', 'dbeut', 'debut', './uploads/1740494442_image_(2).png', '2025-02-25 14:40:42'),
(72, 'corporate', 'Appetizers', 'CORPORATE', 'CORPORATE', './uploads/1740494475_image_(4).png', '2025-02-25 14:41:15'),
(73, 'corporate', 'Appetizers', 'CORPORATE', 'CORPORATE', './uploads/1740494496_image_(4).png', '2025-02-25 14:41:36'),
(74, 'corporate', 'Appetizers', 'CORPORATE', 'CORPORATE', './uploads/1740494526_image_(4).png', '2025-02-25 14:42:06'),
(75, 'corporate', 'Appetizers', 'CORPORATE', 'CORPORATE', './uploads/1740494548_image_(4).png', '2025-02-25 14:42:28'),
(76, 'corporate', 'Appetizers', 'CORPORATE', 'CORPORATE', './uploads/1740494599_image_(4).png', '2025-02-25 14:43:19'),
(77, 'corporate', 'Appetizers', 'CORPORATE', 'CORPORATE', './uploads/1740494617_image_(4).png', '2025-02-25 14:43:37'),
(78, 'childrens', 'Appetizers', 'asdfsdf', 'sdfdfdfd', './uploads/1740494633_image_(3).png', '2025-02-25 14:43:53'),
(79, 'childrens', 'Appetizers', 'asdfsdf', 'sdfdfdfd', './uploads/1740494648_image_(3).png', '2025-02-25 14:44:08'),
(80, 'childrens', 'Appetizers', 'asdfsdf', 'sdfdfdfd', './uploads/1740494663_image_(3).png', '2025-02-25 14:44:23'),
(81, 'childrens', 'Appetizers', 'asdfsdf', 'sdfdfdfd', './uploads/1740494698_image_(3).png', '2025-02-25 14:44:58'),
(82, 'childrens', 'Appetizers', 'asdfsdf', 'sdfdfdfd', './uploads/1740494728_image_(3).png', '2025-02-25 14:45:28'),
(83, 'childrens', 'Appetizers', 'asdfsdf', 'sdfdfdfd', './uploads/1740494736_image_(3).png', '2025-02-25 14:45:36'),
(84, 'childrens', 'Appetizers', 'asdfsdf', 'sdfdfdfd', './uploads/1740494739_image_(3).png', '2025-02-25 14:45:39'),
(85, 'childrens', 'Appetizers', 'asdfsdf', 'sdfdfdfd', './uploads/1740494765_image_(3).png', '2025-02-25 14:46:05');

-- --------------------------------------------------------

--
-- Table structure for table `notifications`
--

CREATE TABLE `notifications` (
  `notification_id` int(11) NOT NULL,
  `user_id` int(11) DEFAULT NULL,
  `admin_id` int(11) DEFAULT NULL,
  `booking_id` int(11) DEFAULT NULL,
  `title` varchar(255) NOT NULL,
  `message` text NOT NULL,
  `type` varchar(50) DEFAULT 'info',
  `is_read` tinyint(1) DEFAULT 0,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `reviews`
--

CREATE TABLE `reviews` (
  `review_id` int(11) NOT NULL,
  `booking_id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `rating` int(11) NOT NULL,
  `comment` text DEFAULT NULL,
  `status` varchar(50) DEFAULT 'pending',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `users`
--

CREATE TABLE `users` (
  `user_id` int(11) NOT NULL,
  `first_name` varchar(50) NOT NULL,
  `middle_name` varchar(50) DEFAULT NULL,
  `last_name` varchar(50) NOT NULL,
  `birthdate` date NOT NULL,
  `gender` varchar(50) NOT NULL,
  `address` varchar(255) NOT NULL,
  `contact_no` varchar(50) DEFAULT NULL,
  `email` varchar(255) NOT NULL,
  `profile_picture` varchar(255) DEFAULT 'default.jpg',
  `password` varchar(255) NOT NULL,
  `status` varchar(50) DEFAULT 'active',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `users`
--

INSERT INTO `users` (`user_id`, `first_name`, `middle_name`, `last_name`, `birthdate`, `gender`, `address`, `contact_no`, `email`, `profile_picture`, `password`, `status`, `created_at`, `updated_at`) VALUES
(1, '123', 'Riel', 'asd', '2025-02-06', 'Male', 'etc ipsum', '09452291123', 'test@asd.com', NULL, '$2y$10$8cBQfuCeIwYImWcVAQG0l.zwcShEOPOn928hDn0YxT6Nc0YLx8.HO', 'active', '2025-02-08 23:16:09', '2025-02-08 23:16:09'),
(2, 'Don', 'Riel', 'Lopez', '2025-02-06', 'Male', 'qdasd', 'asdasd', 'test@asda.com', NULL, '$2y$10$mI8fNQ7FM23aWijlu2zMZOzr5oz7njLT.Zzu7bsnpl0wMiLCc.mou', 'active', '2025-02-08 23:20:33', '2025-02-08 23:20:33'),
(3, 'Test ', 'testmname', 'test Lname', '2010-06-09', 'Male', 'test municipality, test barangay, street, block', '09123456789', 'test@test.com', '1739099633_download__6_-removebg-preview.png', '$2y$10$8pFZjqPvlvZOt0tVkbdi1eAic4IKsEutsmNLANllAwzwmwClpu2aq', 'active', '2025-02-09 10:40:11', '2025-02-09 11:20:05'),
(4, 'Don', 'Riel', 'Lopez', '2025-02-14', 'Male', 'Philippines', '09452291123', 'ljayson785@gmail.com', '1739765370_DALL·E 2025-02-12 17.46.27 - A modern, bold logo for a Roblox clothing store called \'C9\'s Clothing Hub\'. The logo should feature the text \'C9\'s Clothing Hub\' in a stylish and slee.webp', '$2y$10$rD4RXzvy2UxyDcLvfCCta.p0AI.Gz/QPmH2cn1jJ4qq2fgiEAD/s2', 'active', '2025-02-17 04:03:12', '2025-02-17 04:09:30'),
(5, '123', 'sqd', 'sqd', '2025-02-07', 'Male', 'Philippines', '12390812', 'bongbong@test.com', 'default.jpg', '$2y$10$s7EiVedxM2BAe0yDJCmiZuY4FZpntVOqNo2Zj4/aeFoM6plOwN/mm', 'active', '2025-02-17 12:57:54', '2025-02-17 12:57:54'),
(6, 'don', 'lopez', 'lopez', '2002-02-02', 'Male', 'phi', '0922222', 'huncho@test.com', 'default.jpg', '$2y$10$fN8oXsxmXnzolQ4a3KTez.X3Rvqgxipq0XHf2oFtEr0ccH71Ye/HC', 'active', '2025-02-23 20:49:49', '2025-02-23 20:49:49');

--
-- Indexes for dumped tables
--

--
-- Indexes for table `admin_user`
--
ALTER TABLE `admin_user`
  ADD PRIMARY KEY (`admin_id`),
  ADD UNIQUE KEY `email` (`email`);

--
-- Indexes for table `announcements`
--
ALTER TABLE `announcements`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `catering_packages`
--
ALTER TABLE `catering_packages`
  ADD PRIMARY KEY (`package_id`);

--
-- Indexes for table `chat_messages`
--
ALTER TABLE `chat_messages`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `event_bookings`
--
ALTER TABLE `event_bookings`
  ADD PRIMARY KEY (`booking_id`),
  ADD KEY `user_id` (`user_id`),
  ADD KEY `package_id` (`package_id`);

--
-- Indexes for table `menus`
--
ALTER TABLE `menus`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `notifications`
--
ALTER TABLE `notifications`
  ADD PRIMARY KEY (`notification_id`),
  ADD KEY `user_id` (`user_id`),
  ADD KEY `admin_id` (`admin_id`),
  ADD KEY `booking_id` (`booking_id`);

--
-- Indexes for table `reviews`
--
ALTER TABLE `reviews`
  ADD PRIMARY KEY (`review_id`),
  ADD KEY `booking_id` (`booking_id`),
  ADD KEY `user_id` (`user_id`);

--
-- Indexes for table `users`
--
ALTER TABLE `users`
  ADD PRIMARY KEY (`user_id`),
  ADD UNIQUE KEY `email` (`email`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `admin_user`
--
ALTER TABLE `admin_user`
  MODIFY `admin_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=10;

--
-- AUTO_INCREMENT for table `announcements`
--
ALTER TABLE `announcements`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=141;

--
-- AUTO_INCREMENT for table `catering_packages`
--
ALTER TABLE `catering_packages`
  MODIFY `package_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=23;

--
-- AUTO_INCREMENT for table `chat_messages`
--
ALTER TABLE `chat_messages`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `event_bookings`
--
ALTER TABLE `event_bookings`
  MODIFY `booking_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT for table `menus`
--
ALTER TABLE `menus`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=86;

--
-- AUTO_INCREMENT for table `notifications`
--
ALTER TABLE `notifications`
  MODIFY `notification_id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `reviews`
--
ALTER TABLE `reviews`
  MODIFY `review_id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `users`
--
ALTER TABLE `users`
  MODIFY `user_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=7;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `event_bookings`
--
ALTER TABLE `event_bookings`
  ADD CONSTRAINT `event_bookings_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`user_id`),
  ADD CONSTRAINT `event_bookings_ibfk_2` FOREIGN KEY (`package_id`) REFERENCES `catering_packages` (`package_id`);

--
-- Constraints for table `notifications`
--
ALTER TABLE `notifications`
  ADD CONSTRAINT `notifications_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`user_id`),
  ADD CONSTRAINT `notifications_ibfk_2` FOREIGN KEY (`admin_id`) REFERENCES `admin_user` (`admin_id`),
  ADD CONSTRAINT `notifications_ibfk_3` FOREIGN KEY (`booking_id`) REFERENCES `event_bookings` (`booking_id`);

--
-- Constraints for table `reviews`
--
ALTER TABLE `reviews`
  ADD CONSTRAINT `reviews_ibfk_1` FOREIGN KEY (`booking_id`) REFERENCES `event_bookings` (`booking_id`),
  ADD CONSTRAINT `reviews_ibfk_2` FOREIGN KEY (`user_id`) REFERENCES `users` (`user_id`);
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
