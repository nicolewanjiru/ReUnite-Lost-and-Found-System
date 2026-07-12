-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: Jul 07, 2026 at 10:34 AM
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
-- Database: `reunite_db`
--

-- --------------------------------------------------------

--
-- Table structure for table `claims`
--

CREATE TABLE `claims` (
  `claim_id` int(11) NOT NULL,
  `item_id` int(11) NOT NULL,
  `lost_item_id` int(11) DEFAULT NULL,
  `claimant_id` int(11) NOT NULL DEFAULT 0,
  `user_id` int(11) NOT NULL,
  `proof` text DEFAULT NULL,
  `proof_photo` varchar(255) DEFAULT NULL,
  `match_score` decimal(5,2) NOT NULL DEFAULT 0.00,
  `status` varchar(20) NOT NULL DEFAULT 'pending',
  `admin_note` text DEFAULT NULL,
  `date_claimed` datetime NOT NULL DEFAULT current_timestamp(),
  `date_decided` datetime DEFAULT NULL,
  `claim_status` enum('Pending','Approved','Rejected') DEFAULT 'Pending',
  `claim_date` date NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `claims`
--

INSERT INTO `claims` (`claim_id`, `item_id`, `lost_item_id`, `claimant_id`, `user_id`, `proof`, `proof_photo`, `match_score`, `status`, `admin_note`, `date_claimed`, `date_decided`, `claim_status`, `claim_date`) VALUES
(1, 1, NULL, 1, 1, 'It has a cherry sticker on it', NULL, 0.00, 'approved', '', '2026-07-05 17:15:48', '2026-07-06 19:01:22', 'Pending', '2026-07-02');

-- --------------------------------------------------------

--
-- Table structure for table `items`
--

CREATE TABLE `items` (
  `item_id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `item_name` varchar(100) NOT NULL,
  `description` text DEFAULT NULL,
  `category` varchar(50) DEFAULT NULL,
  `location` varchar(100) DEFAULT NULL,
  `status` enum('Lost','Found','Claimed','Returned','Donated') DEFAULT 'Lost',
  `date_reported` date NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `items`
--

INSERT INTO `items` (`item_id`, `user_id`, `item_name`, `description`, `category`, `location`, `status`, `date_reported`) VALUES
(1, 1, 'HP Laptop', 'Black HP Laptop with stickers', 'Electronics', 'Library', '', '2026-06-25'),
(2, 1, 'Student ID', 'Blue Card holder with yellow astronaut', 'Documents', 'Cafeteria', 'Lost', '2026-06-25'),
(4, 1, 'Hoodie', 'Green Hoodie with WARSAW logo', 'Clothing', 'LT 3', 'Lost', '2026-06-25'),
(5, 1, 'HP Laptop', 'Black and has a bunch of stickers on it', 'lost', 'LT 3', '', '2026-07-01'),
(6, 1, 'HP Laptop', 'Black laptop with a bunch of stickers', 'lost', 'LT 3', '', '2026-07-01'),
(7, 1, 'Water bottle', 'Black and white written SPORTS MODE half filled with water', 'lost', 'Cafeteria', '', '2026-07-02'),
(8, 1, 'Water bottle', 'pink and white', 'lost', 'Library', '', '2026-07-03'),
(9, 1, 'Hoodie', 'Green hoodie with WARSAW logo', 'lost', 'LT 3', '', '2026-07-01'),
(10, 2, 'Water bottle', 'Pink and Blue bottle half full of water. Has bold wording in white written \"SPORTS MODE\"', 'lost', 'LT 6', '', '2026-07-01');

-- --------------------------------------------------------

--
-- Table structure for table `notifications`
--

CREATE TABLE `notifications` (
  `notification_id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `message` text NOT NULL,
  `date_sent` date NOT NULL,
  `is_read` tinyint(4) DEFAULT 0
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `users`
--

CREATE TABLE `users` (
  `user_id` int(11) NOT NULL,
  `email` varchar(100) NOT NULL,
  `password` varchar(255) NOT NULL,
  `role` enum('student','admin') DEFAULT 'student'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `users`
--

INSERT INTO `users` (`user_id`, `email`, `password`, `role`) VALUES
(1, 'tyranzomo@gmail.com', '$2y$10$SUQAF7xOk3Eo66d.QA7D3OIULKMHfw6RAVo49mtkQh7zugxJhuuWC', 'admin'),
(2, 'nicoleindimuli05@gmail.com', '$2y$10$F5lo9bY5q5su9.5IpTR5zepb23ujXhvpR6ZPhhZ0j.h/tYGJjQhqu', 'student'),
(3, 'reshmutheu@gmail.com', '$2y$10$LgUfXd2RSPNJPpMhRxrUOO8jb8q0CRkJOPrpcvl83zbLp.MN9HxDK', 'student');

--
-- Indexes for dumped tables
--

--
-- Indexes for table `claims`
--
ALTER TABLE `claims`
  ADD PRIMARY KEY (`claim_id`),
  ADD KEY `item_id` (`item_id`),
  ADD KEY `user_id` (`user_id`);

--
-- Indexes for table `items`
--
ALTER TABLE `items`
  ADD PRIMARY KEY (`item_id`),
  ADD KEY `user_id` (`user_id`);

--
-- Indexes for table `notifications`
--
ALTER TABLE `notifications`
  ADD PRIMARY KEY (`notification_id`),
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
-- AUTO_INCREMENT for table `claims`
--
ALTER TABLE `claims`
  MODIFY `claim_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT for table `items`
--
ALTER TABLE `items`
  MODIFY `item_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=11;

--
-- AUTO_INCREMENT for table `notifications`
--
ALTER TABLE `notifications`
  MODIFY `notification_id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `users`
--
ALTER TABLE `users`
  MODIFY `user_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `claims`
--
ALTER TABLE `claims`
  ADD CONSTRAINT `claims_ibfk_1` FOREIGN KEY (`item_id`) REFERENCES `items` (`item_id`),
  ADD CONSTRAINT `claims_ibfk_2` FOREIGN KEY (`user_id`) REFERENCES `users` (`user_id`);

--
-- Constraints for table `items`
--
ALTER TABLE `items`
  ADD CONSTRAINT `items_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`user_id`);

--
-- Constraints for table `notifications`
--
ALTER TABLE `notifications`
  ADD CONSTRAINT `notifications_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`user_id`);
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
