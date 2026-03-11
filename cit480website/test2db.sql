-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: Mar 11, 2026 at 07:24 PM
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
-- Database: `test2db`
--

-- --------------------------------------------------------

--
-- Table structure for table `contact`
--

CREATE TABLE `contact` (
  `id` int(11) NOT NULL,
  `name` varchar(100) NOT NULL,
  `email` varchar(255) NOT NULL,
  `phone` varchar(30) DEFAULT NULL,
  `subject` varchar(255) DEFAULT NULL,
  `message` text NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `contact`
--

INSERT INTO `contact` (`id`, `name`, `email`, `phone`, `subject`, `message`, `created_at`) VALUES
(1, 'Evelyn', 'evelyn.tran.177@my.csun.edu', '', 'venue', 'plz add venue', '2026-02-04 19:42:00');

-- --------------------------------------------------------

--
-- Table structure for table `reviews`
--

CREATE TABLE `reviews` (
  `id` int(11) NOT NULL,
  `venue_id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `rating` tinyint(3) UNSIGNED NOT NULL CHECK (`rating` between 1 and 5),
  `comment` text DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `reviews`
--

INSERT INTO `reviews` (`id`, `venue_id`, `user_id`, `rating`, `comment`, `created_at`) VALUES
(11, 7, 10, 3, 'This is a test review', '2026-02-18 19:29:51'),
(13, 7, 11, 5, 'The venue was great, it was spacious and had good viewing angles, and was overall a great time.', '2026-02-25 18:23:11');

-- --------------------------------------------------------

--
-- Table structure for table `sessions`
--

CREATE TABLE `sessions` (
  `id` varbinary(128) NOT NULL,
  `user_id` int(11) DEFAULT NULL,
  `data` blob NOT NULL,
  `timestamp` int(10) UNSIGNED NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `sessions`
--

INSERT INTO `sessions` (`id`, `user_id`, `data`, `timestamp`) VALUES
(0x306e637569676d696b30756f34736f6e377074306b3761337432, 11, 0x4c4153545f41435449564954597c693a313737333038303136353b757365725f69647c693a31313b757365725f6e616d657c733a363a224576656c796e223b757365725f656d61696c7c733a31363a226576656c796e40676d61696c2e636f6d223b, 1773080166),
(0x396e3232326d66683367646a34316735746c6f736c386c66616a, 11, 0x4c4153545f41435449564954597c693a313737333235333338313b757365725f69647c693a31313b757365725f6e616d657c733a363a224576656c796e223b757365725f656d61696c7c733a31363a226576656c796e40676d61696c2e636f6d223b, 1773253381),
(0x62736b7675617568676d613633386f7468686468756e38686264, NULL, 0x4c4153545f41435449564954597c693a313737333038313635343b, 1773081654),
(0x6b74336631356a37746375743537653073636d36636b68386c66, NULL, 0x4c4153545f41435449564954597c693a313737333038303233363b, 1773080236),
(0x6c6d38306463757231326b366464666d666f6b68316938756731, 11, 0x4c4153545f41435449564954597c693a313737333037393834323b757365725f69647c693a31313b757365725f6e616d657c733a363a224576656c796e223b757365725f656d61696c7c733a31363a226576656c796e40676d61696c2e636f6d223b, 1773079842),
(0x6c74713065686c6873747676356c36637676727132386f73376f, NULL, '', 1773078809),
(0x6d7268616c323674716a726c706c66306f6e646f72616c766b71, 11, 0x757365725f69647c693a31313b757365725f6e616d657c733a363a224576656c796e223b757365725f656d61696c7c733a31363a226576656c796e40676d61696c2e636f6d223b, 1773078668),
(0x6f3661666b6d723375747538696e756c3531656e397172366335, NULL, 0x4c4153545f41435449564954597c693a313737333037393933363b, 1773079936);

-- --------------------------------------------------------

--
-- Table structure for table `users`
--

CREATE TABLE `users` (
  `id` int(11) NOT NULL,
  `name` varchar(255) NOT NULL,
  `email` varchar(255) NOT NULL,
  `password_hash` varchar(255) NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `profile_image_path` varchar(255) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `users`
--

INSERT INTO `users` (`id`, `name`, `email`, `password_hash`, `created_at`, `profile_image_path`) VALUES
(10, 'Google Reviews', 'googletest@gmail.com', '$2y$10$ufpUi1kaJUdbBDWw7tq1hOIlNVXTf8qKNx8lbaiyNjEkn6va7d7I2', '2026-02-18 18:51:36', NULL),
(11, 'Evelyn', 'evelyn@gmail.com', '$2y$10$Uu1Yd07v5zsj5fsy.5Hm3eIpsE1f.vzjqJ24XaekuNZiydWhRnr5K', '2026-02-25 18:13:08', NULL);

-- --------------------------------------------------------

--
-- Table structure for table `venues`
--

CREATE TABLE `venues` (
  `id` int(11) NOT NULL,
  `name` varchar(255) NOT NULL,
  `address` varchar(255) DEFAULT NULL,
  `city` varchar(120) DEFAULT NULL,
  `state` varchar(60) DEFAULT NULL,
  `venue_url` varchar(2048) DEFAULT NULL,
  `image_url` varchar(2048) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `ai_summary` text DEFAULT NULL,
  `ai_summary_updated_at` datetime DEFAULT NULL,
  `ai_summary_status` varchar(20) NOT NULL DEFAULT 'idle'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `venues`
--

INSERT INTO `venues` (`id`, `name`, `address`, `city`, `state`, `venue_url`, `image_url`, `created_at`, `ai_summary`, `ai_summary_updated_at`, `ai_summary_status`) VALUES
(1, 'Hollywood Palladium', '6215 Sunset Blvd', 'Los Angeles', 'CA', NULL, 'venue-image/image1.jpg', '2025-11-11 04:38:47', NULL, NULL, 'idle'),
(2, 'YouTube Theater', '1011 Stadium Dr', 'Inglewood', 'CA', NULL, 'venue-image/image2.webp', '2025-11-11 04:38:47', NULL, NULL, 'idle'),
(3, 'The Forum', '3900 W Manchester Blvd', 'Inglewood', 'CA', NULL, 'venue-image/image3.jpg', '2025-11-11 04:38:47', NULL, NULL, 'idle'),
(4, 'The Comedy Store', '8433 Sunset Blvd', 'West Hollywood', 'CA', NULL, 'venue-image/image4.jpg', '2025-11-11 04:38:47', NULL, NULL, 'idle'),
(5, 'The Echo', '1822 Sunset Blvd, Los Angeles, CA 90026\r\n\r\n', 'Los Angeles ', 'CA', NULL, 'venue-image/image5.jpg', '2026-02-11 18:29:55', NULL, NULL, 'idle'),
(6, 'Crypto Arena', '1111 S Figueroa St, Los Angeles, CA 90015\r\n', 'Los Angeles', 'CA', NULL, 'venue-image/image6.webp', '2026-02-11 18:29:55', NULL, NULL, 'idle'),
(7, 'Hollywood Bowl', '2301 Highland Ave', 'Los Angeles', 'CA', NULL, 'venue-image/image15.jpg', '2026-02-11 18:33:30', NULL, NULL, 'idle'),
(8, 'Greek Theatre', '2700 N Vermont Ave', 'Los Angeles', 'CA', NULL, 'venue-image/image16.webp', '2026-02-11 18:33:30', NULL, NULL, 'idle'),
(9, 'SoFi Stadium', '1001 Stadium Dr', 'Inglewood', 'CA', NULL, 'venue-image/image17.jpg', '2026-02-11 18:33:30', NULL, NULL, 'idle'),
(10, 'Rose Bowl', '1001 Rose Bowl Dr', 'Pasadena', 'CA', NULL, 'venue-image/image18.jpg', '2026-02-11 18:33:30', NULL, NULL, 'idle'),
(11, 'Intuit Dome', '3930 W Century Blvd', 'Inglewood', 'CA', NULL, 'venue-image/image19.webp', '2026-02-11 18:33:30', NULL, NULL, 'idle'),
(12, 'Walt Disney Concert Hall', '111 S Grand Ave', 'Los Angeles', 'CA', NULL, 'venue-image/image20.jpg', '2026-02-11 18:33:30', NULL, NULL, 'idle'),
(13, 'Hollywood Improv', '8162 Melrose Ave', 'Los Angeles', 'CA', NULL, 'venue-image/image7.jpg', '2026-02-11 18:33:30', NULL, NULL, 'idle'),
(14, 'Laugh Factory', '8001 Sunset Blvd', 'Los Angeles', 'CA', NULL, 'venue-image/image8.jpg', '2026-02-11 18:33:30', NULL, NULL, 'idle'),
(15, 'The Ford', '2580 Cahuenga Blvd E', 'Los Angeles', 'CA', NULL, 'venue-image/image9.jpg', '2026-02-11 18:33:30', NULL, NULL, 'idle'),
(16, 'The Bellwether', '333 S Boylston St', 'Los Angeles', 'CA', NULL, 'venue-image/image10.jpg', '2026-02-11 18:33:30', NULL, NULL, 'idle'),
(17, 'The Fonda', '6166 Hollywood Blvd', 'Los Angeles', 'CA', NULL, 'venue-image/image11.jpg', '2026-02-11 18:33:30', NULL, NULL, 'idle'),
(18, 'Echoplex', '1154 Glendale Blvd', 'Los Angeles', 'CA', NULL, 'venue-image/image12.jpg', '2026-02-11 18:33:30', NULL, NULL, 'idle'),
(19, 'Troubadour', '9081 N Santa Monica Blvd', 'West Hollywood', 'CA', NULL, 'venue-image/image13.jpg', '2026-02-11 18:33:30', NULL, NULL, 'idle'),
(20, 'Shrine Auditorium and Expo Hall', '665 W Jefferson Blvd', 'Los Angeles', 'CA', NULL, 'venue-image/image14.jpg', '2026-02-11 18:33:30', NULL, NULL, 'idle');

--
-- Indexes for dumped tables
--

--
-- Indexes for table `contact`
--
ALTER TABLE `contact`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `reviews`
--
ALTER TABLE `reviews`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `uniq_user_venue` (`venue_id`,`user_id`),
  ADD KEY `venue_id` (`venue_id`),
  ADD KEY `user_id` (`user_id`);

--
-- Indexes for table `sessions`
--
ALTER TABLE `sessions`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_user_id` (`user_id`),
  ADD KEY `idx_timestamp` (`timestamp`);

--
-- Indexes for table `users`
--
ALTER TABLE `users`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `email` (`email`);

--
-- Indexes for table `venues`
--
ALTER TABLE `venues`
  ADD PRIMARY KEY (`id`),
  ADD KEY `name` (`name`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `contact`
--
ALTER TABLE `contact`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT for table `reviews`
--
ALTER TABLE `reviews`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=14;

--
-- AUTO_INCREMENT for table `users`
--
ALTER TABLE `users`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=12;

--
-- AUTO_INCREMENT for table `venues`
--
ALTER TABLE `venues`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=21;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `reviews`
--
ALTER TABLE `reviews`
  ADD CONSTRAINT `reviews_ibfk_1` FOREIGN KEY (`venue_id`) REFERENCES `venues` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `reviews_ibfk_2` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `sessions`
--
ALTER TABLE `sessions`
  ADD CONSTRAINT `fk_sessions_user` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
