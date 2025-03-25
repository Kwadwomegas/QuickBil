-- phpMyAdmin SQL Dump
-- version 5.2.0
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: Mar 25, 2025 at 06:54 PM
-- Server version: 10.4.24-MariaDB
-- PHP Version: 8.1.6

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Database: `quickbil`
--

-- --------------------------------------------------------

--
-- Table structure for table `activity_logs`
--

CREATE TABLE `activity_logs` (
  `id` int(11) NOT NULL,
  `user_id` int(11) DEFAULT NULL,
  `action` varchar(255) DEFAULT NULL,
  `timestamp` datetime DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- --------------------------------------------------------

--
-- Table structure for table `battor_businesses`
--

CREATE TABLE `battor_businesses` (
  `account_number` varchar(20) NOT NULL,
  `business_name` varchar(100) DEFAULT NULL,
  `owner_name` varchar(100) DEFAULT NULL,
  `business_type` varchar(50) DEFAULT NULL,
  `telephone` varchar(20) DEFAULT NULL,
  `business_category` varchar(50) DEFAULT NULL,
  `location` varchar(100) DEFAULT NULL,
  `category` varchar(50) DEFAULT NULL,
  `old_fee` decimal(10,2) DEFAULT NULL,
  `previous_payment` decimal(10,2) DEFAULT NULL,
  `arrears` decimal(10,2) DEFAULT NULL,
  `current_fee` decimal(10,2) DEFAULT NULL,
  `amount_payable` decimal(10,2) DEFAULT NULL,
  `batch` varchar(20) DEFAULT NULL,
  `status` varchar(20) DEFAULT NULL,
  `digital_address` varchar(50) DEFAULT NULL,
  `year` int(11) DEFAULT NULL,
  `latitude` double DEFAULT NULL,
  `longitude` double DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

--
-- Dumping data for table `battor_businesses`
--

INSERT INTO `battor_businesses` (`account_number`, `business_name`, `owner_name`, `business_type`, `telephone`, `business_category`, `location`, `category`, `old_fee`, `previous_payment`, `arrears`, `current_fee`, `amount_payable`, `batch`, `status`, `digital_address`, `year`, `latitude`, `longitude`) VALUES
('BAT001', 'KabTech Consulting', 'Bismark Afful', 'I.T Consulting Firm', '0545041428', 'Services', 'Nungua', 'Large Scale', '0.00', '0.00', '0.00', '700.00', '700.00', '1', 'Active', '', 0, NULL, NULL),
('BAT002', 'KabTech Consulting', 'Bismark Afful', 'I.T Consulting Firm', '0545041428', 'Services', 'Nungua', 'Large Scale', '0.00', '0.00', '0.00', '700.00', '700.00', '1', 'Active', '', 2025, NULL, NULL),
('BAT003', 'Cheers TV', 'Cheers', 'Media', '0568798432', 'Services', 'HQVM+CF5, Accra, Ghana', 'Large Scale', '0.00', '0.00', '0.00', '7000.00', '7000.00', 'Cheers', 'Active', '<br /><b>Warning</b>:  Undefined array key 1 in <b', 2025, 5.5934976, -0.2162688);

-- --------------------------------------------------------

--
-- Table structure for table `battor_payments`
--

CREATE TABLE `battor_payments` (
  `payment_id` varchar(30) NOT NULL,
  `account_number` varchar(20) DEFAULT NULL,
  `business_name` varchar(100) DEFAULT NULL,
  `amount` decimal(10,2) DEFAULT NULL,
  `payment_date` date DEFAULT NULL,
  `receipt_number` varchar(50) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- --------------------------------------------------------

--
-- Table structure for table `juapong_businesses`
--

CREATE TABLE `juapong_businesses` (
  `account_number` varchar(20) NOT NULL,
  `business_name` varchar(100) DEFAULT NULL,
  `owner_name` varchar(100) DEFAULT NULL,
  `business_type` varchar(50) DEFAULT NULL,
  `telephone` varchar(20) DEFAULT NULL,
  `business_category` varchar(50) DEFAULT NULL,
  `location` varchar(100) DEFAULT NULL,
  `category` varchar(50) DEFAULT NULL,
  `old_fee` decimal(10,2) DEFAULT NULL,
  `previous_payment` decimal(10,2) DEFAULT NULL,
  `arrears` decimal(10,2) DEFAULT NULL,
  `current_fee` decimal(10,2) DEFAULT NULL,
  `amount_payable` decimal(10,2) DEFAULT NULL,
  `batch` varchar(20) DEFAULT NULL,
  `status` varchar(20) DEFAULT NULL,
  `digital_address` varchar(50) DEFAULT NULL,
  `year` int(11) DEFAULT NULL,
  `latitude` double DEFAULT NULL,
  `longitude` double DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

--
-- Dumping data for table `juapong_businesses`
--

INSERT INTO `juapong_businesses` (`account_number`, `business_name`, `owner_name`, `business_type`, `telephone`, `business_category`, `location`, `category`, `old_fee`, `previous_payment`, `arrears`, `current_fee`, `amount_payable`, `batch`, `status`, `digital_address`, `year`, `latitude`, `longitude`) VALUES
('JUA001', 'NTDA', 'Aseye Abledu', 'Local Government', '0244657865', 'Services', '', 'Small Scale', '0.00', '1000.00', '-1000.00', '9000.00', '9000.00', '2', 'Active', '<!DOCTYPE html><html lang=\"en\"><head>    <meta cha', 2025, 0, 0);

-- --------------------------------------------------------

--
-- Table structure for table `juapong_payments`
--

CREATE TABLE `juapong_payments` (
  `payment_id` varchar(30) NOT NULL,
  `account_number` varchar(20) DEFAULT NULL,
  `business_name` varchar(100) DEFAULT NULL,
  `amount` decimal(10,2) DEFAULT NULL,
  `payment_date` date DEFAULT NULL,
  `receipt_number` varchar(50) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

--
-- Dumping data for table `juapong_payments`
--

INSERT INTO `juapong_payments` (`payment_id`, `account_number`, `business_name`, `amount`, `payment_date`, `receipt_number`) VALUES
('PAY-001', 'JUA001', 'NTDA', '1000.00', '0000-00-00', 'yujkn');

-- --------------------------------------------------------

--
-- Table structure for table `mepe_businesses`
--

CREATE TABLE `mepe_businesses` (
  `account_number` varchar(20) NOT NULL,
  `business_name` varchar(100) DEFAULT NULL,
  `owner_name` varchar(100) DEFAULT NULL,
  `business_type` varchar(50) DEFAULT NULL,
  `telephone` varchar(20) DEFAULT NULL,
  `business_category` varchar(50) DEFAULT NULL,
  `location` varchar(100) DEFAULT NULL,
  `category` varchar(50) DEFAULT NULL,
  `old_fee` decimal(10,2) DEFAULT NULL,
  `previous_payment` decimal(10,2) DEFAULT NULL,
  `arrears` decimal(10,2) DEFAULT NULL,
  `current_fee` decimal(10,2) DEFAULT NULL,
  `amount_payable` decimal(10,2) DEFAULT NULL,
  `batch` varchar(20) DEFAULT NULL,
  `status` varchar(20) DEFAULT NULL,
  `digital_address` varchar(50) DEFAULT NULL,
  `year` int(11) DEFAULT NULL,
  `latitude` double DEFAULT NULL,
  `longitude` double DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

--
-- Dumping data for table `mepe_businesses`
--

INSERT INTO `mepe_businesses` (`account_number`, `business_name`, `owner_name`, `business_type`, `telephone`, `business_category`, `location`, `category`, `old_fee`, `previous_payment`, `arrears`, `current_fee`, `amount_payable`, `batch`, `status`, `digital_address`, `year`, `latitude`, `longitude`) VALUES
('MEP001', 'Media General', 'Zayne Ewusi', 'Media', '0543258791', 'Services', '', 'Large Scale', '0.00', '0.00', '0.00', '6000.00', '6000.00', '1', 'Active', '<!DOCTYPE html><html lang=\"en\"><head>    <meta cha', 2025, 5.65, -0.23);

-- --------------------------------------------------------

--
-- Table structure for table `mepe_payments`
--

CREATE TABLE `mepe_payments` (
  `payment_id` varchar(30) NOT NULL,
  `account_number` varchar(20) DEFAULT NULL,
  `business_name` varchar(100) DEFAULT NULL,
  `amount` decimal(10,2) DEFAULT NULL,
  `payment_date` date DEFAULT NULL,
  `receipt_number` varchar(50) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- --------------------------------------------------------

--
-- Table structure for table `users`
--

CREATE TABLE `users` (
  `id` int(11) NOT NULL,
  `full_name` varchar(100) NOT NULL,
  `username` varchar(50) NOT NULL,
  `role` enum('Admin','Finance','Budget') NOT NULL,
  `password` varchar(255) NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

--
-- Dumping data for table `users`
--

INSERT INTO `users` (`id`, `full_name`, `username`, `role`, `password`, `created_at`) VALUES
(1, 'Joojo Megas', 'Megas', 'Admin', '$2y$10$JgAfTNCYwfKjbnCo2uOCu.U5K4n0HXRDdvc4Bk5WmV3PPRsvqq0jG', '2025-03-23 02:58:34'),
(2, 'Kusi Francis', 'Kusi', 'Finance', '$2y$10$oC344xOw67Zc7abWZr/0AuDfl9tUwuXDVitiew/z8muom23BK0O12', '2025-03-23 05:30:38'),
(3, 'Asante Bismark', 'Asante', 'Budget', '$2y$10$sAG/f5otYHNqI/oS1knsjuV569g/vOMp9fXRWxDrn5KKtY5tDqQwW', '2025-03-23 05:32:17');

--
-- Indexes for dumped tables
--

--
-- Indexes for table `activity_logs`
--
ALTER TABLE `activity_logs`
  ADD PRIMARY KEY (`id`),
  ADD KEY `user_id` (`user_id`);

--
-- Indexes for table `battor_businesses`
--
ALTER TABLE `battor_businesses`
  ADD PRIMARY KEY (`account_number`);

--
-- Indexes for table `battor_payments`
--
ALTER TABLE `battor_payments`
  ADD PRIMARY KEY (`payment_id`),
  ADD KEY `account_number` (`account_number`);

--
-- Indexes for table `juapong_businesses`
--
ALTER TABLE `juapong_businesses`
  ADD PRIMARY KEY (`account_number`);

--
-- Indexes for table `juapong_payments`
--
ALTER TABLE `juapong_payments`
  ADD PRIMARY KEY (`payment_id`),
  ADD KEY `account_number` (`account_number`);

--
-- Indexes for table `mepe_businesses`
--
ALTER TABLE `mepe_businesses`
  ADD PRIMARY KEY (`account_number`);

--
-- Indexes for table `mepe_payments`
--
ALTER TABLE `mepe_payments`
  ADD PRIMARY KEY (`payment_id`),
  ADD KEY `account_number` (`account_number`);

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
-- AUTO_INCREMENT for table `activity_logs`
--
ALTER TABLE `activity_logs`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `users`
--
ALTER TABLE `users`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `activity_logs`
--
ALTER TABLE `activity_logs`
  ADD CONSTRAINT `activity_logs_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`);

--
-- Constraints for table `battor_payments`
--
ALTER TABLE `battor_payments`
  ADD CONSTRAINT `battor_payments_ibfk_1` FOREIGN KEY (`account_number`) REFERENCES `battor_businesses` (`account_number`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Constraints for table `juapong_payments`
--
ALTER TABLE `juapong_payments`
  ADD CONSTRAINT `juapong_payments_ibfk_1` FOREIGN KEY (`account_number`) REFERENCES `juapong_businesses` (`account_number`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Constraints for table `mepe_payments`
--
ALTER TABLE `mepe_payments`
  ADD CONSTRAINT `mepe_payments_ibfk_1` FOREIGN KEY (`account_number`) REFERENCES `mepe_businesses` (`account_number`) ON DELETE CASCADE ON UPDATE CASCADE;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
