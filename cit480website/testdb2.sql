-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: Feb 11, 2026 at 08:41 PM
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
-- Database: `testdb2`
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
(1, 1, 1, 5, 'Incredible sound and great atmosphere!', '2025-11-11 04:38:47'),
(2, 2, 2, 4, 'Beautiful new venue, seating could be better.', '2025-11-11 04:38:47'),
(3, 3, 3, 5, 'Classic LA spot, always delivers.', '2025-11-11 04:38:47'),
(4, 1, 4, 4, 'This was so much fun!', '2025-11-19 19:02:20'),
(5, 1, 5, 1, ':(', '2025-11-19 19:06:52'),
(6, 2, 6, 4, 'it was a great night and clean venue.', '2025-12-01 18:22:25'),
(7, 1, 7, 4, 'it was a really clean venue and great atmosphere.', '2025-12-03 18:21:35'),
(8, 2, 8, 3, 'Had a great time with friends although directions and signage within the venue could\'ve been more easier to understand.', '2026-01-16 23:29:22'),
(9, 2, 9, 4, 'Had an amazing time!', '2026-01-21 18:42:12');

-- --------------------------------------------------------

--
-- Table structure for table `users`
--

CREATE TABLE `users` (
  `id` int(11) NOT NULL,
  `name` varchar(255) NOT NULL,
  `email` varchar(255) NOT NULL,
  `password_hash` varchar(255) NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `users`
--

INSERT INTO `users` (`id`, `name`, `email`, `password_hash`, `created_at`) VALUES
(1, 'Alice', 'alice@example.com', '$2b$10$examplehashedpassword123', '2025-11-11 04:38:47'),
(2, 'Bob', 'bob@example.com', '$2b$10$examplehashedpassword456', '2025-11-11 04:38:47'),
(3, 'Charlie', 'charlie@example.com', '$2b$10$examplehashedpassword789', '2025-11-11 04:38:47'),
(4, 'joey', 'joey@example.com', '$2y$10$IHKIaK.fgQ1iZr318nsEwOwt8V589rjPYMyjJfQiIb1x4b5SBIio.', '2025-11-19 19:00:02'),
(5, 'eve', 'eve@example.com', '$2y$10$YSaR0e3u2HHbaotOtHSHyuxNYiJHcg6yrCdqUZxnHOmkXhXyEvmny', '2025-11-19 19:06:05'),
(6, 'steven', 'steven@example.com', '$2y$10$UhE3S6nEaMC4kG1ajFSoN.i9/jXdnPlWXSDRuCD2ZuzqqaBEVF8GW', '2025-12-01 18:21:59'),
(7, 'stephanie', 'stephanie@example.com', '$2y$10$qzuS1eNIb8F7UOU3blI9leEIIa2BBnU/2NF/vx08NVyALe.M6C07i', '2025-12-03 18:20:53'),
(8, 'Nathan', 'nathan@yahoo.com', '$2y$10$y5qprWZ417omZUJRLk8GOOgkiQi81hpPoCEgRYKPyBLHyR549LCa6', '2026-01-16 23:18:36'),
(9, 'Joel', 'joel@example.com', '$2y$10$gCpVYobWh/iwBnwqh41Th.RX46xDhBdNvg.AzW5Az00j0kMxeQZR6', '2026-01-21 18:41:48');

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
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `venues`
--

INSERT INTO `venues` (`id`, `name`, `address`, `city`, `state`, `venue_url`, `image_url`, `created_at`) VALUES
(1, 'Hollywood Palladium', '6215 Sunset Blvd', 'Los Angeles', 'CA', NULL, 'venue-image/image1.jpg', '2025-11-11 04:38:47'),
(2, 'YouTube Theater', '1011 Stadium Dr', 'Inglewood', 'CA', NULL, 'venue-image/image2.webp', '2025-11-11 04:38:47'),
(3, 'The Forum', '3900 W Manchester Blvd', 'Inglewood', 'CA', NULL, 'venue-image/image3.jpg', '2025-11-11 04:38:47'),
(4, 'The Comedy Store', '8433 Sunset Blvd', 'West Hollywood', 'CA', NULL, 'venue-image/image4.jpg', '2025-11-11 04:38:47'),
(5, 'The Echo', '1822 Sunset Blvd, Los Angeles, CA 90026\r\n\r\n', 'Los Angeles ', 'CA', NULL, 'venue-image/image5.jpg', '2026-02-11 18:29:55'),
(6, 'Crypto Arena', '1111 S Figueroa St, Los Angeles, CA 90015\r\n', 'Los Angeles', 'CA', NULL, 'venue-image/image6.webp', '2026-02-11 18:29:55'),
(7, 'Hollywood Bowl', '2301 Highland Ave', 'Los Angeles', 'CA', NULL, 'venue-image/image15.jpg', '2026-02-11 18:33:30'),
(8, 'Greek Theatre', '2700 N Vermont Ave', 'Los Angeles', 'CA', NULL, 'venue-image/image16.webp', '2026-02-11 18:33:30'),
(9, 'SoFi Stadium', '1001 Stadium Dr', 'Inglewood', 'CA', NULL, 'venue-image/image17.jpg', '2026-02-11 18:33:30'),
(10, 'Rose Bowl', '1001 Rose Bowl Dr', 'Pasadena', 'CA', NULL, 'venue-image/image18.jpg', '2026-02-11 18:33:30'),
(11, 'Intuit Dome', '3930 W Century Blvd', 'Inglewood', 'CA', NULL, 'venue-image/image19.webp', '2026-02-11 18:33:30'),
(12, 'Walt Disney Concert Hall', '111 S Grand Ave', 'Los Angeles', 'CA', NULL, 'venue-image/image20.jpg', '2026-02-11 18:33:30'),
(13, 'Hollywood Improv', '8162 Melrose Ave', 'Los Angeles', 'CA', NULL, 'venue-image/image7.jpg', '2026-02-11 18:33:30'),
(14, 'Laugh Factory', '8001 Sunset Blvd', 'Los Angeles', 'CA', NULL, 'venue-image/image8.jpg', '2026-02-11 18:33:30'),
(15, 'The Ford', '2580 Cahuenga Blvd E', 'Los Angeles', 'CA', NULL, 'venue-image/image9.jpg', '2026-02-11 18:33:30'),
(16, 'The Bellwether', '333 S Boylston St', 'Los Angeles', 'CA', NULL, 'venue-image/image10.jpg', '2026-02-11 18:33:30'),
(17, 'The Fonda', '6166 Hollywood Blvd', 'Los Angeles', 'CA', NULL, 'venue-image/image11.jpg', '2026-02-11 18:33:30'),
(18, 'Echoplex', '1154 Glendale Blvd', 'Los Angeles', 'CA', NULL, 'venue-image/image12.jpg', '2026-02-11 18:33:30'),
(19, 'Troubadour', '9081 N Santa Monica Blvd', 'West Hollywood', 'CA', NULL, 'venue-image/image13.jpg', '2026-02-11 18:33:30'),
(20, 'Shrine Auditorium and Expo Hall', '665 W Jefferson Blvd', 'Los Angeles', 'CA', NULL, 'venue-image/image14.jpg', '2026-02-11 18:33:30');

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
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=10;

--
-- AUTO_INCREMENT for table `users`
--
ALTER TABLE `users`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=10;

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
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
