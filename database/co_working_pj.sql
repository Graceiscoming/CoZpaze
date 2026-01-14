-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: Jul 14, 2025 at 02:18 PM
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
-- Database: `co_working_pj`
--

-- --------------------------------------------------------

--
-- Table structure for table `booking`
--

CREATE TABLE `booking` (
  `user_id` int(10) UNSIGNED NOT NULL,
  `total_price` double NOT NULL,
  `booking_id` bigint(20) NOT NULL,
  `booking_date` datetime NOT NULL,
  `booking_start_time` time NOT NULL,
  `booking_end_time` time NOT NULL,
  `status` enum('Pending','Confirmed','Finished') DEFAULT NULL,
  `location_id` int(10) UNSIGNED DEFAULT NULL,
  `booking_time` time DEFAULT NULL,
  `created_at` datetime NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `locations`
--

CREATE TABLE `locations` (
  `location_id` int(10) UNSIGNED NOT NULL,
  `location_name` text NOT NULL,
  `description` text NOT NULL,
  `category` text NOT NULL,
  `price_per_hour` double NOT NULL,
  `time` text DEFAULT NULL,
  `uni` text NOT NULL,
  `link` text NOT NULL,
  `latitude` double NOT NULL,
  `longitude` double NOT NULL,
  `star` double NOT NULL,
  `wallet` decimal(10,2) DEFAULT 0.00,
  `image_path` varchar(255) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `locations`
--

INSERT INTO `locations` (`location_id`, `location_name`, `description`, `category`, `price_per_hour`, `time`, `uni`, `link`, `latitude`, `longitude`, `star`, `wallet`, `image_path`) VALUES
(1, 'มหาลัยศรีนครินทรวิโรฒ', '	\r\nเลขที่ 114 ซอยสุขุมวิท 23 ถนนสุขุมวิท แขวงคลองเตยเหนือ เขตวัฒนา กรุงเทพมหานคร 10110', 'Etc.', 100, '10:00-12:00,12:00-14:00,14:00-16:00,16:00-18:00', 'SWU', 'https://maps.app.goo.gl/rdy9yKp31mhXpanh7', 13.745884, 100.5641118, 0, 200.00, '1745049291_มศว.jpg'),
(2, 'มหาวิทยาลัยธรรมศาสตร์ ศูนย์รังสิต', '99 หมู่ที่ 18 ถ. พหลโยธิน ตำบล คลองหนึ่ง อำเภอคลองหลวง ปทุมธานี 12120', 'University', 0, '10:00-12:00,12:00-14:00,14:00-16:00,16:00-18:00,18:00-20:00', 'TU', 'https://maps.app.goo.gl/W3r37LT2PZjqZJv97', 14.0721931, 100.603055, 0, 0.00, '1745049334_มธ.jpg'),
(3, 'มหาวิทยาลัยเกษตรศาสตร์ วิทยาเขตบางเขน', '50 ถนน งามวงศ์วาน แขวงลาดยาว เขตจตุจักร กรุงเทพมหานคร 10900', 'University', 0, '10:00-12:00,12:00-14:00,14:00-16:00,16:00-18:00,18:00-20:00', 'KU', 'https://maps.app.goo.gl/NE9xpMnucCqcNpcf7', 13.8479786, 100.5697013, 0, 0.00, '1745049349_เกษตร.jpg'),
(4, 'จุฬาลงกรณ์มหาวิทยาลัย', '254 ถ. พญาไท แขวงวังใหม่ เขตปทุมวัน กรุงเทพมหานคร 10330', 'University', 0, '10:00-12:00,12:00-14:00,14:00-16:00,16:00-18:00,18:00-20:00', 'CU', 'https://maps.app.goo.gl/5fep47pJbyBEKtZU9', 13.7388624, 100.5258328, 0, 0.00, '1745049258_ฬ.jpg'),
(5, 'มหาวิทยาลัยเทคโนโลยีพระจอมเกล้าธนบุรี', '126 ถ. ประชาอุทิศ แขวงบางมด เขตทุ่งครุ กรุงเทพมหานคร 10140', 'University', 0, '10:00-12:00,12:00-14:00,14:00-16:00,16:00-18:00,18:00-20:00', 'KMUTT', 'https://maps.app.goo.gl/mcJ9drnDNFYQ4FDX9', 13.6512522, 100.4938679, 0, 123.00, '1745048337_บางมด.jpeg'),
(6, 'สถาบันเทคโนโลยีพระจอมเกล้าเจ้าคุณทหารลาดกระบัง', '1 ซอย ฉลองกรุง 1 แขวงลาดกระบัง เขตลาดกระบัง กรุงเทพมหานคร 10520', 'University', 0, '10:00-12:00,12:00-14:00,14:00-16:00,16:00-18:00', 'KMILT', 'https://maps.app.goo.gl/3d1fu1MfMEf57MZy8', 13.7298889, 100.7756574, 0, 861.00, '1745468336_pic5-large.jpg'),
(10, 'SINGHA COMPLEX', '1788 New Petchaburi Rd, Bang Kapi, Huai Khwang, Bangkok 10310', 'CO-Working', 0, '10:00-12:00,12:00-14:00,14:00-16:00,16:00-18:00,18:00-20:00', 'swu', 'https://maps.app.goo.gl/c89MJMLeXRQRJiHu7', 13.7480964, 100.5645784, 0, 0.00, '1745136137_sing.jpg'),
(11, 'Samyan Mitrtown', '944 Rama IV Rd, Wang Mai, Pathum Wan, Bangkok 10330', 'CO-Working', 0, '10:00-12:00,12:00-14:00,14:00-16:00,16:00-18:00,18:00-20:00,20:00-22:00', 'CU', 'https://maps.app.goo.gl/b2Q96LwXCBtbhXkT7', 13.7335498, 100.5282193, 0, 0.00, '1745136055_3.png'),
(12, 'Burapha University', '169 Long Had Bangsaen Rd, Saen Suk, Chon Buri District, Chon Buri 20131', 'University', 0, '10:00-12:00,12:00-14:00,14:00-16:00,16:00-18:00', '123', 'http://localhost/location_set/add_location.php', 13.2813266, 100.9217001, 0, 0.00, '1745468269_465031924_8480605278724922_4892730356906438230_n.jpg');

-- --------------------------------------------------------

--
-- Table structure for table `location_images`
--

CREATE TABLE `location_images` (
  `image_id` int(11) NOT NULL,
  `location_id` int(10) UNSIGNED NOT NULL,
  `image_path` varchar(255) NOT NULL,
  `uploaded_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `location_images`
--

INSERT INTO `location_images` (`image_id`, `location_id`, `image_path`, `uploaded_at`) VALUES
(13, 2, 'reviews/img/6874f50d5d554_1752495373.jpg', '2025-07-14 12:16:13'),
(14, 2, 'reviews/img/6874f516d0e6b_1752495382.jpg', '2025-07-14 12:16:22'),
(15, 1, 'reviews/img/6874f52dcd194_1752495405.png', '2025-07-14 12:16:45'),
(16, 1, 'reviews/img/6874f54ee3c6b_1752495438.jpg', '2025-07-14 12:17:18');

-- --------------------------------------------------------

--
-- Table structure for table `location_keywords`
--

CREATE TABLE `location_keywords` (
  `user_id` int(10) UNSIGNED DEFAULT NULL,
  `keyword` varchar(255) DEFAULT NULL,
  `location_id` int(10) UNSIGNED DEFAULT NULL,
  `id` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `location_keywords`
--

INSERT INTO `location_keywords` (`user_id`, `keyword`, `location_id`, `id`) VALUES
(1, 'swu', 1, 16),
(1, 'มศว.', 1, 17);

-- --------------------------------------------------------

--
-- Table structure for table `location_owners`
--

CREATE TABLE `location_owners` (
  `user_id` int(10) UNSIGNED NOT NULL,
  `location_id` int(10) UNSIGNED NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `location_owners`
--

INSERT INTO `location_owners` (`user_id`, `location_id`) VALUES
(1, 1),
(1, 6),
(1, 12);

-- --------------------------------------------------------

--
-- Table structure for table `reviews`
--

CREATE TABLE `reviews` (
  `user_id` int(10) UNSIGNED NOT NULL,
  `review_id` int(11) NOT NULL,
  `review_text` text NOT NULL,
  `rating` double NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  `location_id` int(11) NOT NULL,
  `review_count` int(11) NOT NULL,
  `total_reviews` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `role`
--

CREATE TABLE `role` (
  `role_id` int(11) NOT NULL,
  `role` varchar(100) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `role`
--

INSERT INTO `role` (`role_id`, `role`) VALUES
(1, 'user'),
(2, 'admin');

-- --------------------------------------------------------

--
-- Table structure for table `searchkeywords`
--

CREATE TABLE `searchkeywords` (
  `id` int(11) NOT NULL,
  `user_id` int(10) UNSIGNED NOT NULL,
  `keyword` varchar(255) DEFAULT NULL,
  `uni` varchar(255) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `searchkeywords`
--

INSERT INTO `searchkeywords` (`id`, `user_id`, `keyword`, `uni`) VALUES
(523, 1, '', ''),
(524, 1, NULL, ''),
(525, 1, NULL, ''),
(526, 1, NULL, ''),
(527, 1, NULL, ''),
(528, 1, NULL, ''),
(529, 1, 'cu', ''),
(530, 1, 'SWU', ''),
(531, 1, NULL, ''),
(532, 1, 'มศว.', ''),
(533, 1, NULL, ''),
(534, 1, NULL, ''),
(535, 1, NULL, ''),
(536, 1, NULL, ''),
(537, 1, NULL, ''),
(538, 1, NULL, ''),
(539, 1, NULL, ''),
(540, 1, NULL, ''),
(541, 1, NULL, ''),
(542, 1, NULL, ''),
(543, 1, NULL, ''),
(544, 1, NULL, '');

-- --------------------------------------------------------

--
-- Table structure for table `transactions`
--

CREATE TABLE `transactions` (
  `id` int(11) NOT NULL,
  `user_id` int(10) UNSIGNED NOT NULL,
  `amount` double NOT NULL,
  `transaction_date` text NOT NULL,
  `transaction_type` text NOT NULL,
  `description` text NOT NULL,
  `slip_image` text NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `transactions`
--

INSERT INTO `transactions` (`id`, `user_id`, `amount`, `transaction_date`, `transaction_type`, `description`, `slip_image`) VALUES
(28, 1, 100, '2025-04-24 11:14:30', 'cancel', '', '1745468070_Screenshot 2024-08-05 210724.png'),
(29, 1, 1000, '2025-07-14 19:03:32', 'success', '', '1752494612_IMG_4520.PNG');

-- --------------------------------------------------------

--
-- Table structure for table `userinfo`
--

CREATE TABLE `userinfo` (
  `user_id` int(10) UNSIGNED NOT NULL,
  `user_pass` varchar(255) NOT NULL,
  `user_mail` text NOT NULL,
  `university_name` varchar(255) NOT NULL,
  `phone_num` text NOT NULL,
  `role_id` int(11) NOT NULL DEFAULT 1,
  `Fullname` text NOT NULL,
  `reset_question` text NOT NULL,
  `reset_token` text NOT NULL,
  `user_name` text DEFAULT NULL,
  `image` text DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `userinfo`
--

INSERT INTO `userinfo` (`user_id`, `user_pass`, `user_mail`, `university_name`, `phone_num`, `role_id`, `Fullname`, `reset_question`, `reset_token`, `user_name`, `image`) VALUES
(1, '$2y$10$5v7SBN29gvVs.gQyahpG0.SYGSHP.wJb6lU5a6/pGAjb8168iGT/S', 'admin@gmail.com', 'SWU', '', 2, 'Admin', 'adminมั้ย', 'adminไง', 'admin', 'user_1.jpg');

-- --------------------------------------------------------

--
-- Table structure for table `wallets`
--

CREATE TABLE `wallets` (
  `user_id` int(10) UNSIGNED NOT NULL,
  `balance` double NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `wallets`
--

INSERT INTO `wallets` (`user_id`, `balance`) VALUES
(1, 1000);

--
-- Indexes for dumped tables
--

--
-- Indexes for table `booking`
--
ALTER TABLE `booking`
  ADD PRIMARY KEY (`booking_id`),
  ADD KEY `fk_location_booking` (`location_id`),
  ADD KEY `fk_user_id_booking` (`user_id`);

--
-- Indexes for table `locations`
--
ALTER TABLE `locations`
  ADD PRIMARY KEY (`location_id`);

--
-- Indexes for table `location_images`
--
ALTER TABLE `location_images`
  ADD PRIMARY KEY (`image_id`),
  ADD KEY `location_id` (`location_id`);

--
-- Indexes for table `location_keywords`
--
ALTER TABLE `location_keywords`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `keyword` (`keyword`),
  ADD KEY `fk_location_keywords` (`location_id`),
  ADD KEY `fk_user_id_location_keywords` (`user_id`);

--
-- Indexes for table `location_owners`
--
ALTER TABLE `location_owners`
  ADD UNIQUE KEY `location_id` (`location_id`,`user_id`),
  ADD UNIQUE KEY `unique_user_location` (`user_id`,`location_id`);

--
-- Indexes for table `reviews`
--
ALTER TABLE `reviews`
  ADD PRIMARY KEY (`review_id`),
  ADD KEY `fk_user_id_reviews` (`user_id`);

--
-- Indexes for table `role`
--
ALTER TABLE `role`
  ADD PRIMARY KEY (`role_id`);

--
-- Indexes for table `searchkeywords`
--
ALTER TABLE `searchkeywords`
  ADD PRIMARY KEY (`id`),
  ADD KEY `fk_searchkeywords_location_keywords` (`keyword`),
  ADD KEY `fk_user_search` (`user_id`);

--
-- Indexes for table `transactions`
--
ALTER TABLE `transactions`
  ADD PRIMARY KEY (`id`),
  ADD KEY `fk_transactions_user` (`user_id`);

--
-- Indexes for table `userinfo`
--
ALTER TABLE `userinfo`
  ADD PRIMARY KEY (`user_id`);

--
-- Indexes for table `wallets`
--
ALTER TABLE `wallets`
  ADD PRIMARY KEY (`user_id`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `booking`
--
ALTER TABLE `booking`
  MODIFY `booking_id` bigint(20) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=37;

--
-- AUTO_INCREMENT for table `locations`
--
ALTER TABLE `locations`
  MODIFY `location_id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=13;

--
-- AUTO_INCREMENT for table `location_images`
--
ALTER TABLE `location_images`
  MODIFY `image_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=17;

--
-- AUTO_INCREMENT for table `location_keywords`
--
ALTER TABLE `location_keywords`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=18;

--
-- AUTO_INCREMENT for table `reviews`
--
ALTER TABLE `reviews`
  MODIFY `review_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=16;

--
-- AUTO_INCREMENT for table `role`
--
ALTER TABLE `role`
  MODIFY `role_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT for table `searchkeywords`
--
ALTER TABLE `searchkeywords`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=545;

--
-- AUTO_INCREMENT for table `transactions`
--
ALTER TABLE `transactions`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=30;

--
-- AUTO_INCREMENT for table `userinfo`
--
ALTER TABLE `userinfo`
  MODIFY `user_id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT for table `wallets`
--
ALTER TABLE `wallets`
  MODIFY `user_id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=18;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `booking`
--
ALTER TABLE `booking`
  ADD CONSTRAINT `fk_location_booking` FOREIGN KEY (`location_id`) REFERENCES `locations` (`location_id`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `fk_user_id_booking` FOREIGN KEY (`user_id`) REFERENCES `userinfo` (`user_id`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Constraints for table `location_images`
--
ALTER TABLE `location_images`
  ADD CONSTRAINT `location_images_ibfk_1` FOREIGN KEY (`location_id`) REFERENCES `locations` (`location_id`) ON DELETE CASCADE;

--
-- Constraints for table `location_keywords`
--
ALTER TABLE `location_keywords`
  ADD CONSTRAINT `fk_location_keywords` FOREIGN KEY (`location_id`) REFERENCES `locations` (`location_id`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `fk_user_id_location_keywords` FOREIGN KEY (`user_id`) REFERENCES `userinfo` (`user_id`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Constraints for table `location_owners`
--
ALTER TABLE `location_owners`
  ADD CONSTRAINT `fk_location_owner` FOREIGN KEY (`location_id`) REFERENCES `locations` (`location_id`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `fk_location_owners_user` FOREIGN KEY (`user_id`) REFERENCES `userinfo` (`user_id`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Constraints for table `reviews`
--
ALTER TABLE `reviews`
  ADD CONSTRAINT `fk_reviews_user` FOREIGN KEY (`user_id`) REFERENCES `userinfo` (`user_id`),
  ADD CONSTRAINT `fk_user_id_reviews` FOREIGN KEY (`user_id`) REFERENCES `userinfo` (`user_id`);

--
-- Constraints for table `searchkeywords`
--
ALTER TABLE `searchkeywords`
  ADD CONSTRAINT `fk_user_search` FOREIGN KEY (`user_id`) REFERENCES `userinfo` (`user_id`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Constraints for table `transactions`
--
ALTER TABLE `transactions`
  ADD CONSTRAINT `fk_transactions_user` FOREIGN KEY (`user_id`) REFERENCES `userinfo` (`user_id`) ON DELETE CASCADE ON UPDATE CASCADE;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
