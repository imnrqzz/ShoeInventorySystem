-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: Jun 01, 2026 at 02:05 AM
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
  `supplier_id` int(10) UNSIGNED DEFAULT NULL,
  `quantity` int(11) NOT NULL DEFAULT 0,
  `min_quantity` int(11) NOT NULL DEFAULT 0,
  `price` decimal(10,2) NOT NULL DEFAULT 0.00,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `items`
--

INSERT INTO `items` (`id`, `name`, `supplier_id`, `quantity`, `min_quantity`, `price`, `created_at`) VALUES
(1, 'Nike Air Max 90 Essentials', 1, 167, 10, 130.00, '2026-05-29 19:20:01'),
(3, 'Air Jordan 1 Retro High OG', 1, 18, 5, 180.00, '2026-05-29 19:20:01'),
(4, 'Puma Clyde Classic Suede', 2, 5, 5, 85.00, '2026-05-29 19:20:01'),
(5, 'Air Force 1', 1, 1, 5, 50.00, '2026-05-29 20:00:33');

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
(3, 3, 'Shoes', 1, 18.00, 5.00, 'pairs', '2026-05-30 05:40:54'),
(4, 4, 'Shoes', 2, 5.00, 5.00, 'pairs', '2026-05-30 06:08:42'),
(5, 5, 'Shoes', 1, 1.00, 5.00, 'pairs', '2026-05-31 19:44:45');

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
(3, 'Puma woo', 'Mark', 'asd', 'asd@gm.com', 'Active', '2026-05-29 20:00:55');

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
(2, 5, 'Restock', 30, 8, '2026-05-31 22:38:48', '2026-06-01 09:30:00', 'Supplier delivery'),
(3, 3, 'Sale', 2, 9, '2026-05-31 22:38:48', '2026-06-01 10:15:00', 'Customer purchase'),
(4, 4, 'Waste', 1, 12, '2026-05-31 22:38:48', '2026-06-01 11:00:00', 'Damaged during storage'),
(5, 3, 'Sale', 5, 7, '2026-05-31 22:38:48', '2026-06-01 14:20:00', 'Online order'),
(6, 1, 'Restock', 44, 12, '2026-05-31 23:44:51', '2026-06-01 07:44:51', 'asd');

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
  `status` varchar(50) DEFAULT 'Active'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `users`
--

INSERT INTO `users` (`id`, `username`, `password_hash`, `created_at`, `role`, `name`, `email`, `status`) VALUES
(7, 'mark', '$2y$10$K2scmmI8OWu48pbISjwVrOHfMApfFTZTErsZmt77xHCwEM3Bl8vuC', '2026-05-29 10:51:38', 'user', 'mark', 'nrqzmrk@asf.com', 'active'),
(8, 'admin_user', '$2y$10$YourHashedPasswordHere', '2026-05-30 07:49:31', 'Admin', 'Admin User1', 'admin@inventory.com', 'inactive'),
(9, 'john_doe', '$2y$10$YourHashedPasswordHere', '2026-05-30 07:49:31', 'Staff', 'John Doe', 'john.doe@shoes.com', 'inactive'),
(12, 'izana', '$2y$10$9kCek/3xnVVOfsLT.XJQJOp9iJ/5DD4mzfTwM1SjRbJ7ZW0ph/yOK', '2026-05-31 20:49:35', 'User', 'Mark Anthony Enriquez', 'markanthonyenriquezzz@gmail.com', 'Active');

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
  ADD UNIQUE KEY `username` (`username`);

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
  MODIFY `order_id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;

--
-- AUTO_INCREMENT for table `transactions`
--
ALTER TABLE `transactions`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=7;

--
-- AUTO_INCREMENT for table `users`
--
ALTER TABLE `users`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=13;

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
