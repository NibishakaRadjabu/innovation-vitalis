-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: Oct 10, 2026 at 02:13 PM
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
-- Database: `innovatio_votalis`
--

-- --------------------------------------------------------

--
-- Table structure for table `admin_registration_settings`
--

CREATE TABLE `admin_registration_settings` (
  `id` tinyint(3) UNSIGNED NOT NULL,
  `code_hash` varchar(255) NOT NULL,
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ;

--
-- Dumping data for table `admin_registration_settings`
--

INSERT INTO `admin_registration_settings` (`id`, `code_hash`, `updated_at`) VALUES
(1, '$2y$10$ln4vMUeDLuYXMwdCNtKEsOkbZkglxniOVEt.KcibQ.WihUIjXViIW', '2026-10-08 06:39:24');

-- --------------------------------------------------------

--
-- Table structure for table `analytics_events`
--
-- Error reading structure for table innovatio_votalis.analytics_events: #1932 - Table &#039;innovatio_votalis.analytics_events&#039; doesn&#039;t exist in engine
-- Error reading data for table innovatio_votalis.analytics_events: #1064 - You have an error in your SQL syntax; check the manual that corresponds to your MariaDB server version for the right syntax to use near &#039;FROM `innovatio_votalis`.`analytics_events`&#039; at line 1

-- --------------------------------------------------------

--
-- Table structure for table `appointments`
--
-- Error reading structure for table innovatio_votalis.appointments: #1932 - Table &#039;innovatio_votalis.appointments&#039; doesn&#039;t exist in engine
-- Error reading data for table innovatio_votalis.appointments: #1064 - You have an error in your SQL syntax; check the manual that corresponds to your MariaDB server version for the right syntax to use near &#039;FROM `innovatio_votalis`.`appointments`&#039; at line 1

-- --------------------------------------------------------

--
-- Table structure for table `appointment_requests`
--

CREATE TABLE `appointment_requests` (
  `id` char(36) NOT NULL,
  `service_id` char(36) NOT NULL,
  `full_name` varchar(120) NOT NULL,
  `email` varchar(254) NOT NULL,
  `phone` varchar(32) DEFAULT NULL,
  `message` varchar(1000) DEFAULT NULL,
  `consent_given` tinyint(1) NOT NULL DEFAULT 0,
  `status` enum('new','contacted','scheduled','cancelled') NOT NULL DEFAULT 'new',
  `patient_id` char(36) DEFAULT NULL,
  `doctor_email` varchar(254) DEFAULT NULL,
  `appointment_type` enum('consultation','follow_up','lab_test') DEFAULT NULL,
  `preferred_at` datetime DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `appointment_requests`
--

INSERT INTO `appointment_requests` (`id`, `service_id`, `full_name`, `email`, `phone`, `message`, `consent_given`, `status`, `patient_id`, `doctor_email`, `appointment_type`, `preferred_at`, `created_at`) VALUES
('e068e6cc-c25c-11f1-8d84-b4b686d0242b', '32858488-c248-11f1-8d84-b4b686d0242b', 'basag', 'nibishakaradjab5@gmail.com', '0733086495', 'vbvbnbnbmnmbbcvcxfcbvnbm', 1, 'new', NULL, NULL, NULL, NULL, '2026-10-07 14:39:18'),
('e6c11bb7-c253-11f1-8d84-b4b686d0242b', '32858488-c248-11f1-8d84-b4b686d0242b', 'basag nanone', 'basagnanone@gmail.com', '0789722814', 'seeking hadache', 1, 'scheduled', '472d57ae-0372-4cee-b6fd-125df8a3e223', 'basagradjabu694@gmail.com', 'consultation', '2026-10-10 18:32:00', '2026-10-07 13:35:03');

-- --------------------------------------------------------

--
-- Table structure for table `audit_logs`
--
-- Error reading structure for table innovatio_votalis.audit_logs: #1932 - Table &#039;innovatio_votalis.audit_logs&#039; doesn&#039;t exist in engine
-- Error reading data for table innovatio_votalis.audit_logs: #1064 - You have an error in your SQL syntax; check the manual that corresponds to your MariaDB server version for the right syntax to use near &#039;FROM `innovatio_votalis`.`audit_logs`&#039; at line 1

-- --------------------------------------------------------

--
-- Table structure for table `billing_invoices`
--
-- Error reading structure for table innovatio_votalis.billing_invoices: #1932 - Table &#039;innovatio_votalis.billing_invoices&#039; doesn&#039;t exist in engine
-- Error reading data for table innovatio_votalis.billing_invoices: #1064 - You have an error in your SQL syntax; check the manual that corresponds to your MariaDB server version for the right syntax to use near &#039;FROM `innovatio_votalis`.`billing_invoices`&#039; at line 1

-- --------------------------------------------------------

--
-- Table structure for table `doctor_availability`
--
-- Error reading structure for table innovatio_votalis.doctor_availability: #1932 - Table &#039;innovatio_votalis.doctor_availability&#039; doesn&#039;t exist in engine
-- Error reading data for table innovatio_votalis.doctor_availability: #1064 - You have an error in your SQL syntax; check the manual that corresponds to your MariaDB server version for the right syntax to use near &#039;FROM `innovatio_votalis`.`doctor_availability`&#039; at line 1

-- --------------------------------------------------------

--
-- Table structure for table `doctor_profiles`
--
-- Error reading structure for table innovatio_votalis.doctor_profiles: #1932 - Table &#039;innovatio_votalis.doctor_profiles&#039; doesn&#039;t exist in engine
-- Error reading data for table innovatio_votalis.doctor_profiles: #1064 - You have an error in your SQL syntax; check the manual that corresponds to your MariaDB server version for the right syntax to use near &#039;FROM `innovatio_votalis`.`doctor_profiles`&#039; at line 1

-- --------------------------------------------------------

--
-- Table structure for table `emergency_contacts`
--
-- Error reading structure for table innovatio_votalis.emergency_contacts: #1932 - Table &#039;innovatio_votalis.emergency_contacts&#039; doesn&#039;t exist in engine
-- Error reading data for table innovatio_votalis.emergency_contacts: #1064 - You have an error in your SQL syntax; check the manual that corresponds to your MariaDB server version for the right syntax to use near &#039;FROM `innovatio_votalis`.`emergency_contacts`&#039; at line 1

-- --------------------------------------------------------

--
-- Table structure for table `health_articles`
--
-- Error reading structure for table innovatio_votalis.health_articles: #1932 - Table &#039;innovatio_votalis.health_articles&#039; doesn&#039;t exist in engine
-- Error reading data for table innovatio_votalis.health_articles: #1064 - You have an error in your SQL syntax; check the manual that corresponds to your MariaDB server version for the right syntax to use near &#039;FROM `innovatio_votalis`.`health_articles`&#039; at line 1

-- --------------------------------------------------------

--
-- Table structure for table `identity_verifications`
--
-- Error reading structure for table innovatio_votalis.identity_verifications: #1932 - Table &#039;innovatio_votalis.identity_verifications&#039; doesn&#039;t exist in engine
-- Error reading data for table innovatio_votalis.identity_verifications: #1064 - You have an error in your SQL syntax; check the manual that corresponds to your MariaDB server version for the right syntax to use near &#039;FROM `innovatio_votalis`.`identity_verifications`&#039; at line 1

-- --------------------------------------------------------

--
-- Table structure for table `laboratory_results`
--
-- Error reading structure for table innovatio_votalis.laboratory_results: #1932 - Table &#039;innovatio_votalis.laboratory_results&#039; doesn&#039;t exist in engine
-- Error reading data for table innovatio_votalis.laboratory_results: #1064 - You have an error in your SQL syntax; check the manual that corresponds to your MariaDB server version for the right syntax to use near &#039;FROM `innovatio_votalis`.`laboratory_results`&#039; at line 1

-- --------------------------------------------------------

--
-- Table structure for table `medical_records`
--
-- Error reading structure for table innovatio_votalis.medical_records: #1932 - Table &#039;innovatio_votalis.medical_records&#039; doesn&#039;t exist in engine
-- Error reading data for table innovatio_votalis.medical_records: #1064 - You have an error in your SQL syntax; check the manual that corresponds to your MariaDB server version for the right syntax to use near &#039;FROM `innovatio_votalis`.`medical_records`&#039; at line 1

-- --------------------------------------------------------

--
-- Table structure for table `medication_catalogue`
--
-- Error reading structure for table innovatio_votalis.medication_catalogue: #1932 - Table &#039;innovatio_votalis.medication_catalogue&#039; doesn&#039;t exist in engine
-- Error reading data for table innovatio_votalis.medication_catalogue: #1064 - You have an error in your SQL syntax; check the manual that corresponds to your MariaDB server version for the right syntax to use near &#039;FROM `innovatio_votalis`.`medication_catalogue`&#039; at line 1

-- --------------------------------------------------------

--
-- Table structure for table `notifications`
--
-- Error reading structure for table innovatio_votalis.notifications: #1932 - Table &#039;innovatio_votalis.notifications&#039; doesn&#039;t exist in engine
-- Error reading data for table innovatio_votalis.notifications: #1064 - You have an error in your SQL syntax; check the manual that corresponds to your MariaDB server version for the right syntax to use near &#039;FROM `innovatio_votalis`.`notifications`&#039; at line 1

-- --------------------------------------------------------

--
-- Table structure for table `password_reset_requests`
--
-- Error reading structure for table innovatio_votalis.password_reset_requests: #1932 - Table &#039;innovatio_votalis.password_reset_requests&#039; doesn&#039;t exist in engine
-- Error reading data for table innovatio_votalis.password_reset_requests: #1064 - You have an error in your SQL syntax; check the manual that corresponds to your MariaDB server version for the right syntax to use near &#039;FROM `innovatio_votalis`.`password_reset_requests`&#039; at line 1

-- --------------------------------------------------------

--
-- Table structure for table `patients`
--

CREATE TABLE `patients` (
  `id` char(36) NOT NULL,
  `full_name` varchar(120) NOT NULL,
  `email` varchar(254) NOT NULL,
  `phone` varchar(32) DEFAULT NULL,
  `date_of_birth` date DEFAULT NULL,
  `gender` varchar(40) DEFAULT NULL,
  `password_hash` varchar(255) NOT NULL,
  `privacy_consent_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `patients`
--

INSERT INTO `patients` (`id`, `full_name`, `email`, `phone`, `date_of_birth`, `gender`, `password_hash`, `privacy_consent_at`, `created_at`) VALUES
('472d57ae-0372-4cee-b6fd-125df8a3e223', 'basag nanone', 'basagnanone@gmail.com', '0789722814', '2007-01-07', 'male', '$2y$10$VoPZivraLVFSPmvMCjLrIuP6djMdCoXtgtzVYKqcD.ELShLlerQjm', '2026-10-07 13:31:07', '2026-10-07 13:31:07');

-- --------------------------------------------------------

--
-- Table structure for table `patient_password_resets`
--

CREATE TABLE `patient_password_resets` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `patient_id` char(36) NOT NULL,
  `token_hash` char(64) NOT NULL,
  `expires_at` datetime NOT NULL,
  `used_at` datetime DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `payments`
--
-- Error reading structure for table innovatio_votalis.payments: #1932 - Table &#039;innovatio_votalis.payments&#039; doesn&#039;t exist in engine
-- Error reading data for table innovatio_votalis.payments: #1064 - You have an error in your SQL syntax; check the manual that corresponds to your MariaDB server version for the right syntax to use near &#039;FROM `innovatio_votalis`.`payments`&#039; at line 1

-- --------------------------------------------------------

--
-- Table structure for table `payment_destinations`
--

CREATE TABLE `payment_destinations` (
  `id` tinyint(3) UNSIGNED NOT NULL,
  `bank_name` varchar(120) DEFAULT NULL,
  `bank_account_name` varchar(120) DEFAULT NULL,
  `bank_account_number` varchar(80) DEFAULT NULL,
  `mtn_momo_name` varchar(120) DEFAULT NULL,
  `mtn_momo_number` varchar(32) DEFAULT NULL,
  `mtn_momo_code` varchar(80) DEFAULT NULL,
  `airtel_money_name` varchar(120) DEFAULT NULL,
  `airtel_money_number` varchar(32) DEFAULT NULL,
  `airtel_money_code` varchar(80) DEFAULT NULL,
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ;

--
-- Dumping data for table `payment_destinations`
--

INSERT INTO `payment_destinations` (`id`, `bank_name`, `bank_account_name`, `bank_account_number`, `mtn_momo_name`, `mtn_momo_number`, `mtn_momo_code`, `airtel_money_name`, `airtel_money_number`, `airtel_money_code`, `updated_at`) VALUES
(1, NULL, NULL, NULL, 'nibishaka radjab', '0789722814', '1822201', NULL, NULL, NULL, '2026-10-08 08:53:27');

-- --------------------------------------------------------

--
-- Table structure for table `prescriptions`
--
-- Error reading structure for table innovatio_votalis.prescriptions: #1932 - Table &#039;innovatio_votalis.prescriptions&#039; doesn&#039;t exist in engine
-- Error reading data for table innovatio_votalis.prescriptions: #1064 - You have an error in your SQL syntax; check the manual that corresponds to your MariaDB server version for the right syntax to use near &#039;FROM `innovatio_votalis`.`prescriptions`&#039; at line 1

-- --------------------------------------------------------

--
-- Table structure for table `services`
--

CREATE TABLE `services` (
  `id` char(36) NOT NULL,
  `slug` varchar(80) NOT NULL,
  `name` varchar(120) NOT NULL,
  `summary` text NOT NULL,
  `active` tinyint(1) NOT NULL DEFAULT 1,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `price_rwf` bigint(20) UNSIGNED DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `services`
--

INSERT INTO `services` (`id`, `slug`, `name`, `summary`, `active`, `created_at`, `price_rwf`) VALUES
('32857973-c248-11f1-8d84-b4b686d0242b', 'virtual-care', 'Virtual care', 'Speak with a qualified clinician from the comfort of home.', 1, '2026-10-07 12:11:16', NULL),
('32858488-c248-11f1-8d84-b4b686d0242b', 'preventive-health', 'Preventive health', 'Practical screenings and care plans shaped around your goals.', 1, '2026-10-07 12:11:16', NULL),
('3285860d-c248-11f1-8d84-b4b686d0242b', 'care-coordination', 'Care coordination', 'One clear path across providers, referrals, and next steps.', 1, '2026-10-07 12:11:16', NULL);

-- --------------------------------------------------------

--
-- Table structure for table `service_payment_methods`
--

CREATE TABLE `service_payment_methods` (
  `service_id` char(36) NOT NULL,
  `method` enum('mtn_momo','airtel_money','bank_transfer','cash') NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `service_payment_methods`
--

INSERT INTO `service_payment_methods` (`service_id`, `method`) VALUES
('32857973-c248-11f1-8d84-b4b686d0242b', 'mtn_momo'),
('32858488-c248-11f1-8d84-b4b686d0242b', 'mtn_momo'),
('3285860d-c248-11f1-8d84-b4b686d0242b', 'mtn_momo');

-- --------------------------------------------------------

--
-- Table structure for table `staff_users`
--

CREATE TABLE `staff_users` (
  `id` char(36) NOT NULL,
  `full_name` varchar(120) NOT NULL,
  `email` varchar(254) NOT NULL,
  `password_hash` varchar(255) NOT NULL,
  `role` enum('admin','doctor','coordinator') NOT NULL DEFAULT 'coordinator',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `staff_users`
--

INSERT INTO `staff_users` (`id`, `full_name`, `email`, `password_hash`, `role`, `created_at`) VALUES
('005bc51a-c2e3-11f1-b376-b4b686d0242b', 'Radjab', 'niyomama@gmail.com', '$2y$10$DqQRoeG6gixBVL2cq13.q.0OBFI17et2v5/I7KQUSo.oh1yW9c9DO', 'admin', '2026-10-08 06:39:24'),
('32ea7ef0-c252-11f1-8d84-b4b686d0242b', 'basag radjab', 'basagradjabu694@gmail.com', '$2y$10$nq3Zbcx22c/6Hfh4yR5c9.9wfzj0S7VgDxrw6JYpIf/pyjwruO5OG', 'doctor', '2026-10-07 13:22:52'),
('610b6a16-c26e-11f1-8d84-b4b686d0242b', 'admin', 'admin@gmail.com', 'admin@123', 'admin', '2026-10-07 16:44:35'),
('ce0d4e6c-c24a-11f1-8d84-b4b686d0242b', 'nibishaka Radjab', 'nibishakaradjab5@gmail.com', '$2y$10$iy6nUt0V4hWeW0qn9Gyc0euxJDjRg8rQ.NsqTMwUmAA.dHk8vh1.O', 'coordinator', '2026-10-07 12:29:56');

-- --------------------------------------------------------

--
-- Table structure for table `telemedicine_calls`
--

CREATE TABLE `telemedicine_calls` (
  `room_id` char(36) NOT NULL,
  `access_hash` char(64) NOT NULL,
  `doctor_email` varchar(254) NOT NULL,
  `patient_id` char(36) DEFAULT NULL,
  `status` enum('waiting','connected','ended') NOT NULL DEFAULT 'waiting',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `expires_at` datetime DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `telemedicine_calls`
--

INSERT INTO `telemedicine_calls` (`room_id`, `access_hash`, `doctor_email`, `patient_id`, `status`, `created_at`, `expires_at`) VALUES
('ff4fc111-8fb6-48c4-b68b-44065de60bab', 'bb55b8eca2a6715afd26acc2b34605a0b1ae9f370114df7c46d48a72761bd943', 'basagradjabu694@gmail.com', '472d57ae-0372-4cee-b6fd-125df8a3e223', 'connected', '2026-10-09 16:32:02', '2026-10-09 20:32:02');

-- --------------------------------------------------------

--
-- Table structure for table `telemedicine_sessions`
--
-- Error reading structure for table innovatio_votalis.telemedicine_sessions: #1932 - Table &#039;innovatio_votalis.telemedicine_sessions&#039; doesn&#039;t exist in engine
-- Error reading data for table innovatio_votalis.telemedicine_sessions: #1064 - You have an error in your SQL syntax; check the manual that corresponds to your MariaDB server version for the right syntax to use near &#039;FROM `innovatio_votalis`.`telemedicine_sessions`&#039; at line 1

-- --------------------------------------------------------

--
-- Table structure for table `telemedicine_signals`
--

CREATE TABLE `telemedicine_signals` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `room_id` char(36) NOT NULL,
  `sender` enum('doctor','patient') NOT NULL,
  `signal_type` enum('offer','answer','candidate','hangup') NOT NULL,
  `payload` mediumtext DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `telemedicine_signals`
--

INSERT INTO `telemedicine_signals` (`id`, `room_id`, `sender`, `signal_type`, `payload`, `created_at`) VALUES
(14, 'ff4fc111-8fb6-48c4-b68b-44065de60bab', 'doctor', 'offer', '{\"type\":\"offer\",\"sdp\":\"v=0\\r\\no=- 2370124344476692985 2 IN IP4 127.0.0.1\\r\\ns=-\\r\\nt=0 0\\r\\na=group:BUNDLE 0 1 2\\r\\na=extmap-allow-mixed\\r\\na=msid-semantic: WMS e42a3c9c-c6db-48d2-a21c-5723e57927f5\\r\\nm=audio 9 UDP/TLS/RTP/SAVPF 111 63 9 0 8 13 110 126\\r\\nc=IN IP4 0.0.0.0\\r\\na=rtcp:9 IN IP4 0.0.0.0\\r\\na=ice-ufrag:IGAO\\r\\na=ice-pwd:r+p9o5+lISx9VKe5tTb2436P\\r\\na=ice-options:trickle\\r\\na=fingerprint:sha-256 91:B5:8F:D5:9D:7B:C1:F2:72:89:52:72:F3:9D:7C:87:E8:88:7B:8D:FA:D8:24:36:60:DF:52:6F:33:39:10:0D\\r\\na=setup:actpass\\r\\na=mid:0\\r\\na=extmap:1 urn:ietf:params:rtp-hdrext:ssrc-audio-level\\r\\na=extmap:2 http://www.webrtc.org/experiments/rtp-hdrext/abs-send-time\\r\\na=extmap:3 http://www.ietf.org/id/draft-holmer-rmcat-transport-wide-cc-extensions-01\\r\\na=extmap:4 urn:ietf:params:rtp-hdrext:sdes:mid\\r\\na=sendrecv\\r\\na=msid:e42a3c9c-c6db-48d2-a21c-5723e57927f5 0b33a18e-7c6b-4415-98b8-07844e3466e4\\r\\na=rtcp-mux\\r\\na=rtcp-rsize\\r\\na=rtcp-xr:rcvr-rtt=all\\r\\na=rtpmap:111 opus/48000/2\\r\\na=rtcp-fb:111 transport-cc\\r\\na=fmtp:111 minptime=10;useinbandfec=1\\r\\na=rtpmap:63 red/48000/2\\r\\na=fmtp:63 111/111\\r\\na=rtpmap:9 G722/8000\\r\\na=rtpmap:0 PCMU/8000\\r\\na=rtpmap:8 PCMA/8000\\r\\na=rtpmap:13 CN/8000\\r\\na=rtpmap:110 telephone-event/48000\\r\\na=rtpmap:126 telephone-event/8000\\r\\na=ssrc:1537680448 cname:bj68cbSCK8lf8dv5\\r\\na=ssrc:1537680448 msid:e42a3c9c-c6db-48d2-a21c-5723e57927f5 0b33a18e-7c6b-4415-98b8-07844e3466e4\\r\\nm=video 9 UDP/TLS/RTP/SAVPF 96 97 102 103 104 107 108 109 114 115 116 117 39 40 45 46 98 99 100 101 118 119 122 123 124\\r\\nc=IN IP4 0.0.0.0\\r\\na=rtcp:9 IN IP4 0.0.0.0\\r\\na=ice-ufrag:IGAO\\r\\na=ice-pwd:r+p9o5+lISx9VKe5tTb2436P\\r\\na=ice-options:trickle\\r\\na=fingerprint:sha-256 91:B5:8F:D5:9D:7B:C1:F2:72:89:52:72:F3:9D:7C:87:E8:88:7B:8D:FA:D8:24:36:60:DF:52:6F:33:39:10:0D\\r\\na=setup:actpass\\r\\na=mid:1\\r\\na=extmap:14 urn:ietf:params:rtp-hdrext:toffset\\r\\na=extmap:2 http://www.webrtc.org/experiments/rtp-hdrext/abs-send-time\\r\\na=extmap:13 urn:3gpp:video-orientation\\r\\na=extmap:3 http://www.ietf.org/id/draft-holmer-rmcat-transport-wide-cc-extensions-01\\r\\na=extmap:5 http://www.webrtc.org/experiments/rtp-hdrext/playout-delay\\r\\na=extmap:6 http://www.webrtc.org/experiments/rtp-hdrext/video-content-type\\r\\na=extmap:7 http://www.webrtc.org/experiments/rtp-hdrext/video-timing\\r\\na=extmap:8 http://www.webrtc.org/experiments/rtp-hdrext/color-space\\r\\na=extmap:4 urn:ietf:params:rtp-hdrext:sdes:mid\\r\\na=extmap:10 urn:ietf:params:rtp-hdrext:sdes:rtp-stream-id\\r\\na=extmap:11 urn:ietf:params:rtp-hdrext:sdes:repaired-rtp-stream-id\\r\\na=sendrecv\\r\\na=msid:e42a3c9c-c6db-48d2-a21c-5723e57927f5 d22302a0-fed8-42fd-ab35-098be641f923\\r\\na=rtcp-mux\\r\\na=rtcp-rsize\\r\\na=rtcp-xr:rcvr-rtt=all\\r\\na=rtpmap:96 VP8/90000\\r\\na=rtcp-fb:96 goog-remb\\r\\na=rtcp-fb:96 transport-cc\\r\\na=rtcp-fb:96 ccm fir\\r\\na=rtcp-fb:96 nack\\r\\na=rtcp-fb:96 nack pli\\r\\na=rtpmap:97 rtx/90000\\r\\na=fmtp:97 apt=96\\r\\na=rtpmap:102 H264/90000\\r\\na=rtcp-fb:102 goog-remb\\r\\na=rtcp-fb:102 transport-cc\\r\\na=rtcp-fb:102 ccm fir\\r\\na=rtcp-fb:102 nack\\r\\na=rtcp-fb:102 nack pli\\r\\na=fmtp:102 level-asymmetry-allowed=1;packetization-mode=1;profile-level-id=42001f\\r\\na=rtpmap:103 rtx/90000\\r\\na=fmtp:103 apt=102\\r\\na=rtpmap:104 H264/90000\\r\\na=rtcp-fb:104 goog-remb\\r\\na=rtcp-fb:104 transport-cc\\r\\na=rtcp-fb:104 ccm fir\\r\\na=rtcp-fb:104 nack\\r\\na=rtcp-fb:104 nack pli\\r\\na=fmtp:104 level-asymmetry-allowed=1;packetization-mode=0;profile-level-id=42001f\\r\\na=rtpmap:107 rtx/90000\\r\\na=fmtp:107 apt=104\\r\\na=rtpmap:108 H264/90000\\r\\na=rtcp-fb:108 goog-remb\\r\\na=rtcp-fb:108 transport-cc\\r\\na=rtcp-fb:108 ccm fir\\r\\na=rtcp-fb:108 nack\\r\\na=rtcp-fb:108 nack pli\\r\\na=fmtp:108 level-asymmetry-allowed=1;packetization-mode=1;profile-level-id=42e01f\\r\\na=rtpmap:109 rtx/90000\\r\\na=fmtp:109 apt=108\\r\\na=rtpmap:114 H264/90000\\r\\na=rtcp-fb:114 goog-remb\\r\\na=rtcp-fb:114 transport-cc\\r\\na=rtcp-fb:114 ccm fir\\r\\na=rtcp-fb:114 nack\\r\\na=rtcp-fb:114 nack pli\\r\\na=fmtp:114 level-asymmetry-allowed=1;packetization-mode=0;profile-level-id=42e01f\\r\\na=rtpmap:115 rtx/90000\\r\\na=fmtp:115 apt=114\\r\\na=rtpmap:116 H264/90000\\r\\na=rtcp-fb:116 goog-remb\\r\\na=rtcp-fb:116 transport-cc\\r\\na=rtcp-fb:116 ccm fir\\r\\na=rtcp-fb:116 nack\\r\\na=rtcp-fb:116 nack pli\\r\\na=fmtp:116 level-asymmetry-allowed=1;packetization-mode=1;profile-level-id=4d001f\\r\\na=rtpmap:117 rtx/90000\\r\\na=fmtp:117 apt=116\\r\\na=rtpmap:39 H264/90000\\r\\na=rtcp-fb:39 goog-remb\\r\\na=rtcp-fb:39 transport-cc\\r\\na=rtcp-fb:39 ccm fir\\r\\na=rtcp-fb:39 nack\\r\\na=rtcp-fb:39 nack pli\\r\\na=fmtp:39 level-asymmetry-allowed=1;packetization-mode=0;profile-level-id=4d001f\\r\\na=rtpmap:40 rtx/90000\\r\\na=fmtp:40 apt=39\\r\\na=rtpmap:45 AV1/90000\\r\\na=rtcp-fb:45 goog-remb\\r\\na=rtcp-fb:45 transport-cc\\r\\na=rtcp-fb:45 ccm fir\\r\\na=rtcp-fb:45 nack\\r\\na=rtcp-fb:45 nack pli\\r\\na=fmtp:45 level-idx=5;profile=0;tier=0\\r\\na=rtpmap:46 rtx/90000\\r\\na=fmtp:46 apt=45\\r\\na=rtpmap:98 VP9/90000\\r\\na=rtcp-fb:98 goog-remb\\r\\na=rtcp-fb:98 transport-cc\\r\\na=rtcp-fb:98 ccm fir\\r\\na=rtcp-fb:98 nack\\r\\na=rtcp-fb:98 nack pli\\r\\na=fmtp:98 profile-id=0\\r\\na=rtpmap:99 rtx/90000\\r\\na=fmtp:99 apt=98\\r\\na=rtpmap:100 VP9/90000\\r\\na=rtcp-fb:100 goog-remb\\r\\na=rtcp-fb:100 transport-cc\\r\\na=rtcp-fb:100 ccm fir\\r\\na=rtcp-fb:100 nack\\r\\na=rtcp-fb:100 nack pli\\r\\na=fmtp:100 profile-id=2\\r\\na=rtpmap:101 rtx/90000\\r\\na=fmtp:101 apt=100\\r\\na=rtpmap:118 H264/90000\\r\\na=rtcp-fb:118 goog-remb\\r\\na=rtcp-fb:118 transport-cc\\r\\na=rtcp-fb:118 ccm fir\\r\\na=rtcp-fb:118 nack\\r\\na=rtcp-fb:118 nack pli\\r\\na=fmtp:118 level-asymmetry-allowed=1;packetization-mode=1;profile-level-id=64001f\\r\\na=rtpmap:119 rtx/90000\\r\\na=fmtp:119 apt=118\\r\\na=rtpmap:122 red/90000\\r\\na=rtpmap:123 rtx/90000\\r\\na=fmtp:123 apt=122\\r\\na=rtpmap:124 ulpfec/90000\\r\\na=ssrc-group:FID 339155021 68863910\\r\\na=ssrc:339155021 cname:bj68cbSCK8lf8dv5\\r\\na=ssrc:339155021 msid:e42a3c9c-c6db-48d2-a21c-5723e57927f5 d22302a0-fed8-42fd-ab35-098be641f923\\r\\na=ssrc:68863910 cname:bj68cbSCK8lf8dv5\\r\\na=ssrc:68863910 msid:e42a3c9c-c6db-48d2-a21c-5723e57927f5 d22302a0-fed8-42fd-ab35-098be641f923\\r\\nm=application 9 UDP/DTLS/SCTP webrtc-datachannel\\r\\nc=IN IP4 0.0.0.0\\r\\na=ice-ufrag:IGAO\\r\\na=ice-pwd:r+p9o5+lISx9VKe5tTb2436P\\r\\na=ice-options:trickle\\r\\na=fingerprint:sha-256 91:B5:8F:D5:9D:7B:C1:F2:72:89:52:72:F3:9D:7C:87:E8:88:7B:8D:FA:D8:24:36:60:DF:52:6F:33:39:10:0D\\r\\na=setup:actpass\\r\\na=mid:2\\r\\na=sctp-port:5000\\r\\na=max-message-size:262144\\r\\n\"}', '2026-10-09 16:32:02'),
(15, 'ff4fc111-8fb6-48c4-b68b-44065de60bab', 'doctor', 'candidate', '{\"candidate\":\"candidate:2543402442 1 udp 2122260223 192.168.27.68 59860 typ host generation 0 ufrag IGAO network-id 1 network-cost 10\",\"sdpMid\":\"0\",\"sdpMLineIndex\":0,\"usernameFragment\":\"IGAO\"}', '2026-10-09 16:32:02'),
(16, 'ff4fc111-8fb6-48c4-b68b-44065de60bab', 'doctor', 'candidate', '{\"candidate\":\"candidate:2543402442 1 udp 2122260223 192.168.27.68 59861 typ host generation 0 ufrag IGAO network-id 1 network-cost 10\",\"sdpMid\":\"1\",\"sdpMLineIndex\":1,\"usernameFragment\":\"IGAO\"}', '2026-10-09 16:32:02'),
(17, 'ff4fc111-8fb6-48c4-b68b-44065de60bab', 'doctor', 'candidate', '{\"candidate\":\"candidate:2543402442 1 udp 2122260223 192.168.27.68 59862 typ host generation 0 ufrag IGAO network-id 1 network-cost 10\",\"sdpMid\":\"2\",\"sdpMLineIndex\":2,\"usernameFragment\":\"IGAO\"}', '2026-10-09 16:32:02'),
(18, 'ff4fc111-8fb6-48c4-b68b-44065de60bab', 'doctor', 'candidate', '{\"candidate\":\"candidate:1765009758 1 tcp 1518280447 192.168.27.68 9 typ host tcptype active generation 0 ufrag IGAO network-id 1 network-cost 10\",\"sdpMid\":\"0\",\"sdpMLineIndex\":0,\"usernameFragment\":\"IGAO\"}', '2026-10-09 16:32:02'),
(19, 'ff4fc111-8fb6-48c4-b68b-44065de60bab', 'doctor', 'candidate', '{\"candidate\":\"candidate:1765009758 1 tcp 1518280447 192.168.27.68 9 typ host tcptype active generation 0 ufrag IGAO network-id 1 network-cost 10\",\"sdpMid\":\"1\",\"sdpMLineIndex\":1,\"usernameFragment\":\"IGAO\"}', '2026-10-09 16:32:02'),
(20, 'ff4fc111-8fb6-48c4-b68b-44065de60bab', 'doctor', 'candidate', '{\"candidate\":\"candidate:1765009758 1 tcp 1518280447 192.168.27.68 9 typ host tcptype active generation 0 ufrag IGAO network-id 1 network-cost 10\",\"sdpMid\":\"2\",\"sdpMLineIndex\":2,\"usernameFragment\":\"IGAO\"}', '2026-10-09 16:32:02'),
(21, 'ff4fc111-8fb6-48c4-b68b-44065de60bab', 'patient', 'answer', '{\"type\":\"answer\",\"sdp\":\"v=0\\r\\no=- 3697539414088958903 2 IN IP4 127.0.0.1\\r\\ns=-\\r\\nt=0 0\\r\\na=group:BUNDLE 0 1 2\\r\\na=extmap-allow-mixed\\r\\na=msid-semantic: WMS 661be6d2-095d-4cba-b0aa-d86592f45e01\\r\\nm=audio 9 UDP/TLS/RTP/SAVPF 111 63 9 0 8 13 110 126\\r\\nc=IN IP4 0.0.0.0\\r\\na=rtcp:9 IN IP4 0.0.0.0\\r\\na=ice-ufrag:U++0\\r\\na=ice-pwd:D1oaLMiocQ/oXx3BT7SxciTF\\r\\na=ice-options:trickle\\r\\na=fingerprint:sha-256 D3:BA:52:C7:64:52:7B:B7:6D:84:74:B3:90:B1:68:C7:F1:5E:E7:47:31:EE:55:C8:C9:C5:80:E1:32:E7:06:DE\\r\\na=setup:active\\r\\na=mid:0\\r\\na=extmap:1 urn:ietf:params:rtp-hdrext:ssrc-audio-level\\r\\na=extmap:2 http://www.webrtc.org/experiments/rtp-hdrext/abs-send-time\\r\\na=extmap:3 http://www.ietf.org/id/draft-holmer-rmcat-transport-wide-cc-extensions-01\\r\\na=extmap:4 urn:ietf:params:rtp-hdrext:sdes:mid\\r\\na=sendrecv\\r\\na=msid:661be6d2-095d-4cba-b0aa-d86592f45e01 381b0c8b-5e4f-4750-9c5e-acc64b0c8eb7\\r\\na=rtcp-mux\\r\\na=rtcp-rsize\\r\\na=rtcp-xr:rcvr-rtt=all\\r\\na=rtpmap:111 opus/48000/2\\r\\na=rtcp-fb:111 transport-cc\\r\\na=fmtp:111 minptime=10;useinbandfec=1\\r\\na=rtpmap:63 red/48000/2\\r\\na=fmtp:63 111/111\\r\\na=rtpmap:9 G722/8000\\r\\na=rtpmap:0 PCMU/8000\\r\\na=rtpmap:8 PCMA/8000\\r\\na=rtpmap:13 CN/8000\\r\\na=rtpmap:110 telephone-event/48000\\r\\na=rtpmap:126 telephone-event/8000\\r\\na=ssrc:3484881269 cname:/xOkVZfjvJRqenTg\\r\\nm=video 9 UDP/TLS/RTP/SAVPF 96 97 102 103 104 107 108 109 114 115 116 117 39 40 45 46 98 99 100 101 118 119 122 123 124\\r\\nc=IN IP4 0.0.0.0\\r\\na=rtcp:9 IN IP4 0.0.0.0\\r\\na=ice-ufrag:U++0\\r\\na=ice-pwd:D1oaLMiocQ/oXx3BT7SxciTF\\r\\na=ice-options:trickle\\r\\na=fingerprint:sha-256 D3:BA:52:C7:64:52:7B:B7:6D:84:74:B3:90:B1:68:C7:F1:5E:E7:47:31:EE:55:C8:C9:C5:80:E1:32:E7:06:DE\\r\\na=setup:active\\r\\na=mid:1\\r\\na=extmap:14 urn:ietf:params:rtp-hdrext:toffset\\r\\na=extmap:2 http://www.webrtc.org/experiments/rtp-hdrext/abs-send-time\\r\\na=extmap:13 urn:3gpp:video-orientation\\r\\na=extmap:3 http://www.ietf.org/id/draft-holmer-rmcat-transport-wide-cc-extensions-01\\r\\na=extmap:5 http://www.webrtc.org/experiments/rtp-hdrext/playout-delay\\r\\na=extmap:6 http://www.webrtc.org/experiments/rtp-hdrext/video-content-type\\r\\na=extmap:7 http://www.webrtc.org/experiments/rtp-hdrext/video-timing\\r\\na=extmap:8 http://www.webrtc.org/experiments/rtp-hdrext/color-space\\r\\na=extmap:4 urn:ietf:params:rtp-hdrext:sdes:mid\\r\\na=extmap:10 urn:ietf:params:rtp-hdrext:sdes:rtp-stream-id\\r\\na=extmap:11 urn:ietf:params:rtp-hdrext:sdes:repaired-rtp-stream-id\\r\\na=sendrecv\\r\\na=msid:661be6d2-095d-4cba-b0aa-d86592f45e01 929bacc5-56b0-4151-9b6f-1c5701f69ed8\\r\\na=rtcp-mux\\r\\na=rtcp-rsize\\r\\na=rtcp-xr:rcvr-rtt=all\\r\\na=rtpmap:96 VP8/90000\\r\\na=rtcp-fb:96 goog-remb\\r\\na=rtcp-fb:96 transport-cc\\r\\na=rtcp-fb:96 ccm fir\\r\\na=rtcp-fb:96 nack\\r\\na=rtcp-fb:96 nack pli\\r\\na=rtpmap:97 rtx/90000\\r\\na=fmtp:97 apt=96\\r\\na=rtpmap:102 H264/90000\\r\\na=rtcp-fb:102 goog-remb\\r\\na=rtcp-fb:102 transport-cc\\r\\na=rtcp-fb:102 ccm fir\\r\\na=rtcp-fb:102 nack\\r\\na=rtcp-fb:102 nack pli\\r\\na=fmtp:102 level-asymmetry-allowed=1;packetization-mode=1;profile-level-id=42001f\\r\\na=rtpmap:103 rtx/90000\\r\\na=fmtp:103 apt=102\\r\\na=rtpmap:104 H264/90000\\r\\na=rtcp-fb:104 goog-remb\\r\\na=rtcp-fb:104 transport-cc\\r\\na=rtcp-fb:104 ccm fir\\r\\na=rtcp-fb:104 nack\\r\\na=rtcp-fb:104 nack pli\\r\\na=fmtp:104 level-asymmetry-allowed=1;packetization-mode=0;profile-level-id=42001f\\r\\na=rtpmap:107 rtx/90000\\r\\na=fmtp:107 apt=104\\r\\na=rtpmap:108 H264/90000\\r\\na=rtcp-fb:108 goog-remb\\r\\na=rtcp-fb:108 transport-cc\\r\\na=rtcp-fb:108 ccm fir\\r\\na=rtcp-fb:108 nack\\r\\na=rtcp-fb:108 nack pli\\r\\na=fmtp:108 level-asymmetry-allowed=1;packetization-mode=1;profile-level-id=42e01f\\r\\na=rtpmap:109 rtx/90000\\r\\na=fmtp:109 apt=108\\r\\na=rtpmap:114 H264/90000\\r\\na=rtcp-fb:114 goog-remb\\r\\na=rtcp-fb:114 transport-cc\\r\\na=rtcp-fb:114 ccm fir\\r\\na=rtcp-fb:114 nack\\r\\na=rtcp-fb:114 nack pli\\r\\na=fmtp:114 level-asymmetry-allowed=1;packetization-mode=0;profile-level-id=42e01f\\r\\na=rtpmap:115 rtx/90000\\r\\na=fmtp:115 apt=114\\r\\na=rtpmap:116 H264/90000\\r\\na=rtcp-fb:116 goog-remb\\r\\na=rtcp-fb:116 transport-cc\\r\\na=rtcp-fb:116 ccm fir\\r\\na=rtcp-fb:116 nack\\r\\na=rtcp-fb:116 nack pli\\r\\na=fmtp:116 level-asymmetry-allowed=1;packetization-mode=1;profile-level-id=4d001f\\r\\na=rtpmap:117 rtx/90000\\r\\na=fmtp:117 apt=116\\r\\na=rtpmap:39 H264/90000\\r\\na=rtcp-fb:39 goog-remb\\r\\na=rtcp-fb:39 transport-cc\\r\\na=rtcp-fb:39 ccm fir\\r\\na=rtcp-fb:39 nack\\r\\na=rtcp-fb:39 nack pli\\r\\na=fmtp:39 level-asymmetry-allowed=1;packetization-mode=0;profile-level-id=4d001f\\r\\na=rtpmap:40 rtx/90000\\r\\na=fmtp:40 apt=39\\r\\na=rtpmap:45 AV1/90000\\r\\na=rtcp-fb:45 goog-remb\\r\\na=rtcp-fb:45 transport-cc\\r\\na=rtcp-fb:45 ccm fir\\r\\na=rtcp-fb:45 nack\\r\\na=rtcp-fb:45 nack pli\\r\\na=fmtp:45 level-idx=5;profile=0;tier=0\\r\\na=rtpmap:46 rtx/90000\\r\\na=fmtp:46 apt=45\\r\\na=rtpmap:98 VP9/90000\\r\\na=rtcp-fb:98 goog-remb\\r\\na=rtcp-fb:98 transport-cc\\r\\na=rtcp-fb:98 ccm fir\\r\\na=rtcp-fb:98 nack\\r\\na=rtcp-fb:98 nack pli\\r\\na=fmtp:98 profile-id=0\\r\\na=rtpmap:99 rtx/90000\\r\\na=fmtp:99 apt=98\\r\\na=rtpmap:100 VP9/90000\\r\\na=rtcp-fb:100 goog-remb\\r\\na=rtcp-fb:100 transport-cc\\r\\na=rtcp-fb:100 ccm fir\\r\\na=rtcp-fb:100 nack\\r\\na=rtcp-fb:100 nack pli\\r\\na=fmtp:100 profile-id=2\\r\\na=rtpmap:101 rtx/90000\\r\\na=fmtp:101 apt=100\\r\\na=rtpmap:118 H264/90000\\r\\na=rtcp-fb:118 goog-remb\\r\\na=rtcp-fb:118 transport-cc\\r\\na=rtcp-fb:118 ccm fir\\r\\na=rtcp-fb:118 nack\\r\\na=rtcp-fb:118 nack pli\\r\\na=fmtp:118 level-asymmetry-allowed=1;packetization-mode=1;profile-level-id=64001f\\r\\na=rtpmap:119 rtx/90000\\r\\na=fmtp:119 apt=118\\r\\na=rtpmap:122 red/90000\\r\\na=rtpmap:123 rtx/90000\\r\\na=fmtp:123 apt=122\\r\\na=rtpmap:124 ulpfec/90000\\r\\na=ssrc-group:FID 1786457975 3183366411\\r\\na=ssrc:1786457975 cname:/xOkVZfjvJRqenTg\\r\\na=ssrc:3183366411 cname:/xOkVZfjvJRqenTg\\r\\nm=application 9 UDP/DTLS/SCTP webrtc-datachannel\\r\\nc=IN IP4 0.0.0.0\\r\\na=ice-ufrag:U++0\\r\\na=ice-pwd:D1oaLMiocQ/oXx3BT7SxciTF\\r\\na=ice-options:trickle\\r\\na=fingerprint:sha-256 D3:BA:52:C7:64:52:7B:B7:6D:84:74:B3:90:B1:68:C7:F1:5E:E7:47:31:EE:55:C8:C9:C5:80:E1:32:E7:06:DE\\r\\na=setup:active\\r\\na=mid:2\\r\\na=sctp-port:5000\\r\\na=max-message-size:262144\\r\\n\"}', '2026-10-09 16:33:36'),
(22, 'ff4fc111-8fb6-48c4-b68b-44065de60bab', 'patient', 'candidate', '{\"candidate\":\"candidate:2624145787 1 udp 2122260223 192.168.27.68 53943 typ host generation 0 ufrag U++0 network-id 1 network-cost 10\",\"sdpMid\":\"0\",\"sdpMLineIndex\":0,\"usernameFragment\":\"U++0\"}', '2026-10-09 16:33:36');

-- --------------------------------------------------------

--
-- Table structure for table `users`
--
-- Error reading structure for table innovatio_votalis.users: #1932 - Table &#039;innovatio_votalis.users&#039; doesn&#039;t exist in engine
-- Error reading data for table innovatio_votalis.users: #1064 - You have an error in your SQL syntax; check the manual that corresponds to your MariaDB server version for the right syntax to use near &#039;FROM `innovatio_votalis`.`users`&#039; at line 1

--
-- Indexes for dumped tables
--

--
-- Indexes for table `admin_registration_settings`
--
ALTER TABLE `admin_registration_settings`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `appointment_requests`
--
ALTER TABLE `appointment_requests`
  ADD PRIMARY KEY (`id`),
  ADD KEY `fk_appointment_service` (`service_id`),
  ADD KEY `appointment_status_created_idx` (`status`,`created_at`),
  ADD KEY `appointment_patient_idx` (`patient_id`,`preferred_at`);

--
-- Indexes for table `patients`
--
ALTER TABLE `patients`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `email` (`email`);

--
-- Indexes for table `patient_password_resets`
--
ALTER TABLE `patient_password_resets`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `token_hash` (`token_hash`),
  ADD KEY `patient_password_reset_lookup_idx` (`patient_id`,`used_at`,`expires_at`),
  ADD KEY `patient_password_reset_expiry_idx` (`expires_at`);

--
-- Indexes for table `payment_destinations`
--
ALTER TABLE `payment_destinations`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `services`
--
ALTER TABLE `services`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `slug` (`slug`);

--
-- Indexes for table `service_payment_methods`
--
ALTER TABLE `service_payment_methods`
  ADD PRIMARY KEY (`service_id`,`method`);

--
-- Indexes for table `staff_users`
--
ALTER TABLE `staff_users`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `email` (`email`);

--
-- Indexes for table `telemedicine_calls`
--
ALTER TABLE `telemedicine_calls`
  ADD PRIMARY KEY (`room_id`),
  ADD KEY `telemedicine_expiry_idx` (`expires_at`),
  ADD KEY `telemedicine_patient_idx` (`patient_id`);

--
-- Indexes for table `telemedicine_signals`
--
ALTER TABLE `telemedicine_signals`
  ADD PRIMARY KEY (`id`),
  ADD KEY `telemedicine_signal_poll_idx` (`room_id`,`id`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `patient_password_resets`
--
ALTER TABLE `patient_password_resets`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT for table `telemedicine_signals`
--
ALTER TABLE `telemedicine_signals`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=23;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `appointment_requests`
--
ALTER TABLE `appointment_requests`
  ADD CONSTRAINT `fk_appointment_service` FOREIGN KEY (`service_id`) REFERENCES `services` (`id`);

--
-- Constraints for table `patient_password_resets`
--
ALTER TABLE `patient_password_resets`
  ADD CONSTRAINT `fk_patient_password_reset_patient` FOREIGN KEY (`patient_id`) REFERENCES `patients` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `service_payment_methods`
--
ALTER TABLE `service_payment_methods`
  ADD CONSTRAINT `fk_service_payment_method_service` FOREIGN KEY (`service_id`) REFERENCES `services` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `telemedicine_signals`
--
ALTER TABLE `telemedicine_signals`
  ADD CONSTRAINT `fk_telemedicine_signal_room` FOREIGN KEY (`room_id`) REFERENCES `telemedicine_calls` (`room_id`) ON DELETE CASCADE;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
