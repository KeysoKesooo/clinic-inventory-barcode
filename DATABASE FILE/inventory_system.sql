-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: Oct 22, 2025 at 03:03 PM
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
-- Database: `inventory_system`
--

-- --------------------------------------------------------

--
-- Table structure for table `categories`
--

CREATE TABLE `categories` (
  `id` int(11) UNSIGNED NOT NULL,
  `name` varchar(60) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_general_ci;

--
-- Dumping data for table `categories`
--

INSERT INTO `categories` (`id`, `name`) VALUES
(14, 'sakit ng ulo'),
(13, 'sipon'),
(12, 'ubo');

-- --------------------------------------------------------

--
-- Table structure for table `media`
--

CREATE TABLE `media` (
  `id` int(11) UNSIGNED NOT NULL,
  `file_name` varchar(255) NOT NULL,
  `file_type` varchar(100) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=latin1 COLLATE=latin1_swedish_ci;

-- --------------------------------------------------------

--
-- Table structure for table `products`
--

CREATE TABLE `products` (
  `id` int(11) UNSIGNED NOT NULL,
  `name` varchar(255) NOT NULL,
  `product_photo` varchar(255) DEFAULT NULL,
  `quantity` varchar(50) DEFAULT NULL,
  `buy_price` decimal(25,2) DEFAULT NULL,
  `sale_price` decimal(25,2) NOT NULL,
  `categorie_id` int(11) UNSIGNED NOT NULL,
  `media_id` int(11) DEFAULT 0,
  `date` datetime NOT NULL,
  `expiration_date` datetime DEFAULT NULL,
  `dosage` varchar(100) DEFAULT NULL,
  `description` text DEFAULT NULL,
  `pcs_per_box` int(11) DEFAULT 1
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_general_ci;

--
-- Dumping data for table `products`
--

INSERT INTO `products` (`id`, `name`, `product_photo`, `quantity`, `buy_price`, `sale_price`, `categorie_id`, `media_id`, `date`, `expiration_date`, `dosage`, `description`, `pcs_per_box`) VALUES
(17, 'medikol', 'Discord-Logo-PNG-Images.png', '363', NULL, 0.00, 14, 0, '2025-08-17 15:20:18', NULL, '10mg', 'pang sakit ng ulo', 1),
(25, 'Lagundi', 'remix-rumble-1080x1080.jpg', '10', NULL, 0.00, 14, 0, '2025-08-23 09:56:48', NULL, '500mg', 'box', 1),
(26, 'Newsep', '', '100', NULL, 0.00, 13, 0, '2025-08-23 10:36:47', NULL, '10mg', 'box', 1),
(35, 'Paracetamol', NULL, '50', NULL, 0.00, 14, 0, '2025-09-15 12:35:32', NULL, '500mg', 'Reduces headache and fever', 1),
(36, 'Aspirin', NULL, '30', NULL, 0.00, 14, 0, '2025-09-15 12:35:32', NULL, '100mg', 'Relieves mild pain and inflammation', 1),
(37, 'Bisolvon', NULL, '40', NULL, 0.00, 12, 0, '2025-09-15 12:35:32', NULL, '5ml', 'Helps to loosen mucus in the airways', 1),
(38, 'Amoxicillin', '', '25', NULL, 0.00, 13, 0, '2025-09-15 12:35:32', '2025-09-11 00:00:00', '250mg', 'Antibiotic for bacterial infections', 1),
(39, 'Vicks Vaporub', '', '60', NULL, 0.00, 12, 0, '2025-09-15 12:35:32', '2025-09-24 00:00:00', '10g', 'Relieves cough and congestion', 1),
(41, 'Panadol', '', '43', NULL, 0.00, 14, 0, '2025-09-15 12:35:32', '2025-09-21 00:00:00', '500mg', 'Pain relief for headaches', 1),
(43, 'Yakap', '', '49', NULL, 0.00, 14, 0, '2025-09-19 09:24:24', '2025-10-04 00:00:00', '500mg', 'Reduces headache and fever', 1),
(44, 'test', '', '10', NULL, 0.00, 14, 0, '2025-09-29 14:16:05', '2025-10-03 00:00:00', '10mg', 'test1', 1),
(48, 'test', '', '1000', NULL, 0.00, 13, 0, '2025-09-29 15:07:47', '2025-10-31 00:00:00', '500mg', '', 10);

-- --------------------------------------------------------

--
-- Table structure for table `sales`
--

CREATE TABLE `sales` (
  `id` int(11) UNSIGNED NOT NULL,
  `product_id` int(11) UNSIGNED NOT NULL,
  `qty` int(11) NOT NULL,
  `price` decimal(25,2) NOT NULL,
  `date` date NOT NULL,
  `issued_to` varchar(255) DEFAULT NULL,
  `issued_by` int(11) NOT NULL,
  `status` enum('dispense','restock') NOT NULL DEFAULT 'dispense'
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_general_ci;

--
-- Dumping data for table `sales`
--

INSERT INTO `sales` (`id`, `product_id`, `qty`, `price`, `date`, `issued_to`, `issued_by`, `status`) VALUES
(28, 17, 1, 0.00, '2025-08-23', '', 0, 'dispense'),
(29, 17, 1, 0.00, '2025-08-23', '', 0, 'dispense'),
(32, 17, 10, 0.00, '2025-08-24', '', 0, 'dispense'),
(33, 17, 1, 0.00, '2025-08-24', '', 0, 'dispense'),
(34, 17, 1, 0.00, '2025-08-24', '', 0, 'dispense'),
(36, 41, 1, 0.00, '2025-09-15', '', 0, 'dispense'),
(37, 41, 1, 0.00, '2025-09-15', '', 0, 'dispense'),
(38, 17, 1, 0.00, '2025-09-29', '', 0, 'dispense'),
(39, 17, 1, 0.00, '2025-09-29', '', 0, 'dispense'),
(40, 17, 1, 0.00, '2025-10-03', 'Neil', 9, 'dispense'),
(41, 43, 1, 0.00, '2025-10-03', 'lalove', 9, 'dispense'),
(42, 17, 1, 0.00, '2025-10-07', 'Neil', 9, 'dispense'),
(43, 17, 1, 0.00, '2025-10-07', NULL, 9, 'restock'),
(44, 17, 1, 0.00, '2025-10-07', 'Neil', 9, 'dispense'),
(45, 48, 10, 0.00, '2025-10-22', 'Grace', 9, 'dispense'),
(46, 48, 10, 0.00, '2025-10-22', NULL, 9, 'restock'),
(47, 48, 10, 0.00, '2025-10-22', NULL, 9, 'restock'),
(48, 48, 10, 0.00, '2025-10-22', 'Grace', 9, 'dispense');

-- --------------------------------------------------------

--
-- Table structure for table `users`
--

CREATE TABLE `users` (
  `id` int(11) UNSIGNED NOT NULL,
  `name` varchar(60) NOT NULL,
  `username` varchar(50) NOT NULL,
  `password` varchar(255) NOT NULL,
  `user_level` int(11) NOT NULL,
  `image` varchar(255) NOT NULL DEFAULT 'no_image.png',
  `status` int(1) NOT NULL,
  `last_login` datetime DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=latin1 COLLATE=latin1_swedish_ci;

--
-- Dumping data for table `users`
--

INSERT INTO `users` (`id`, `name`, `username`, `password`, `user_level`, `image`, `status`, `last_login`) VALUES
(2, 'dyan Walker', 'special', 'ba36b97a41e7faf742ab09bf88405ac04f99599a', 2, 'no_image.png', 1, '2021-04-04 19:53:26'),
(9, 'Grace Ortilliano', 'AdminGrace', '$2y$10$EwCk9DUA3q6nK3IasZc7hOu3xL9S8odIYqIhJ6sEt2BlL8zVChREK', 1, 'no_image.png', 1, '2025-10-22 14:46:46'),
(11, 'Gracia Jeff', 'GraceAdmin', '$2y$10$WT3qcklkBsv8kRNyGcVSvu49kIYr/qvw6zM6eacHBdpb9aPXgNAUa', 1, 'no_image.png', 1, '2025-09-15 10:33:40'),
(12, 'Ruel  Weng', 'Nurse', '$2y$10$m1JpYXnYngz6xuV2EucF7unAAah9ZgVNAShyW3nIfcVXuyZ10tshi', 2, 'no_image.png', 1, '2025-08-12 09:47:08'),
(13, 'try patingin', 'try', '$2y$10$ABfsXYHbRbUHRA/5zM/iw.C3LCqgMr7j65rNlwPfnRE3FW7NtnTFO', 1, 'no_image.png', 1, NULL),
(31, 'John Doe', 'jdoe', '$2y$10$z91Y9PwH5LCcxRKUyWU/yuQTPmL4L1ypSJatSg3IJsV.LlY51BfOO', 1, 'no_image.png', 1, NULL),
(32, 'Jane Smith', 'jsmith', '$2y$10$Fs8C49geUzjsTYyACKWdd.Y7TfdrOL89z.A0oJz/Q/6mY6L8dUAlK', 2, 'no_image.png', 1, NULL),
(33, 'Mark Johnson', 'mjohnson', '$2y$10$F.bUZPelsn1aKQdGHe1IpuAUGZ.o0yo0VWyhUCNAQHA8jLZYfg8QW', 1, 'no_image.png', 1, NULL),
(34, 'Linda Brown', 'lbrown', '$2y$10$5E3ZiWRJF/fKepeo2pUpkOe7dN0aemvlGKIKSBWt6VwqMJRqDMpWW', 2, 'no_image.png', 1, NULL),
(35, 'Paul White', 'pwhite', '$2y$10$exdwDHpjD7oWIwKUrjGm1OL3mFXNSnjCNdsfN/L7/oavsFxmSwlIG', 2, 'no_image.png', 1, NULL),
(36, 'Alice Green', 'agreen', '$2y$10$QKp3PZVc/jv8ZNxIq.7NBOxG/01THL3Abh7W6laLz1JEwOlaafp96', 1, 'no_image.png', 1, NULL),
(37, 'Robert Black', 'rblack', '$2y$10$47dBoC8X5cE5xG/aazfZQuOjFoKlBIDTsnouciQ5MhyajfvcOwXRS', 2, 'no_image.png', 1, NULL),
(38, 'Mary Adams', 'madams', '$2y$10$V6CZ1EPoUPwXsia2tsFEaOzw6JPzbnRpaqiMxNAU5LudHJA55anTG', 1, 'no_image.png', 1, NULL),
(39, 'James Clark', 'jclark', '$2y$10$eMmsC4iV/bz3uCbvIn1nFecRNxp2PvGCzxyORfH/1ywqFaJMoNBjG', 2, 'no_image.png', 1, NULL),
(40, 'Patricia Lewis', 'plewis', '$2y$10$Fo5QxHS7bz3RhHE6h/RIdOVN1sBYEGMFZ9I6hONNLLHdZ//guNOwm', 2, 'no_image.png', 1, NULL),
(41, 'Michael Hall', 'mhall', '$2y$10$aCm5/y08Y2zPJNSY0bYMIOQUVQfVwoKkjiEz0oJ5FOwYW58L69Znm', 1, 'no_image.png', 1, NULL),
(42, 'Elizabeth Young', 'eyoung', '$2y$10$cBjanzKBfQ8NgGuIMXEMDuBpWIGWIANmNeYkZZbgAjoLCVGfwZTe2', 2, 'no_image.png', 1, NULL),
(43, 'William King', 'wking', '$2y$10$HVFJeoG5Zjtot6hv/trmKOW6m/YSpev0UQbN3n8MN9p6oZNhrjIzi', 1, 'no_image.png', 1, NULL),
(44, 'Barbara Wright', 'bwright', '$2y$10$gvS5BbpHGmZ46ifLMETrT.UgMZ8DBb511ng4stKokyX25xZuZLHlO', 2, 'no_image.png', 1, NULL),
(45, 'David Scott', 'dscott', '$2y$10$dvdd2y7CLVFIX17JfnJv.eWQOT2McBYdiP0gE.W.idHg3cj8.zfaW', 2, 'no_image.png', 1, NULL),
(46, 'Susan Baker', 'sbaker', '$2y$10$fhzcgG9yYZOsJQD.QtYMouYspUv9uThQFXbQpZIOPkWh3cg8dKX6a', 1, 'no_image.png', 1, NULL),
(47, 'Richard Allen', 'rallen', '$2y$10$tf8K8cg3N4uLopz4lPL./eix/A5BbjN/IgOpEKDHOD1/4pb0xCvRW', 2, 'no_image.png', 1, NULL),
(48, 'Karen Hill', 'khill', '$2y$10$YYS08QDMLf.dRrPOMdu4uOg8i2L4pQpW3gLvju/pdqXpNYwQovIVm', 1, 'no_image.png', 1, NULL),
(49, 'Joseph Moore', 'jmoore', '$2y$10$W3z7mBMObg8qyixpzmsOpeMuZqYv5RRAYpPLjYDxgwN/bw7LfE3Wm', 2, 'no_image.png', 1, NULL),
(50, 'Nancy Turner', 'nturner', '$2y$10$QRTNNAmlAk8XTmPPoRMKO.MOmB3.msx.YQA6WYb3pseAyNHo46kKq', 2, 'no_image.png', 1, NULL),
(51, 'staff staff', 'staff', '$2y$10$Q/2WAZRzhv1wit3Pqi6t2edeSyGRG1GyQWTBHKyp7QiS4gYb5Vtty', 2, 'no_image.png', 1, '2025-09-15 14:01:25');

-- --------------------------------------------------------

--
-- Table structure for table `user_groups`
--

CREATE TABLE `user_groups` (
  `id` int(11) NOT NULL,
  `group_name` varchar(150) NOT NULL,
  `group_level` int(11) NOT NULL,
  `group_status` int(1) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=latin1 COLLATE=latin1_swedish_ci;

--
-- Dumping data for table `user_groups`
--

INSERT INTO `user_groups` (`id`, `group_name`, `group_level`, `group_status`) VALUES
(1, 'Admin', 1, 1),
(2, 'Staff', 2, 1);

--
-- Indexes for dumped tables
--

--
-- Indexes for table `categories`
--
ALTER TABLE `categories`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `name` (`name`);

--
-- Indexes for table `media`
--
ALTER TABLE `media`
  ADD PRIMARY KEY (`id`),
  ADD KEY `id` (`id`);

--
-- Indexes for table `products`
--
ALTER TABLE `products`
  ADD PRIMARY KEY (`id`),
  ADD KEY `categorie_id` (`categorie_id`),
  ADD KEY `media_id` (`media_id`);

--
-- Indexes for table `sales`
--
ALTER TABLE `sales`
  ADD PRIMARY KEY (`id`),
  ADD KEY `product_id` (`product_id`);

--
-- Indexes for table `users`
--
ALTER TABLE `users`
  ADD PRIMARY KEY (`id`),
  ADD KEY `user_level` (`user_level`);

--
-- Indexes for table `user_groups`
--
ALTER TABLE `user_groups`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `group_level` (`group_level`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `categories`
--
ALTER TABLE `categories`
  MODIFY `id` int(11) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=16;

--
-- AUTO_INCREMENT for table `media`
--
ALTER TABLE `media`
  MODIFY `id` int(11) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `products`
--
ALTER TABLE `products`
  MODIFY `id` int(11) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=50;

--
-- AUTO_INCREMENT for table `sales`
--
ALTER TABLE `sales`
  MODIFY `id` int(11) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=49;

--
-- AUTO_INCREMENT for table `users`
--
ALTER TABLE `users`
  MODIFY `id` int(11) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=53;

--
-- AUTO_INCREMENT for table `user_groups`
--
ALTER TABLE `user_groups`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `products`
--
ALTER TABLE `products`
  ADD CONSTRAINT `FK_products` FOREIGN KEY (`categorie_id`) REFERENCES `categories` (`id`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Constraints for table `sales`
--
ALTER TABLE `sales`
  ADD CONSTRAINT `SK` FOREIGN KEY (`product_id`) REFERENCES `products` (`id`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Constraints for table `users`
--
ALTER TABLE `users`
  ADD CONSTRAINT `FK_user` FOREIGN KEY (`user_level`) REFERENCES `user_groups` (`group_level`) ON DELETE CASCADE ON UPDATE CASCADE;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
