-- This is run to initialize an empty database

-- phpMyAdmin SQL Dump
-- version 4.0.10deb1
-- http://www.phpmyadmin.net
--
-- Host: localhost
-- Generation Time: Sep 11, 2016 at 02:20 AM
-- Server version: 5.5.50-0ubuntu0.14.04.1
-- PHP Version: 5.5.9-1ubuntu4.19

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
SET time_zone = "+00:00";

--
-- Database: `ht`
--

-- --------------------------------------------------------

--
-- Table structure for table `email`
--

CREATE TABLE IF NOT EXISTS `email` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `website_name` varchar(100) NOT NULL,
  `smtp_server` varchar(100) NOT NULL,
  `smtp_port` int(10) NOT NULL,
  `email_login` varchar(150) NOT NULL,
  `email_pass` varchar(100) NOT NULL,
  `from_name` varchar(100) NOT NULL,
  `from_email` varchar(150) NOT NULL,
  `transport` varchar(255) NOT NULL,
  `verify_url` varchar(255) NOT NULL,
  `email_act` int(1) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 AUTO_INCREMENT=2 ;

-- --------------------------------------------------------

--
-- Table structure for table `ht_images`
--

CREATE TABLE IF NOT EXISTS `ht_images` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `ht_job_id` int(11) NOT NULL,
  `side` tinyint(4) NOT NULL,
  `image_type` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `bucket_name` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `key_name` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `image_url` text COLLATE utf8_unicode_ci NOT NULL,
  `image_height` int(11) NOT NULL,
  `image_width` int(11) NOT NULL,
  `modified_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `ht_job_id` (`ht_job_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci AUTO_INCREMENT=1 ;

-- --------------------------------------------------------

--
-- Table structure for table `ht_jobs`
--

CREATE TABLE IF NOT EXISTS `ht_jobs` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `client_id` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `profile_id` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `is_initialized` tinyint(4) NOT NULL DEFAULT '0',
  `transcriber_user_id` int(11) DEFAULT NULL,
  `checker_user_id` int(11) DEFAULT NULL,
  `uploaded_at` datetime NOT NULL,
  `created_at` datetime NOT NULL,
  `modified_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `transcribed_at` datetime DEFAULT NULL,
  `checked_at` datetime DEFAULT NULL,
  `uploader_email` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL,
  `uploader_lname` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL,
  `uploader_fname` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `client_id` (`client_id`),
  KEY `profile_id` (`profile_id`),
  KEY `transcriber_user_id` (`transcriber_user_id`),
  KEY `checker_user_id` (`checker_user_id`),
  KEY `transcribed_at` (`transcribed_at`),
  KEY `checked_at` (`checked_at`),
  KEY `uploader_email` (`uploader_email`),
  KEY `is_initialized` (`is_initialized`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci AUTO_INCREMENT=1 ;

-- --------------------------------------------------------

--
-- Table structure for table `ht_waiting`
--

CREATE TABLE IF NOT EXISTS `ht_waiting` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `is_uploaded` int(11) NOT NULL DEFAULT '0' COMMENT 'a non zero means it uploaded',
  `user_id` int(11) NOT NULL COMMENT 'who uploaded',
  `client_id` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `profile_id` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `upload_result` text COLLATE utf8_unicode_ci,
  `front_path` text COLLATE utf8_unicode_ci,
  `front_file_type` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL,
  `front_width` int(11) DEFAULT NULL,
  `front_height` int(11) DEFAULT NULL,
  `back_path` text COLLATE utf8_unicode_ci,
  `back_file_type` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL,
  `back_width` int(11) DEFAULT NULL,
  `back_height` int(11) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `uploaded_at` datetime DEFAULT NULL,
  `uploader_email` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL,
  `uploader_lname` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL,
  `uploader_fname` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `client_id` (`client_id`),
  KEY `user_id` (`user_id`),
  KEY `is_uploaded` (`is_uploaded`),
  KEY `ht_waiting_upload_result` (`upload_result`(50)) COMMENT 'a non null value but uploaded is 0 means an error'
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci AUTO_INCREMENT=58 ;

-- --------------------------------------------------------

--
-- Table structure for table `keys`
--

CREATE TABLE IF NOT EXISTS `keys` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `stripe_ts` varchar(255) NOT NULL,
  `stripe_tp` varchar(255) NOT NULL,
  `stripe_ls` varchar(255) NOT NULL,
  `stripe_lp` varchar(255) NOT NULL,
  `recap_pub` varchar(100) NOT NULL,
  `recap_pri` varchar(100) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 AUTO_INCREMENT=1 ;

-- --------------------------------------------------------

--
-- Table structure for table `pages`
--

CREATE TABLE IF NOT EXISTS `pages` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `page` varchar(100) NOT NULL,
  `private` int(11) NOT NULL DEFAULT '0',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 AUTO_INCREMENT=34 ;

-- --------------------------------------------------------

--
-- Table structure for table `permissions`
--

CREATE TABLE IF NOT EXISTS `permissions` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `name` varchar(150) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 AUTO_INCREMENT=7 ;

-- --------------------------------------------------------

--
-- Table structure for table `permission_page_matches`
--

CREATE TABLE IF NOT EXISTS `permission_page_matches` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `permission_id` int(15) NOT NULL,
  `page_id` int(15) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 AUTO_INCREMENT=21 ;

-- --------------------------------------------------------

--
-- Table structure for table `profiles`
--

CREATE TABLE IF NOT EXISTS `profiles` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `user_id` int(11) NOT NULL,
  `bio` text NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 AUTO_INCREMENT=21 ;

-- --------------------------------------------------------

--
-- Table structure for table `settings`
--

CREATE TABLE IF NOT EXISTS `settings` (
  `id` int(50) NOT NULL AUTO_INCREMENT,
  `recaptcha` int(1) NOT NULL DEFAULT '0',
  `force_ssl` int(1) NOT NULL,
  `login_type` varchar(20) NOT NULL,
  `css_sample` int(1) NOT NULL,
  `us_css1` varchar(255) NOT NULL,
  `us_css2` varchar(255) NOT NULL,
  `us_css3` varchar(255) NOT NULL,
  `css1` varchar(255) NOT NULL,
  `css2` varchar(255) NOT NULL,
  `css3` varchar(255) NOT NULL,
  `site_name` varchar(100) NOT NULL,
  `language` varchar(255) NOT NULL,
  `track_guest` int(1) NOT NULL,
  `site_offline` int(1) NOT NULL,
  `force_pr` int(1) NOT NULL,
  `reserved1` varchar(100) NOT NULL,
  `reserverd2` varchar(100) NOT NULL,
  `custom1` varchar(100) NOT NULL,
  `custom2` varchar(100) NOT NULL,
  `custom3` varchar(100) NOT NULL,
  `website_url` varchar(255) DEFAULT NULL,
  `s3_bucket_name` varchar(255) DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 AUTO_INCREMENT=2 ;

-- --------------------------------------------------------

--
-- Table structure for table `users`
--

CREATE TABLE IF NOT EXISTS `users` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `email` varchar(155) NOT NULL,
  `username` varchar(255) NOT NULL,
  `password` varchar(255) NOT NULL,
  `fname` varchar(255) NOT NULL,
  `lname` varchar(255) NOT NULL,
  `permissions` int(11) NOT NULL,
  `logins` int(100) NOT NULL,
  `account_owner` tinyint(4) NOT NULL DEFAULT '0',
  `account_id` int(11) NOT NULL DEFAULT '0',
  `company` varchar(255) NOT NULL,
  `stripe_cust_id` varchar(255) NOT NULL,
  `billing_phone` varchar(20) NOT NULL,
  `billing_srt1` varchar(255) NOT NULL,
  `billing_srt2` varchar(255) NOT NULL,
  `billing_city` varchar(255) NOT NULL,
  `billing_state` varchar(255) NOT NULL,
  `billing_zip_code` varchar(255) NOT NULL,
  `join_date` datetime NOT NULL,
  `last_login` datetime NOT NULL,
  `email_verified` tinyint(4) NOT NULL DEFAULT '0',
  `vericode` varchar(15) NOT NULL,
  `title` varchar(100) NOT NULL,
  `active` int(1) NOT NULL,
  `custom1` varchar(255) NOT NULL,
  `custom2` varchar(255) NOT NULL,
  `custom3` varchar(255) NOT NULL,
  `custom4` varchar(255) NOT NULL,
  `custom5` varchar(255) NOT NULL,
  PRIMARY KEY (`id`),
  KEY `EMAIL` (`email`) USING BTREE
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 AUTO_INCREMENT=21 ;

-- --------------------------------------------------------

--
-- Table structure for table `users_online`
--

CREATE TABLE IF NOT EXISTS `users_online` (
  `id` int(10) NOT NULL AUTO_INCREMENT,
  `ip` varchar(15) NOT NULL,
  `timestamp` varchar(15) NOT NULL,
  `user_id` int(10) NOT NULL,
  `session` varchar(50) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 AUTO_INCREMENT=28 ;

-- --------------------------------------------------------

--
-- Table structure for table `users_session`
--

CREATE TABLE IF NOT EXISTS `users_session` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `user_id` int(11) NOT NULL,
  `hash` varchar(255) NOT NULL,
  `uagent` text,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 AUTO_INCREMENT=52 ;

-- --------------------------------------------------------

--
-- Table structure for table `user_permission_matches`
--

CREATE TABLE IF NOT EXISTS `user_permission_matches` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `user_id` int(11) NOT NULL,
  `permission_id` int(11) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 AUTO_INCREMENT=110 ;


-- phpMyAdmin SQL Dump
-- version 4.0.10deb1
-- http://www.phpmyadmin.net
--
-- Host: localhost
-- Generation Time: Sep 11, 2016 at 02:22 AM
-- Server version: 5.5.50-0ubuntu0.14.04.1
-- PHP Version: 5.5.9-1ubuntu4.19

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
SET time_zone = "+00:00";

--
-- Database: `ht`
--

--
-- Dumping data for table `pages`
--

INSERT INTO `pages` (`id`, `page`, `private`) VALUES
  (1, 'index.php', 0),
  (2, 'z_us_root.php', 0),
  (3, 'users/account.php', 1),
  (4, 'users/admin.php', 1),
  (5, 'users/admin_page.php', 1),
  (6, 'users/admin_pages.php', 1),
  (7, 'users/admin_permission.php', 1),
  (8, 'users/admin_permissions.php', 1),
  (9, 'users/admin_user.php', 1),
  (10, 'users/admin_users.php', 1),
  (11, 'users/edit_profile.php', 1),
  (12, 'users/email_settings.php', 1),
  (13, 'users/email_test.php', 1),
  (14, 'users/forgot_password.php', 0),
  (15, 'users/forgot_password_reset.php', 0),
  (16, 'users/index.php', 0),
  (17, 'users/init.php', 0),
  (18, 'users/join.php', 0),
  (19, 'users/joinThankYou.php', 0),
  (20, 'users/login.php', 0),
  (21, 'users/logout.php', 0),
  (22, 'users/profile.php', 1),
  (23, 'users/times.php', 0),
  (24, 'users/user_settings.php', 1),
  (25, 'users/verify.php', 0),
  (26, 'users/verify_resend.php', 0),
  (27, 'users/view_all_users.php', 1),
  (28, 'usersc/empty.php', 0),
  (29, 'info.php', 1),
  (30, 'pages/check.php', 1),
  (31, 'pages/status.php', 1),
  (32, 'pages/transcribe.php', 1),
  (33, 'pages/upload.php', 1);

--
-- Dumping data for table `permissions`
--

INSERT INTO `permissions` (`id`, `name`) VALUES
  (1, 'User'),
  (2, 'Administrator'),
  (3, 'Uploader'),
  (4, 'Transcriber'),
  (5, 'Checker');

--
-- Dumping data for table `permission_page_matches`
--

INSERT INTO `permission_page_matches` (`id`, `permission_id`, `page_id`) VALUES
  (2, 2, 27),
  (3, 1, 24),
  (4, 1, 22),
  (5, 2, 13),
  (6, 2, 12),
  (7, 1, 11),
  (8, 2, 10),
  (9, 2, 9),
  (10, 2, 8),
  (11, 2, 7),
  (12, 2, 6),
  (13, 2, 5),
  (14, 2, 4),
  (15, 1, 3),
  (16, 2, 29),
  (17, 3, 33),
  (18, 4, 32),
  (19, 2, 31),
  (20, 5, 30);

--
-- Dumping data for table `settings`
--

INSERT INTO `settings` (`id`, `recaptcha`, `force_ssl`, `login_type`, `css_sample`, `us_css1`, `us_css2`, `us_css3`, `css1`, `css2`, `css3`, `site_name`, `language`, `track_guest`, `site_offline`, `force_pr`, `reserved1`, `reserverd2`, `custom1`, `custom2`, `custom3`, `website_url`, `s3_bucket_name`) VALUES
  (1, 1, 0, '', 1, '../users/css/color_schemes/standard.css', '../users/css/sb-admin-rtl.css', '../users/css/sb-admin.css', '', '', '', 'HT', 'en', 0, 0, 0, '', '', '', '', '', 'http://localhost/ht', 'texdevelopers-test-ht');
