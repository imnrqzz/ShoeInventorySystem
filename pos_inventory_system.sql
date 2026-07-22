-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: Jul 22, 2026 at 12:53 PM
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
-- Database: `pos_inventory_system`
--

-- --------------------------------------------------------

--
-- Table structure for table `items`
--

CREATE TABLE `items` (
  `id` int(10) UNSIGNED NOT NULL,
  `name` varchar(255) NOT NULL,
  `category` varchar(50) DEFAULT 'lifestyle',
  `supplier_id` int(10) UNSIGNED DEFAULT NULL,
  `quantity` int(11) NOT NULL DEFAULT 0,
  `min_quantity` int(11) NOT NULL DEFAULT 0,
  `price` decimal(10,2) NOT NULL DEFAULT 0.00,
  `image` varchar(500) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `items`
--

INSERT INTO `items` (`id`, `name`, `category`, `supplier_id`, `quantity`, `min_quantity`, `price`, `image`, `created_at`) VALUES
(1, 'Nike Air Max 90 Essentials', 'sport', 1, 35, 10, 130.00, 'https://images.pexels.com/photos/2505055/pexels-photo-2505055.jpeg?auto=compress&cs=tinysrgb&w=1260&h=750', '2026-05-29 19:20:01'),
(2, 'onitsuka', 'lifestyle', 6, 34, 5, 30.00, 'https://images.pexels.com/photos/2529148/pexels-photo-2529148.jpeg?auto=compress&cs=tinysrgb&w=1260&h=750', '2026-06-01 00:51:37'),
(3, 'Air Jordan 1 Retro High OG', 'classic', 1, 18, 5, 180.00, 'https://images.pexels.com/photos/19090/pexels-photo.jpg?auto=compress&cs=tinysrgb&w=1260&h=750', '2026-05-29 19:20:01'),
(4, 'Puma Clyde Classic Suede', 'classic', 2, 50, 5, 85.00, 'https://images.pexels.com/photos/267202/pexels-photo-267202.jpeg?auto=compress&cs=tinysrgb&w=1260&h=750', '2026-05-29 19:20:01'),
(5, 'Air Force 1', 'sport', 1, 30, 5, 50.00, 'https://images.pexels.com/photos/19090/pexels-photo.jpg?auto=compress&cs=tinysrgb&w=1260&h=750', '2026-05-29 20:00:33'),
(6, 'Nike Pegasus 42', 'lifestyle', 1, 0, 5, 131.25, NULL, '2026-07-19 06:30:25');

-- --------------------------------------------------------

--
-- Table structure for table `stock`
--

CREATE TABLE `stock` (
  `id` int(10) UNSIGNED NOT NULL,
  `item_id` int(10) UNSIGNED NOT NULL,
  `category` varchar(100) DEFAULT 'Shoes',
  `supplier_id` int(10) UNSIGNED DEFAULT NULL,
  `current_qty` decimal(10,2) NOT NULL DEFAULT 0.00,
  `min_threshold` decimal(10,2) NOT NULL DEFAULT 0.00,
  `unit` varchar(20) DEFAULT 'pairs',
  `last_updated` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `stock`
--

INSERT INTO `stock` (`id`, `item_id`, `category`, `supplier_id`, `current_qty`, `min_threshold`, `unit`, `last_updated`) VALUES
(1, 1, 'Shoes', 1, 35.00, 10.00, 'pairs', '2026-05-30 05:40:54'),
(3, 3, 'Shoes', 1, 18.00, 5.00, 'pairs', '2026-07-11 01:13:52'),
(4, 4, 'Shoes', 2, 50.00, 5.00, 'pairs', '2026-07-22 09:10:49'),
(5, 5, 'Shoes', 1, 30.00, 5.00, 'pairs', '2026-07-19 06:34:11');

-- --------------------------------------------------------

--
-- Table structure for table `suppliers`
--

CREATE TABLE `suppliers` (
  `order_id` int(10) UNSIGNED NOT NULL,
  `company_name` varchar(255) NOT NULL,
  `contact_person` varchar(255) NOT NULL,
  `category` varchar(255) NOT NULL,
  `phone_email` varchar(255) NOT NULL,
  `status` varchar(50) NOT NULL DEFAULT 'Active',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `suppliers`
--

INSERT INTO `suppliers` (`order_id`, `company_name`, `contact_person`, `category`, `phone_email`, `status`, `created_at`) VALUES
(1, 'Nike Southeast Asia', 'John Doe', 'Athletic Footwear', 'orders@nike-dist.com', 'Active', '2026-05-29 19:17:35'),
(2, 'Adidas Distribution', 'Jane Smith', 'Sports Apparel & Shoes', '+63 917 123 4567', 'Active', '2026-05-29 19:17:35'),
(3, 'Puma woohoo', 'Mark', 'asd', 'asd@gm.com', 'Active', '2026-05-29 20:00:55'),
(6, 'sc', 'Mark', 'Rubber Shoes', '09124081259', 'Active', '2026-06-01 00:52:20'),
(7, 'NIke Asia', 'Juan Dela Cruz', 'Rubber Shoes', '09123456789', 'Active', '2026-07-19 06:33:03');

-- --------------------------------------------------------

--
-- Table structure for table `transactions`
--

CREATE TABLE `transactions` (
  `id` int(11) NOT NULL,
  `item_id` int(10) UNSIGNED NOT NULL,
  `transaction_type` varchar(20) DEFAULT NULL,
  `quantity` int(11) NOT NULL,
  `user_id` int(10) UNSIGNED DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `transaction_date` datetime DEFAULT current_timestamp(),
  `reason` varchar(255) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `transactions`
--

INSERT INTO `transactions` (`id`, `item_id`, `transaction_type`, `quantity`, `user_id`, `created_at`, `transaction_date`, `reason`) VALUES
(1, 1, 'Restock', 50, 7, '2026-05-31 22:38:48', '2026-06-01 08:00:00', 'Initial stock entry'),
(2, 5, 'Restock', 30, NULL, '2026-05-31 22:38:48', '2026-06-01 09:30:00', 'Supplier delivery'),
(3, 3, 'Sale', 2, NULL, '2026-05-31 22:38:48', '2026-06-01 10:15:00', 'Customer purchase'),
(4, 4, 'Waste', 1, NULL, '2026-05-31 22:38:48', '2026-06-01 11:00:00', 'Damaged during storage'),
(5, 3, 'Sale', 5, 7, '2026-05-31 22:38:48', '2026-06-01 14:20:00', 'Online order'),
(6, 1, 'Restock', 44, NULL, '2026-05-31 23:44:51', '2026-06-01 07:44:51', 'asd'),
(7, 2, 'Restock', 34, 7, '2026-06-01 00:53:07', '2026-06-01 08:53:07', 'Critical Stock / Low Stock');

-- --------------------------------------------------------

--
-- Table structure for table `users`
--

CREATE TABLE `users` (
  `id` int(10) UNSIGNED NOT NULL,
  `username` varchar(100) NOT NULL,
  `password_hash` varchar(255) NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `role` varchar(50) NOT NULL DEFAULT 'user',
  `name` varchar(255) DEFAULT NULL,
  `email` varchar(255) DEFAULT NULL,
  `status` varchar(50) DEFAULT 'Active',
  `provider` enum('local','google','facebook') NOT NULL DEFAULT 'local',
  `provider_id` varchar(190) DEFAULT NULL,
  `avatar` varchar(500) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `users`
--

INSERT INTO `users` (`id`, `username`, `password_hash`, `created_at`, `role`, `name`, `email`, `status`, `provider`, `provider_id`, `avatar`) VALUES
(7, 'mark', '$2y$10$K2scmmI8OWu48pbISjwVrOHfMApfFTZTErsZmt77xHCwEM3Bl8vuC', '2026-05-29 10:51:38', 'Staff', 'mark', 'nrqzmrk@asf.com', 'active', 'local', NULL, NULL),
(14, '', '', '2026-07-09 10:37:40', 'user', 'Mark Anthony Enriquez', 'markanthonyenriquezzz@gmail.com', 'Active', 'facebook', '1311847634445784', 'https://platform-lookaside.fbsbx.com/platform/profilepic/?asid=1311847634445784&height=50&width=50&ext=1786185461&hash=Aful-GOW8W2_ZhG7MKG54BTJ'),
(15, 'kram123', '$2y$10$7cX6WX.DKHyceCP.0i6pEO8FTd51ysiSmmsZ3lrSWpoEOn67ZQANe', '2026-07-19 06:25:57', 'Admin', 'Mark Anthony Enriquez', 'nrqzmrk@yahoo.com', 'Active', 'local', NULL, NULL),
(16, 'beandr', '$2y$10$PF.T0/34pbqnAmW6/n1GFOWKEkhmgsChnyDP0NY/KnJ1l2jA7x5J6', '2026-07-21 02:26:12', 'Staff', 'bea', 'beandr@gmail.com', 'Active', 'local', NULL, NULL),
(18, 'admin', '$2y$10$QIrFPRj8M34winZFkY2zDOCadHVNu40EueH9isnPc/ztLrmy7mxDS', '2026-07-22 08:57:29', 'Admin', 'Admin', 'admin@inventory.com', 'Active', 'local', NULL, NULL),
(19, 'basto', '$2y$10$MOMtxxNtPH7g2F2y130S5uEnjHWB4xQ6uLB0eg1vMVDeSuKdzxCJi', '2026-07-22 10:19:44', 'Staff', 'Prince Christian Basto', 'princebasto123@gmail.com', 'Inactive', 'local', NULL, NULL);

--
-- Indexes for dumped tables
--

--
-- Indexes for table `items`
--
ALTER TABLE `items`
  ADD PRIMARY KEY (`id`),
  ADD KEY `items_ibfk_1` (`supplier_id`);

--
-- Indexes for table `stock`
--
ALTER TABLE `stock`
  ADD PRIMARY KEY (`id`),
  ADD KEY `fk_stock_items` (`item_id`),
  ADD KEY `fk_stock_suppliers` (`supplier_id`);

--
-- Indexes for table `suppliers`
--
ALTER TABLE `suppliers`
  ADD PRIMARY KEY (`order_id`);

--
-- Indexes for table `transactions`
--
ALTER TABLE `transactions`
  ADD PRIMARY KEY (`id`),
  ADD KEY `item_id` (`item_id`),
  ADD KEY `user_id` (`user_id`);

--
-- Indexes for table `users`
--
ALTER TABLE `users`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `username` (`username`),
  ADD KEY `idx_provider` (`provider`,`provider_id`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `items`
--
ALTER TABLE `items`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=7;

--
-- AUTO_INCREMENT for table `stock`
--
ALTER TABLE `stock`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=7;

--
-- AUTO_INCREMENT for table `suppliers`
--
ALTER TABLE `suppliers`
  MODIFY `order_id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=8;

--
-- AUTO_INCREMENT for table `transactions`
--
ALTER TABLE `transactions`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=8;

--
-- AUTO_INCREMENT for table `users`
--
ALTER TABLE `users`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=20;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `items`
--
ALTER TABLE `items`
  ADD CONSTRAINT `items_ibfk_1` FOREIGN KEY (`supplier_id`) REFERENCES `suppliers` (`order_id`) ON DELETE SET NULL;

--
-- Constraints for table `stock`
--
ALTER TABLE `stock`
  ADD CONSTRAINT `fk_stock_items` FOREIGN KEY (`item_id`) REFERENCES `items` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `fk_stock_suppliers` FOREIGN KEY (`supplier_id`) REFERENCES `suppliers` (`order_id`) ON DELETE SET NULL ON UPDATE CASCADE;

--
-- Constraints for table `transactions`
--
ALTER TABLE `transactions`
  ADD CONSTRAINT `transactions_ibfk_1` FOREIGN KEY (`item_id`) REFERENCES `items` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `transactions_ibfk_2` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE SET NULL;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
