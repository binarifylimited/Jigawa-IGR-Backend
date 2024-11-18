-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: Nov 18, 2024 at 05:54 AM
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
-- Database: `plateauigr_ibs`
--

-- --------------------------------------------------------

--
-- Table structure for table `administrative_users`
--

CREATE TABLE `administrative_users` (
  `id` int(11) NOT NULL,
  `fullname` varchar(100) NOT NULL,
  `email` varchar(50) NOT NULL,
  `phone` varchar(15) NOT NULL,
  `password` longtext NOT NULL,
  `role` enum('super_admin','admin_support','support') NOT NULL,
  `img` longtext NOT NULL,
  `verification_status` enum('true','false') NOT NULL,
  `date_updated` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  `date_created` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=latin1 COLLATE=latin1_swedish_ci;

--
-- Dumping data for table `administrative_users`
--

INSERT INTO `administrative_users` (`id`, `fullname`, `email`, `phone`, `password`, `role`, `img`, `verification_status`, `date_updated`, `date_created`) VALUES
(1, 'Abubakar Admin', 'assadeeq929@hotmail.com', '1234567890', '$2y$10$3tMhVsyqL70RG79KaaUGKOWV9nVisfXYtdUXnhcoHNzbYEkDr0eg6', 'super_admin', '', 'false', '2024-10-23 00:52:58', '2024-10-16 13:15:06'),
(3, 'John Doe', 'admin@example.com', '1234567890', '$2y$10$3tMhVsyqL70RG79KaaUGKOWV9nVisfXYtdUXnhcoHNzbYEkDr0eg6', 'super_admin', 'path/to/image.jpg', 'true', '2024-10-16 13:23:50', '2024-10-16 13:23:50');

-- --------------------------------------------------------

--
-- Table structure for table `admin_permissions`
--

CREATE TABLE `admin_permissions` (
  `id` int(11) NOT NULL,
  `admin_id` int(11) NOT NULL,
  `permission_id` int(11) NOT NULL,
  `date_created` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `admin_permissions`
--

INSERT INTO `admin_permissions` (`id`, `admin_id`, `permission_id`, `date_created`) VALUES
(3, 3, 1, '2024-10-16 13:23:50'),
(4, 3, 2, '2024-10-16 13:23:50'),
(5, 3, 3, '2024-10-16 13:23:50'),
(6, 3, 4, '2024-10-16 13:23:50'),
(7, 4, 1, '2024-10-16 13:32:37'),
(8, 4, 2, '2024-10-16 13:32:37'),
(9, 4, 3, '2024-10-16 13:32:37'),
(10, 4, 4, '2024-10-16 13:32:37');

-- --------------------------------------------------------

--
-- Table structure for table `api_audit_logs`
--

CREATE TABLE `api_audit_logs` (
  `id` int(11) NOT NULL,
  `user_id` int(11) DEFAULT NULL,
  `user_type` varchar(50) DEFAULT NULL,
  `endpoint` varchar(255) NOT NULL,
  `method` varchar(10) NOT NULL,
  `request_payload` text NOT NULL,
  `response_payload` text NOT NULL,
  `status_code` int(11) NOT NULL,
  `ip_address` varchar(50) DEFAULT NULL,
  `user_agent` varchar(255) DEFAULT NULL,
  `timestamp` datetime DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `employee_salary_and_benefits`
--

CREATE TABLE `employee_salary_and_benefits` (
  `id` int(11) NOT NULL,
  `employee_id` int(11) NOT NULL,
  `basic_salary` decimal(10,2) NOT NULL,
  `date_employed` date NOT NULL,
  `housing` decimal(10,2) DEFAULT 0.00,
  `transport` decimal(10,2) DEFAULT 0.00,
  `utility` decimal(10,2) DEFAULT 0.00,
  `medical` decimal(10,2) DEFAULT 0.00,
  `entertainment` decimal(10,2) DEFAULT 0.00,
  `leaves` int(11) DEFAULT 0,
  `annual_gross_income` decimal(12,2) GENERATED ALWAYS AS (`basic_salary` + `housing` + `transport` + `utility` + `medical` + `entertainment`) STORED,
  `new_gross` decimal(12,2) DEFAULT NULL,
  `monthly_tax_payable` decimal(10,2) DEFAULT NULL,
  `created_date` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `employee_salary_and_benefits`
--

INSERT INTO `employee_salary_and_benefits` (`id`, `employee_id`, `basic_salary`, `date_employed`, `housing`, `transport`, `utility`, `medical`, `entertainment`, `leaves`, `new_gross`, `monthly_tax_payable`, `created_date`) VALUES
(2, 2, 1200000.00, '2024-10-20', 200000.00, 150000.00, 100000.00, 50000.00, 80000.00, 30, 1780000.00, 16753.68, '2024-10-20 14:09:09');

-- --------------------------------------------------------

--
-- Table structure for table `enumerator_corporate_info`
--

CREATE TABLE `enumerator_corporate_info` (
  `id` int(11) NOT NULL,
  `user_tax_number` varchar(50) NOT NULL,
  `category` varchar(100) DEFAULT NULL,
  `name` varchar(255) NOT NULL,
  `industry` varchar(255) DEFAULT NULL,
  `staff_quota` int(11) DEFAULT 0,
  `tin` varchar(50) DEFAULT NULL,
  `email` varchar(100) DEFAULT NULL,
  `state` varchar(50) DEFAULT NULL,
  `lga` varchar(50) DEFAULT NULL,
  `address` text DEFAULT NULL,
  `area` varchar(100) DEFAULT NULL,
  `tax_category` varchar(100) DEFAULT NULL,
  `business_type` varchar(100) DEFAULT NULL,
  `revenue_return` decimal(15,2) DEFAULT 0.00,
  `valuation` decimal(15,2) DEFAULT 0.00,
  `img` text DEFAULT NULL,
  `time_in` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `enumerator_corporate_info`
--

INSERT INTO `enumerator_corporate_info` (`id`, `user_tax_number`, `category`, `name`, `industry`, `staff_quota`, `tin`, `email`, `state`, `lga`, `address`, `area`, `tax_category`, `business_type`, `revenue_return`, `valuation`, `img`, `time_in`) VALUES
(2, '4002045594', 'corporate', 'Corporate Business', 'Manufacturing', 100, NULL, 'corporate@example.com', 'Lagos', 'Ikeja', '456 Business District', 'Central', 'Commercial', 'Manufacturing', 5000000.00, 10000000.00, NULL, '2024-10-22 23:26:55');

-- --------------------------------------------------------

--
-- Table structure for table `enumerator_property_info`
--

CREATE TABLE `enumerator_property_info` (
  `id` int(11) NOT NULL,
  `user_tax_number` varchar(50) NOT NULL,
  `property_id` varchar(50) NOT NULL,
  `property_file` text DEFAULT NULL,
  `property_type` varchar(100) DEFAULT NULL,
  `property_area` decimal(10,2) DEFAULT NULL,
  `latitude` decimal(9,6) DEFAULT NULL,
  `longitude` decimal(9,6) DEFAULT NULL,
  `state` varchar(50) DEFAULT NULL,
  `lga` varchar(50) DEFAULT NULL,
  `address` text DEFAULT NULL,
  `area` varchar(100) DEFAULT NULL,
  `tax_category` varchar(100) DEFAULT NULL,
  `img` text DEFAULT NULL,
  `timeIn` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `enumerator_property_info`
--

INSERT INTO `enumerator_property_info` (`id`, `user_tax_number`, `property_id`, `property_file`, `property_type`, `property_area`, `latitude`, `longitude`, `state`, `lga`, `address`, `area`, `tax_category`, `img`, `timeIn`) VALUES
(4, '4002045594', 'PROP001', 'path/to/property/file.pdf', 'Residential', 500.00, 6.524400, 3.379200, 'Lagos', 'Ikeja', '123 Main St', 'GRA', 'Residential', 'path/to/image.jpg', '2024-10-22 23:26:55');

-- --------------------------------------------------------

--
-- Table structure for table `enumerator_tax_payers`
--

CREATE TABLE `enumerator_tax_payers` (
  `id` int(11) NOT NULL,
  `tax_number` varchar(50) NOT NULL,
  `first_name` varchar(100) NOT NULL,
  `last_name` varchar(100) NOT NULL,
  `email` varchar(100) NOT NULL,
  `phone` varchar(20) DEFAULT NULL,
  `password` longtext NOT NULL,
  `tin` varchar(50) DEFAULT NULL,
  `employment_status` varchar(50) DEFAULT NULL,
  `id_type` varchar(50) DEFAULT NULL,
  `id_number` varchar(50) DEFAULT NULL,
  `business_status` varchar(50) DEFAULT NULL,
  `business_type` varchar(100) DEFAULT NULL,
  `position` varchar(100) DEFAULT NULL,
  `state` varchar(50) DEFAULT NULL,
  `lga` varchar(50) DEFAULT NULL,
  `address` text DEFAULT NULL,
  `area` varchar(100) DEFAULT NULL,
  `verification_date` date DEFAULT NULL,
  `account_status` enum('active','inactive') DEFAULT 'inactive',
  `tin_status` enum('verified','unverified') DEFAULT 'unverified',
  `category` enum('individual','corporate') NOT NULL,
  `account_type` enum('regular','premium') DEFAULT 'regular',
  `property_owner` enum('yes','no') DEFAULT 'no',
  `revenue_return` decimal(15,2) DEFAULT 0.00,
  `valuation` decimal(15,2) DEFAULT 0.00,
  `created_by_enumerator_user` varchar(50) DEFAULT NULL,
  `img` text DEFAULT NULL,
  `verification` varchar(50) DEFAULT NULL,
  `verification_code` varchar(100) DEFAULT NULL,
  `staff_quota` int(11) DEFAULT 0,
  `timeIn` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `enumerator_tax_payers`
--

INSERT INTO `enumerator_tax_payers` (`id`, `tax_number`, `first_name`, `last_name`, `email`, `phone`, `password`, `tin`, `employment_status`, `id_type`, `id_number`, `business_status`, `business_type`, `position`, `state`, `lga`, `address`, `area`, `verification_date`, `account_status`, `tin_status`, `category`, `account_type`, `property_owner`, `revenue_return`, `valuation`, `created_by_enumerator_user`, `img`, `verification`, `verification_code`, `staff_quota`, `timeIn`) VALUES
(4, '4002045594', 'Jane', 'Doe', 'janedoe@example.com', '08012345678', '$2y$10$itgIhezPCH8O3OebU2LjquYvWdkvuCUY4sZIOJfPBFTLiL0ZL7ig2', '1234567890', 'Employed', 'National ID', 'A123456789', 'Active', 'Retail', 'Manager', 'Lagos', 'Ikeja', '123 Market Street', 'Central', NULL, 'active', NULL, 'corporate', NULL, NULL, NULL, NULL, '1', NULL, NULL, 'C34EF7', NULL, '2024-10-22 23:26:55');

-- --------------------------------------------------------

--
-- Table structure for table `enumerator_users`
--

CREATE TABLE `enumerator_users` (
  `id` int(11) NOT NULL,
  `agent_id` varchar(20) NOT NULL,
  `fullname` varchar(200) NOT NULL,
  `password` longtext NOT NULL,
  `email` varchar(200) NOT NULL,
  `address` varchar(200) NOT NULL,
  `state` varchar(30) NOT NULL,
  `lga` varchar(30) NOT NULL,
  `phone` varchar(15) NOT NULL,
  `img` longtext DEFAULT NULL,
  `timeIn` timestamp NOT NULL DEFAULT current_timestamp(),
  `status` enum('Active','Deactivated') NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=latin1 COLLATE=latin1_swedish_ci;

--
-- Dumping data for table `enumerator_users`
--

INSERT INTO `enumerator_users` (`id`, `agent_id`, `fullname`, `password`, `email`, `address`, `state`, `lga`, `phone`, `img`, `timeIn`, `status`) VALUES
(1, 'A12345', 'Enumerator User', '$2y$10$xheibSPlOk2yABSMzOcX3eBRcZ/JcPJ1etucQtHz.z7JRHNTmMHgu', 'enumerator@example.com', '123 Main Street', 'Lagos', 'Ikeja', '1234567890', 'path/to/image.jpg', '2024-10-16 18:36:56', 'Active');

-- --------------------------------------------------------

--
-- Table structure for table `gateway_payload`
--

CREATE TABLE `gateway_payload` (
  `id` int(11) NOT NULL,
  `gateway` text NOT NULL,
  `payload` longtext NOT NULL,
  `result_out` longtext DEFAULT NULL,
  `date_created` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `gateway_payload`
--

INSERT INTO `gateway_payload` (`id`, `gateway`, `payload`, `result_out`, `date_created`) VALUES
(1, 'paystack', '{\"event\":\"charge.success\",\"data\":{\"id\":4286956222,\"domain\":\"live\",\"status\":\"success\",\"reference\":\"T671076302190401\",\"amount\":8500000,\"paid_at\":\"2024-10-18T19:34:27.000Z\",\"authorization\":{\"card_type\":\"transfer\",\"bank\":\"OPay Digital Services Limited (OPay)\"},\"metadata\":{\"custom_fields\":[{\"display_name\":\"Invoice Number\",\"variable_name\":\"invoice_number\",\"value\":\"7982379824\"}]}}}', '', '2024-10-24 09:57:33'),
(2, 'paystack', '{\"event\":\"charge.success\",\"data\":{\"id\":4286956222,\"domain\":\"live\",\"status\":\"success\",\"reference\":\"T671076302190401\",\"amount\":8500000,\"paid_at\":\"2024-10-18T19:34:27.000Z\",\"authorization\":{\"card_type\":\"transfer\",\"bank\":\"OPay Digital Services Limited (OPay)\"},\"metadata\":{\"custom_fields\":[{\"display_name\":\"Invoice Number\",\"variable_name\":\"invoice_number\",\"value\":\"7982379824\"}]}}}', '', '2024-10-24 10:03:30'),
(3, 'paystack', '{\"event\":\"charge.success\",\"data\":{\"id\":4286956222,\"domain\":\"live\",\"status\":\"success\",\"reference\":\"T671076302190401\",\"amount\":8500000,\"paid_at\":\"2024-10-18T19:34:27.000Z\",\"authorization\":{\"card_type\":\"transfer\",\"bank\":\"OPay Digital Services Limited (OPay)\"},\"metadata\":{\"custom_fields\":[{\"display_name\":\"Invoice Number\",\"variable_name\":\"invoice_number\",\"value\":\"INV-6718C06887D13\"}]}}}', '', '2024-10-24 10:05:48'),
(4, 'paystack', '{\"event\":\"charge.success\",\"data\":{\"id\":4286956222,\"domain\":\"live\",\"status\":\"success\",\"reference\":\"T671076302190401\",\"amount\":8500000,\"paid_at\":\"2024-10-18T19:34:27.000Z\",\"authorization\":{\"card_type\":\"transfer\",\"bank\":\"OPay Digital Services Limited (OPay)\"},\"metadata\":{\"custom_fields\":[{\"display_name\":\"Invoice Number\",\"variable_name\":\"invoice_number\",\"value\":\"INV-6718C06887D13\"}]}}}', '', '2024-10-24 10:07:45'),
(5, 'paystack', '{\"event\":\"charge.success\",\"data\":{\"id\":4286956222,\"domain\":\"live\",\"status\":\"success\",\"reference\":\"T671076302190401\",\"amount\":8500000,\"paid_at\":\"2024-10-18T19:34:27.000Z\",\"authorization\":{\"card_type\":\"transfer\",\"bank\":\"OPay Digital Services Limited (OPay)\"},\"metadata\":{\"custom_fields\":[{\"display_name\":\"Invoice Number\",\"variable_name\":\"invoice_number\",\"value\":\"INV-6718C06887D13\"}]}}}', '', '2024-10-24 10:19:17'),
(6, 'paystack', '{\"event\":\"charge.success\",\"data\":{\"id\":4286956222,\"domain\":\"live\",\"status\":\"success\",\"reference\":\"T671076302190401\",\"amount\":8500000,\"paid_at\":\"2024-10-18T19:34:27.000Z\",\"authorization\":{\"card_type\":\"transfer\",\"bank\":\"OPay Digital Services Limited (OPay)\"},\"metadata\":{\"custom_fields\":[{\"display_name\":\"Invoice Number\",\"variable_name\":\"invoice_number\",\"value\":\"INV-6718C06887D13\"}]}}}', '', '2024-10-24 10:22:36'),
(7, 'paystack', '{\"event\":\"charge.success\",\"data\":{\"id\":4286956222,\"domain\":\"live\",\"status\":\"success\",\"reference\":\"T671076302190401\",\"amount\":8500000,\"paid_at\":\"2024-10-18T19:34:27.000Z\",\"authorization\":{\"card_type\":\"transfer\",\"bank\":\"OPay Digital Services Limited (OPay)\"},\"metadata\":{\"custom_fields\":[{\"display_name\":\"Invoice Number\",\"variable_name\":\"invoice_number\",\"value\":\"INV-6718C06887D13\"}]}}}', '', '2024-10-24 10:35:18'),
(8, 'paystack', '{\"event\":\"charge.success\",\"data\":{\"id\":4286956222,\"domain\":\"live\",\"status\":\"success\",\"reference\":\"T671076302190401\",\"amount\":8500000,\"paid_at\":\"2024-10-18T19:34:27.000Z\",\"authorization\":{\"card_type\":\"transfer\",\"bank\":\"OPay Digital Services Limited (OPay)\"},\"metadata\":{\"custom_fields\":[{\"display_name\":\"Invoice Number\",\"variable_name\":\"invoice_number\",\"value\":\"INV-6718C06887D13\"}]}}}', '', '2024-10-24 10:41:44'),
(9, 'paystack', '{\"event\":\"charge.success\",\"data\":{\"id\":4286956222,\"domain\":\"live\",\"status\":\"success\",\"reference\":\"T671076302190401\",\"amount\":8500000,\"paid_at\":\"2024-10-18T19:34:27.000Z\",\"authorization\":{\"card_type\":\"transfer\",\"bank\":\"OPay Digital Services Limited (OPay)\"},\"metadata\":{\"custom_fields\":[{\"display_name\":\"Invoice Number\",\"variable_name\":\"invoice_number\",\"value\":\"INV-6718C06887D13\"}]}}}', '', '2024-10-24 10:43:40'),
(10, 'paystack', '{\"event\":\"charge.success\",\"data\":{\"id\":4286956222,\"domain\":\"live\",\"status\":\"success\",\"reference\":\"T671076302190401\",\"amount\":8500000,\"paid_at\":\"2024-10-18T19:34:27.000Z\",\"authorization\":{\"card_type\":\"transfer\",\"bank\":\"OPay Digital Services Limited (OPay)\"},\"metadata\":{\"custom_fields\":[{\"display_name\":\"Invoice Number\",\"variable_name\":\"invoice_number\",\"value\":\"INV-6718C06887D13\"}]}}}', '', '2024-10-24 10:44:06'),
(11, 'paystack', '{\"event\":\"charge.success\",\"data\":{\"id\":4286956222,\"domain\":\"live\",\"status\":\"success\",\"reference\":\"T671076302190401\",\"amount\":8500000,\"paid_at\":\"2024-10-18T19:34:27.000Z\",\"authorization\":{\"card_type\":\"transfer\",\"bank\":\"OPay Digital Services Limited (OPay)\"},\"metadata\":{\"custom_fields\":[{\"display_name\":\"Invoice Number\",\"variable_name\":\"invoice_number\",\"value\":\"INV-6718C06887D13\"}]}}}', '', '2024-10-24 10:44:26'),
(12, 'paystack', '{\"event\":\"charge.success\",\"data\":{\"id\":4286956222,\"domain\":\"live\",\"status\":\"success\",\"reference\":\"T671076302190401\",\"amount\":8500000,\"paid_at\":\"2024-10-18T19:34:27.000Z\",\"authorization\":{\"card_type\":\"transfer\",\"bank\":\"OPay Digital Services Limited (OPay)\"},\"metadata\":{\"custom_fields\":[{\"display_name\":\"Invoice Number\",\"variable_name\":\"invoice_number\",\"value\":\"INV-6718C06887D13\"}]}}}', '', '2024-10-24 10:44:29'),
(13, 'paystack', '{\"event\":\"charge.success\",\"data\":{\"id\":4286956222,\"domain\":\"live\",\"status\":\"success\",\"reference\":\"T671076302190401\",\"amount\":8500000,\"paid_at\":\"2024-10-18T19:34:27.000Z\",\"authorization\":{\"card_type\":\"transfer\",\"bank\":\"OPay Digital Services Limited (OPay)\"},\"metadata\":{\"custom_fields\":[{\"display_name\":\"Invoice Number\",\"variable_name\":\"invoice_number\",\"value\":\"INV-6718C06887D13\"}]}}}', '', '2024-10-24 10:51:19'),
(14, 'paystack', '{\"event\":\"charge.success\",\"data\":{\"id\":4286956222,\"domain\":\"live\",\"status\":\"success\",\"reference\":\"T671076302190401\",\"amount\":8500000,\"paid_at\":\"2024-10-18T19:34:27.000Z\",\"authorization\":{\"card_type\":\"transfer\",\"bank\":\"OPay Digital Services Limited (OPay)\"},\"metadata\":{\"custom_fields\":[{\"display_name\":\"Invoice Number\",\"variable_name\":\"invoice_number\",\"value\":\"INV-6718C06887D13\"}]}}}', '', '2024-10-24 10:51:22'),
(15, 'paystack', '{\"event\":\"charge.success\",\"data\":{\"id\":4286956222,\"domain\":\"live\",\"status\":\"success\",\"reference\":\"T671076302190401\",\"amount\":8500000,\"paid_at\":\"2024-10-18T19:34:27.000Z\",\"authorization\":{\"card_type\":\"transfer\",\"bank\":\"OPay Digital Services Limited (OPay)\"},\"metadata\":{\"custom_fields\":[{\"display_name\":\"Invoice Number\",\"variable_name\":\"invoice_number\",\"value\":\"INV-6718C06887D13\"}]}}}', '', '2024-10-24 10:51:48'),
(16, 'paystack', '{\"event\":\"charge.success\",\"data\":{\"id\":4286956222,\"domain\":\"live\",\"status\":\"success\",\"reference\":\"T671076302190401\",\"amount\":8500000,\"paid_at\":\"2024-10-18T19:34:27.000Z\",\"authorization\":{\"card_type\":\"transfer\",\"bank\":\"OPay Digital Services Limited (OPay)\"},\"metadata\":{\"custom_fields\":[{\"display_name\":\"Invoice Number\",\"variable_name\":\"invoice_number\",\"value\":\"INV-6718C06887D13\"}]}}}', '', '2024-10-24 10:51:54'),
(17, 'paystack', '{\"event\":\"charge.success\",\"data\":{\"id\":4286956222,\"domain\":\"live\",\"status\":\"success\",\"reference\":\"T671076302190401\",\"amount\":8500000,\"paid_at\":\"2024-10-18T19:34:27.000Z\",\"authorization\":{\"card_type\":\"transfer\",\"bank\":\"OPay Digital Services Limited (OPay)\"},\"metadata\":{\"custom_fields\":[{\"display_name\":\"Invoice Number\",\"variable_name\":\"invoice_number\",\"value\":\"INV-6718C06887D13\"}]}}}', '', '2024-10-24 10:52:29'),
(18, 'paystack', '{\"event\":\"charge.success\",\"data\":{\"id\":4286956222,\"domain\":\"live\",\"status\":\"success\",\"reference\":\"T671076302190401\",\"amount\":8500000,\"paid_at\":\"2024-10-18T19:34:27.000Z\",\"authorization\":{\"card_type\":\"transfer\",\"bank\":\"OPay Digital Services Limited (OPay)\"},\"metadata\":{\"custom_fields\":[{\"display_name\":\"Invoice Number\",\"variable_name\":\"invoice_number\",\"value\":\"INV-6718C06887D13\"}]}}}', '', '2024-10-24 10:54:14'),
(19, 'paystack', '{\"event\":\"charge.success\",\"data\":{\"id\":4286956222,\"domain\":\"live\",\"status\":\"success\",\"reference\":\"T671076302190401\",\"amount\":8500000,\"paid_at\":\"2024-10-18T19:34:27.000Z\",\"authorization\":{\"card_type\":\"transfer\",\"bank\":\"OPay Digital Services Limited (OPay)\"},\"metadata\":{\"custom_fields\":[{\"display_name\":\"Invoice Number\",\"variable_name\":\"invoice_number\",\"value\":\"INV-6718C06887D13\"}]}}}', '', '2024-10-24 10:54:48'),
(20, 'paystack', '{\"event\":\"charge.success\",\"data\":{\"id\":4286956222,\"domain\":\"live\",\"status\":\"success\",\"reference\":\"T671076302190401\",\"amount\":8500000,\"paid_at\":\"2024-10-18T19:34:27.000Z\",\"authorization\":{\"card_type\":\"transfer\",\"bank\":\"OPay Digital Services Limited (OPay)\"},\"metadata\":{\"custom_fields\":[{\"display_name\":\"Invoice Number\",\"variable_name\":\"invoice_number\",\"value\":\"INV-6718C06887D13\"}]}}}', '', '2024-10-24 10:55:29'),
(21, 'paystack', '{\"event\":\"charge.success\",\"data\":{\"id\":4286956222,\"domain\":\"live\",\"status\":\"success\",\"reference\":\"T671076302190401\",\"amount\":8500000,\"paid_at\":\"2024-10-18T19:34:27.000Z\",\"authorization\":{\"card_type\":\"transfer\",\"bank\":\"OPay Digital Services Limited (OPay)\"},\"metadata\":{\"custom_fields\":[{\"display_name\":\"Invoice Number\",\"variable_name\":\"invoice_number\",\"value\":\"INV-6718C06887D13\"}]}}}', '', '2024-10-24 10:56:20'),
(22, 'paystack', '{\"event\":\"charge.success\",\"data\":{\"id\":4286956222,\"domain\":\"live\",\"status\":\"success\",\"reference\":\"T671076302190401\",\"amount\":8500000,\"paid_at\":\"2024-10-18T19:34:27.000Z\",\"authorization\":{\"card_type\":\"transfer\",\"bank\":\"OPay Digital Services Limited (OPay)\"},\"metadata\":{\"custom_fields\":[{\"display_name\":\"Invoice Number\",\"variable_name\":\"invoice_number\",\"value\":\"INV-6718C06887D13\"}]}}}', '', '2024-10-24 10:56:44'),
(23, 'paystack', '{\"event\":\"charge.success\",\"data\":{\"id\":4286956222,\"domain\":\"live\",\"status\":\"success\",\"reference\":\"T671076302190401\",\"amount\":8500000,\"paid_at\":\"2024-10-18T19:34:27.000Z\",\"authorization\":{\"card_type\":\"transfer\",\"bank\":\"OPay Digital Services Limited (OPay)\"},\"metadata\":{\"custom_fields\":[{\"display_name\":\"Invoice Number\",\"variable_name\":\"invoice_number\",\"value\":\"INV-6718C06887D13\"}]}}}', '', '2024-10-24 10:56:58'),
(24, 'paystack', '{\"event\":\"charge.success\",\"data\":{\"id\":4286956222,\"domain\":\"live\",\"status\":\"success\",\"reference\":\"T671076302190401\",\"amount\":8500000,\"paid_at\":\"2024-10-18T19:34:27.000Z\",\"authorization\":{\"card_type\":\"transfer\",\"bank\":\"OPay Digital Services Limited (OPay)\"},\"metadata\":{\"custom_fields\":[{\"display_name\":\"Invoice Number\",\"variable_name\":\"invoice_number\",\"value\":\"INV-6718C06887D13\"}]}}}', '', '2024-10-24 10:57:28'),
(25, 'paystack', '{\"event\":\"charge.success\",\"data\":{\"id\":4286956222,\"domain\":\"live\",\"status\":\"success\",\"reference\":\"T671076302190401\",\"amount\":8500000,\"paid_at\":\"2024-10-18T19:34:27.000Z\",\"authorization\":{\"card_type\":\"transfer\",\"bank\":\"OPay Digital Services Limited (OPay)\"},\"metadata\":{\"custom_fields\":[{\"display_name\":\"Invoice Number\",\"variable_name\":\"invoice_number\",\"value\":\"INV-6718C06887D13\"}]}}}', '', '2024-10-24 10:57:30'),
(26, 'paystack', '{\"event\":\"charge.success\",\"data\":{\"id\":4286956222,\"domain\":\"live\",\"status\":\"success\",\"reference\":\"T671076302190401\",\"amount\":8500000,\"paid_at\":\"2024-10-18T19:34:27.000Z\",\"authorization\":{\"card_type\":\"transfer\",\"bank\":\"OPay Digital Services Limited (OPay)\"},\"metadata\":{\"custom_fields\":[{\"display_name\":\"Invoice Number\",\"variable_name\":\"invoice_number\",\"value\":\"INV-6718C06887D13\"}]}}}', '', '2024-10-24 11:02:28'),
(27, 'paystack', '{\"event\":\"charge.success\",\"data\":{\"id\":4286956222,\"domain\":\"live\",\"status\":\"success\",\"reference\":\"T671076302190401\",\"amount\":8500000,\"paid_at\":\"2024-10-18T19:34:27.000Z\",\"authorization\":{\"card_type\":\"transfer\",\"bank\":\"OPay Digital Services Limited (OPay)\"},\"metadata\":{\"custom_fields\":[{\"display_name\":\"Invoice Number\",\"variable_name\":\"invoice_number\",\"value\":\"INV-6718C06887D13\"}]}}}', '', '2024-10-24 11:02:30'),
(28, 'paystack', '{\"event\":\"charge.success\",\"data\":{\"id\":4286956222,\"domain\":\"live\",\"status\":\"success\",\"reference\":\"T671076302190401\",\"amount\":8500000,\"paid_at\":\"2024-10-18T19:34:27.000Z\",\"authorization\":{\"card_type\":\"transfer\",\"bank\":\"OPay Digital Services Limited (OPay)\"},\"metadata\":{\"custom_fields\":[{\"display_name\":\"Invoice Number\",\"variable_name\":\"invoice_number\",\"value\":\"INV-6718C06887D13\"}]}}}', '', '2024-10-24 11:02:40'),
(29, 'paystack', '{\"event\":\"charge.success\",\"data\":{\"id\":4286956222,\"domain\":\"live\",\"status\":\"success\",\"reference\":\"T671076302190401\",\"amount\":8500000,\"paid_at\":\"2024-10-18T19:34:27.000Z\",\"authorization\":{\"card_type\":\"transfer\",\"bank\":\"OPay Digital Services Limited (OPay)\"},\"metadata\":{\"custom_fields\":[{\"display_name\":\"Invoice Number\",\"variable_name\":\"invoice_number\",\"value\":\"INV-6718C06887D13\"}]}}}', '', '2024-10-24 11:03:31'),
(30, 'credo', '{\"event\":\"TRANSACTION.SUCCESSFUL\",\"data\":{\"businessCode\":\"700607010880003\",\"serviceCode\":null,\"transRef\":\"Y7B000yBkA117lle363w\",\"businessRef\":\"1136sEohDz1727087068\",\"debitedAmount\":50,\"transAmount\":50,\"transFeeAmount\":3.25,\"settlementAmount\":46.75,\"customerId\":\"joshypeterz@gmail.com\",\"transactionDate\":1727087161363,\"currencyCode\":\"NGN\",\"status\":0,\"paymentMethodType\":\"Gtbank Plc\",\"paymentMethod\":\"Bank Transfer\",\"businessId\":0,\"narration\":null,\"customer\":{\"customerEmail\":\"joshypeterz@gmail.com\",\"firstName\":\"Joshua Omueta\",\"lastName\":\"INV-6718C06887D13\",\"phoneNo\":\"08138909022\"},\"metadata\":[]}}', '', '2024-10-24 21:28:11'),
(31, 'credo', '{\"event\":\"TRANSACTION.SUCCESSFUL\",\"data\":{\"businessCode\":\"700607010880003\",\"serviceCode\":null,\"transRef\":\"Y7B000yBkA117lle363w\",\"businessRef\":\"1136sEohDz1727087068\",\"debitedAmount\":50,\"transAmount\":50,\"transFeeAmount\":3.25,\"settlementAmount\":46.75,\"customerId\":\"joshypeterz@gmail.com\",\"transactionDate\":1727087161363,\"currencyCode\":\"NGN\",\"status\":0,\"paymentMethodType\":\"Gtbank Plc\",\"paymentMethod\":\"Bank Transfer\",\"businessId\":0,\"narration\":null,\"customer\":{\"customerEmail\":\"joshypeterz@gmail.com\",\"firstName\":\"Joshua Omueta\",\"lastName\":\"INV-6718C06887D13\",\"phoneNo\":\"08138909022\"},\"metadata\":[]}}', '', '2024-10-24 21:28:16'),
(32, 'credo', '{\"event\":\"TRANSACTION.FAIL\",\"data\":{\"businessCode\":\"700607010880003\",\"serviceCode\":null,\"transRef\":\"Y7B000yBkA117lle363w\",\"businessRef\":\"1136sEohDz1727087068\",\"debitedAmount\":50,\"transAmount\":50,\"transFeeAmount\":3.25,\"settlementAmount\":46.75,\"customerId\":\"joshypeterz@gmail.com\",\"transactionDate\":1727087161363,\"currencyCode\":\"NGN\",\"status\":0,\"paymentMethodType\":\"Gtbank Plc\",\"paymentMethod\":\"Bank Transfer\",\"businessId\":0,\"narration\":null,\"customer\":{\"customerEmail\":\"joshypeterz@gmail.com\",\"firstName\":\"Joshua Omueta\",\"lastName\":\"INV-6718C06887D13\",\"phoneNo\":\"08138909022\"},\"metadata\":[]}}', '', '2024-10-24 21:29:23'),
(33, 'credo', '{\"event\":\"TRANSACTION.f\",\"data\":{\"businessCode\":\"700607010880003\",\"serviceCode\":null,\"transRef\":\"Y7B000yBkA117lle363w\",\"businessRef\":\"1136sEohDz1727087068\",\"debitedAmount\":50,\"transAmount\":50,\"transFeeAmount\":3.25,\"settlementAmount\":46.75,\"customerId\":\"joshypeterz@gmail.com\",\"transactionDate\":1727087161363,\"currencyCode\":\"NGN\",\"status\":0,\"paymentMethodType\":\"Gtbank Plc\",\"paymentMethod\":\"Bank Transfer\",\"businessId\":0,\"narration\":null,\"customer\":{\"customerEmail\":\"joshypeterz@gmail.com\",\"firstName\":\"Joshua Omueta\",\"lastName\":\"INV-6718C06887D13\",\"phoneNo\":\"08138909022\"},\"metadata\":[]}}', '', '2024-10-24 21:34:53'),
(34, 'credo', '{\"event\":\"TRANSACTION.f\",\"data\":{\"businessCode\":\"700607010880003\",\"serviceCode\":null,\"transRef\":\"Y7B000yBkA117lle363w\",\"businessRef\":\"1136sEohDz1727087068\",\"debitedAmount\":50,\"transAmount\":50,\"transFeeAmount\":3.25,\"settlementAmount\":46.75,\"customerId\":\"joshypeterz@gmail.com\",\"transactionDate\":1727087161363,\"currencyCode\":\"NGN\",\"status\":0,\"paymentMethodType\":\"Gtbank Plc\",\"paymentMethod\":\"Bank Transfer\",\"businessId\":0,\"narration\":null,\"customer\":{\"customerEmail\":\"joshypeterz@gmail.com\",\"firstName\":\"Joshua Omueta\",\"lastName\":\"INV-6718C06887D13\",\"phoneNo\":\"08138909022\"},\"metadata\":[]}}', '', '2024-10-24 21:35:14'),
(35, 'credo', '{\"event\":\"TRANSACTION.f\",\"data\":{\"businessCode\":\"700607010880003\",\"serviceCode\":null,\"transRef\":\"Y7B000yBkA117lle363w\",\"businessRef\":\"1136sEohDz1727087068\",\"debitedAmount\":50,\"transAmount\":50,\"transFeeAmount\":3.25,\"settlementAmount\":46.75,\"customerId\":\"joshypeterz@gmail.com\",\"transactionDate\":1727087161363,\"currencyCode\":\"NGN\",\"status\":0,\"paymentMethodType\":\"Gtbank Plc\",\"paymentMethod\":\"Bank Transfer\",\"businessId\":0,\"narration\":null,\"customer\":{\"customerEmail\":\"joshypeterz@gmail.com\",\"firstName\":\"Joshua Omueta\",\"lastName\":\"INV-6718C06887D13\",\"phoneNo\":\"08138909022\"},\"metadata\":[]}}', '', '2024-10-24 21:36:50'),
(36, 'credo', '{\"event\":\"TRANSACTION.f\",\"data\":{\"businessCode\":\"700607010880003\",\"serviceCode\":null,\"transRef\":\"Y7B000yBkA117lle363w\",\"businessRef\":\"1136sEohDz1727087068\",\"debitedAmount\":50,\"transAmount\":50,\"transFeeAmount\":3.25,\"settlementAmount\":46.75,\"customerId\":\"joshypeterz@gmail.com\",\"transactionDate\":1727087161363,\"currencyCode\":\"NGN\",\"status\":0,\"paymentMethodType\":\"Gtbank Plc\",\"paymentMethod\":\"Bank Transfer\",\"businessId\":0,\"narration\":null,\"customer\":{\"customerEmail\":\"joshypeterz@gmail.com\",\"firstName\":\"Joshua Omueta\",\"lastName\":\"INV-6718C06887D13\",\"phoneNo\":\"08138909022\"},\"metadata\":[]}}', '', '2024-10-24 21:37:08'),
(37, 'credo', '{\"event\":\"TRANSACTION.SUCCESSFUL\",\"data\":{\"businessCode\":\"700607010880003\",\"serviceCode\":null,\"transRef\":\"Y7B000yBkA117lle363w\",\"businessRef\":\"1136sEohDz1727087068\",\"debitedAmount\":50,\"transAmount\":50,\"transFeeAmount\":3.25,\"settlementAmount\":46.75,\"customerId\":\"joshypeterz@gmail.com\",\"transactionDate\":1727087161363,\"currencyCode\":\"NGN\",\"status\":0,\"paymentMethodType\":\"Gtbank Plc\",\"paymentMethod\":\"Bank Transfer\",\"businessId\":0,\"narration\":null,\"customer\":{\"customerEmail\":\"joshypeterz@gmail.com\",\"firstName\":\"Joshua Omueta\",\"lastName\":\"INV-6718C06887D13\",\"phoneNo\":\"08138909022\"},\"metadata\":[]}}', '', '2024-10-24 21:37:39'),
(38, 'credo', '{\"event\":\"TRANSACTION.SUCCESSFUL\",\"data\":{\"businessCode\":\"700607010880003\",\"serviceCode\":null,\"transRef\":\"Y7B000yBkA117lle363w\",\"businessRef\":\"1136sEohDz1727087068\",\"debitedAmount\":50,\"transAmount\":50,\"transFeeAmount\":3.25,\"settlementAmount\":46.75,\"customerId\":\"joshypeterz@gmail.com\",\"transactionDate\":1727087161363,\"currencyCode\":\"NGN\",\"status\":0,\"paymentMethodType\":\"Gtbank Plc\",\"paymentMethod\":\"Bank Transfer\",\"businessId\":0,\"narration\":null,\"customer\":{\"customerEmail\":\"joshypeterz@gmail.com\",\"firstName\":\"Joshua Omueta\",\"lastName\":\"INV-6718C06887D13\",\"phoneNo\":\"08138909022\"},\"metadata\":[]}}', '', '2024-10-24 21:37:42'),
(39, 'credo', '{\"event\":\"TRANSACTION.SUCCESSFUL\",\"data\":{\"businessCode\":\"700607010880003\",\"serviceCode\":null,\"transRef\":\"Y7B000yBkA117lle363w\",\"businessRef\":\"1136sEohDz1727087068\",\"debitedAmount\":50,\"transAmount\":50,\"transFeeAmount\":3.25,\"settlementAmount\":46.75,\"customerId\":\"joshypeterz@gmail.com\",\"transactionDate\":1727087161363,\"currencyCode\":\"NGN\",\"status\":0,\"paymentMethodType\":\"Gtbank Plc\",\"paymentMethod\":\"Bank Transfer\",\"businessId\":0,\"narration\":null,\"customer\":{\"customerEmail\":\"joshypeterz@gmail.com\",\"firstName\":\"Joshua Omueta\",\"lastName\":\"INV-6718C06887D13\",\"phoneNo\":\"08138909022\"},\"metadata\":[]}}', '', '2024-10-24 21:37:44'),
(40, 'credo', '{\"event\":\"TRANSACTION.SUCCESSFUL\",\"data\":{\"businessCode\":\"700607010880003\",\"serviceCode\":null,\"transRef\":\"Y7B000yBkA117lle363w\",\"businessRef\":\"1136sEohDz1727087068\",\"debitedAmount\":50,\"transAmount\":50,\"transFeeAmount\":3.25,\"settlementAmount\":46.75,\"customerId\":\"joshypeterz@gmail.com\",\"transactionDate\":1727087161363,\"currencyCode\":\"NGN\",\"status\":0,\"paymentMethodType\":\"Gtbank Plc\",\"paymentMethod\":\"Bank Transfer\",\"businessId\":0,\"narration\":null,\"customer\":{\"customerEmail\":\"joshypeterz@gmail.com\",\"firstName\":\"Joshua Omueta\",\"lastName\":\"INV-6718C06887D13\",\"phoneNo\":\"08138909022\"},\"metadata\":[]}}', '', '2024-10-24 21:45:17'),
(41, 'credo', '{\"event\":\"TRANSACTION.f\",\"data\":{\"businessCode\":\"700607010880003\",\"serviceCode\":null,\"transRef\":\"Y7B000yBkA117lle363w\",\"businessRef\":\"1136sEohDz1727087068\",\"debitedAmount\":50,\"transAmount\":50,\"transFeeAmount\":3.25,\"settlementAmount\":46.75,\"customerId\":\"joshypeterz@gmail.com\",\"transactionDate\":1727087161363,\"currencyCode\":\"NGN\",\"status\":0,\"paymentMethodType\":\"Gtbank Plc\",\"paymentMethod\":\"Bank Transfer\",\"businessId\":0,\"narration\":null,\"customer\":{\"customerEmail\":\"joshypeterz@gmail.com\",\"firstName\":\"Joshua Omueta\",\"lastName\":\"INV-6718C06887D13\",\"phoneNo\":\"08138909022\"},\"metadata\":[]}}', '', '2024-10-24 21:45:24'),
(42, 'credo', '{\"event\":\"TRANSACTION.f\",\"data\":{\"businessCode\":\"700607010880003\",\"serviceCode\":null,\"transRef\":\"Y7B000yBkA117lle363w\",\"businessRef\":\"1136sEohDz1727087068\",\"debitedAmount\":50,\"transAmount\":50,\"transFeeAmount\":3.25,\"settlementAmount\":46.75,\"customerId\":\"joshypeterz@gmail.com\",\"transactionDate\":1727087161363,\"currencyCode\":\"NGN\",\"status\":0,\"paymentMethodType\":\"Gtbank Plc\",\"paymentMethod\":\"Bank Transfer\",\"businessId\":0,\"narration\":null,\"customer\":{\"customerEmail\":\"joshypeterz@gmail.com\",\"firstName\":\"Joshua Omueta\",\"lastName\":\"INV-6718C06887D13\",\"phoneNo\":\"08138909022\"},\"metadata\":[]}}', '', '2024-10-24 21:45:29'),
(43, 'credo', '{\"event\":\"TRANSACTION.SUCCESSFUL\",\"data\":{\"businessCode\":\"700607010880003\",\"serviceCode\":null,\"transRef\":\"Y7B000yBkA117lle363w\",\"businessRef\":\"1136sEohDz1727087068\",\"debitedAmount\":50,\"transAmount\":50,\"transFeeAmount\":3.25,\"settlementAmount\":46.75,\"customerId\":\"joshypeterz@gmail.com\",\"transactionDate\":1727087161363,\"currencyCode\":\"NGN\",\"status\":0,\"paymentMethodType\":\"Gtbank Plc\",\"paymentMethod\":\"Bank Transfer\",\"businessId\":0,\"narration\":null,\"customer\":{\"customerEmail\":\"joshypeterz@gmail.com\",\"firstName\":\"Joshua Omueta\",\"lastName\":\"INV-6718C06887D13\",\"phoneNo\":\"08138909022\"},\"metadata\":[]}}', '', '2024-10-24 21:45:54'),
(44, 'paydirect', '\"<PaymentNotificationRequest xmlns:ns2=\\\"http:\\/\\/techquest.interswitchng.com\\/\\\" xmlns:ns3=\\\"http:\\/\\/www.w3.org\\/2003\\/05\\/soap-envelope\\\">\\r\\n    <ServiceUrl>https:\\/\\/plateauigr.com\\/php\\/paydirect\\/<\\/ServiceUrl>\\r\\n    <ServiceUsername><\\/ServiceUsername>\\r\\n    <ServicePassword><\\/ServicePassword>\\r\\n    <FtpUrl>https:\\/\\/plateauigr.com\\/php\\/paydirect\\/<\\/FtpUrl>\\r\\n    <FtpUsername><\\/FtpUsername>\\r\\n    <FtpPassword><\\/FtpPassword>\\r\\n    <Payments>\\r\\n        <Payment>\\r\\n            <PaymentLogId>291449489<\\/PaymentLogId>\\r\\n            <CustReference>INV-6718C06887D13<\\/CustReference>\\r\\n            <AlternateCustReference>PLS-3205781946<\\/AlternateCustReference>\\r\\n            <Amount>2407000.00<\\/Amount>\\r\\n            <PaymentMethod>Internal Transfer<\\/PaymentMethod>\\r\\n            <PaymentReference>SBP|BRH|PGT|24-10-2024|249262<\\/PaymentReference>\\r\\n            <TerminalId><\\/TerminalId>\\r\\n            <ChannelName>Bank Branc<\\/ChannelName>\\r\\n            <Location>322 - JOS<\\/Location>\\r\\n            <PaymentDate>10\\/24\\/2024 14:32:33<\\/PaymentDate>\\r\\n            <InstitutionId>PGT<\\/InstitutionId>\\r\\n            <InstitutionName>Plat Govt<\\/InstitutionName>\\r\\n            <BranchName>322 - JOS<\\/BranchName>\\r\\n            <BankName>Sterling Bank Plc<\\/BankName>\\r\\n            <CustomerName>JOS MAIN MARKET AUTHORITY \\ufffd<\\/CustomerName>\\r\\n            <OtherCustomerInfo>|<\\/OtherCustomerInfo>\\r\\n            <ReceiptNo>2412533854<\\/ReceiptNo>\\r\\n            <CollectionsAccount>0064030950<\\/CollectionsAccount>\\r\\n            <BankCode>SBP<\\/BankCode>\\r\\n            <CustomerAddress><\\/CustomerAddress>\\r\\n            <CustomerPhoneNumber><\\/CustomerPhoneNumber>\\r\\n            <DepositorName><\\/DepositorName>\\r\\n            <DepositSlipNumber>001<\\/DepositSlipNumber>\\r\\n            <PaymentCurrency>566<\\/PaymentCurrency>\\r\\n            <PaymentItems>\\r\\n                <PaymentItem>\\r\\n                    <ItemName>Rent on Govt Building<\\/ItemName>\\r\\n                    <ItemCode>403<\\/ItemCode>\\r\\n                    <ItemAmount>2407000.00<\\/ItemAmount>\\r\\n                    <LeadBankCode>FBN<\\/LeadBankCode>\\r\\n                    <LeadBankCbnCode>011<\\/LeadBankCbnCode>\\r\\n                    <LeadBankName>First Bank of Nigeria Plc<\\/LeadBankName>\\r\\n                    <CategoryCode><\\/CategoryCode>\\r\\n                    <CategoryName><\\/CategoryName>\\r\\n                <\\/PaymentItem>\\r\\n            <\\/PaymentItems>\\r\\n            <ProductGroupCode>HTTPGENERICv31<\\/ProductGroupCode>\\r\\n            <PaymentStatus>0<\\/PaymentStatus>\\r\\n            <IsReversal>False<\\/IsReversal>\\r\\n            <SettlementDate>10\\/25\\/2024 00:00:01<\\/SettlementDate>\\r\\n            <FeeName><\\/FeeName>\\r\\n            <ThirdPartyCode><\\/ThirdPartyCode>\\r\\n            <OriginalPaymentLogId><\\/OriginalPaymentLogId>\\r\\n            <OriginalPaymentReference><\\/OriginalPaymentReference>\\r\\n            <Teller>Chinnan Rindi<\\/Teller>\\r\\n        <\\/Payment>\\r\\n    <\\/Payments>\\r\\n<\\/PaymentNotificationRequest>\"', '', '2024-10-28 06:03:13'),
(45, 'paydirect', '\"<PaymentNotificationRequest xmlns:ns2=\\\"http:\\/\\/techquest.interswitchng.com\\/\\\" xmlns:ns3=\\\"http:\\/\\/www.w3.org\\/2003\\/05\\/soap-envelope\\\">\\r\\n    <ServiceUrl>https:\\/\\/plateauigr.com\\/php\\/paydirect\\/<\\/ServiceUrl>\\r\\n    <ServiceUsername><\\/ServiceUsername>\\r\\n    <ServicePassword><\\/ServicePassword>\\r\\n    <FtpUrl>https:\\/\\/plateauigr.com\\/php\\/paydirect\\/<\\/FtpUrl>\\r\\n    <FtpUsername><\\/FtpUsername>\\r\\n    <FtpPassword><\\/FtpPassword>\\r\\n    <Payments>\\r\\n        <Payment>\\r\\n            <PaymentLogId>291449489<\\/PaymentLogId>\\r\\n            <CustReference>INV-6718C06887D13<\\/CustReference>\\r\\n            <AlternateCustReference>PLS-3205781946<\\/AlternateCustReference>\\r\\n            <Amount>2407000.00<\\/Amount>\\r\\n            <PaymentMethod>Internal Transfer<\\/PaymentMethod>\\r\\n            <PaymentReference>SBP|BRH|PGT|24-10-2024|249262<\\/PaymentReference>\\r\\n            <TerminalId><\\/TerminalId>\\r\\n            <ChannelName>Bank Branc<\\/ChannelName>\\r\\n            <Location>322 - JOS<\\/Location>\\r\\n            <PaymentDate>10\\/24\\/2024 14:32:33<\\/PaymentDate>\\r\\n            <InstitutionId>PGT<\\/InstitutionId>\\r\\n            <InstitutionName>Plat Govt<\\/InstitutionName>\\r\\n            <BranchName>322 - JOS<\\/BranchName>\\r\\n            <BankName>Sterling Bank Plc<\\/BankName>\\r\\n            <CustomerName>JOS MAIN MARKET AUTHORITY \\ufffd<\\/CustomerName>\\r\\n            <OtherCustomerInfo>|<\\/OtherCustomerInfo>\\r\\n            <ReceiptNo>2412533854<\\/ReceiptNo>\\r\\n            <CollectionsAccount>0064030950<\\/CollectionsAccount>\\r\\n            <BankCode>SBP<\\/BankCode>\\r\\n            <CustomerAddress><\\/CustomerAddress>\\r\\n            <CustomerPhoneNumber><\\/CustomerPhoneNumber>\\r\\n            <DepositorName><\\/DepositorName>\\r\\n            <DepositSlipNumber>001<\\/DepositSlipNumber>\\r\\n            <PaymentCurrency>566<\\/PaymentCurrency>\\r\\n            <PaymentItems>\\r\\n                <PaymentItem>\\r\\n                    <ItemName>Rent on Govt Building<\\/ItemName>\\r\\n                    <ItemCode>403<\\/ItemCode>\\r\\n                    <ItemAmount>2407000.00<\\/ItemAmount>\\r\\n                    <LeadBankCode>FBN<\\/LeadBankCode>\\r\\n                    <LeadBankCbnCode>011<\\/LeadBankCbnCode>\\r\\n                    <LeadBankName>First Bank of Nigeria Plc<\\/LeadBankName>\\r\\n                    <CategoryCode><\\/CategoryCode>\\r\\n                    <CategoryName><\\/CategoryName>\\r\\n                <\\/PaymentItem>\\r\\n            <\\/PaymentItems>\\r\\n            <ProductGroupCode>HTTPGENERICv31<\\/ProductGroupCode>\\r\\n            <PaymentStatus>0<\\/PaymentStatus>\\r\\n            <IsReversal>False<\\/IsReversal>\\r\\n            <SettlementDate>10\\/25\\/2024 00:00:01<\\/SettlementDate>\\r\\n            <FeeName><\\/FeeName>\\r\\n            <ThirdPartyCode><\\/ThirdPartyCode>\\r\\n            <OriginalPaymentLogId><\\/OriginalPaymentLogId>\\r\\n            <OriginalPaymentReference><\\/OriginalPaymentReference>\\r\\n            <Teller>Chinnan Rindi<\\/Teller>\\r\\n        <\\/Payment>\\r\\n    <\\/Payments>\\r\\n<\\/PaymentNotificationRequest>\"', NULL, '2024-11-01 06:23:28'),
(46, 'credo', '{\"event\":\"TRANSACTION.SUCCESSFUL\",\"data\":{\"businessCode\":\"700607010880003\",\"serviceCode\":null,\"transRef\":\"Y7B000yBkA117lle363w\",\"businessRef\":\"1136sEohDz1727087068\",\"debitedAmount\":50,\"transAmount\":50,\"transFeeAmount\":3.25,\"settlementAmount\":46.75,\"customerId\":\"joshypeterz@gmail.com\",\"transactionDate\":1727087161363,\"currencyCode\":\"NGN\",\"status\":0,\"paymentMethodType\":\"Gtbank Plc\",\"paymentMethod\":\"Bank Transfer\",\"businessId\":0,\"narration\":null,\"customer\":{\"customerEmail\":\"joshypeterz@gmail.com\",\"firstName\":\"Joshua Omueta\",\"lastName\":\"INV-6718C06887D13\",\"phoneNo\":\"08138909022\"},\"metadata\":[]}}', '{\"status\":\"success\",\"message\":\"Payment registered successfully\"}', '2024-11-01 06:42:11'),
(47, 'paydirect', '\"<PaymentNotificationRequest xmlns:ns2=\\\"http:\\/\\/techquest.interswitchng.com\\/\\\" xmlns:ns3=\\\"http:\\/\\/www.w3.org\\/2003\\/05\\/soap-envelope\\\">\\r\\n    <ServiceUrl>https:\\/\\/plateauigr.com\\/php\\/paydirect\\/<\\/ServiceUrl>\\r\\n    <ServiceUsername><\\/ServiceUsername>\\r\\n    <ServicePassword><\\/ServicePassword>\\r\\n    <FtpUrl>https:\\/\\/plateauigr.com\\/php\\/paydirect\\/<\\/FtpUrl>\\r\\n    <FtpUsername><\\/FtpUsername>\\r\\n    <FtpPassword><\\/FtpPassword>\\r\\n    <Payments>\\r\\n        <Payment>\\r\\n            <PaymentLogId>291449489<\\/PaymentLogId>\\r\\n            <CustReference>INV-6718C06887D13<\\/CustReference>\\r\\n            <AlternateCustReference>PLS-3205781946<\\/AlternateCustReference>\\r\\n            <Amount>2407000.00<\\/Amount>\\r\\n            <PaymentMethod>Internal Transfer<\\/PaymentMethod>\\r\\n            <PaymentReference>SBP|BRH|PGT|24-10-2024|249262<\\/PaymentReference>\\r\\n            <TerminalId><\\/TerminalId>\\r\\n            <ChannelName>Bank Branc<\\/ChannelName>\\r\\n            <Location>322 - JOS<\\/Location>\\r\\n            <PaymentDate>10\\/24\\/2024 14:32:33<\\/PaymentDate>\\r\\n            <InstitutionId>PGT<\\/InstitutionId>\\r\\n            <InstitutionName>Plat Govt<\\/InstitutionName>\\r\\n            <BranchName>322 - JOS<\\/BranchName>\\r\\n            <BankName>Sterling Bank Plc<\\/BankName>\\r\\n            <CustomerName>JOS MAIN MARKET AUTHORITY \\ufffd<\\/CustomerName>\\r\\n            <OtherCustomerInfo>|<\\/OtherCustomerInfo>\\r\\n            <ReceiptNo>2412533854<\\/ReceiptNo>\\r\\n            <CollectionsAccount>0064030950<\\/CollectionsAccount>\\r\\n            <BankCode>SBP<\\/BankCode>\\r\\n            <CustomerAddress><\\/CustomerAddress>\\r\\n            <CustomerPhoneNumber><\\/CustomerPhoneNumber>\\r\\n            <DepositorName><\\/DepositorName>\\r\\n            <DepositSlipNumber>001<\\/DepositSlipNumber>\\r\\n            <PaymentCurrency>566<\\/PaymentCurrency>\\r\\n            <PaymentItems>\\r\\n                <PaymentItem>\\r\\n                    <ItemName>Rent on Govt Building<\\/ItemName>\\r\\n                    <ItemCode>403<\\/ItemCode>\\r\\n                    <ItemAmount>2407000.00<\\/ItemAmount>\\r\\n                    <LeadBankCode>FBN<\\/LeadBankCode>\\r\\n                    <LeadBankCbnCode>011<\\/LeadBankCbnCode>\\r\\n                    <LeadBankName>First Bank of Nigeria Plc<\\/LeadBankName>\\r\\n                    <CategoryCode><\\/CategoryCode>\\r\\n                    <CategoryName><\\/CategoryName>\\r\\n                <\\/PaymentItem>\\r\\n            <\\/PaymentItems>\\r\\n            <ProductGroupCode>HTTPGENERICv31<\\/ProductGroupCode>\\r\\n            <PaymentStatus>0<\\/PaymentStatus>\\r\\n            <IsReversal>False<\\/IsReversal>\\r\\n            <SettlementDate>10\\/25\\/2024 00:00:01<\\/SettlementDate>\\r\\n            <FeeName><\\/FeeName>\\r\\n            <ThirdPartyCode><\\/ThirdPartyCode>\\r\\n            <OriginalPaymentLogId><\\/OriginalPaymentLogId>\\r\\n            <OriginalPaymentReference><\\/OriginalPaymentReference>\\r\\n            <Teller>Chinnan Rindi<\\/Teller>\\r\\n        <\\/Payment>\\r\\n    <\\/Payments>\\r\\n<\\/PaymentNotificationRequest>\"', NULL, '2024-11-01 06:58:45'),
(48, 'paydirect', '\"<PaymentNotificationRequest xmlns:ns2=\\\"http:\\/\\/techquest.interswitchng.com\\/\\\" xmlns:ns3=\\\"http:\\/\\/www.w3.org\\/2003\\/05\\/soap-envelope\\\">\\r\\n    <ServiceUrl>https:\\/\\/plateauigr.com\\/php\\/paydirect\\/<\\/ServiceUrl>\\r\\n    <ServiceUsername><\\/ServiceUsername>\\r\\n    <ServicePassword><\\/ServicePassword>\\r\\n    <FtpUrl>https:\\/\\/plateauigr.com\\/php\\/paydirect\\/<\\/FtpUrl>\\r\\n    <FtpUsername><\\/FtpUsername>\\r\\n    <FtpPassword><\\/FtpPassword>\\r\\n    <Payments>\\r\\n        <Payment>\\r\\n            <PaymentLogId>291449489<\\/PaymentLogId>\\r\\n            <CustReference>INV-6718C06887D13<\\/CustReference>\\r\\n            <AlternateCustReference>PLS-3205781946<\\/AlternateCustReference>\\r\\n            <Amount>2407000.00<\\/Amount>\\r\\n            <PaymentMethod>Internal Transfer<\\/PaymentMethod>\\r\\n            <PaymentReference>SBP|BRH|PGT|24-10-2024|249262<\\/PaymentReference>\\r\\n            <TerminalId><\\/TerminalId>\\r\\n            <ChannelName>Bank Branc<\\/ChannelName>\\r\\n            <Location>322 - JOS<\\/Location>\\r\\n            <PaymentDate>10\\/24\\/2024 14:32:33<\\/PaymentDate>\\r\\n            <InstitutionId>PGT<\\/InstitutionId>\\r\\n            <InstitutionName>Plat Govt<\\/InstitutionName>\\r\\n            <BranchName>322 - JOS<\\/BranchName>\\r\\n            <BankName>Sterling Bank Plc<\\/BankName>\\r\\n            <CustomerName>JOS MAIN MARKET AUTHORITY \\ufffd<\\/CustomerName>\\r\\n            <OtherCustomerInfo>|<\\/OtherCustomerInfo>\\r\\n            <ReceiptNo>2412533854<\\/ReceiptNo>\\r\\n            <CollectionsAccount>0064030950<\\/CollectionsAccount>\\r\\n            <BankCode>SBP<\\/BankCode>\\r\\n            <CustomerAddress><\\/CustomerAddress>\\r\\n            <CustomerPhoneNumber><\\/CustomerPhoneNumber>\\r\\n            <DepositorName><\\/DepositorName>\\r\\n            <DepositSlipNumber>001<\\/DepositSlipNumber>\\r\\n            <PaymentCurrency>566<\\/PaymentCurrency>\\r\\n            <PaymentItems>\\r\\n                <PaymentItem>\\r\\n                    <ItemName>Rent on Govt Building<\\/ItemName>\\r\\n                    <ItemCode>403<\\/ItemCode>\\r\\n                    <ItemAmount>2407000.00<\\/ItemAmount>\\r\\n                    <LeadBankCode>FBN<\\/LeadBankCode>\\r\\n                    <LeadBankCbnCode>011<\\/LeadBankCbnCode>\\r\\n                    <LeadBankName>First Bank of Nigeria Plc<\\/LeadBankName>\\r\\n                    <CategoryCode><\\/CategoryCode>\\r\\n                    <CategoryName><\\/CategoryName>\\r\\n                <\\/PaymentItem>\\r\\n            <\\/PaymentItems>\\r\\n            <ProductGroupCode>HTTPGENERICv31<\\/ProductGroupCode>\\r\\n            <PaymentStatus>0<\\/PaymentStatus>\\r\\n            <IsReversal>False<\\/IsReversal>\\r\\n            <SettlementDate>10\\/25\\/2024 00:00:01<\\/SettlementDate>\\r\\n            <FeeName><\\/FeeName>\\r\\n            <ThirdPartyCode><\\/ThirdPartyCode>\\r\\n            <OriginalPaymentLogId><\\/OriginalPaymentLogId>\\r\\n            <OriginalPaymentReference><\\/OriginalPaymentReference>\\r\\n            <Teller>Chinnan Rindi<\\/Teller>\\r\\n        <\\/Payment>\\r\\n    <\\/Payments>\\r\\n<\\/PaymentNotificationRequest>\"', NULL, '2024-11-01 07:09:04'),
(49, 'paydirect', '\"<PaymentNotificationRequest xmlns:ns2=\\\"http:\\/\\/techquest.interswitchng.com\\/\\\" xmlns:ns3=\\\"http:\\/\\/www.w3.org\\/2003\\/05\\/soap-envelope\\\">\\r\\n    <ServiceUrl>https:\\/\\/plateauigr.com\\/php\\/paydirect\\/<\\/ServiceUrl>\\r\\n    <ServiceUsername><\\/ServiceUsername>\\r\\n    <ServicePassword><\\/ServicePassword>\\r\\n    <FtpUrl>https:\\/\\/plateauigr.com\\/php\\/paydirect\\/<\\/FtpUrl>\\r\\n    <FtpUsername><\\/FtpUsername>\\r\\n    <FtpPassword><\\/FtpPassword>\\r\\n    <Payments>\\r\\n        <Payment>\\r\\n            <PaymentLogId>291449489<\\/PaymentLogId>\\r\\n            <CustReference>INV-6718C06887D13<\\/CustReference>\\r\\n            <AlternateCustReference>PLS-3205781946<\\/AlternateCustReference>\\r\\n            <Amount>2407000.00<\\/Amount>\\r\\n            <PaymentMethod>Internal Transfer<\\/PaymentMethod>\\r\\n            <PaymentReference>SBP|BRH|PGT|24-10-2024|249262<\\/PaymentReference>\\r\\n            <TerminalId><\\/TerminalId>\\r\\n            <ChannelName>Bank Branc<\\/ChannelName>\\r\\n            <Location>322 - JOS<\\/Location>\\r\\n            <PaymentDate>10\\/24\\/2024 14:32:33<\\/PaymentDate>\\r\\n            <InstitutionId>PGT<\\/InstitutionId>\\r\\n            <InstitutionName>Plat Govt<\\/InstitutionName>\\r\\n            <BranchName>322 - JOS<\\/BranchName>\\r\\n            <BankName>Sterling Bank Plc<\\/BankName>\\r\\n            <CustomerName>JOS MAIN MARKET AUTHORITY \\ufffd<\\/CustomerName>\\r\\n            <OtherCustomerInfo>|<\\/OtherCustomerInfo>\\r\\n            <ReceiptNo>2412533854<\\/ReceiptNo>\\r\\n            <CollectionsAccount>0064030950<\\/CollectionsAccount>\\r\\n            <BankCode>SBP<\\/BankCode>\\r\\n            <CustomerAddress><\\/CustomerAddress>\\r\\n            <CustomerPhoneNumber><\\/CustomerPhoneNumber>\\r\\n            <DepositorName><\\/DepositorName>\\r\\n            <DepositSlipNumber>001<\\/DepositSlipNumber>\\r\\n            <PaymentCurrency>566<\\/PaymentCurrency>\\r\\n            <PaymentItems>\\r\\n                <PaymentItem>\\r\\n                    <ItemName>Rent on Govt Building<\\/ItemName>\\r\\n                    <ItemCode>403<\\/ItemCode>\\r\\n                    <ItemAmount>2407000.00<\\/ItemAmount>\\r\\n                    <LeadBankCode>FBN<\\/LeadBankCode>\\r\\n                    <LeadBankCbnCode>011<\\/LeadBankCbnCode>\\r\\n                    <LeadBankName>First Bank of Nigeria Plc<\\/LeadBankName>\\r\\n                    <CategoryCode><\\/CategoryCode>\\r\\n                    <CategoryName><\\/CategoryName>\\r\\n                <\\/PaymentItem>\\r\\n            <\\/PaymentItems>\\r\\n            <ProductGroupCode>HTTPGENERICv31<\\/ProductGroupCode>\\r\\n            <PaymentStatus>0<\\/PaymentStatus>\\r\\n            <IsReversal>False<\\/IsReversal>\\r\\n            <SettlementDate>10\\/25\\/2024 00:00:01<\\/SettlementDate>\\r\\n            <FeeName><\\/FeeName>\\r\\n            <ThirdPartyCode><\\/ThirdPartyCode>\\r\\n            <OriginalPaymentLogId><\\/OriginalPaymentLogId>\\r\\n            <OriginalPaymentReference><\\/OriginalPaymentReference>\\r\\n            <Teller>Chinnan Rindi<\\/Teller>\\r\\n        <\\/Payment>\\r\\n    <\\/Payments>\\r\\n<\\/PaymentNotificationRequest>\"', NULL, '2024-11-01 07:13:35'),
(50, 'paydirect', '\"<PaymentNotificationRequest xmlns:ns2=\\\"http:\\/\\/techquest.interswitchng.com\\/\\\" xmlns:ns3=\\\"http:\\/\\/www.w3.org\\/2003\\/05\\/soap-envelope\\\">\\r\\n    <ServiceUrl>https:\\/\\/plateauigr.com\\/php\\/paydirect\\/<\\/ServiceUrl>\\r\\n    <ServiceUsername><\\/ServiceUsername>\\r\\n    <ServicePassword><\\/ServicePassword>\\r\\n    <FtpUrl>https:\\/\\/plateauigr.com\\/php\\/paydirect\\/<\\/FtpUrl>\\r\\n    <FtpUsername><\\/FtpUsername>\\r\\n    <FtpPassword><\\/FtpPassword>\\r\\n    <Payments>\\r\\n        <Payment>\\r\\n            <PaymentLogId>291449489<\\/PaymentLogId>\\r\\n            <CustReference>INV-6718C06887D13<\\/CustReference>\\r\\n            <AlternateCustReference>PLS-3205781946<\\/AlternateCustReference>\\r\\n            <Amount>2407000.00<\\/Amount>\\r\\n            <PaymentMethod>Internal Transfer<\\/PaymentMethod>\\r\\n            <PaymentReference>SBP|BRH|PGT|24-10-2024|249262<\\/PaymentReference>\\r\\n            <TerminalId><\\/TerminalId>\\r\\n            <ChannelName>Bank Branc<\\/ChannelName>\\r\\n            <Location>322 - JOS<\\/Location>\\r\\n            <PaymentDate>10\\/24\\/2024 14:32:33<\\/PaymentDate>\\r\\n            <InstitutionId>PGT<\\/InstitutionId>\\r\\n            <InstitutionName>Plat Govt<\\/InstitutionName>\\r\\n            <BranchName>322 - JOS<\\/BranchName>\\r\\n            <BankName>Sterling Bank Plc<\\/BankName>\\r\\n            <CustomerName>JOS MAIN MARKET AUTHORITY \\ufffd<\\/CustomerName>\\r\\n            <OtherCustomerInfo>|<\\/OtherCustomerInfo>\\r\\n            <ReceiptNo>2412533854<\\/ReceiptNo>\\r\\n            <CollectionsAccount>0064030950<\\/CollectionsAccount>\\r\\n            <BankCode>SBP<\\/BankCode>\\r\\n            <CustomerAddress><\\/CustomerAddress>\\r\\n            <CustomerPhoneNumber><\\/CustomerPhoneNumber>\\r\\n            <DepositorName><\\/DepositorName>\\r\\n            <DepositSlipNumber>001<\\/DepositSlipNumber>\\r\\n            <PaymentCurrency>566<\\/PaymentCurrency>\\r\\n            <PaymentItems>\\r\\n                <PaymentItem>\\r\\n                    <ItemName>Rent on Govt Building<\\/ItemName>\\r\\n                    <ItemCode>403<\\/ItemCode>\\r\\n                    <ItemAmount>2407000.00<\\/ItemAmount>\\r\\n                    <LeadBankCode>FBN<\\/LeadBankCode>\\r\\n                    <LeadBankCbnCode>011<\\/LeadBankCbnCode>\\r\\n                    <LeadBankName>First Bank of Nigeria Plc<\\/LeadBankName>\\r\\n                    <CategoryCode><\\/CategoryCode>\\r\\n                    <CategoryName><\\/CategoryName>\\r\\n                <\\/PaymentItem>\\r\\n            <\\/PaymentItems>\\r\\n            <ProductGroupCode>HTTPGENERICv31<\\/ProductGroupCode>\\r\\n            <PaymentStatus>0<\\/PaymentStatus>\\r\\n            <IsReversal>False<\\/IsReversal>\\r\\n            <SettlementDate>10\\/25\\/2024 00:00:01<\\/SettlementDate>\\r\\n            <FeeName><\\/FeeName>\\r\\n            <ThirdPartyCode><\\/ThirdPartyCode>\\r\\n            <OriginalPaymentLogId><\\/OriginalPaymentLogId>\\r\\n            <OriginalPaymentReference><\\/OriginalPaymentReference>\\r\\n            <Teller>Chinnan Rindi<\\/Teller>\\r\\n        <\\/Payment>\\r\\n    <\\/Payments>\\r\\n<\\/PaymentNotificationRequest>\"', NULL, '2024-11-01 07:14:45'),
(51, 'paydirect', '\"<PaymentNotificationRequest xmlns:ns2=\\\"http:\\/\\/techquest.interswitchng.com\\/\\\" xmlns:ns3=\\\"http:\\/\\/www.w3.org\\/2003\\/05\\/soap-envelope\\\">\\r\\n    <ServiceUrl>https:\\/\\/plateauigr.com\\/php\\/paydirect\\/<\\/ServiceUrl>\\r\\n    <ServiceUsername><\\/ServiceUsername>\\r\\n    <ServicePassword><\\/ServicePassword>\\r\\n    <FtpUrl>https:\\/\\/plateauigr.com\\/php\\/paydirect\\/<\\/FtpUrl>\\r\\n    <FtpUsername><\\/FtpUsername>\\r\\n    <FtpPassword><\\/FtpPassword>\\r\\n    <Payments>\\r\\n        <Payment>\\r\\n            <PaymentLogId>291449489<\\/PaymentLogId>\\r\\n            <CustReference>INV-6718C06887D13<\\/CustReference>\\r\\n            <AlternateCustReference>PLS-3205781946<\\/AlternateCustReference>\\r\\n            <Amount>2407000.00<\\/Amount>\\r\\n            <PaymentMethod>Internal Transfer<\\/PaymentMethod>\\r\\n            <PaymentReference>SBP|BRH|PGT|24-10-2024|249262<\\/PaymentReference>\\r\\n            <TerminalId><\\/TerminalId>\\r\\n            <ChannelName>Bank Branc<\\/ChannelName>\\r\\n            <Location>322 - JOS<\\/Location>\\r\\n            <PaymentDate>10\\/24\\/2024 14:32:33<\\/PaymentDate>\\r\\n            <InstitutionId>PGT<\\/InstitutionId>\\r\\n            <InstitutionName>Plat Govt<\\/InstitutionName>\\r\\n            <BranchName>322 - JOS<\\/BranchName>\\r\\n            <BankName>Sterling Bank Plc<\\/BankName>\\r\\n            <CustomerName>JOS MAIN MARKET AUTHORITY \\ufffd<\\/CustomerName>\\r\\n            <OtherCustomerInfo>|<\\/OtherCustomerInfo>\\r\\n            <ReceiptNo>2412533854<\\/ReceiptNo>\\r\\n            <CollectionsAccount>0064030950<\\/CollectionsAccount>\\r\\n            <BankCode>SBP<\\/BankCode>\\r\\n            <CustomerAddress><\\/CustomerAddress>\\r\\n            <CustomerPhoneNumber><\\/CustomerPhoneNumber>\\r\\n            <DepositorName><\\/DepositorName>\\r\\n            <DepositSlipNumber>001<\\/DepositSlipNumber>\\r\\n            <PaymentCurrency>566<\\/PaymentCurrency>\\r\\n            <PaymentItems>\\r\\n                <PaymentItem>\\r\\n                    <ItemName>Rent on Govt Building<\\/ItemName>\\r\\n                    <ItemCode>403<\\/ItemCode>\\r\\n                    <ItemAmount>2407000.00<\\/ItemAmount>\\r\\n                    <LeadBankCode>FBN<\\/LeadBankCode>\\r\\n                    <LeadBankCbnCode>011<\\/LeadBankCbnCode>\\r\\n                    <LeadBankName>First Bank of Nigeria Plc<\\/LeadBankName>\\r\\n                    <CategoryCode><\\/CategoryCode>\\r\\n                    <CategoryName><\\/CategoryName>\\r\\n                <\\/PaymentItem>\\r\\n            <\\/PaymentItems>\\r\\n            <ProductGroupCode>HTTPGENERICv31<\\/ProductGroupCode>\\r\\n            <PaymentStatus>0<\\/PaymentStatus>\\r\\n            <IsReversal>False<\\/IsReversal>\\r\\n            <SettlementDate>10\\/25\\/2024 00:00:01<\\/SettlementDate>\\r\\n            <FeeName><\\/FeeName>\\r\\n            <ThirdPartyCode><\\/ThirdPartyCode>\\r\\n            <OriginalPaymentLogId><\\/OriginalPaymentLogId>\\r\\n            <OriginalPaymentReference><\\/OriginalPaymentReference>\\r\\n            <Teller>Chinnan Rindi<\\/Teller>\\r\\n        <\\/Payment>\\r\\n    <\\/Payments>\\r\\n<\\/PaymentNotificationRequest>\"', NULL, '2024-11-01 07:15:48');
INSERT INTO `gateway_payload` (`id`, `gateway`, `payload`, `result_out`, `date_created`) VALUES
(52, 'paydirect', '\"<PaymentNotificationRequest xmlns:ns2=\\\"http:\\/\\/techquest.interswitchng.com\\/\\\" xmlns:ns3=\\\"http:\\/\\/www.w3.org\\/2003\\/05\\/soap-envelope\\\">\\r\\n    <ServiceUrl>https:\\/\\/plateauigr.com\\/php\\/paydirect\\/<\\/ServiceUrl>\\r\\n    <ServiceUsername><\\/ServiceUsername>\\r\\n    <ServicePassword><\\/ServicePassword>\\r\\n    <FtpUrl>https:\\/\\/plateauigr.com\\/php\\/paydirect\\/<\\/FtpUrl>\\r\\n    <FtpUsername><\\/FtpUsername>\\r\\n    <FtpPassword><\\/FtpPassword>\\r\\n    <Payments>\\r\\n        <Payment>\\r\\n            <PaymentLogId>291449489<\\/PaymentLogId>\\r\\n            <CustReference>INV-6718C06887D13<\\/CustReference>\\r\\n            <AlternateCustReference>PLS-3205781946<\\/AlternateCustReference>\\r\\n            <Amount>2407000.00<\\/Amount>\\r\\n            <PaymentMethod>Internal Transfer<\\/PaymentMethod>\\r\\n            <PaymentReference>SBP|BRH|PGT|24-10-2024|249262<\\/PaymentReference>\\r\\n            <TerminalId><\\/TerminalId>\\r\\n            <ChannelName>Bank Branc<\\/ChannelName>\\r\\n            <Location>322 - JOS<\\/Location>\\r\\n            <PaymentDate>10\\/24\\/2024 14:32:33<\\/PaymentDate>\\r\\n            <InstitutionId>PGT<\\/InstitutionId>\\r\\n            <InstitutionName>Plat Govt<\\/InstitutionName>\\r\\n            <BranchName>322 - JOS<\\/BranchName>\\r\\n            <BankName>Sterling Bank Plc<\\/BankName>\\r\\n            <CustomerName>JOS MAIN MARKET AUTHORITY \\ufffd<\\/CustomerName>\\r\\n            <OtherCustomerInfo>|<\\/OtherCustomerInfo>\\r\\n            <ReceiptNo>2412533854<\\/ReceiptNo>\\r\\n            <CollectionsAccount>0064030950<\\/CollectionsAccount>\\r\\n            <BankCode>SBP<\\/BankCode>\\r\\n            <CustomerAddress><\\/CustomerAddress>\\r\\n            <CustomerPhoneNumber><\\/CustomerPhoneNumber>\\r\\n            <DepositorName><\\/DepositorName>\\r\\n            <DepositSlipNumber>001<\\/DepositSlipNumber>\\r\\n            <PaymentCurrency>566<\\/PaymentCurrency>\\r\\n            <PaymentItems>\\r\\n                <PaymentItem>\\r\\n                    <ItemName>Rent on Govt Building<\\/ItemName>\\r\\n                    <ItemCode>403<\\/ItemCode>\\r\\n                    <ItemAmount>2407000.00<\\/ItemAmount>\\r\\n                    <LeadBankCode>FBN<\\/LeadBankCode>\\r\\n                    <LeadBankCbnCode>011<\\/LeadBankCbnCode>\\r\\n                    <LeadBankName>First Bank of Nigeria Plc<\\/LeadBankName>\\r\\n                    <CategoryCode><\\/CategoryCode>\\r\\n                    <CategoryName><\\/CategoryName>\\r\\n                <\\/PaymentItem>\\r\\n            <\\/PaymentItems>\\r\\n            <ProductGroupCode>HTTPGENERICv31<\\/ProductGroupCode>\\r\\n            <PaymentStatus>0<\\/PaymentStatus>\\r\\n            <IsReversal>False<\\/IsReversal>\\r\\n            <SettlementDate>10\\/25\\/2024 00:00:01<\\/SettlementDate>\\r\\n            <FeeName><\\/FeeName>\\r\\n            <ThirdPartyCode><\\/ThirdPartyCode>\\r\\n            <OriginalPaymentLogId><\\/OriginalPaymentLogId>\\r\\n            <OriginalPaymentReference><\\/OriginalPaymentReference>\\r\\n            <Teller>Chinnan Rindi<\\/Teller>\\r\\n        <\\/Payment>\\r\\n    <\\/Payments>\\r\\n<\\/PaymentNotificationRequest>\"', NULL, '2024-11-01 07:15:59'),
(53, 'paydirect', '\"<PaymentNotificationRequest xmlns:ns2=\\\"http:\\/\\/techquest.interswitchng.com\\/\\\" xmlns:ns3=\\\"http:\\/\\/www.w3.org\\/2003\\/05\\/soap-envelope\\\">\\r\\n    <ServiceUrl>https:\\/\\/plateauigr.com\\/php\\/paydirect\\/<\\/ServiceUrl>\\r\\n    <ServiceUsername><\\/ServiceUsername>\\r\\n    <ServicePassword><\\/ServicePassword>\\r\\n    <FtpUrl>https:\\/\\/plateauigr.com\\/php\\/paydirect\\/<\\/FtpUrl>\\r\\n    <FtpUsername><\\/FtpUsername>\\r\\n    <FtpPassword><\\/FtpPassword>\\r\\n    <Payments>\\r\\n        <Payment>\\r\\n            <PaymentLogId>291449489<\\/PaymentLogId>\\r\\n            <CustReference>INV-6718C06887D13<\\/CustReference>\\r\\n            <AlternateCustReference>PLS-3205781946<\\/AlternateCustReference>\\r\\n            <Amount>2407000.00<\\/Amount>\\r\\n            <PaymentMethod>Internal Transfer<\\/PaymentMethod>\\r\\n            <PaymentReference>SBP|BRH|PGT|24-10-2024|249262<\\/PaymentReference>\\r\\n            <TerminalId><\\/TerminalId>\\r\\n            <ChannelName>Bank Branc<\\/ChannelName>\\r\\n            <Location>322 - JOS<\\/Location>\\r\\n            <PaymentDate>10\\/24\\/2024 14:32:33<\\/PaymentDate>\\r\\n            <InstitutionId>PGT<\\/InstitutionId>\\r\\n            <InstitutionName>Plat Govt<\\/InstitutionName>\\r\\n            <BranchName>322 - JOS<\\/BranchName>\\r\\n            <BankName>Sterling Bank Plc<\\/BankName>\\r\\n            <CustomerName>JOS MAIN MARKET AUTHORITY \\ufffd<\\/CustomerName>\\r\\n            <OtherCustomerInfo>|<\\/OtherCustomerInfo>\\r\\n            <ReceiptNo>2412533854<\\/ReceiptNo>\\r\\n            <CollectionsAccount>0064030950<\\/CollectionsAccount>\\r\\n            <BankCode>SBP<\\/BankCode>\\r\\n            <CustomerAddress><\\/CustomerAddress>\\r\\n            <CustomerPhoneNumber><\\/CustomerPhoneNumber>\\r\\n            <DepositorName><\\/DepositorName>\\r\\n            <DepositSlipNumber>001<\\/DepositSlipNumber>\\r\\n            <PaymentCurrency>566<\\/PaymentCurrency>\\r\\n            <PaymentItems>\\r\\n                <PaymentItem>\\r\\n                    <ItemName>Rent on Govt Building<\\/ItemName>\\r\\n                    <ItemCode>403<\\/ItemCode>\\r\\n                    <ItemAmount>2407000.00<\\/ItemAmount>\\r\\n                    <LeadBankCode>FBN<\\/LeadBankCode>\\r\\n                    <LeadBankCbnCode>011<\\/LeadBankCbnCode>\\r\\n                    <LeadBankName>First Bank of Nigeria Plc<\\/LeadBankName>\\r\\n                    <CategoryCode><\\/CategoryCode>\\r\\n                    <CategoryName><\\/CategoryName>\\r\\n                <\\/PaymentItem>\\r\\n            <\\/PaymentItems>\\r\\n            <ProductGroupCode>HTTPGENERICv31<\\/ProductGroupCode>\\r\\n            <PaymentStatus>0<\\/PaymentStatus>\\r\\n            <IsReversal>False<\\/IsReversal>\\r\\n            <SettlementDate>10\\/25\\/2024 00:00:01<\\/SettlementDate>\\r\\n            <FeeName><\\/FeeName>\\r\\n            <ThirdPartyCode><\\/ThirdPartyCode>\\r\\n            <OriginalPaymentLogId><\\/OriginalPaymentLogId>\\r\\n            <OriginalPaymentReference><\\/OriginalPaymentReference>\\r\\n            <Teller>Chinnan Rindi<\\/Teller>\\r\\n        <\\/Payment>\\r\\n    <\\/Payments>\\r\\n<\\/PaymentNotificationRequest>\"', NULL, '2024-11-01 07:17:50'),
(54, 'paydirect', '\"<PaymentNotificationRequest xmlns:ns2=\\\"http:\\/\\/techquest.interswitchng.com\\/\\\" xmlns:ns3=\\\"http:\\/\\/www.w3.org\\/2003\\/05\\/soap-envelope\\\">\\r\\n    <ServiceUrl>https:\\/\\/plateauigr.com\\/php\\/paydirect\\/<\\/ServiceUrl>\\r\\n    <ServiceUsername><\\/ServiceUsername>\\r\\n    <ServicePassword><\\/ServicePassword>\\r\\n    <FtpUrl>https:\\/\\/plateauigr.com\\/php\\/paydirect\\/<\\/FtpUrl>\\r\\n    <FtpUsername><\\/FtpUsername>\\r\\n    <FtpPassword><\\/FtpPassword>\\r\\n    <Payments>\\r\\n        <Payment>\\r\\n            <PaymentLogId>291449489<\\/PaymentLogId>\\r\\n            <CustReference>INV-6718C06887D13<\\/CustReference>\\r\\n            <AlternateCustReference>PLS-3205781946<\\/AlternateCustReference>\\r\\n            <Amount>2407000.00<\\/Amount>\\r\\n            <PaymentMethod>Internal Transfer<\\/PaymentMethod>\\r\\n            <PaymentReference>SBP|BRH|PGT|24-10-2024|249262<\\/PaymentReference>\\r\\n            <TerminalId><\\/TerminalId>\\r\\n            <ChannelName>Bank Branc<\\/ChannelName>\\r\\n            <Location>322 - JOS<\\/Location>\\r\\n            <PaymentDate>10\\/24\\/2024 14:32:33<\\/PaymentDate>\\r\\n            <InstitutionId>PGT<\\/InstitutionId>\\r\\n            <InstitutionName>Plat Govt<\\/InstitutionName>\\r\\n            <BranchName>322 - JOS<\\/BranchName>\\r\\n            <BankName>Sterling Bank Plc<\\/BankName>\\r\\n            <CustomerName>JOS MAIN MARKET AUTHORITY \\ufffd<\\/CustomerName>\\r\\n            <OtherCustomerInfo>|<\\/OtherCustomerInfo>\\r\\n            <ReceiptNo>2412533854<\\/ReceiptNo>\\r\\n            <CollectionsAccount>0064030950<\\/CollectionsAccount>\\r\\n            <BankCode>SBP<\\/BankCode>\\r\\n            <CustomerAddress><\\/CustomerAddress>\\r\\n            <CustomerPhoneNumber><\\/CustomerPhoneNumber>\\r\\n            <DepositorName><\\/DepositorName>\\r\\n            <DepositSlipNumber>001<\\/DepositSlipNumber>\\r\\n            <PaymentCurrency>566<\\/PaymentCurrency>\\r\\n            <PaymentItems>\\r\\n                <PaymentItem>\\r\\n                    <ItemName>Rent on Govt Building<\\/ItemName>\\r\\n                    <ItemCode>403<\\/ItemCode>\\r\\n                    <ItemAmount>2407000.00<\\/ItemAmount>\\r\\n                    <LeadBankCode>FBN<\\/LeadBankCode>\\r\\n                    <LeadBankCbnCode>011<\\/LeadBankCbnCode>\\r\\n                    <LeadBankName>First Bank of Nigeria Plc<\\/LeadBankName>\\r\\n                    <CategoryCode><\\/CategoryCode>\\r\\n                    <CategoryName><\\/CategoryName>\\r\\n                <\\/PaymentItem>\\r\\n            <\\/PaymentItems>\\r\\n            <ProductGroupCode>HTTPGENERICv31<\\/ProductGroupCode>\\r\\n            <PaymentStatus>0<\\/PaymentStatus>\\r\\n            <IsReversal>False<\\/IsReversal>\\r\\n            <SettlementDate>10\\/25\\/2024 00:00:01<\\/SettlementDate>\\r\\n            <FeeName><\\/FeeName>\\r\\n            <ThirdPartyCode><\\/ThirdPartyCode>\\r\\n            <OriginalPaymentLogId><\\/OriginalPaymentLogId>\\r\\n            <OriginalPaymentReference><\\/OriginalPaymentReference>\\r\\n            <Teller>Chinnan Rindi<\\/Teller>\\r\\n        <\\/Payment>\\r\\n    <\\/Payments>\\r\\n<\\/PaymentNotificationRequest>\"', NULL, '2024-11-01 07:18:22'),
(55, 'paydirect', '\"<PaymentNotificationRequest xmlns:ns2=\\\"http:\\/\\/techquest.interswitchng.com\\/\\\" xmlns:ns3=\\\"http:\\/\\/www.w3.org\\/2003\\/05\\/soap-envelope\\\">\\r\\n    <ServiceUrl>https:\\/\\/plateauigr.com\\/php\\/paydirect\\/<\\/ServiceUrl>\\r\\n    <ServiceUsername><\\/ServiceUsername>\\r\\n    <ServicePassword><\\/ServicePassword>\\r\\n    <FtpUrl>https:\\/\\/plateauigr.com\\/php\\/paydirect\\/<\\/FtpUrl>\\r\\n    <FtpUsername><\\/FtpUsername>\\r\\n    <FtpPassword><\\/FtpPassword>\\r\\n    <Payments>\\r\\n        <Payment>\\r\\n            <PaymentLogId>291449489<\\/PaymentLogId>\\r\\n            <CustReference>INV-6718C06887D13<\\/CustReference>\\r\\n            <AlternateCustReference>PLS-3205781946<\\/AlternateCustReference>\\r\\n            <Amount>2407000.00<\\/Amount>\\r\\n            <PaymentMethod>Internal Transfer<\\/PaymentMethod>\\r\\n            <PaymentReference>SBP|BRH|PGT|24-10-2024|249262<\\/PaymentReference>\\r\\n            <TerminalId><\\/TerminalId>\\r\\n            <ChannelName>Bank Branc<\\/ChannelName>\\r\\n            <Location>322 - JOS<\\/Location>\\r\\n            <PaymentDate>10\\/24\\/2024 14:32:33<\\/PaymentDate>\\r\\n            <InstitutionId>PGT<\\/InstitutionId>\\r\\n            <InstitutionName>Plat Govt<\\/InstitutionName>\\r\\n            <BranchName>322 - JOS<\\/BranchName>\\r\\n            <BankName>Sterling Bank Plc<\\/BankName>\\r\\n            <CustomerName>JOS MAIN MARKET AUTHORITY \\ufffd<\\/CustomerName>\\r\\n            <OtherCustomerInfo>|<\\/OtherCustomerInfo>\\r\\n            <ReceiptNo>2412533854<\\/ReceiptNo>\\r\\n            <CollectionsAccount>0064030950<\\/CollectionsAccount>\\r\\n            <BankCode>SBP<\\/BankCode>\\r\\n            <CustomerAddress><\\/CustomerAddress>\\r\\n            <CustomerPhoneNumber><\\/CustomerPhoneNumber>\\r\\n            <DepositorName><\\/DepositorName>\\r\\n            <DepositSlipNumber>001<\\/DepositSlipNumber>\\r\\n            <PaymentCurrency>566<\\/PaymentCurrency>\\r\\n            <PaymentItems>\\r\\n                <PaymentItem>\\r\\n                    <ItemName>Rent on Govt Building<\\/ItemName>\\r\\n                    <ItemCode>403<\\/ItemCode>\\r\\n                    <ItemAmount>2407000.00<\\/ItemAmount>\\r\\n                    <LeadBankCode>FBN<\\/LeadBankCode>\\r\\n                    <LeadBankCbnCode>011<\\/LeadBankCbnCode>\\r\\n                    <LeadBankName>First Bank of Nigeria Plc<\\/LeadBankName>\\r\\n                    <CategoryCode><\\/CategoryCode>\\r\\n                    <CategoryName><\\/CategoryName>\\r\\n                <\\/PaymentItem>\\r\\n            <\\/PaymentItems>\\r\\n            <ProductGroupCode>HTTPGENERICv31<\\/ProductGroupCode>\\r\\n            <PaymentStatus>0<\\/PaymentStatus>\\r\\n            <IsReversal>False<\\/IsReversal>\\r\\n            <SettlementDate>10\\/25\\/2024 00:00:01<\\/SettlementDate>\\r\\n            <FeeName><\\/FeeName>\\r\\n            <ThirdPartyCode><\\/ThirdPartyCode>\\r\\n            <OriginalPaymentLogId><\\/OriginalPaymentLogId>\\r\\n            <OriginalPaymentReference><\\/OriginalPaymentReference>\\r\\n            <Teller>Chinnan Rindi<\\/Teller>\\r\\n        <\\/Payment>\\r\\n    <\\/Payments>\\r\\n<\\/PaymentNotificationRequest>\"', '{\"PaymentNotificationResponse\":{\"Payments\":{\"Payment\":{\"PaymentLogId\":\"291449489\",\"Status\":0,\"StatusMessage\":\"Success\"}}}}', '2024-11-01 07:28:33'),
(56, 'credo', '{\"event\":\"TRANSACTION.SUCCESSFUL\",\"data\":{\"businessCode\":\"700607010880003\",\"serviceCode\":null,\"transRef\":\"Y7B000yBkA117lle363w\",\"businessRef\":\"1136sEohDz1727087068\",\"debitedAmount\":50,\"transAmount\":50,\"transFeeAmount\":3.25,\"settlementAmount\":46.75,\"customerId\":\"joshypeterz@gmail.com\",\"transactionDate\":1727087161363,\"currencyCode\":\"NGN\",\"status\":0,\"paymentMethodType\":\"Gtbank Plc\",\"paymentMethod\":\"Bank Transfer\",\"businessId\":0,\"narration\":null,\"customer\":{\"customerEmail\":\"joshypeterz@gmail.com\",\"firstName\":\"Joshua Omueta\",\"lastName\":\"INV-6718C06887D13\",\"phoneNo\":\"08138909022\"},\"metadata\":[]}}', NULL, '2024-11-01 07:29:49'),
(57, 'credo', '{\"event\":\"TRANSACTION.SUCCESSFUL\",\"data\":{\"businessCode\":\"700607010880003\",\"serviceCode\":null,\"transRef\":\"Y7B000yBkA117lle363w\",\"businessRef\":\"1136sEohDz1727087068\",\"debitedAmount\":50,\"transAmount\":50,\"transFeeAmount\":3.25,\"settlementAmount\":46.75,\"customerId\":\"joshypeterz@gmail.com\",\"transactionDate\":1727087161363,\"currencyCode\":\"NGN\",\"status\":0,\"paymentMethodType\":\"Gtbank Plc\",\"paymentMethod\":\"Bank Transfer\",\"businessId\":0,\"narration\":null,\"customer\":{\"customerEmail\":\"joshypeterz@gmail.com\",\"firstName\":\"Joshua Omueta\",\"lastName\":\"INV-6718C06887D13\",\"phoneNo\":\"08138909022\"},\"metadata\":[]}}', '{\"status\":\"error\",\"message\":\"This invoice is already paid\"}', '2024-11-01 07:36:22'),
(58, 'paystack', '{\"event\":\"charge.success\",\"data\":{\"id\":4286956222,\"domain\":\"live\",\"status\":\"success\",\"reference\":\"T671076302190401\",\"amount\":8500000,\"paid_at\":\"2024-10-18T19:34:27.000Z\",\"authorization\":{\"card_type\":\"transfer\",\"bank\":\"OPay Digital Services Limited (OPay)\"},\"metadata\":{\"custom_fields\":[{\"display_name\":\"Invoice Number\",\"variable_name\":\"invoice_number\",\"value\":\"INV-6718C06887D13\"}]}}}', '{\"status\":\"error\",\"message\":\"This invoice is already paid\"}', '2024-11-01 07:37:16'),
(59, 'paydirect', '\"<PaymentNotificationRequest xmlns:ns2=\\\"http:\\/\\/techquest.interswitchng.com\\/\\\" xmlns:ns3=\\\"http:\\/\\/www.w3.org\\/2003\\/05\\/soap-envelope\\\">\\r\\n    <ServiceUrl>https:\\/\\/plateauigr.com\\/php\\/paydirect\\/<\\/ServiceUrl>\\r\\n    <ServiceUsername><\\/ServiceUsername>\\r\\n    <ServicePassword><\\/ServicePassword>\\r\\n    <FtpUrl>https:\\/\\/plateauigr.com\\/php\\/paydirect\\/<\\/FtpUrl>\\r\\n    <FtpUsername><\\/FtpUsername>\\r\\n    <FtpPassword><\\/FtpPassword>\\r\\n    <Payments>\\r\\n        <Payment>\\r\\n            <PaymentLogId>291449489<\\/PaymentLogId>\\r\\n            <CustReference>INV-6718C06887D13<\\/CustReference>\\r\\n            <AlternateCustReference>PLS-3205781946<\\/AlternateCustReference>\\r\\n            <Amount>2407000.00<\\/Amount>\\r\\n            <PaymentMethod>Internal Transfer<\\/PaymentMethod>\\r\\n            <PaymentReference>SBP|BRH|PGT|24-10-2024|249262<\\/PaymentReference>\\r\\n            <TerminalId><\\/TerminalId>\\r\\n            <ChannelName>Bank Branc<\\/ChannelName>\\r\\n            <Location>322 - JOS<\\/Location>\\r\\n            <PaymentDate>10\\/24\\/2024 14:32:33<\\/PaymentDate>\\r\\n            <InstitutionId>PGT<\\/InstitutionId>\\r\\n            <InstitutionName>Plat Govt<\\/InstitutionName>\\r\\n            <BranchName>322 - JOS<\\/BranchName>\\r\\n            <BankName>Sterling Bank Plc<\\/BankName>\\r\\n            <CustomerName>JOS MAIN MARKET AUTHORITY \\ufffd<\\/CustomerName>\\r\\n            <OtherCustomerInfo>|<\\/OtherCustomerInfo>\\r\\n            <ReceiptNo>2412533854<\\/ReceiptNo>\\r\\n            <CollectionsAccount>0064030950<\\/CollectionsAccount>\\r\\n            <BankCode>SBP<\\/BankCode>\\r\\n            <CustomerAddress><\\/CustomerAddress>\\r\\n            <CustomerPhoneNumber><\\/CustomerPhoneNumber>\\r\\n            <DepositorName><\\/DepositorName>\\r\\n            <DepositSlipNumber>001<\\/DepositSlipNumber>\\r\\n            <PaymentCurrency>566<\\/PaymentCurrency>\\r\\n            <PaymentItems>\\r\\n                <PaymentItem>\\r\\n                    <ItemName>Rent on Govt Building<\\/ItemName>\\r\\n                    <ItemCode>403<\\/ItemCode>\\r\\n                    <ItemAmount>2407000.00<\\/ItemAmount>\\r\\n                    <LeadBankCode>FBN<\\/LeadBankCode>\\r\\n                    <LeadBankCbnCode>011<\\/LeadBankCbnCode>\\r\\n                    <LeadBankName>First Bank of Nigeria Plc<\\/LeadBankName>\\r\\n                    <CategoryCode><\\/CategoryCode>\\r\\n                    <CategoryName><\\/CategoryName>\\r\\n                <\\/PaymentItem>\\r\\n            <\\/PaymentItems>\\r\\n            <ProductGroupCode>HTTPGENERICv31<\\/ProductGroupCode>\\r\\n            <PaymentStatus>0<\\/PaymentStatus>\\r\\n            <IsReversal>False<\\/IsReversal>\\r\\n            <SettlementDate>10\\/25\\/2024 00:00:01<\\/SettlementDate>\\r\\n            <FeeName><\\/FeeName>\\r\\n            <ThirdPartyCode><\\/ThirdPartyCode>\\r\\n            <OriginalPaymentLogId><\\/OriginalPaymentLogId>\\r\\n            <OriginalPaymentReference><\\/OriginalPaymentReference>\\r\\n            <Teller>Chinnan Rindi<\\/Teller>\\r\\n        <\\/Payment>\\r\\n    <\\/Payments>\\r\\n<\\/PaymentNotificationRequest>\"', '{\"PaymentNotificationResponse\":{\"Payments\":{\"Payment\":{\"PaymentLogId\":\"291449489\",\"Status\":0,\"StatusMessage\":\"Duplicate\"}}}}', '2024-11-01 07:40:07'),
(60, 'paydirect', '\"<PaymentNotificationRequest xmlns:ns2=\\\"http:\\/\\/techquest.interswitchng.com\\/\\\" xmlns:ns3=\\\"http:\\/\\/www.w3.org\\/2003\\/05\\/soap-envelope\\\">\\r\\n    <ServiceUrl>https:\\/\\/plateauigr.com\\/php\\/paydirect\\/<\\/ServiceUrl>\\r\\n    <ServiceUsername><\\/ServiceUsername>\\r\\n    <ServicePassword><\\/ServicePassword>\\r\\n    <FtpUrl>https:\\/\\/plateauigr.com\\/php\\/paydirect\\/<\\/FtpUrl>\\r\\n    <FtpUsername><\\/FtpUsername>\\r\\n    <FtpPassword><\\/FtpPassword>\\r\\n    <Payments>\\r\\n        <Payment>\\r\\n            <PaymentLogId>291449489<\\/PaymentLogId>\\r\\n            <CustReference>INV-6718C06887D13<\\/CustReference>\\r\\n            <AlternateCustReference>PLS-3205781946<\\/AlternateCustReference>\\r\\n            <Amount>2407000.00<\\/Amount>\\r\\n            <PaymentMethod>Internal Transfer<\\/PaymentMethod>\\r\\n            <PaymentReference>SBP|BRH|PGT|24-10-2024|249262<\\/PaymentReference>\\r\\n            <TerminalId><\\/TerminalId>\\r\\n            <ChannelName>Bank Branc<\\/ChannelName>\\r\\n            <Location>322 - JOS<\\/Location>\\r\\n            <PaymentDate>10\\/24\\/2024 14:32:33<\\/PaymentDate>\\r\\n            <InstitutionId>PGT<\\/InstitutionId>\\r\\n            <InstitutionName>Plat Govt<\\/InstitutionName>\\r\\n            <BranchName>322 - JOS<\\/BranchName>\\r\\n            <BankName>Sterling Bank Plc<\\/BankName>\\r\\n            <CustomerName>JOS MAIN MARKET AUTHORITY \\ufffd<\\/CustomerName>\\r\\n            <OtherCustomerInfo>|<\\/OtherCustomerInfo>\\r\\n            <ReceiptNo>2412533854<\\/ReceiptNo>\\r\\n            <CollectionsAccount>0064030950<\\/CollectionsAccount>\\r\\n            <BankCode>SBP<\\/BankCode>\\r\\n            <CustomerAddress><\\/CustomerAddress>\\r\\n            <CustomerPhoneNumber><\\/CustomerPhoneNumber>\\r\\n            <DepositorName><\\/DepositorName>\\r\\n            <DepositSlipNumber>001<\\/DepositSlipNumber>\\r\\n            <PaymentCurrency>566<\\/PaymentCurrency>\\r\\n            <PaymentItems>\\r\\n                <PaymentItem>\\r\\n                    <ItemName>Rent on Govt Building<\\/ItemName>\\r\\n                    <ItemCode>403<\\/ItemCode>\\r\\n                    <ItemAmount>2407000.00<\\/ItemAmount>\\r\\n                    <LeadBankCode>FBN<\\/LeadBankCode>\\r\\n                    <LeadBankCbnCode>011<\\/LeadBankCbnCode>\\r\\n                    <LeadBankName>First Bank of Nigeria Plc<\\/LeadBankName>\\r\\n                    <CategoryCode><\\/CategoryCode>\\r\\n                    <CategoryName><\\/CategoryName>\\r\\n                <\\/PaymentItem>\\r\\n            <\\/PaymentItems>\\r\\n            <ProductGroupCode>HTTPGENERICv31<\\/ProductGroupCode>\\r\\n            <PaymentStatus>0<\\/PaymentStatus>\\r\\n            <IsReversal>False<\\/IsReversal>\\r\\n            <SettlementDate>10\\/25\\/2024 00:00:01<\\/SettlementDate>\\r\\n            <FeeName><\\/FeeName>\\r\\n            <ThirdPartyCode><\\/ThirdPartyCode>\\r\\n            <OriginalPaymentLogId><\\/OriginalPaymentLogId>\\r\\n            <OriginalPaymentReference><\\/OriginalPaymentReference>\\r\\n            <Teller>Chinnan Rindi<\\/Teller>\\r\\n        <\\/Payment>\\r\\n    <\\/Payments>\\r\\n<\\/PaymentNotificationRequest>\"', '{\"PaymentNotificationResponse\":{\"Payments\":{\"Payment\":{\"PaymentLogId\":\"291449489\",\"Status\":0,\"StatusMessage\":\"Duplicate\"}}}}', '2024-11-01 07:45:30'),
(61, 'paydirect', '\"<PaymentNotificationRequest xmlns:ns2=\\\"http:\\/\\/techquest.interswitchng.com\\/\\\" xmlns:ns3=\\\"http:\\/\\/www.w3.org\\/2003\\/05\\/soap-envelope\\\">\\r\\n    <ServiceUrl>https:\\/\\/plateauigr.com\\/php\\/paydirect\\/<\\/ServiceUrl>\\r\\n    <ServiceUsername><\\/ServiceUsername>\\r\\n    <ServicePassword><\\/ServicePassword>\\r\\n    <FtpUrl>https:\\/\\/plateauigr.com\\/php\\/paydirect\\/<\\/FtpUrl>\\r\\n    <FtpUsername><\\/FtpUsername>\\r\\n    <FtpPassword><\\/FtpPassword>\\r\\n    <Payments>\\r\\n        <Payment>\\r\\n            <PaymentLogId>291449489<\\/PaymentLogId>\\r\\n            <CustReference>INV-6718C06887D13<\\/CustReference>\\r\\n            <AlternateCustReference>PLS-3205781946<\\/AlternateCustReference>\\r\\n            <Amount>2407000.00<\\/Amount>\\r\\n            <PaymentMethod>Internal Transfer<\\/PaymentMethod>\\r\\n            <PaymentReference>SBP|BRH|PGT|24-10-2024|249262<\\/PaymentReference>\\r\\n            <TerminalId><\\/TerminalId>\\r\\n            <ChannelName>Bank Branc<\\/ChannelName>\\r\\n            <Location>322 - JOS<\\/Location>\\r\\n            <PaymentDate>10\\/24\\/2024 14:32:33<\\/PaymentDate>\\r\\n            <InstitutionId>PGT<\\/InstitutionId>\\r\\n            <InstitutionName>Plat Govt<\\/InstitutionName>\\r\\n            <BranchName>322 - JOS<\\/BranchName>\\r\\n            <BankName>Sterling Bank Plc<\\/BankName>\\r\\n            <CustomerName>JOS MAIN MARKET AUTHORITY \\ufffd<\\/CustomerName>\\r\\n            <OtherCustomerInfo>|<\\/OtherCustomerInfo>\\r\\n            <ReceiptNo>2412533854<\\/ReceiptNo>\\r\\n            <CollectionsAccount>0064030950<\\/CollectionsAccount>\\r\\n            <BankCode>SBP<\\/BankCode>\\r\\n            <CustomerAddress><\\/CustomerAddress>\\r\\n            <CustomerPhoneNumber><\\/CustomerPhoneNumber>\\r\\n            <DepositorName><\\/DepositorName>\\r\\n            <DepositSlipNumber>001<\\/DepositSlipNumber>\\r\\n            <PaymentCurrency>566<\\/PaymentCurrency>\\r\\n            <PaymentItems>\\r\\n                <PaymentItem>\\r\\n                    <ItemName>Rent on Govt Building<\\/ItemName>\\r\\n                    <ItemCode>403<\\/ItemCode>\\r\\n                    <ItemAmount>2407000.00<\\/ItemAmount>\\r\\n                    <LeadBankCode>FBN<\\/LeadBankCode>\\r\\n                    <LeadBankCbnCode>011<\\/LeadBankCbnCode>\\r\\n                    <LeadBankName>First Bank of Nigeria Plc<\\/LeadBankName>\\r\\n                    <CategoryCode><\\/CategoryCode>\\r\\n                    <CategoryName><\\/CategoryName>\\r\\n                <\\/PaymentItem>\\r\\n            <\\/PaymentItems>\\r\\n            <ProductGroupCode>HTTPGENERICv31<\\/ProductGroupCode>\\r\\n            <PaymentStatus>0<\\/PaymentStatus>\\r\\n            <IsReversal>False<\\/IsReversal>\\r\\n            <SettlementDate>10\\/25\\/2024 00:00:01<\\/SettlementDate>\\r\\n            <FeeName><\\/FeeName>\\r\\n            <ThirdPartyCode><\\/ThirdPartyCode>\\r\\n            <OriginalPaymentLogId><\\/OriginalPaymentLogId>\\r\\n            <OriginalPaymentReference><\\/OriginalPaymentReference>\\r\\n            <Teller>Chinnan Rindi<\\/Teller>\\r\\n        <\\/Payment>\\r\\n    <\\/Payments>\\r\\n<\\/PaymentNotificationRequest>\"', '{\"PaymentNotificationResponse\":{\"Payments\":{\"Payment\":{\"PaymentLogId\":\"291449489\",\"Status\":0,\"StatusMessage\":\"Duplicate\"}}}}', '2024-11-01 07:45:56'),
(62, 'paydirect', '\"<PaymentNotificationRequest xmlns:ns2=\\\"http:\\/\\/techquest.interswitchng.com\\/\\\" xmlns:ns3=\\\"http:\\/\\/www.w3.org\\/2003\\/05\\/soap-envelope\\\">\\r\\n    <ServiceUrl>https:\\/\\/plateauigr.com\\/php\\/paydirect\\/<\\/ServiceUrl>\\r\\n    <ServiceUsername><\\/ServiceUsername>\\r\\n    <ServicePassword><\\/ServicePassword>\\r\\n    <FtpUrl>https:\\/\\/plateauigr.com\\/php\\/paydirect\\/<\\/FtpUrl>\\r\\n    <FtpUsername><\\/FtpUsername>\\r\\n    <FtpPassword><\\/FtpPassword>\\r\\n    <Payments>\\r\\n        <Payment>\\r\\n            <PaymentLogId>291449489<\\/PaymentLogId>\\r\\n            <CustReference>INV-6718C06887D13<\\/CustReference>\\r\\n            <AlternateCustReference>PLS-3205781946<\\/AlternateCustReference>\\r\\n            <Amount>2407000.00<\\/Amount>\\r\\n            <PaymentMethod>Internal Transfer<\\/PaymentMethod>\\r\\n            <PaymentReference>SBP|BRH|PGT|24-10-2024|249262<\\/PaymentReference>\\r\\n            <TerminalId><\\/TerminalId>\\r\\n            <ChannelName>Bank Branc<\\/ChannelName>\\r\\n            <Location>322 - JOS<\\/Location>\\r\\n            <PaymentDate>10\\/24\\/2024 14:32:33<\\/PaymentDate>\\r\\n            <InstitutionId>PGT<\\/InstitutionId>\\r\\n            <InstitutionName>Plat Govt<\\/InstitutionName>\\r\\n            <BranchName>322 - JOS<\\/BranchName>\\r\\n            <BankName>Sterling Bank Plc<\\/BankName>\\r\\n            <CustomerName>JOS MAIN MARKET AUTHORITY \\ufffd<\\/CustomerName>\\r\\n            <OtherCustomerInfo>|<\\/OtherCustomerInfo>\\r\\n            <ReceiptNo>2412533854<\\/ReceiptNo>\\r\\n            <CollectionsAccount>0064030950<\\/CollectionsAccount>\\r\\n            <BankCode>SBP<\\/BankCode>\\r\\n            <CustomerAddress><\\/CustomerAddress>\\r\\n            <CustomerPhoneNumber><\\/CustomerPhoneNumber>\\r\\n            <DepositorName><\\/DepositorName>\\r\\n            <DepositSlipNumber>001<\\/DepositSlipNumber>\\r\\n            <PaymentCurrency>566<\\/PaymentCurrency>\\r\\n            <PaymentItems>\\r\\n                <PaymentItem>\\r\\n                    <ItemName>Rent on Govt Building<\\/ItemName>\\r\\n                    <ItemCode>403<\\/ItemCode>\\r\\n                    <ItemAmount>2407000.00<\\/ItemAmount>\\r\\n                    <LeadBankCode>FBN<\\/LeadBankCode>\\r\\n                    <LeadBankCbnCode>011<\\/LeadBankCbnCode>\\r\\n                    <LeadBankName>First Bank of Nigeria Plc<\\/LeadBankName>\\r\\n                    <CategoryCode><\\/CategoryCode>\\r\\n                    <CategoryName><\\/CategoryName>\\r\\n                <\\/PaymentItem>\\r\\n            <\\/PaymentItems>\\r\\n            <ProductGroupCode>HTTPGENERICv31<\\/ProductGroupCode>\\r\\n            <PaymentStatus>0<\\/PaymentStatus>\\r\\n            <IsReversal>False<\\/IsReversal>\\r\\n            <SettlementDate>10\\/25\\/2024 00:00:01<\\/SettlementDate>\\r\\n            <FeeName><\\/FeeName>\\r\\n            <ThirdPartyCode><\\/ThirdPartyCode>\\r\\n            <OriginalPaymentLogId><\\/OriginalPaymentLogId>\\r\\n            <OriginalPaymentReference><\\/OriginalPaymentReference>\\r\\n            <Teller>Chinnan Rindi<\\/Teller>\\r\\n        <\\/Payment>\\r\\n    <\\/Payments>\\r\\n<\\/PaymentNotificationRequest>\"', '{\"PaymentNotificationResponse\":{\"Payments\":{\"Payment\":{\"PaymentLogId\":\"291449489\",\"Status\":0,\"StatusMessage\":\"Duplicate\"}}}}', '2024-11-01 07:46:13'),
(63, 'paydirect', '\"<PaymentNotificationRequest xmlns:ns2=\\\"http:\\/\\/techquest.interswitchng.com\\/\\\" xmlns:ns3=\\\"http:\\/\\/www.w3.org\\/2003\\/05\\/soap-envelope\\\">\\r\\n    <ServiceUrl>https:\\/\\/plateauigr.com\\/php\\/paydirect\\/<\\/ServiceUrl>\\r\\n    <ServiceUsername><\\/ServiceUsername>\\r\\n    <ServicePassword><\\/ServicePassword>\\r\\n    <FtpUrl>https:\\/\\/plateauigr.com\\/php\\/paydirect\\/<\\/FtpUrl>\\r\\n    <FtpUsername><\\/FtpUsername>\\r\\n    <FtpPassword><\\/FtpPassword>\\r\\n    <Payments>\\r\\n        <Payment>\\r\\n            <PaymentLogId>291449489<\\/PaymentLogId>\\r\\n            <CustReference>INV-6718C06887D13<\\/CustReference>\\r\\n            <AlternateCustReference>PLS-3205781946<\\/AlternateCustReference>\\r\\n            <Amount>2407000.00<\\/Amount>\\r\\n            <PaymentMethod>Internal Transfer<\\/PaymentMethod>\\r\\n            <PaymentReference>SBP|BRH|PGT|24-10-2024|249262<\\/PaymentReference>\\r\\n            <TerminalId><\\/TerminalId>\\r\\n            <ChannelName>Bank Branc<\\/ChannelName>\\r\\n            <Location>322 - JOS<\\/Location>\\r\\n            <PaymentDate>10\\/24\\/2024 14:32:33<\\/PaymentDate>\\r\\n            <InstitutionId>PGT<\\/InstitutionId>\\r\\n            <InstitutionName>Plat Govt<\\/InstitutionName>\\r\\n            <BranchName>322 - JOS<\\/BranchName>\\r\\n            <BankName>Sterling Bank Plc<\\/BankName>\\r\\n            <CustomerName>JOS MAIN MARKET AUTHORITY \\ufffd<\\/CustomerName>\\r\\n            <OtherCustomerInfo>|<\\/OtherCustomerInfo>\\r\\n            <ReceiptNo>2412533854<\\/ReceiptNo>\\r\\n            <CollectionsAccount>0064030950<\\/CollectionsAccount>\\r\\n            <BankCode>SBP<\\/BankCode>\\r\\n            <CustomerAddress><\\/CustomerAddress>\\r\\n            <CustomerPhoneNumber><\\/CustomerPhoneNumber>\\r\\n            <DepositorName><\\/DepositorName>\\r\\n            <DepositSlipNumber>001<\\/DepositSlipNumber>\\r\\n            <PaymentCurrency>566<\\/PaymentCurrency>\\r\\n            <PaymentItems>\\r\\n                <PaymentItem>\\r\\n                    <ItemName>Rent on Govt Building<\\/ItemName>\\r\\n                    <ItemCode>403<\\/ItemCode>\\r\\n                    <ItemAmount>2407000.00<\\/ItemAmount>\\r\\n                    <LeadBankCode>FBN<\\/LeadBankCode>\\r\\n                    <LeadBankCbnCode>011<\\/LeadBankCbnCode>\\r\\n                    <LeadBankName>First Bank of Nigeria Plc<\\/LeadBankName>\\r\\n                    <CategoryCode><\\/CategoryCode>\\r\\n                    <CategoryName><\\/CategoryName>\\r\\n                <\\/PaymentItem>\\r\\n            <\\/PaymentItems>\\r\\n            <ProductGroupCode>HTTPGENERICv31<\\/ProductGroupCode>\\r\\n            <PaymentStatus>0<\\/PaymentStatus>\\r\\n            <IsReversal>False<\\/IsReversal>\\r\\n            <SettlementDate>10\\/25\\/2024 00:00:01<\\/SettlementDate>\\r\\n            <FeeName><\\/FeeName>\\r\\n            <ThirdPartyCode><\\/ThirdPartyCode>\\r\\n            <OriginalPaymentLogId><\\/OriginalPaymentLogId>\\r\\n            <OriginalPaymentReference><\\/OriginalPaymentReference>\\r\\n            <Teller>Chinnan Rindi<\\/Teller>\\r\\n        <\\/Payment>\\r\\n    <\\/Payments>\\r\\n<\\/PaymentNotificationRequest>\"', NULL, '2024-11-01 07:49:37'),
(64, 'paydirect', '\"<PaymentNotificationRequest xmlns:ns2=\\\"http:\\/\\/techquest.interswitchng.com\\/\\\" xmlns:ns3=\\\"http:\\/\\/www.w3.org\\/2003\\/05\\/soap-envelope\\\">\\r\\n    <ServiceUrl>https:\\/\\/plateauigr.com\\/php\\/paydirect\\/<\\/ServiceUrl>\\r\\n    <ServiceUsername><\\/ServiceUsername>\\r\\n    <ServicePassword><\\/ServicePassword>\\r\\n    <FtpUrl>https:\\/\\/plateauigr.com\\/php\\/paydirect\\/<\\/FtpUrl>\\r\\n    <FtpUsername><\\/FtpUsername>\\r\\n    <FtpPassword><\\/FtpPassword>\\r\\n    <Payments>\\r\\n        <Payment>\\r\\n            <PaymentLogId>291449489<\\/PaymentLogId>\\r\\n            <CustReference>INV-6718C06887D13<\\/CustReference>\\r\\n            <AlternateCustReference>PLS-3205781946<\\/AlternateCustReference>\\r\\n            <Amount>2407000.00<\\/Amount>\\r\\n            <PaymentMethod>Internal Transfer<\\/PaymentMethod>\\r\\n            <PaymentReference>SBP|BRH|PGT|24-10-2024|249262<\\/PaymentReference>\\r\\n            <TerminalId><\\/TerminalId>\\r\\n            <ChannelName>Bank Branc<\\/ChannelName>\\r\\n            <Location>322 - JOS<\\/Location>\\r\\n            <PaymentDate>10\\/24\\/2024 14:32:33<\\/PaymentDate>\\r\\n            <InstitutionId>PGT<\\/InstitutionId>\\r\\n            <InstitutionName>Plat Govt<\\/InstitutionName>\\r\\n            <BranchName>322 - JOS<\\/BranchName>\\r\\n            <BankName>Sterling Bank Plc<\\/BankName>\\r\\n            <CustomerName>JOS MAIN MARKET AUTHORITY \\ufffd<\\/CustomerName>\\r\\n            <OtherCustomerInfo>|<\\/OtherCustomerInfo>\\r\\n            <ReceiptNo>2412533854<\\/ReceiptNo>\\r\\n            <CollectionsAccount>0064030950<\\/CollectionsAccount>\\r\\n            <BankCode>SBP<\\/BankCode>\\r\\n            <CustomerAddress><\\/CustomerAddress>\\r\\n            <CustomerPhoneNumber><\\/CustomerPhoneNumber>\\r\\n            <DepositorName><\\/DepositorName>\\r\\n            <DepositSlipNumber>001<\\/DepositSlipNumber>\\r\\n            <PaymentCurrency>566<\\/PaymentCurrency>\\r\\n            <PaymentItems>\\r\\n                <PaymentItem>\\r\\n                    <ItemName>Rent on Govt Building<\\/ItemName>\\r\\n                    <ItemCode>403<\\/ItemCode>\\r\\n                    <ItemAmount>2407000.00<\\/ItemAmount>\\r\\n                    <LeadBankCode>FBN<\\/LeadBankCode>\\r\\n                    <LeadBankCbnCode>011<\\/LeadBankCbnCode>\\r\\n                    <LeadBankName>First Bank of Nigeria Plc<\\/LeadBankName>\\r\\n                    <CategoryCode><\\/CategoryCode>\\r\\n                    <CategoryName><\\/CategoryName>\\r\\n                <\\/PaymentItem>\\r\\n            <\\/PaymentItems>\\r\\n            <ProductGroupCode>HTTPGENERICv31<\\/ProductGroupCode>\\r\\n            <PaymentStatus>0<\\/PaymentStatus>\\r\\n            <IsReversal>False<\\/IsReversal>\\r\\n            <SettlementDate>10\\/25\\/2024 00:00:01<\\/SettlementDate>\\r\\n            <FeeName><\\/FeeName>\\r\\n            <ThirdPartyCode><\\/ThirdPartyCode>\\r\\n            <OriginalPaymentLogId><\\/OriginalPaymentLogId>\\r\\n            <OriginalPaymentReference><\\/OriginalPaymentReference>\\r\\n            <Teller>Chinnan Rindi<\\/Teller>\\r\\n        <\\/Payment>\\r\\n    <\\/Payments>\\r\\n<\\/PaymentNotificationRequest>\"', '{\"PaymentNotificationResponse\":{\"Payments\":{\"Payment\":{\"PaymentLogId\":\"291449489\",\"Status\":0,\"StatusMessage\":\"Duplicate\"}}}}', '2024-11-01 07:51:50'),
(65, 'paydirect', '\"<PaymentNotificationRequest xmlns:ns2=\\\"http:\\/\\/techquest.interswitchng.com\\/\\\" xmlns:ns3=\\\"http:\\/\\/www.w3.org\\/2003\\/05\\/soap-envelope\\\">\\r\\n    <ServiceUrl>https:\\/\\/plateauigr.com\\/php\\/paydirect\\/<\\/ServiceUrl>\\r\\n    <ServiceUsername><\\/ServiceUsername>\\r\\n    <ServicePassword><\\/ServicePassword>\\r\\n    <FtpUrl>https:\\/\\/plateauigr.com\\/php\\/paydirect\\/<\\/FtpUrl>\\r\\n    <FtpUsername><\\/FtpUsername>\\r\\n    <FtpPassword><\\/FtpPassword>\\r\\n    <Payments>\\r\\n        <Payment>\\r\\n            <PaymentLogId>291449489<\\/PaymentLogId>\\r\\n            <CustReference>INV-6718C06887D13<\\/CustReference>\\r\\n            <AlternateCustReference>PLS-3205781946<\\/AlternateCustReference>\\r\\n            <Amount>2407000.00<\\/Amount>\\r\\n            <PaymentMethod>Internal Transfer<\\/PaymentMethod>\\r\\n            <PaymentReference>SBP|BRH|PGT|24-10-2024|249262<\\/PaymentReference>\\r\\n            <TerminalId><\\/TerminalId>\\r\\n            <ChannelName>Bank Branc<\\/ChannelName>\\r\\n            <Location>322 - JOS<\\/Location>\\r\\n            <PaymentDate>10\\/24\\/2024 14:32:33<\\/PaymentDate>\\r\\n            <InstitutionId>PGT<\\/InstitutionId>\\r\\n            <InstitutionName>Plat Govt<\\/InstitutionName>\\r\\n            <BranchName>322 - JOS<\\/BranchName>\\r\\n            <BankName>Sterling Bank Plc<\\/BankName>\\r\\n            <CustomerName>JOS MAIN MARKET AUTHORITY \\ufffd<\\/CustomerName>\\r\\n            <OtherCustomerInfo>|<\\/OtherCustomerInfo>\\r\\n            <ReceiptNo>2412533854<\\/ReceiptNo>\\r\\n            <CollectionsAccount>0064030950<\\/CollectionsAccount>\\r\\n            <BankCode>SBP<\\/BankCode>\\r\\n            <CustomerAddress><\\/CustomerAddress>\\r\\n            <CustomerPhoneNumber><\\/CustomerPhoneNumber>\\r\\n            <DepositorName><\\/DepositorName>\\r\\n            <DepositSlipNumber>001<\\/DepositSlipNumber>\\r\\n            <PaymentCurrency>566<\\/PaymentCurrency>\\r\\n            <PaymentItems>\\r\\n                <PaymentItem>\\r\\n                    <ItemName>Rent on Govt Building<\\/ItemName>\\r\\n                    <ItemCode>403<\\/ItemCode>\\r\\n                    <ItemAmount>2407000.00<\\/ItemAmount>\\r\\n                    <LeadBankCode>FBN<\\/LeadBankCode>\\r\\n                    <LeadBankCbnCode>011<\\/LeadBankCbnCode>\\r\\n                    <LeadBankName>First Bank of Nigeria Plc<\\/LeadBankName>\\r\\n                    <CategoryCode><\\/CategoryCode>\\r\\n                    <CategoryName><\\/CategoryName>\\r\\n                <\\/PaymentItem>\\r\\n            <\\/PaymentItems>\\r\\n            <ProductGroupCode>HTTPGENERICv31<\\/ProductGroupCode>\\r\\n            <PaymentStatus>0<\\/PaymentStatus>\\r\\n            <IsReversal>False<\\/IsReversal>\\r\\n            <SettlementDate>10\\/25\\/2024 00:00:01<\\/SettlementDate>\\r\\n            <FeeName><\\/FeeName>\\r\\n            <ThirdPartyCode><\\/ThirdPartyCode>\\r\\n            <OriginalPaymentLogId><\\/OriginalPaymentLogId>\\r\\n            <OriginalPaymentReference><\\/OriginalPaymentReference>\\r\\n            <Teller>Chinnan Rindi<\\/Teller>\\r\\n        <\\/Payment>\\r\\n    <\\/Payments>\\r\\n<\\/PaymentNotificationRequest>\"', NULL, '2024-11-01 07:58:19'),
(66, 'paydirect', '\"<PaymentNotificationRequest xmlns:ns2=\\\"http:\\/\\/techquest.interswitchng.com\\/\\\" xmlns:ns3=\\\"http:\\/\\/www.w3.org\\/2003\\/05\\/soap-envelope\\\">\\r\\n    <ServiceUrl>https:\\/\\/plateauigr.com\\/php\\/paydirect\\/<\\/ServiceUrl>\\r\\n    <ServiceUsername><\\/ServiceUsername>\\r\\n    <ServicePassword><\\/ServicePassword>\\r\\n    <FtpUrl>https:\\/\\/plateauigr.com\\/php\\/paydirect\\/<\\/FtpUrl>\\r\\n    <FtpUsername><\\/FtpUsername>\\r\\n    <FtpPassword><\\/FtpPassword>\\r\\n    <Payments>\\r\\n        <Payment>\\r\\n            <PaymentLogId>291449489<\\/PaymentLogId>\\r\\n            <CustReference>INV-6718C06887D13<\\/CustReference>\\r\\n            <AlternateCustReference>PLS-3205781946<\\/AlternateCustReference>\\r\\n            <Amount>2407000.00<\\/Amount>\\r\\n            <PaymentMethod>Internal Transfer<\\/PaymentMethod>\\r\\n            <PaymentReference>SBP|BRH|PGT|24-10-2024|249262<\\/PaymentReference>\\r\\n            <TerminalId><\\/TerminalId>\\r\\n            <ChannelName>Bank Branc<\\/ChannelName>\\r\\n            <Location>322 - JOS<\\/Location>\\r\\n            <PaymentDate>10\\/24\\/2024 14:32:33<\\/PaymentDate>\\r\\n            <InstitutionId>PGT<\\/InstitutionId>\\r\\n            <InstitutionName>Plat Govt<\\/InstitutionName>\\r\\n            <BranchName>322 - JOS<\\/BranchName>\\r\\n            <BankName>Sterling Bank Plc<\\/BankName>\\r\\n            <CustomerName>JOS MAIN MARKET AUTHORITY \\ufffd<\\/CustomerName>\\r\\n            <OtherCustomerInfo>|<\\/OtherCustomerInfo>\\r\\n            <ReceiptNo>2412533854<\\/ReceiptNo>\\r\\n            <CollectionsAccount>0064030950<\\/CollectionsAccount>\\r\\n            <BankCode>SBP<\\/BankCode>\\r\\n            <CustomerAddress><\\/CustomerAddress>\\r\\n            <CustomerPhoneNumber><\\/CustomerPhoneNumber>\\r\\n            <DepositorName><\\/DepositorName>\\r\\n            <DepositSlipNumber>001<\\/DepositSlipNumber>\\r\\n            <PaymentCurrency>566<\\/PaymentCurrency>\\r\\n            <PaymentItems>\\r\\n                <PaymentItem>\\r\\n                    <ItemName>Rent on Govt Building<\\/ItemName>\\r\\n                    <ItemCode>403<\\/ItemCode>\\r\\n                    <ItemAmount>2407000.00<\\/ItemAmount>\\r\\n                    <LeadBankCode>FBN<\\/LeadBankCode>\\r\\n                    <LeadBankCbnCode>011<\\/LeadBankCbnCode>\\r\\n                    <LeadBankName>First Bank of Nigeria Plc<\\/LeadBankName>\\r\\n                    <CategoryCode><\\/CategoryCode>\\r\\n                    <CategoryName><\\/CategoryName>\\r\\n                <\\/PaymentItem>\\r\\n            <\\/PaymentItems>\\r\\n            <ProductGroupCode>HTTPGENERICv31<\\/ProductGroupCode>\\r\\n            <PaymentStatus>0<\\/PaymentStatus>\\r\\n            <IsReversal>False<\\/IsReversal>\\r\\n            <SettlementDate>10\\/25\\/2024 00:00:01<\\/SettlementDate>\\r\\n            <FeeName><\\/FeeName>\\r\\n            <ThirdPartyCode><\\/ThirdPartyCode>\\r\\n            <OriginalPaymentLogId><\\/OriginalPaymentLogId>\\r\\n            <OriginalPaymentReference><\\/OriginalPaymentReference>\\r\\n            <Teller>Chinnan Rindi<\\/Teller>\\r\\n        <\\/Payment>\\r\\n    <\\/Payments>\\r\\n<\\/PaymentNotificationRequest>\"', '{\"PaymentNotificationResponse\":{\"Payments\":{\"Payment\":{\"PaymentLogId\":\"291449489\",\"Status\":0,\"StatusMessage\":\"Duplicate\"}}}}', '2024-11-01 07:58:30'),
(67, 'paydirect', '\"<PaymentNotificationRequest xmlns:ns2=\\\"http:\\/\\/techquest.interswitchng.com\\/\\\" xmlns:ns3=\\\"http:\\/\\/www.w3.org\\/2003\\/05\\/soap-envelope\\\">\\r\\n    <ServiceUrl>https:\\/\\/plateauigr.com\\/php\\/paydirect\\/<\\/ServiceUrl>\\r\\n    <ServiceUsername><\\/ServiceUsername>\\r\\n    <ServicePassword><\\/ServicePassword>\\r\\n    <FtpUrl>https:\\/\\/plateauigr.com\\/php\\/paydirect\\/<\\/FtpUrl>\\r\\n    <FtpUsername><\\/FtpUsername>\\r\\n    <FtpPassword><\\/FtpPassword>\\r\\n    <Payments>\\r\\n        <Payment>\\r\\n            <PaymentLogId>291449489<\\/PaymentLogId>\\r\\n            <CustReference>INV-6718C06887D13<\\/CustReference>\\r\\n            <AlternateCustReference>PLS-3205781946<\\/AlternateCustReference>\\r\\n            <Amount>2407000.00<\\/Amount>\\r\\n            <PaymentMethod>Internal Transfer<\\/PaymentMethod>\\r\\n            <PaymentReference>SBP|BRH|PGT|24-10-2024|249262<\\/PaymentReference>\\r\\n            <TerminalId><\\/TerminalId>\\r\\n            <ChannelName>Bank Branc<\\/ChannelName>\\r\\n            <Location>322 - JOS<\\/Location>\\r\\n            <PaymentDate>10\\/24\\/2024 14:32:33<\\/PaymentDate>\\r\\n            <InstitutionId>PGT<\\/InstitutionId>\\r\\n            <InstitutionName>Plat Govt<\\/InstitutionName>\\r\\n            <BranchName>322 - JOS<\\/BranchName>\\r\\n            <BankName>Sterling Bank Plc<\\/BankName>\\r\\n            <CustomerName>JOS MAIN MARKET AUTHORITY \\ufffd<\\/CustomerName>\\r\\n            <OtherCustomerInfo>|<\\/OtherCustomerInfo>\\r\\n            <ReceiptNo>2412533854<\\/ReceiptNo>\\r\\n            <CollectionsAccount>0064030950<\\/CollectionsAccount>\\r\\n            <BankCode>SBP<\\/BankCode>\\r\\n            <CustomerAddress><\\/CustomerAddress>\\r\\n            <CustomerPhoneNumber><\\/CustomerPhoneNumber>\\r\\n            <DepositorName><\\/DepositorName>\\r\\n            <DepositSlipNumber>001<\\/DepositSlipNumber>\\r\\n            <PaymentCurrency>566<\\/PaymentCurrency>\\r\\n            <PaymentItems>\\r\\n                <PaymentItem>\\r\\n                    <ItemName>Rent on Govt Building<\\/ItemName>\\r\\n                    <ItemCode>403<\\/ItemCode>\\r\\n                    <ItemAmount>2407000.00<\\/ItemAmount>\\r\\n                    <LeadBankCode>FBN<\\/LeadBankCode>\\r\\n                    <LeadBankCbnCode>011<\\/LeadBankCbnCode>\\r\\n                    <LeadBankName>First Bank of Nigeria Plc<\\/LeadBankName>\\r\\n                    <CategoryCode><\\/CategoryCode>\\r\\n                    <CategoryName><\\/CategoryName>\\r\\n                <\\/PaymentItem>\\r\\n            <\\/PaymentItems>\\r\\n            <ProductGroupCode>HTTPGENERICv31<\\/ProductGroupCode>\\r\\n            <PaymentStatus>0<\\/PaymentStatus>\\r\\n            <IsReversal>False<\\/IsReversal>\\r\\n            <SettlementDate>10\\/25\\/2024 00:00:01<\\/SettlementDate>\\r\\n            <FeeName><\\/FeeName>\\r\\n            <ThirdPartyCode><\\/ThirdPartyCode>\\r\\n            <OriginalPaymentLogId><\\/OriginalPaymentLogId>\\r\\n            <OriginalPaymentReference><\\/OriginalPaymentReference>\\r\\n            <Teller>Chinnan Rindi<\\/Teller>\\r\\n        <\\/Payment>\\r\\n    <\\/Payments>\\r\\n<\\/PaymentNotificationRequest>\"', '{\"PaymentNotificationResponse\":{\"Payments\":{\"Payment\":{\"PaymentLogId\":null,\"Status\":1,\"StatusMessage\":\"Rejected By System\"}}}}', '2024-11-01 08:01:16'),
(68, 'paydirect', '\"<PaymentNotificationRequest xmlns:ns2=\\\"http:\\/\\/techquest.interswitchng.com\\/\\\" xmlns:ns3=\\\"http:\\/\\/www.w3.org\\/2003\\/05\\/soap-envelope\\\">\\r\\n    <ServiceUrl>https:\\/\\/plateauigr.com\\/php\\/paydirect\\/<\\/ServiceUrl>\\r\\n    <ServiceUsername><\\/ServiceUsername>\\r\\n    <ServicePassword><\\/ServicePassword>\\r\\n    <FtpUrl>https:\\/\\/plateauigr.com\\/php\\/paydirect\\/<\\/FtpUrl>\\r\\n    <FtpUsername><\\/FtpUsername>\\r\\n    <FtpPassword><\\/FtpPassword>\\r\\n    <Payments>\\r\\n        <Payment>\\r\\n            <PaymentLogId>291449489<\\/PaymentLogId>\\r\\n            <CustReference>INV-6718C06887D13<\\/CustReference>\\r\\n            <AlternateCustReference>PLS-3205781946<\\/AlternateCustReference>\\r\\n            <Amount>2407000.00<\\/Amount>\\r\\n            <PaymentMethod>Internal Transfer<\\/PaymentMethod>\\r\\n            <PaymentReference>SBP|BRH|PGT|24-10-2024|249262<\\/PaymentReference>\\r\\n            <TerminalId><\\/TerminalId>\\r\\n            <ChannelName>Bank Branc<\\/ChannelName>\\r\\n            <Location>322 - JOS<\\/Location>\\r\\n            <PaymentDate>10\\/24\\/2024 14:32:33<\\/PaymentDate>\\r\\n            <InstitutionId>PGT<\\/InstitutionId>\\r\\n            <InstitutionName>Plat Govt<\\/InstitutionName>\\r\\n            <BranchName>322 - JOS<\\/BranchName>\\r\\n            <BankName>Sterling Bank Plc<\\/BankName>\\r\\n            <CustomerName>JOS MAIN MARKET AUTHORITY \\ufffd<\\/CustomerName>\\r\\n            <OtherCustomerInfo>|<\\/OtherCustomerInfo>\\r\\n            <ReceiptNo>2412533854<\\/ReceiptNo>\\r\\n            <CollectionsAccount>0064030950<\\/CollectionsAccount>\\r\\n            <BankCode>SBP<\\/BankCode>\\r\\n            <CustomerAddress><\\/CustomerAddress>\\r\\n            <CustomerPhoneNumber><\\/CustomerPhoneNumber>\\r\\n            <DepositorName><\\/DepositorName>\\r\\n            <DepositSlipNumber>001<\\/DepositSlipNumber>\\r\\n            <PaymentCurrency>566<\\/PaymentCurrency>\\r\\n            <PaymentItems>\\r\\n                <PaymentItem>\\r\\n                    <ItemName>Rent on Govt Building<\\/ItemName>\\r\\n                    <ItemCode>403<\\/ItemCode>\\r\\n                    <ItemAmount>2407000.00<\\/ItemAmount>\\r\\n                    <LeadBankCode>FBN<\\/LeadBankCode>\\r\\n                    <LeadBankCbnCode>011<\\/LeadBankCbnCode>\\r\\n                    <LeadBankName>First Bank of Nigeria Plc<\\/LeadBankName>\\r\\n                    <CategoryCode><\\/CategoryCode>\\r\\n                    <CategoryName><\\/CategoryName>\\r\\n                <\\/PaymentItem>\\r\\n            <\\/PaymentItems>\\r\\n            <ProductGroupCode>HTTPGENERICv31<\\/ProductGroupCode>\\r\\n            <PaymentStatus>0<\\/PaymentStatus>\\r\\n            <IsReversal>False<\\/IsReversal>\\r\\n            <SettlementDate>10\\/25\\/2024 00:00:01<\\/SettlementDate>\\r\\n            <FeeName><\\/FeeName>\\r\\n            <ThirdPartyCode><\\/ThirdPartyCode>\\r\\n            <OriginalPaymentLogId><\\/OriginalPaymentLogId>\\r\\n            <OriginalPaymentReference><\\/OriginalPaymentReference>\\r\\n            <Teller>Chinnan Rindi<\\/Teller>\\r\\n        <\\/Payment>\\r\\n    <\\/Payments>\\r\\n<\\/PaymentNotificationRequest>\"', '{\"Payments\":{\"Payment\":{\"PaymentLogId\":null,\"Status\":1,\"StatusMessage\":\"Rejected By System\"}}}', '2024-11-01 08:02:33');
INSERT INTO `gateway_payload` (`id`, `gateway`, `payload`, `result_out`, `date_created`) VALUES
(69, 'paydirect', '\"<PaymentNotificationRequest xmlns:ns2=\\\"http:\\/\\/techquest.interswitchng.com\\/\\\" xmlns:ns3=\\\"http:\\/\\/www.w3.org\\/2003\\/05\\/soap-envelope\\\">\\r\\n    <ServiceUrl>https:\\/\\/plateauigr.com\\/php\\/paydirect\\/<\\/ServiceUrl>\\r\\n    <ServiceUsername><\\/ServiceUsername>\\r\\n    <ServicePassword><\\/ServicePassword>\\r\\n    <FtpUrl>https:\\/\\/plateauigr.com\\/php\\/paydirect\\/<\\/FtpUrl>\\r\\n    <FtpUsername><\\/FtpUsername>\\r\\n    <FtpPassword><\\/FtpPassword>\\r\\n    <Payments>\\r\\n        <Payment>\\r\\n            <PaymentLogId>291449489<\\/PaymentLogId>\\r\\n            <CustReference>INV-6718C06887D13<\\/CustReference>\\r\\n            <AlternateCustReference>PLS-3205781946<\\/AlternateCustReference>\\r\\n            <Amount>2407000.00<\\/Amount>\\r\\n            <PaymentMethod>Internal Transfer<\\/PaymentMethod>\\r\\n            <PaymentReference>SBP|BRH|PGT|24-10-2024|249262<\\/PaymentReference>\\r\\n            <TerminalId><\\/TerminalId>\\r\\n            <ChannelName>Bank Branc<\\/ChannelName>\\r\\n            <Location>322 - JOS<\\/Location>\\r\\n            <PaymentDate>10\\/24\\/2024 14:32:33<\\/PaymentDate>\\r\\n            <InstitutionId>PGT<\\/InstitutionId>\\r\\n            <InstitutionName>Plat Govt<\\/InstitutionName>\\r\\n            <BranchName>322 - JOS<\\/BranchName>\\r\\n            <BankName>Sterling Bank Plc<\\/BankName>\\r\\n            <CustomerName>JOS MAIN MARKET AUTHORITY \\ufffd<\\/CustomerName>\\r\\n            <OtherCustomerInfo>|<\\/OtherCustomerInfo>\\r\\n            <ReceiptNo>2412533854<\\/ReceiptNo>\\r\\n            <CollectionsAccount>0064030950<\\/CollectionsAccount>\\r\\n            <BankCode>SBP<\\/BankCode>\\r\\n            <CustomerAddress><\\/CustomerAddress>\\r\\n            <CustomerPhoneNumber><\\/CustomerPhoneNumber>\\r\\n            <DepositorName><\\/DepositorName>\\r\\n            <DepositSlipNumber>001<\\/DepositSlipNumber>\\r\\n            <PaymentCurrency>566<\\/PaymentCurrency>\\r\\n            <PaymentItems>\\r\\n                <PaymentItem>\\r\\n                    <ItemName>Rent on Govt Building<\\/ItemName>\\r\\n                    <ItemCode>403<\\/ItemCode>\\r\\n                    <ItemAmount>2407000.00<\\/ItemAmount>\\r\\n                    <LeadBankCode>FBN<\\/LeadBankCode>\\r\\n                    <LeadBankCbnCode>011<\\/LeadBankCbnCode>\\r\\n                    <LeadBankName>First Bank of Nigeria Plc<\\/LeadBankName>\\r\\n                    <CategoryCode><\\/CategoryCode>\\r\\n                    <CategoryName><\\/CategoryName>\\r\\n                <\\/PaymentItem>\\r\\n            <\\/PaymentItems>\\r\\n            <ProductGroupCode>HTTPGENERICv31<\\/ProductGroupCode>\\r\\n            <PaymentStatus>0<\\/PaymentStatus>\\r\\n            <IsReversal>False<\\/IsReversal>\\r\\n            <SettlementDate>10\\/25\\/2024 00:00:01<\\/SettlementDate>\\r\\n            <FeeName><\\/FeeName>\\r\\n            <ThirdPartyCode><\\/ThirdPartyCode>\\r\\n            <OriginalPaymentLogId><\\/OriginalPaymentLogId>\\r\\n            <OriginalPaymentReference><\\/OriginalPaymentReference>\\r\\n            <Teller>Chinnan Rindi<\\/Teller>\\r\\n        <\\/Payment>\\r\\n    <\\/Payments>\\r\\n<\\/PaymentNotificationRequest>\"', '{\"Payments\":{\"Payment\":{\"PaymentLogId\":\"291449489\",\"Status\":0,\"StatusMessage\":\"Duplicate\"}}}', '2024-11-01 08:03:12'),
(70, 'paydirect', '\"<PaymentNotificationRequest xmlns:ns2=\\\"http:\\/\\/techquest.interswitchng.com\\/\\\" xmlns:ns3=\\\"http:\\/\\/www.w3.org\\/2003\\/05\\/soap-envelope\\\">\\r\\n    <ServiceUrl>https:\\/\\/plateauigr.com\\/php\\/paydirect\\/<\\/ServiceUrl>\\r\\n    <ServiceUsername><\\/ServiceUsername>\\r\\n    <ServicePassword><\\/ServicePassword>\\r\\n    <FtpUrl>https:\\/\\/plateauigr.com\\/php\\/paydirect\\/<\\/FtpUrl>\\r\\n    <FtpUsername><\\/FtpUsername>\\r\\n    <FtpPassword><\\/FtpPassword>\\r\\n    <Payments>\\r\\n        <Payment>\\r\\n            <PaymentLogId>291449489<\\/PaymentLogId>\\r\\n            <CustReference>INV-6718C06887D13<\\/CustReference>\\r\\n            <AlternateCustReference>PLS-3205781946<\\/AlternateCustReference>\\r\\n            <Amount>2407000.00<\\/Amount>\\r\\n            <PaymentMethod>Internal Transfer<\\/PaymentMethod>\\r\\n            <PaymentReference>SBP|BRH|PGT|24-10-2024|249262<\\/PaymentReference>\\r\\n            <TerminalId><\\/TerminalId>\\r\\n            <ChannelName>Bank Branc<\\/ChannelName>\\r\\n            <Location>322 - JOS<\\/Location>\\r\\n            <PaymentDate>10\\/24\\/2024 14:32:33<\\/PaymentDate>\\r\\n            <InstitutionId>PGT<\\/InstitutionId>\\r\\n            <InstitutionName>Plat Govt<\\/InstitutionName>\\r\\n            <BranchName>322 - JOS<\\/BranchName>\\r\\n            <BankName>Sterling Bank Plc<\\/BankName>\\r\\n            <CustomerName>JOS MAIN MARKET AUTHORITY \\ufffd<\\/CustomerName>\\r\\n            <OtherCustomerInfo>|<\\/OtherCustomerInfo>\\r\\n            <ReceiptNo>2412533854<\\/ReceiptNo>\\r\\n            <CollectionsAccount>0064030950<\\/CollectionsAccount>\\r\\n            <BankCode>SBP<\\/BankCode>\\r\\n            <CustomerAddress><\\/CustomerAddress>\\r\\n            <CustomerPhoneNumber><\\/CustomerPhoneNumber>\\r\\n            <DepositorName><\\/DepositorName>\\r\\n            <DepositSlipNumber>001<\\/DepositSlipNumber>\\r\\n            <PaymentCurrency>566<\\/PaymentCurrency>\\r\\n            <PaymentItems>\\r\\n                <PaymentItem>\\r\\n                    <ItemName>Rent on Govt Building<\\/ItemName>\\r\\n                    <ItemCode>403<\\/ItemCode>\\r\\n                    <ItemAmount>2407000.00<\\/ItemAmount>\\r\\n                    <LeadBankCode>FBN<\\/LeadBankCode>\\r\\n                    <LeadBankCbnCode>011<\\/LeadBankCbnCode>\\r\\n                    <LeadBankName>First Bank of Nigeria Plc<\\/LeadBankName>\\r\\n                    <CategoryCode><\\/CategoryCode>\\r\\n                    <CategoryName><\\/CategoryName>\\r\\n                <\\/PaymentItem>\\r\\n            <\\/PaymentItems>\\r\\n            <ProductGroupCode>HTTPGENERICv31<\\/ProductGroupCode>\\r\\n            <PaymentStatus>0<\\/PaymentStatus>\\r\\n            <IsReversal>False<\\/IsReversal>\\r\\n            <SettlementDate>10\\/25\\/2024 00:00:01<\\/SettlementDate>\\r\\n            <FeeName><\\/FeeName>\\r\\n            <ThirdPartyCode><\\/ThirdPartyCode>\\r\\n            <OriginalPaymentLogId><\\/OriginalPaymentLogId>\\r\\n            <OriginalPaymentReference><\\/OriginalPaymentReference>\\r\\n            <Teller>Chinnan Rindi<\\/Teller>\\r\\n        <\\/Payment>\\r\\n    <\\/Payments>\\r\\n<\\/PaymentNotificationRequest>\"', '{\"Payments\":{\"Payment\":{\"PaymentLogId\":\"291449489\",\"Status\":0,\"StatusMessage\":\"Success\"}}}', '2024-11-01 08:14:52'),
(71, 'paydirect', '\"<PaymentNotificationRequest xmlns:ns2=\\\"http:\\/\\/techquest.interswitchng.com\\/\\\" xmlns:ns3=\\\"http:\\/\\/www.w3.org\\/2003\\/05\\/soap-envelope\\\">\\r\\n    <ServiceUrl>https:\\/\\/plateauigr.com\\/php\\/paydirect\\/<\\/ServiceUrl>\\r\\n    <ServiceUsername><\\/ServiceUsername>\\r\\n    <ServicePassword><\\/ServicePassword>\\r\\n    <FtpUrl>https:\\/\\/plateauigr.com\\/php\\/paydirect\\/<\\/FtpUrl>\\r\\n    <FtpUsername><\\/FtpUsername>\\r\\n    <FtpPassword><\\/FtpPassword>\\r\\n    <Payments>\\r\\n        <Payment>\\r\\n            <PaymentLogId>291449489<\\/PaymentLogId>\\r\\n            <CustReference>INV-6718C06887D13<\\/CustReference>\\r\\n            <AlternateCustReference>PLS-3205781946<\\/AlternateCustReference>\\r\\n            <Amount>2407000.00<\\/Amount>\\r\\n            <PaymentMethod>Internal Transfer<\\/PaymentMethod>\\r\\n            <PaymentReference>SBP|BRH|PGT|24-10-2024|249262<\\/PaymentReference>\\r\\n            <TerminalId><\\/TerminalId>\\r\\n            <ChannelName>Bank Branc<\\/ChannelName>\\r\\n            <Location>322 - JOS<\\/Location>\\r\\n            <PaymentDate>10\\/24\\/2024 14:32:33<\\/PaymentDate>\\r\\n            <InstitutionId>PGT<\\/InstitutionId>\\r\\n            <InstitutionName>Plat Govt<\\/InstitutionName>\\r\\n            <BranchName>322 - JOS<\\/BranchName>\\r\\n            <BankName>Sterling Bank Plc<\\/BankName>\\r\\n            <CustomerName>JOS MAIN MARKET AUTHORITY \\ufffd<\\/CustomerName>\\r\\n            <OtherCustomerInfo>|<\\/OtherCustomerInfo>\\r\\n            <ReceiptNo>2412533854<\\/ReceiptNo>\\r\\n            <CollectionsAccount>0064030950<\\/CollectionsAccount>\\r\\n            <BankCode>SBP<\\/BankCode>\\r\\n            <CustomerAddress><\\/CustomerAddress>\\r\\n            <CustomerPhoneNumber><\\/CustomerPhoneNumber>\\r\\n            <DepositorName><\\/DepositorName>\\r\\n            <DepositSlipNumber>001<\\/DepositSlipNumber>\\r\\n            <PaymentCurrency>566<\\/PaymentCurrency>\\r\\n            <PaymentItems>\\r\\n                <PaymentItem>\\r\\n                    <ItemName>Rent on Govt Building<\\/ItemName>\\r\\n                    <ItemCode>403<\\/ItemCode>\\r\\n                    <ItemAmount>2407000.00<\\/ItemAmount>\\r\\n                    <LeadBankCode>FBN<\\/LeadBankCode>\\r\\n                    <LeadBankCbnCode>011<\\/LeadBankCbnCode>\\r\\n                    <LeadBankName>First Bank of Nigeria Plc<\\/LeadBankName>\\r\\n                    <CategoryCode><\\/CategoryCode>\\r\\n                    <CategoryName><\\/CategoryName>\\r\\n                <\\/PaymentItem>\\r\\n            <\\/PaymentItems>\\r\\n            <ProductGroupCode>HTTPGENERICv31<\\/ProductGroupCode>\\r\\n            <PaymentStatus>0<\\/PaymentStatus>\\r\\n            <IsReversal>False<\\/IsReversal>\\r\\n            <SettlementDate>10\\/25\\/2024 00:00:01<\\/SettlementDate>\\r\\n            <FeeName><\\/FeeName>\\r\\n            <ThirdPartyCode><\\/ThirdPartyCode>\\r\\n            <OriginalPaymentLogId><\\/OriginalPaymentLogId>\\r\\n            <OriginalPaymentReference><\\/OriginalPaymentReference>\\r\\n            <Teller>Chinnan Rindi<\\/Teller>\\r\\n        <\\/Payment>\\r\\n    <\\/Payments>\\r\\n<\\/PaymentNotificationRequest>\"', '{\"Payments\":{\"Payment\":{\"PaymentLogId\":\"291449489\",\"Status\":0,\"StatusMessage\":\"Duplicate\"}}}', '2024-11-01 08:15:05'),
(72, 'credo', '{\"event\":\"TRANSACTION.SUCCESSFUL\",\"data\":{\"businessCode\":\"700607010880003\",\"serviceCode\":null,\"transRef\":\"Y7B000yBkA117lle363w\",\"businessRef\":\"1136sEohDz1727087068\",\"debitedAmount\":50,\"transAmount\":50,\"transFeeAmount\":3.25,\"settlementAmount\":46.75,\"customerId\":\"joshypeterz@gmail.com\",\"transactionDate\":1727087161363,\"currencyCode\":\"NGN\",\"status\":0,\"paymentMethodType\":\"Gtbank Plc\",\"paymentMethod\":\"Bank Transfer\",\"businessId\":0,\"narration\":null,\"customer\":{\"customerEmail\":\"joshypeterz@gmail.com\",\"firstName\":\"Joshua Omueta\",\"lastName\":\"INV-6718C06887D13\",\"phoneNo\":\"08138909022\"},\"metadata\":[]}}', '{\"status\":\"error\",\"message\":\"This invoice is already paid\"}', '2024-11-01 08:15:12');

-- --------------------------------------------------------

--
-- Table structure for table `invoices`
--

CREATE TABLE `invoices` (
  `id` int(11) NOT NULL,
  `tax_number` varchar(255) NOT NULL,
  `invoice_type` enum('direct','demand notice') NOT NULL,
  `tax_office` varchar(255) NOT NULL,
  `lga` varchar(255) NOT NULL,
  `revenue_head` varchar(255) NOT NULL,
  `invoice_number` varchar(255) NOT NULL,
  `due_date` date NOT NULL,
  `payment_status` enum('paid','unpaid','pending') NOT NULL,
  `amount_paid` decimal(10,2) DEFAULT 0.00,
  `description` text DEFAULT NULL,
  `date_created` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `invoices`
--

INSERT INTO `invoices` (`id`, `tax_number`, `invoice_type`, `tax_office`, `lga`, `revenue_head`, `invoice_number`, `due_date`, `payment_status`, `amount_paid`, `description`, `date_created`) VALUES
(3, '8873322586', 'direct', 'Lagos Tax Office', 'Ikeja', '[{\"revenue_head_id\":5,\"amount\":5000},{\"revenue_head_id\":6,\"amount\":3000},{\"revenue_head_id\":6,\"amount\":3000}]', 'INV-6718C06887D13', '2024-11-23', 'paid', 2407000.00, 'This is a sample invoice', '2024-10-23 09:22:48'),
(4, '5859751591', 'direct', 'Lagos Tax Office', 'Ikeja', '[{\"revenue_head_id\":4,\"amount\":5000},{\"revenue_head_id\":6,\"amount\":3000}]', 'INV-6718C06887D14', '2024-11-23', 'unpaid', 2407000.00, 'This is a sample invoice', '2024-10-23 09:22:48');

-- --------------------------------------------------------

--
-- Table structure for table `mda`
--

CREATE TABLE `mda` (
  `id` int(11) NOT NULL,
  `fullname` varchar(255) NOT NULL,
  `mda_code` text NOT NULL,
  `email` varchar(100) NOT NULL,
  `phone` varchar(20) DEFAULT NULL,
  `industry` varchar(100) DEFAULT NULL,
  `allow_payment` enum('yes','no') NOT NULL DEFAULT 'no',
  `status` enum('active','inactive') DEFAULT 'inactive',
  `time_in` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `mda`
--

INSERT INTO `mda` (`id`, `fullname`, `mda_code`, `email`, `phone`, `industry`, `allow_payment`, `status`, `time_in`) VALUES
(4, 'Ministry of Finance', 'MOF123', 'mof@example.com', '08012345678', 'Finance', 'yes', 'active', '2024-10-23 01:32:53'),
(5, 'Ministry of Interior', 'MOF1234', 'mof4@example.com', '08012345678', 'FCT', 'yes', 'active', '2024-10-23 01:32:53');

-- --------------------------------------------------------

--
-- Table structure for table `mda_admin_permissions`
--

CREATE TABLE `mda_admin_permissions` (
  `mda_admin_id` int(11) DEFAULT NULL,
  `permission_id` int(11) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `mda_admin_permissions`
--

INSERT INTO `mda_admin_permissions` (`mda_admin_id`, `permission_id`) VALUES
(1, 1),
(1, 2),
(1, 3),
(2, 1),
(2, 2),
(2, 3);

-- --------------------------------------------------------

--
-- Table structure for table `mda_contact_info`
--

CREATE TABLE `mda_contact_info` (
  `id` int(11) NOT NULL,
  `mda_id` int(11) NOT NULL,
  `state` varchar(100) DEFAULT NULL,
  `geolocation` text DEFAULT NULL,
  `lga` varchar(100) DEFAULT NULL,
  `address` text DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `mda_contact_info`
--

INSERT INTO `mda_contact_info` (`id`, `mda_id`, `state`, `geolocation`, `lga`, `address`) VALUES
(2, 4, 'Lagos', '6.5244, 3.3792', 'Ikeja', '45612345 Health Street');

-- --------------------------------------------------------

--
-- Table structure for table `mda_permissions`
--

CREATE TABLE `mda_permissions` (
  `id` int(11) NOT NULL,
  `permission_name` varchar(100) NOT NULL,
  `category` varchar(100) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `mda_permissions`
--

INSERT INTO `mda_permissions` (`id`, `permission_name`, `category`) VALUES
(1, 'Full Access to Dashboard', 'Dashboard'),
(2, 'View Dashboard Only', 'Dashboard'),
(3, 'Full Access to Revenue Head', 'Revenue Head'),
(4, 'View Revenue Head Only', 'Revenue Head'),
(5, 'Full Access to Payment', 'Payment'),
(6, 'View Payment Only', 'Payment'),
(7, 'Full Access to User Management', 'Users'),
(8, 'View User List', 'Users'),
(9, 'Edit User Details', 'Users'),
(10, 'Full Access to Reports', 'Reports'),
(11, 'Generate Reports', 'Reports'),
(12, 'View Reports Only', 'Reports');

-- --------------------------------------------------------

--
-- Table structure for table `mda_users`
--

CREATE TABLE `mda_users` (
  `id` int(11) NOT NULL,
  `mda_id` int(11) DEFAULT NULL,
  `name` varchar(200) DEFAULT NULL,
  `email` varchar(200) NOT NULL,
  `phone` varchar(15) DEFAULT NULL,
  `password` longtext DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `img` longtext DEFAULT NULL,
  `office_name` varchar(100) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=latin1 COLLATE=latin1_swedish_ci;

--
-- Dumping data for table `mda_users`
--

INSERT INTO `mda_users` (`id`, `mda_id`, `name`, `email`, `phone`, `password`, `created_at`, `img`, `office_name`) VALUES
(2, 101, 'MDA User', 'mdauser@example.com', '1234567890', '$2y$10$SXgRQrFFHJDZpt3W32PSg..NoIDa5SAaZDvLkuVUQSFyz1k1Buj1S', '2024-10-16 18:01:53', NULL, 'Finance Office');

-- --------------------------------------------------------

--
-- Table structure for table `payment_collection`
--

CREATE TABLE `payment_collection` (
  `id` int(11) NOT NULL,
  `user_id` text NOT NULL,
  `invoice_number` varchar(255) NOT NULL,
  `payment_channel` varchar(255) NOT NULL,
  `payment_method` varchar(255) NOT NULL,
  `payment_bank` varchar(255) NOT NULL,
  `payment_gateway` varchar(255) NOT NULL,
  `payment_reference_number` varchar(255) NOT NULL,
  `receipt_number` varchar(255) NOT NULL,
  `amount_paid` decimal(10,2) NOT NULL,
  `date_payment_created` date NOT NULL,
  `timeIn` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `payment_collection`
--

INSERT INTO `payment_collection` (`id`, `user_id`, `invoice_number`, `payment_channel`, `payment_method`, `payment_bank`, `payment_gateway`, `payment_reference_number`, `receipt_number`, `amount_paid`, `date_payment_created`, `timeIn`) VALUES
(17, '5859751591', 'INV-6718C06887D13', 'InterSwitch', 'Internal Transfer', 'Sterling Bank Plc', '', '291449489', '2412533854', 2407000.00, '2024-10-24', '2024-11-01 08:14:53');

-- --------------------------------------------------------

--
-- Table structure for table `payment_locks`
--

CREATE TABLE `payment_locks` (
  `invoice_number` varchar(50) NOT NULL,
  `locked_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `permissions`
--

CREATE TABLE `permissions` (
  `id` int(11) NOT NULL,
  `permission_name` varchar(100) NOT NULL,
  `category` varchar(50) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `permissions`
--

INSERT INTO `permissions` (`id`, `permission_name`, `category`) VALUES
(1, 'Full Access to Dashboard', 'Dashboard'),
(2, 'View Revenue Performance', 'Analytics'),
(3, 'View Invoice Manager', 'Analytics'),
(4, 'View Taxpayer Relations', 'Analytics'),
(5, 'View TIN Requests Manager', 'Analytics'),
(6, 'View Impressions', 'Analytics'),
(7, 'View MDA List', 'MDAs'),
(8, 'Create MDA', 'MDAs'),
(9, 'Activate/Deactivate MDA', 'MDAs'),
(10, 'Create Revenue Head', 'MDAs'),
(11, 'Update Revenue Head', 'MDAs'),
(12, 'Approve Revenue Head', 'MDAs'),
(13, 'Activate/Deactivate Revenue Head', 'MDAs'),
(14, 'View Invoice List', 'Reports'),
(15, 'Generate Invoice Report', 'Reports'),
(16, 'View Collection List', 'Reports'),
(17, 'Generate Collection Report', 'Reports'),
(18, 'View Settlement List', 'Reports'),
(19, 'Generate Settlement Report', 'Reports'),
(20, 'View Tax Payer List', 'Tax Payers'),
(21, 'Edit Tax Payer Details', 'Tax Payers'),
(22, 'Activate/Deactivate Taxpayer', 'Tax Payers'),
(23, 'Allocate Applicable Taxes', 'Tax Payers'),
(24, 'Download Tax Payer Report', 'Tax Payers'),
(25, 'Register New User', 'Enumeration'),
(26, 'View General Enumeration List', 'Enumeration'),
(27, 'Update Taxpayer Details', 'Enumeration'),
(28, 'Download Enumeration Report', 'Enumeration'),
(29, 'Access Enumeration Dashboard', 'Enumeration'),
(30, 'View Audit Trail Module', 'Audit Trails'),
(31, 'Analyze Audit Logs (using filters)', 'Audit Trails'),
(32, 'Generate Audit Reports', 'Audit Trails'),
(33, 'Manage Audit Logs', 'Audit Trails'),
(34, 'View Admin User List', 'User Management'),
(35, 'Create New User and Assign Role', 'User Management'),
(36, 'Update User Role', 'User Management'),
(37, 'Activate/Deactivate Users', 'User Management'),
(38, 'Create New Post - Gallery', 'CMS'),
(39, 'Create New Post - News', 'CMS'),
(40, 'Manage Publication - Gallery', 'CMS'),
(41, 'Manage Publication - News', 'CMS'),
(42, 'View Support Module', 'Support'),
(43, 'Respond to Tickets', 'Support'),
(44, 'Escalate Issues', 'Support'),
(45, 'Export Support Data', 'Support'),
(46, 'Generate Support Report', 'Support'),
(47, 'First Reviewer', 'ETCC'),
(48, 'Second Reviewer', 'ETCC'),
(49, 'Third Reviewer', 'ETCC'),
(50, 'View Payee', 'Payee'),
(51, 'Edit Payee', 'Payee'),
(52, 'Full Access to Tax Manager', 'Tax Manager'),
(53, 'No Access to Tax Manager', 'Tax Manager');

-- --------------------------------------------------------

--
-- Table structure for table `revenue_heads`
--

CREATE TABLE `revenue_heads` (
  `id` int(11) NOT NULL,
  `mda_id` int(11) DEFAULT NULL,
  `item_code` varchar(50) NOT NULL,
  `item_name` varchar(255) NOT NULL,
  `category` enum('individual','corporate','state','federal') NOT NULL,
  `amount` decimal(15,2) NOT NULL,
  `status` enum('active','inactive') DEFAULT 'inactive',
  `frequency` enum('one-time','recurring','monthly','quarterly','yearly') NOT NULL,
  `time_in` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `revenue_heads`
--

INSERT INTO `revenue_heads` (`id`, `mda_id`, `item_code`, `item_name`, `category`, `amount`, `status`, `frequency`, `time_in`) VALUES
(4, 5, 'R001', 'Property Tax', 'individual', 5000.00, 'active', 'yearly', '2024-10-23 05:20:18'),
(5, 4, 'R002', 'Property Tax', 'corporate', 5000.00, 'active', 'yearly', '2024-10-23 05:20:18'),
(6, 4, 'R003', 'Vehicle Tax', 'state', 2000.00, 'active', 'monthly', '2024-10-23 05:20:18');

-- --------------------------------------------------------

--
-- Table structure for table `special_users_`
--

CREATE TABLE `special_users_` (
  `id` int(11) NOT NULL,
  `payer_id` varchar(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL,
  `name` varchar(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL,
  `industry` varchar(40) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL,
  `staff_quota` varchar(20) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL,
  `official_TIN` varchar(40) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci DEFAULT NULL,
  `email` varchar(80) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL,
  `phone` varchar(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL,
  `password` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL,
  `state` varchar(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL,
  `lga` varchar(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL,
  `address` varchar(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL,
  `category` enum('Private','Public') CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL,
  `timeIn` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=latin1 COLLATE=latin1_swedish_ci;

--
-- Dumping data for table `special_users_`
--

INSERT INTO `special_users_` (`id`, `payer_id`, `name`, `industry`, `staff_quota`, `official_TIN`, `email`, `phone`, `password`, `state`, `lga`, `address`, `category`, `timeIn`) VALUES
(2, '5859751591', 'Special User', 'Manufacturing', '50', 'TIN123456', 'specialuser@example.com', '1234567890', '$2y$10$M4WPXdFY8RQgGySBcBlV6.wNN2ISLNFVva8x0W91xE17jnJfuBvRi', 'Lagos', 'Ikeja', '123 Business Street', '', '2024-10-17 07:34:12');

-- --------------------------------------------------------

--
-- Table structure for table `special_user_employees`
--

CREATE TABLE `special_user_employees` (
  `id` int(11) NOT NULL,
  `fullname` varchar(50) DEFAULT NULL,
  `email` varchar(50) DEFAULT NULL,
  `phone` varchar(50) DEFAULT NULL,
  `payer_id` varchar(50) DEFAULT NULL,
  `associated_special_user_id` int(20) NOT NULL,
  `created_date` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=latin1 COLLATE=latin1_swedish_ci;

--
-- Dumping data for table `special_user_employees`
--

INSERT INTO `special_user_employees` (`id`, `fullname`, `email`, `phone`, `payer_id`, `associated_special_user_id`, `created_date`) VALUES
(2, 'John Doe', 'john@example.com', '08012345678', '1234567890', 2, '2024-10-20 14:09:09');

-- --------------------------------------------------------

--
-- Table structure for table `taxpayer`
--

CREATE TABLE `taxpayer` (
  `id` int(11) NOT NULL,
  `tax_number` varchar(50) NOT NULL,
  `category` varchar(50) NOT NULL,
  `first_name` varchar(100) NOT NULL,
  `surname` varchar(100) NOT NULL,
  `email` varchar(100) NOT NULL,
  `phone` varchar(20) NOT NULL,
  `state` varchar(50) NOT NULL,
  `lga` varchar(50) DEFAULT NULL,
  `address` text DEFAULT NULL,
  `employment_status` varchar(50) DEFAULT NULL,
  `number_of_staff` int(11) DEFAULT 0,
  `business_own` tinyint(1) DEFAULT 0,
  `img` varchar(255) DEFAULT NULL,
  `created_time` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_time` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `taxpayer`
--

INSERT INTO `taxpayer` (`id`, `tax_number`, `category`, `first_name`, `surname`, `email`, `phone`, `state`, `lga`, `address`, `employment_status`, `number_of_staff`, `business_own`, `img`, `created_time`, `updated_time`) VALUES
(1, '8873322586', 'Corporate', 'John', 'Doe', 'john@example.com', '08012345678', 'Lagos', 'Ikeja', '123 Business Street', 'Employed', 50, 1, 'path/to/image.jpg', '2024-10-20 17:04:32', '2024-10-20 17:04:32');

-- --------------------------------------------------------

--
-- Table structure for table `taxpayer_business`
--

CREATE TABLE `taxpayer_business` (
  `id` int(11) NOT NULL,
  `taxpayer_id` int(11) DEFAULT NULL,
  `business_type` varchar(100) DEFAULT NULL,
  `annual_revenue` decimal(15,2) DEFAULT 0.00,
  `value_business` decimal(15,2) DEFAULT 0.00
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `taxpayer_business`
--

INSERT INTO `taxpayer_business` (`id`, `taxpayer_id`, `business_type`, `annual_revenue`, `value_business`) VALUES
(1, 1, 'Manufacturing', 5000000.00, 10000000.00);

-- --------------------------------------------------------

--
-- Table structure for table `taxpayer_identification`
--

CREATE TABLE `taxpayer_identification` (
  `id` int(11) NOT NULL,
  `taxpayer_id` int(11) DEFAULT NULL,
  `tin` varchar(50) DEFAULT NULL,
  `nin` varchar(50) DEFAULT NULL,
  `bvn` varchar(50) DEFAULT NULL,
  `id_type` varchar(50) DEFAULT NULL,
  `id_number` varchar(50) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `taxpayer_identification`
--

INSERT INTO `taxpayer_identification` (`id`, `taxpayer_id`, `tin`, `nin`, `bvn`, `id_type`, `id_number`) VALUES
(1, 1, '1234567890', '123456789012', '12345678901', 'National ID', 'A123456789');

-- --------------------------------------------------------

--
-- Table structure for table `taxpayer_representative`
--

CREATE TABLE `taxpayer_representative` (
  `id` int(11) NOT NULL,
  `taxpayer_id` int(11) DEFAULT NULL,
  `rep_firstname` varchar(100) DEFAULT NULL,
  `rep_surname` varchar(100) DEFAULT NULL,
  `rep_email` varchar(100) DEFAULT NULL,
  `rep_phone` varchar(20) DEFAULT NULL,
  `rep_position` varchar(100) DEFAULT NULL,
  `rep_state` varchar(50) DEFAULT NULL,
  `rep_lga` varchar(50) DEFAULT NULL,
  `rep_address` text DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `taxpayer_representative`
--

INSERT INTO `taxpayer_representative` (`id`, `taxpayer_id`, `rep_firstname`, `rep_surname`, `rep_email`, `rep_phone`, `rep_position`, `rep_state`, `rep_lga`, `rep_address`) VALUES
(1, 1, 'Jane', 'Doe', 'jane@example.com', '08012345679', 'Manager', 'Lagos', 'Ikeja', '456 Representative Street');

-- --------------------------------------------------------

--
-- Table structure for table `taxpayer_security`
--

CREATE TABLE `taxpayer_security` (
  `id` int(11) NOT NULL,
  `taxpayer_id` int(11) DEFAULT NULL,
  `password` varchar(255) NOT NULL,
  `verification_status` enum('pending','verified') DEFAULT 'pending',
  `verification_code` varchar(100) DEFAULT NULL,
  `tin_status` enum('pending','issued') DEFAULT 'pending',
  `new_tin` tinyint(1) DEFAULT 0
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `taxpayer_security`
--

INSERT INTO `taxpayer_security` (`id`, `taxpayer_id`, `password`, `verification_status`, `verification_code`, `tin_status`, `new_tin`) VALUES
(1, 1, '$2y$10$RDwgzWfPss/WlI9CuUJO1.gqBB0R28xheuk3O4hednrXAATSUjd0K', '', '9A9EAC', '', NULL);

-- --------------------------------------------------------

--
-- Table structure for table `user_activity_log`
--

CREATE TABLE `user_activity_log` (
  `id` int(11) NOT NULL,
  `user_id` int(11) DEFAULT NULL,
  `activity_type` varchar(100) NOT NULL,
  `activity_description` text DEFAULT NULL,
  `ip_address` varchar(45) DEFAULT NULL,
  `user_agent` varchar(255) DEFAULT NULL,
  `location` varchar(255) DEFAULT NULL,
  `status` enum('success','failure') DEFAULT 'success',
  `error_message` text DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `session_id` varchar(255) DEFAULT NULL,
  `referrer` varchar(255) DEFAULT NULL,
  `timestamp` datetime DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Indexes for dumped tables
--

--
-- Indexes for table `administrative_users`
--
ALTER TABLE `administrative_users`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `admin_permissions`
--
ALTER TABLE `admin_permissions`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `api_audit_logs`
--
ALTER TABLE `api_audit_logs`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `employee_salary_and_benefits`
--
ALTER TABLE `employee_salary_and_benefits`
  ADD PRIMARY KEY (`id`),
  ADD KEY `employee_id` (`employee_id`);

--
-- Indexes for table `enumerator_corporate_info`
--
ALTER TABLE `enumerator_corporate_info`
  ADD PRIMARY KEY (`id`),
  ADD KEY `user_tax_number` (`user_tax_number`);

--
-- Indexes for table `enumerator_property_info`
--
ALTER TABLE `enumerator_property_info`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `property_id` (`property_id`),
  ADD KEY `user_tax_number` (`user_tax_number`);

--
-- Indexes for table `enumerator_tax_payers`
--
ALTER TABLE `enumerator_tax_payers`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `tax_number` (`tax_number`);

--
-- Indexes for table `enumerator_users`
--
ALTER TABLE `enumerator_users`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `email` (`email`);

--
-- Indexes for table `gateway_payload`
--
ALTER TABLE `gateway_payload`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `invoices`
--
ALTER TABLE `invoices`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `mda`
--
ALTER TABLE `mda`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `email` (`email`);

--
-- Indexes for table `mda_contact_info`
--
ALTER TABLE `mda_contact_info`
  ADD PRIMARY KEY (`id`),
  ADD KEY `mda_id` (`mda_id`);

--
-- Indexes for table `mda_permissions`
--
ALTER TABLE `mda_permissions`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `permission_name` (`permission_name`);

--
-- Indexes for table `mda_users`
--
ALTER TABLE `mda_users`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `payment_collection`
--
ALTER TABLE `payment_collection`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `invoice_number` (`invoice_number`),
  ADD UNIQUE KEY `payment_reference_number` (`payment_reference_number`);

--
-- Indexes for table `payment_locks`
--
ALTER TABLE `payment_locks`
  ADD PRIMARY KEY (`invoice_number`);

--
-- Indexes for table `permissions`
--
ALTER TABLE `permissions`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `permission_name` (`permission_name`);

--
-- Indexes for table `revenue_heads`
--
ALTER TABLE `revenue_heads`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `special_users_`
--
ALTER TABLE `special_users_`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `special_user_employees`
--
ALTER TABLE `special_user_employees`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `taxpayer`
--
ALTER TABLE `taxpayer`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `taxpayer_business`
--
ALTER TABLE `taxpayer_business`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `taxpayer_identification`
--
ALTER TABLE `taxpayer_identification`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `taxpayer_representative`
--
ALTER TABLE `taxpayer_representative`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `taxpayer_security`
--
ALTER TABLE `taxpayer_security`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `user_activity_log`
--
ALTER TABLE `user_activity_log`
  ADD PRIMARY KEY (`id`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `administrative_users`
--
ALTER TABLE `administrative_users`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- AUTO_INCREMENT for table `admin_permissions`
--
ALTER TABLE `admin_permissions`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=11;

--
-- AUTO_INCREMENT for table `api_audit_logs`
--
ALTER TABLE `api_audit_logs`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `employee_salary_and_benefits`
--
ALTER TABLE `employee_salary_and_benefits`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT for table `enumerator_corporate_info`
--
ALTER TABLE `enumerator_corporate_info`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT for table `enumerator_property_info`
--
ALTER TABLE `enumerator_property_info`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- AUTO_INCREMENT for table `enumerator_tax_payers`
--
ALTER TABLE `enumerator_tax_payers`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- AUTO_INCREMENT for table `enumerator_users`
--
ALTER TABLE `enumerator_users`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT for table `gateway_payload`
--
ALTER TABLE `gateway_payload`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=73;

--
-- AUTO_INCREMENT for table `invoices`
--
ALTER TABLE `invoices`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- AUTO_INCREMENT for table `mda`
--
ALTER TABLE `mda`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;

--
-- AUTO_INCREMENT for table `mda_contact_info`
--
ALTER TABLE `mda_contact_info`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT for table `mda_permissions`
--
ALTER TABLE `mda_permissions`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=13;

--
-- AUTO_INCREMENT for table `mda_users`
--
ALTER TABLE `mda_users`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT for table `payment_collection`
--
ALTER TABLE `payment_collection`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=18;

--
-- AUTO_INCREMENT for table `permissions`
--
ALTER TABLE `permissions`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=54;

--
-- AUTO_INCREMENT for table `revenue_heads`
--
ALTER TABLE `revenue_heads`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=7;

--
-- AUTO_INCREMENT for table `special_users_`
--
ALTER TABLE `special_users_`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT for table `special_user_employees`
--
ALTER TABLE `special_user_employees`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT for table `taxpayer`
--
ALTER TABLE `taxpayer`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT for table `taxpayer_business`
--
ALTER TABLE `taxpayer_business`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT for table `taxpayer_identification`
--
ALTER TABLE `taxpayer_identification`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT for table `taxpayer_representative`
--
ALTER TABLE `taxpayer_representative`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT for table `taxpayer_security`
--
ALTER TABLE `taxpayer_security`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT for table `user_activity_log`
--
ALTER TABLE `user_activity_log`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `employee_salary_and_benefits`
--
ALTER TABLE `employee_salary_and_benefits`
  ADD CONSTRAINT `employee_salary_and_benefits_ibfk_1` FOREIGN KEY (`employee_id`) REFERENCES `special_user_employees` (`id`);

--
-- Constraints for table `enumerator_corporate_info`
--
ALTER TABLE `enumerator_corporate_info`
  ADD CONSTRAINT `enumerator_corporate_info_ibfk_1` FOREIGN KEY (`user_tax_number`) REFERENCES `enumerator_tax_payers` (`tax_number`);

--
-- Constraints for table `enumerator_property_info`
--
ALTER TABLE `enumerator_property_info`
  ADD CONSTRAINT `enumerator_property_info_ibfk_1` FOREIGN KEY (`user_tax_number`) REFERENCES `enumerator_tax_payers` (`tax_number`);

--
-- Constraints for table `mda_contact_info`
--
ALTER TABLE `mda_contact_info`
  ADD CONSTRAINT `mda_contact_info_ibfk_1` FOREIGN KEY (`mda_id`) REFERENCES `mda` (`id`) ON DELETE CASCADE;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
