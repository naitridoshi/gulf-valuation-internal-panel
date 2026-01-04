-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: Jan 04, 2026 at 09:09 AM
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
-- Database: `production_db`
--

-- --------------------------------------------------------

--
-- Table structure for table `banks`
--

CREATE TABLE `banks` (
  `id` int(11) NOT NULL,
  `name` varchar(100) NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `banks`
--

INSERT INTO `banks` (`id`, `name`, `created_at`) VALUES
(1, 'Bank Muscat', '2025-09-05 09:31:47'),
(2, 'Bank Dhofar', '2025-09-05 09:31:47'),
(3, 'National Bank of Oman', '2025-09-05 09:31:47'),
(4, 'Sohar International', '2025-09-05 09:31:47'),
(5, 'Ahli Bank', '2025-09-05 09:31:47'),
(6, 'Oman Arab Bank', '2025-09-05 09:31:47'),
(7, 'Bank Nizwa', '2025-09-05 09:31:47'),
(8, 'Alizz Islamic Bank', '2025-09-05 09:31:47'),
(9, 'Oman Housing Bank', '2025-09-05 09:31:47'),
(10, 'Oman Development Bank', '2025-09-05 09:31:47'),
(11, 'Bank of Beirut', '2025-09-05 09:31:47'),
(12, 'First Abu Dhabi Bank (Oman Branch)', '2025-09-05 09:31:47'),
(13, 'Qatar National Bank (Oman Branch)', '2025-09-05 09:31:47'),
(14, 'Bank of Baroda (Oman Branch)', '2025-09-05 09:31:47'),
(15, 'State Bank of India (Oman Branch)', '2025-09-05 09:31:47'),
(16, 'Standard Chartered Bank (Oman Branch)', '2025-09-05 09:31:47'),
(17, 'Bank Melli Iran (Oman Branch)', '2025-09-05 09:31:47'),
(18, 'Qatar National Bank', '2025-09-05 09:31:47'),
(19, 'First Abu Dhabi Bank', '2025-09-05 09:31:47'),
(20, 'Emirates NBD', '2025-09-05 09:31:47'),
(21, 'Dubai Islamic Bank', '2025-09-05 09:31:47'),
(22, 'Mashreq Bank', '2025-09-05 09:31:47'),
(23, 'Saudi National Bank (SNB)', '2025-09-05 09:31:47'),
(24, 'Al Rajhi Bank', '2025-09-05 09:31:47'),
(25, 'Riyad Bank', '2025-09-05 09:31:47'),
(26, 'Arab National Bank', '2025-09-05 09:31:47'),
(27, 'Kuwait Finance House', '2025-09-05 09:31:47'),
(28, 'Gulf Bank Kuwait', '2025-09-05 09:31:47'),
(29, 'National Bank of Kuwait', '2025-09-05 09:31:47'),
(30, 'Arab Bank', '2025-09-05 09:31:47'),
(31, 'Jordan Islamic Bank', '2025-09-05 09:31:47'),
(32, 'Ahli United Bank', '2025-09-05 09:31:47'),
(33, 'Bahrain Islamic Bank', '2025-09-05 09:31:47'),
(34, 'Muscat Finance Company', '2025-09-05 09:31:47'),
(35, 'FINCORP SAOG', '2025-09-05 09:31:47'),
(36, 'United Finance Company', '2025-09-05 09:31:47'),
(37, 'Al Omaniya Financial Services', '2025-09-05 09:31:47'),
(38, 'Taageer Finance', '2025-09-05 09:31:47'),
(39, 'National Finance Company', '2025-09-05 09:31:47'),
(40, 'Dhofar International Development & Investment Holding', '2025-09-05 09:31:47'),
(41, 'GroFin Oman', '2025-09-05 09:31:47'),
(71, 'AHLI ISLAMIC BANK', '2026-01-04 06:07:56'),
(72, '', '2026-01-04 06:32:22');

-- --------------------------------------------------------

--
-- Table structure for table `car_models`
--

CREATE TABLE `car_models` (
  `id` int(11) NOT NULL,
  `car_company` varchar(100) NOT NULL,
  `car_model` varchar(100) NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `car_models`
--

INSERT INTO `car_models` (`id`, `car_company`, `car_model`, `created_at`) VALUES
(1, 'HONDA', 'ACCORD', '2025-09-14 19:03:01'),
(13, 'KIA', 'ACCORD', '2025-09-28 09:35:10'),
(20, 'AUDI', 'ACCORD', '2025-09-29 06:02:14'),
(21, 'AUDI', 'dfa', '2025-12-25 15:08:43'),
(22, 'HONDA', '2021', '2025-12-31 08:42:51'),
(27, 'AUDI', '2021', '2025-12-31 09:17:52'),
(29, 'AUDI', '2022', '2026-01-04 06:02:41'),
(31, 'MERCEDES', '2017', '2026-01-04 07:11:23');

-- --------------------------------------------------------

--
-- Table structure for table `places`
--

CREATE TABLE `places` (
  `id` int(11) NOT NULL,
  `name` varchar(100) NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `places`
--

INSERT INTO `places` (`id`, `name`, `created_at`) VALUES
(1, 'BARKA', '2025-09-14 19:03:01'),
(17, 'MUSCAT', '2025-09-29 05:53:26'),
(31, 'HAIL', '2026-01-04 07:11:23');

-- --------------------------------------------------------

--
-- Table structure for table `requestors`
--

CREATE TABLE `requestors` (
  `id` int(11) NOT NULL,
  `name` varchar(100) NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `requestors`
--

INSERT INTO `requestors` (`id`, `name`, `created_at`) VALUES
(1, 'SAIF ABDULLAH MOHAMMED AL SUBHI', '2025-09-14 19:03:01'),
(2, 'MAHMOOD ABDULLAH MOHAMED AL SUBHI', '2025-09-14 19:03:01'),
(30, 'Naitri Doshi', '2025-09-29 05:53:26'),
(35, 'HAMED MUSLEM', '2025-12-31 08:42:51'),
(40, 'HAMED MUSLEM c', '2025-12-31 09:17:52'),
(42, 'Hetal', '2026-01-04 06:02:41'),
(43, 'KHALID SAID AHMED AL YAHYAI', '2026-01-04 06:32:22');

-- --------------------------------------------------------

--
-- Table structure for table `settings`
--

CREATE TABLE `settings` (
  `id` int(11) NOT NULL,
  `setting_key` varchar(50) NOT NULL,
  `setting_value` text NOT NULL,
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `settings`
--

INSERT INTO `settings` (`id`, `setting_key`, `setting_value`, `updated_at`) VALUES
(1, 'timezone', 'Asia/Muscat', '2025-12-31 07:27:42'),
(2, 'company_name', 'Gulf Adjusters and Surveyors', '2025-12-31 07:27:42'),
(3, 'logo', './assets/images/company_logo.png', '2025-12-31 07:27:42'),
(4, 'ref_prefix', 'GAS/VAL/', '2025-12-31 07:27:42'),
(5, 'ref_number', '21003', '2026-01-04 07:11:23'),
(6, 'valuation_footer', 'Based on our observation, age, maintenance and performance…\r\nThis report is true to the best of our knowledge…\r\nFor GULF ADJUSTERS, SURVEYORS & SERVICES LLC\r\nAuthorized Signatory\r\nNote: Refer Basis of Valuation…', '2025-12-31 07:27:42'),
(19, 'valuation_statement', 'Based on our observation, age, maintenance and performance of the vehicle, we are of the opinion that the present market value of the above vehicle with the existing specification on “as is where is conditions” is approximately', '2025-12-31 07:27:42'),
(20, 'report_disclaimer', 'This report is true to the best of our knowledge and is issued without prejudice subject to the valuation as per condition of the vehicle at the date, place and time of our inspection/observation.', '2025-12-31 07:27:42'),
(21, 'company_signature', 'For GULF ADJUSTERS, SURVEYORS & SERVICES LLC', '2025-12-31 07:27:42');

-- --------------------------------------------------------

--
-- Table structure for table `users`
--

CREATE TABLE `users` (
  `id` int(11) NOT NULL,
  `username` varchar(50) NOT NULL,
  `password` varchar(255) NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `users`
--

INSERT INTO `users` (`id`, `username`, `password`, `created_at`) VALUES
(1, 'admin', '$2y$10$Q8f7z6o6yXz3K3z9g2vW7eN4q1l2k3m4n5o6p7q8r9s0t1u2v3w4x5', '2025-09-04 22:27:08'),
(4, 'Darshit', '$2y$10$lhHipcNmPLUiv/Uq2.XLVO/26mkJT11rPb7SySbCsq8brVDYRcpZC', '2025-09-04 23:11:12'),
(5, 'Hetal', '$2y$10$GfM0ll1AMEOGL0HzxtptBupVAezMgxD59IfwfcWYlmTzfh3/vqSoa', '2025-09-15 17:46:05'),
(6, 'Parth', '$2y$10$td6towxOzZm.YiYAuw4EJegBT3RljyU/YnJxeVdgVFrSnSzf7iXme', '2025-10-16 09:22:55');

-- --------------------------------------------------------

--
-- Table structure for table `valuations`
--

CREATE TABLE `valuations` (
  `id` int(11) NOT NULL,
  `ref_number` varchar(50) DEFAULT NULL,
  `valuation_date` date DEFAULT NULL,
  `requestor_name` varchar(255) DEFAULT NULL,
  `requestor_contact_2` varchar(50) DEFAULT NULL,
  `bank_id` int(11) NOT NULL,
  `buyer` varchar(255) DEFAULT NULL,
  `seller` varchar(255) DEFAULT NULL,
  `place_of_asset` varchar(255) DEFAULT NULL,
  `car_company` varchar(255) DEFAULT NULL,
  `vehicle_type` varchar(255) DEFAULT NULL,
  `car_model` varchar(255) DEFAULT NULL,
  `registration_number` varchar(50) DEFAULT NULL,
  `year_of_manufacture` int(11) DEFAULT NULL,
  `date_of_registration` date DEFAULT NULL,
  `chassis_number` varchar(100) DEFAULT NULL,
  `engine_number` varchar(100) DEFAULT NULL,
  `odometer_reading` varchar(50) DEFAULT NULL,
  `features` text DEFAULT NULL,
  `special_note` text DEFAULT NULL,
  `engine_transmission` varchar(255) DEFAULT NULL,
  `body_paint` varchar(255) DEFAULT NULL,
  `tyres` varchar(255) DEFAULT NULL,
  `valuation_amount` decimal(10,3) DEFAULT NULL,
  `forced_sale_valuation_amount` decimal(10,3) DEFAULT NULL,
  `vehicle_color` varchar(255) DEFAULT NULL,
  `transmission_type` varchar(255) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `valuations`
--

INSERT INTO `valuations` (`id`, `ref_number`, `valuation_date`, `requestor_name`, `requestor_contact_2`, `bank_id`, `buyer`, `seller`, `place_of_asset`, `car_company`, `vehicle_type`, `car_model`, `registration_number`, `year_of_manufacture`, `date_of_registration`, `chassis_number`, `engine_number`, `odometer_reading`, `features`, `special_note`, `engine_transmission`, `body_paint`, `tyres`, `valuation_amount`, `forced_sale_valuation_amount`, `vehicle_color`, `transmission_type`) VALUES
(1, 'GAS/VAL/25/0001', '2025-09-14', 'SAIF ABDULLAH MOHAMMED AL SUBHI', '97064643', 2, 'SAIF ABDULLAH MOHAMMED AL SUBHI', NULL, 'BARKA', 'KIA', 'Hatchback', 'ACCORD', '67935 Y/WHITE', 2015, '2025-09-03', '1HGCV1F43JA006189', 'NIL', '159659 KM', 'FULL OPTION- CD, MP3, POWER WINDOW, SENSOR, BLUETOOTH, SUNROOF, LEATHER SEATS, NAVIGATOR, AIR BAGS & ALLOY WHEELS', 'REFURBISHED, PROCURED FROM UAE, VALUATION BASED ON OMAN DEALER PRICING.', 'OK', 'OK', 'OK', 6250.000, 2500.000, NULL, NULL),
(2, 'GAS/VAL/25/0002', '2025-09-29', 'Naitri Doshi', '8839374389', 1, 'DARSHIT MEHTA', NULL, 'MUSCAT', 'KIA', 'SUV', 'ACCORD', '67935 Y/WHITE', 2021, '2025-09-01', '1HGCV1F43JA006189', '48438738994232', '159659 KM', 'FULL OPTION- CD, MP3, POWER WINDOW, SENSOR, BLUETOOTH, SUNROOF, LEATHER SEATS, NAVIGATOR, AIR BAGS & ALLOY WHEELS', 'REFURBISHED, PROCURED FROM UAE, VALUATION BASED ON OMAN DEALER PRICING.', 'OK', 'OK', 'OK', 6640.000, 6000.000, NULL, NULL),
(3, 'GAS/VAL/25/0003', '2025-09-29', 'MAHMOOD ABDULLAH MOHAMED AL SUBHI', NULL, 2, 'DARSHIT MEHTA', NULL, 'MUSCAT', 'AUDI', 'Sedan', 'ACCORD', '67935 Y/WHITE', 2010, '2025-09-08', '1HGCV1F43JA006189', '48438738994232', '159659 KM', 'FULL OPTION- CD, MP3, POWER WINDOW, SENSOR, BLUETOOTH, SUNROOF, LEATHER SEATS, NAVIGATOR, AIR BAGS & ALLOY WHEELS', 'REFURBISHED, PROCURED FROM UAE, VALUATION BASED ON OMAN DEALER PRICING.', 'OK', 'OK', 'OK', 6778.000, 6000.000, NULL, NULL),
(4, 'GAS/VAL/25/0004', '2025-12-25', 'MAHMOOD ABDULLAH MOHAMED AL SUBHI', NULL, 5, 'sa', 'fasd', 'MUSCAT', 'AUDI', 'as', 'dfa', 'adf', 2013, '2025-01-01', 'fda', 'adf', '21561', 'FULL OPTION- CD, MP3, POWER WINDOW, SENSOR, BLUETOOTH, SUNROOF, LEATHER SEATS, NAVIGATOR, AIR BAGS & ALLOY WHEELS', 'REFURBISHED, PROCURED FROM UAE, VALUATION BASED ON OMAN DEALER PRICING.', 'OK', 'OK', 'OK', 23534.000, 324.000, NULL, NULL),
(5, 'GAS/VAL/25/0005', '2025-12-31', 'MAHMOOD ABDULLAH MOHAMED AL SUBHI', '92777937', 2, 'HAMED MUSLEM', 'YASIR MOHAMMED', 'BARKA', 'AUDI', 'LAND CRUISER VXR V8 4.6L MADE IN JAPAN', '2021', '6494 BB', 2014, '2015-05-12', 'JTMHX01J3M4213318', '1UR08492901', '190636 KM', 'FULL OPTION- CD, MP3, POWER WINDOW, SENSOR, BLUETOOTH, SUNROOF, LEATHER SEATS, NAVIGATOR, AIR BAGS & ALLOY WHEELS', 'REFURBISHED, PROCURED FROM UAE, VALUATION BASED ON OMAN DEALER PRICING.', 'GOOD', 'OK', 'OK', 2852.000, 2915.000, 'WHITE', 'AUTOMATIC'),
(7, 'GAS/VAL/26/21001', '2026-01-04', 'KHALID SAID AHMED AL YAHYAI', '9897126', 71, 'KHALID SAID AHMED AL YAHYAI', '-', 'MUSCAT', 'HONDA', 'GLE', '2021', '8089 D', 2021, '2025-05-01', 'CVH', 'FDNDJ', '261322 KM', 'FULL OPTION- CD, MP3, POWER WINDOW, SENSOR, BLUETOOTH, SUNROOF, LEATHER SEATS, NAVIGATOR, AIR BAGS & ALLOY WHEELS', 'REFURBISHED, PROCURED FROM UAE, VALUATION BASED ON OMAN DEALER PRICING.', 'OK', 'OK', 'OK', 6250.000, NULL, 'WHITE', 'AUTOMATIC'),
(8, 'GAS/VAL/26/21002', '2026-01-04', 'KHALID SAID AHMED AL YAHYAI', '98971267', 71, 'KHALID SAID AHMED AL YAHYAI', 'FAISAL SAID AHMED AL YAHYAI', 'HAIL', 'MERCEDES', 'GLE 43 AMG 3.0L MADE IN USA', '2017', '8089 D', 2016, '2020-06-16', '4JGED6EB9HA054289', 'NIL', '291922 KM', 'FULL OPTION- CD, MP3, POWER WINDOW, SENSOR, BLUETOOTH, SUNROOF, LEATHER SEATS, NAVIGATOR, AIR BAGS & ALLOY WHEELS', 'REFURBISHED, PROCURED FROM UAE, VALUATION BASED ON OMAN DEALER PRICING.', 'OK', 'OK', 'OK', 10500.000, NULL, 'BLACK', 'AUTOMATIC');

-- --------------------------------------------------------

--
-- Table structure for table `valuations_backup`
--

CREATE TABLE `valuations_backup` (
  `id` int(11) NOT NULL DEFAULT 0,
  `ref_number` varchar(50) NOT NULL,
  `valuation_date` date NOT NULL,
  `requestor_name` varchar(100) NOT NULL,
  `bank_name` varchar(100) NOT NULL,
  `buyer` varchar(255) DEFAULT NULL,
  `seller` varchar(100) NOT NULL,
  `place_of_asset` varchar(100) NOT NULL,
  `car_company` varchar(100) NOT NULL,
  `vehicle_type` varchar(100) NOT NULL,
  `car_model` varchar(100) NOT NULL,
  `registration_number` varchar(50) NOT NULL,
  `year_of_manufacture` int(11) NOT NULL,
  `date_of_registration` date NOT NULL,
  `chassis_number` varchar(50) NOT NULL,
  `engine_number` varchar(50) NOT NULL,
  `odometer_reading` varchar(50) NOT NULL,
  `features` text NOT NULL,
  `special_note` text NOT NULL,
  `engine_transmission` text NOT NULL,
  `body_paint` text NOT NULL,
  `tyres` text NOT NULL,
  `valuation_amount` decimal(10,2) NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `vehicle_types`
--

CREATE TABLE `vehicle_types` (
  `id` int(11) NOT NULL,
  `type` varchar(100) NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `vehicle_types`
--

INSERT INTO `vehicle_types` (`id`, `type`, `created_at`) VALUES
(1, 'Sedan', '2025-09-05 09:31:47'),
(2, 'SUV', '2025-09-05 09:31:47'),
(3, 'Truck', '2025-09-05 09:31:47'),
(4, 'Coupe', '2025-09-05 09:31:47'),
(5, 'Hatchback', '2025-09-05 09:31:47'),
(26, 'as', '2025-12-25 15:08:43'),
(27, 'LAND CRUISER VXR V8 4.6L MADE IN JAPAN', '2025-12-31 08:42:51'),
(35, 'GLE', '2026-01-04 06:32:22'),
(36, 'GLE 43 AMG 3.0L MADE IN USA', '2026-01-04 07:11:23');

--
-- Indexes for dumped tables
--

--
-- Indexes for table `banks`
--
ALTER TABLE `banks`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `name` (`name`);

--
-- Indexes for table `car_models`
--
ALTER TABLE `car_models`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `car_company` (`car_company`,`car_model`);

--
-- Indexes for table `places`
--
ALTER TABLE `places`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `name` (`name`);

--
-- Indexes for table `requestors`
--
ALTER TABLE `requestors`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `name` (`name`);

--
-- Indexes for table `settings`
--
ALTER TABLE `settings`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `setting_key` (`setting_key`);

--
-- Indexes for table `users`
--
ALTER TABLE `users`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `username` (`username`);

--
-- Indexes for table `valuations`
--
ALTER TABLE `valuations`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `vehicle_types`
--
ALTER TABLE `vehicle_types`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `type` (`type`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `banks`
--
ALTER TABLE `banks`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=74;

--
-- AUTO_INCREMENT for table `car_models`
--
ALTER TABLE `car_models`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=32;

--
-- AUTO_INCREMENT for table `places`
--
ALTER TABLE `places`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=32;

--
-- AUTO_INCREMENT for table `requestors`
--
ALTER TABLE `requestors`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=45;

--
-- AUTO_INCREMENT for table `settings`
--
ALTER TABLE `settings`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=78;

--
-- AUTO_INCREMENT for table `users`
--
ALTER TABLE `users`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=7;

--
-- AUTO_INCREMENT for table `valuations`
--
ALTER TABLE `valuations`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=9;

--
-- AUTO_INCREMENT for table `vehicle_types`
--
ALTER TABLE `vehicle_types`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=37;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
