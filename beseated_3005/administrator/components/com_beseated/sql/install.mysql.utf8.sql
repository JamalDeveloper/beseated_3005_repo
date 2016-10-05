-- phpMyAdmin SQL Dump
-- version 3.4.10.1deb1
-- http://www.phpmyadmin.net
--
-- Host: localhost
-- Generation Time: Apr 12, 2014 at 04:26 PM
-- Server version: 5.5.35
-- PHP Version: 5.3.10-1ubuntu3.11

SET SQL_MODE="NO_AUTO_VALUE_ON_ZERO";
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8 */;

--
-- Database: `beseated`
--

-- --------------------------------------------------------

--
-- Table structure for table `#__beseated_rating`
--

DROP TABLE IF EXISTS `#__beseated_rating`;
CREATE TABLE IF NOT EXISTS `#__beseated_rating` (
  `rating_id` int(11) NOT NULL AUTO_INCREMENT,
  `user_id` int(11) NOT NULL,
  `element_id` int(11) NOT NULL,
  `element_type` varchar(255) NOT NULL,
  `avg_rating` decimal(6,2) NOT NULL,
  `food_rating` decimal(6,2) NOT NULL,
  `service_rating` decimal(6,2) NOT NULL,
  `atmosphere_rating` decimal(6,2) NOT NULL,
  `value_rating` decimal(6,2) NOT NULL,
  `rating_count` int(11) NOT NULL,
  `rating_comment` varchar(255) NOT NULL,
  `published` int(11) NOT NULL,
  `time_stamp` bigint(20) NOT NULL,
  `created` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  KEY `user_id` (`user_id`),
  KEY `element_id` (`element_id`),
  KEY `element_type` (`element_type`),
  KEY `published` (`published`),
  PRIMARY KEY (`rating_id`)
)
ENGINE=InnoDB
DEFAULT CHARSET=utf8
COMMENT='Store Ratings values for all type of element'
AUTO_INCREMENT=1 ;

-- --------------------------------------------------------

--
-- Table structure for table `#__beseated_payment_status`
--

DROP TABLE IF EXISTS `#__beseated_payment_status`;
CREATE TABLE IF NOT EXISTS `#__beseated_payment_status` (
  `payment_id` int(11) NOT NULL AUTO_INCREMENT,
  `booking_id` int(11) NOT NULL,
  `booking_type` varchar(255) NOT NULL,
  `user_id` int(11) NOT NULL,
  `amount` decimal(6,2) NOT NULL,
  `transaction_id` decimal(6,2) NOT NULL,
  `payment_status` decimal(6,2) NOT NULL,
  `time_stamp` bigint(20) NOT NULL,
  `created` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  KEY `booking_id` (`booking_id`),
  KEY `booking_type` (`booking_type`),
  KEY `user_id` (`user_id`),
  KEY `payment_status` (`payment_status`),
  PRIMARY KEY (`payment_id`)
)
ENGINE=InnoDB
DEFAULT CHARSET=utf8
COMMENT='Store Payment gateway respsonse'
AUTO_INCREMENT=1 ;

-- --------------------------------------------------------

--
-- Table structure for table `#__beseated_message_connection`
--

DROP TABLE IF EXISTS `#__beseated_message_connection`;
CREATE TABLE IF NOT EXISTS `#__beseated_message_connection` (
  `connection_id` int(11) NOT NULL AUTO_INCREMENT,
  `from_user_id` int(11) NOT NULL,
  `to_user_id` int(11) NOT NULL,
  `last_message_id` int(11) NOT NULL,
  `time_stamp` bigint(20) NOT NULL,
  `created` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  KEY `from_user_id` (`from_user_id`),
  KEY `to_user_id` (`to_user_id`),
  PRIMARY KEY (`connection_id`)
)
ENGINE=InnoDB
DEFAULT CHARSET=utf8
COMMENT='Store Message connection of users and elements'
AUTO_INCREMENT=1 ;

-- --------------------------------------------------------

--
-- Table structure for table `#__beseated_message`
--

DROP TABLE IF EXISTS `#__beseated_message`;
CREATE TABLE IF NOT EXISTS `#__beseated_message` (
  `message_id` int(11) NOT NULL AUTO_INCREMENT,
  `connection_id` int(11) NOT NULL,
  `from_user_id` int(11) NOT NULL,
  `to_user_id` int(11) NOT NULL,
  `message_type` varchar(255) NOT NULL,
  `message_body` text NOT NULL,
  `extra_params` text NOT NULL,
  `time_stamp` bigint(20) NOT NULL,
  `created` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  KEY `connection_id` (`connection_id`),
  KEY `from_user_id` (`from_user_id`),
  KEY `to_user_id` (`to_user_id`),
  PRIMARY KEY (`message_id`)
)
ENGINE=InnoDB
DEFAULT CHARSET=utf8
COMMENT='Store Message conversation'
AUTO_INCREMENT=1 ;

-- --------------------------------------------------------

--
-- Table structure for table `#__beseated_favourite`
--

DROP TABLE IF EXISTS `#__beseated_favourite`;
CREATE TABLE IF NOT EXISTS `#__beseated_favourite` (
  `favourite_id` int(11) NOT NULL AUTO_INCREMENT,
  `element_id` int(11) NOT NULL,
  `element_type` varchar(255) NOT NULL,
  `user_id` int(11) NOT NULL,
  `time_stamp` bigint(20) NOT NULL,
  `created` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  KEY `element_id` (`element_id`),
  KEY `element_type` (`element_type`),
  KEY `user_id` (`user_id`),
  PRIMARY KEY (`favourite_id`)
)
ENGINE=InnoDB
DEFAULT CHARSET=utf8
COMMENT='Store Beseated Guest users favourite vanues and companies'
AUTO_INCREMENT=1 ;

-- --------------------------------------------------------

--
-- Table structure for table `#__beseated_black_list`
--

DROP TABLE IF EXISTS `#__beseated_black_list`;
CREATE TABLE IF NOT EXISTS `#__beseated_black_list` (
  `blacklist_id` int(11) NOT NULL AUTO_INCREMENT,
  `element_id` int(11) NOT NULL,
  `element_type` varchar(255) NOT NULL,
  `user_id` int(11) NOT NULL,
  `time_stamp` bigint(20) NOT NULL,
  `created` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  KEY `element_id` (`element_id`),
  KEY `element_type` (`element_type`),
  KEY `user_id` (`user_id`),
  PRIMARY KEY (`blacklist_id`)
)
ENGINE=InnoDB
DEFAULT CHARSET=utf8
COMMENT='Store Beseated vanues and companies black listed beseated guest users'
AUTO_INCREMENT=1 ;

-- --------------------------------------------------------

--
-- Table structure for table `#__beseated_status`
--
DROP TABLE IF EXISTS `#__beseated_status`;
CREATE TABLE IF NOT EXISTS `#__beseated_status` (
  `status_id` int(11) NOT NULL AUTO_INCREMENT,
  `status_name` varchar(255) NOT NULL,
  `status_display` varchar(255) NOT NULL,
  KEY `status_name` (`status_name`),
  PRIMARY KEY (`status_id`)
)
ENGINE=InnoDB
DEFAULT CHARSET=utf8
AUTO_INCREMENT=1;

-- --------------------------------------------------------

--
-- Table structure for table `#__beseated_element_images`
--

DROP TABLE IF EXISTS `#__beseated_element_images`;
CREATE TABLE IF NOT EXISTS `#__beseated_element_images` (
  `image_id` int(11) NOT NULL AUTO_INCREMENT,
  `element_id` int(11) NOT NULL,
  `element_type` varchar(255) NOT NULL,
  `thumb_image` varchar(255) NOT NULL,
  `image` varchar(255) NOT NULL,
  `is_video` int(11) NOT NULL,
  `file_type` varchar(255) NOT NULL,
  `time_stamp` bigint(20) NOT NULL,
  `created` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  KEY `element_id` (`element_id`),
  KEY `element_type` (`element_type`),
  PRIMARY KEY (`image_id`)
)
ENGINE=InnoDB
DEFAULT CHARSET=utf8
COMMENT='Store Beseated vanues and companies mulitple images'
AUTO_INCREMENT=1 ;

-- --------------------------------------------------------

--
-- Table structure for table `#__beseated_chauffeur`
--

DROP TABLE IF EXISTS `#__beseated_chauffeur`;
CREATE TABLE IF NOT EXISTS `#__beseated_chauffeur` (
  `chauffeur_id` int(11) NOT NULL AUTO_INCREMENT,
  `chauffeur_name` varchar(255) NOT NULL,
  `location` varchar(255) NOT NULL,
  `city` varchar(255) NOT NULL,
  `currency_code` varchar(255) NOT NULL,
  `currency_sign` varchar(255) NOT NULL,
  `avg_ratting` decimal(6,2) NOT NULL,
  `latitude` varchar(255) NOT NULL,
  `longitude` varchar(255) NOT NULL,
  `has_service` int(11) NOT NULL,
  `published` int(11) NOT NULL,
  `time_stamp` bigint(20) NOT NULL,
  `created` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  KEY `chauffeur_name` (`chauffeur_name`),
  KEY `published` (`published`),
  PRIMARY KEY (`chauffeur_id`)
)
ENGINE=InnoDB
DEFAULT CHARSET=utf8
COMMENT='Store Beseated Chauffeur details'
AUTO_INCREMENT=1 ;

-- --------------------------------------------------------

--
-- Table structure for table `#__beseated_chauffeur_services`
--

DROP TABLE IF EXISTS `#__beseated_chauffeur_services`;
CREATE TABLE IF NOT EXISTS `#__beseated_chauffeur_services` (
  `service_id` int(11) NOT NULL AUTO_INCREMENT,
  `chauffeur_id` int(11) NOT NULL,
  `service_name` varchar(255) NOT NULL,
  `service_type` varchar(255) NOT NULL,
  `capacity` int(11) NOT NULL,
  `thumb_image` varchar(255) NOT NULL,
  `image` varchar(255) NOT NULL,
  `published` int(11) NOT NULL,
  `time_stamp` bigint(20) NOT NULL,
  `created` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  KEY `chauffeur_id` (`chauffeur_id`),
  KEY `published` (`published`),
  PRIMARY KEY (`service_id`)
)
ENGINE=InnoDB
DEFAULT CHARSET=utf8
COMMENT='Store Beseated Chauffeur services'
AUTO_INCREMENT=1 ;

-- --------------------------------------------------------

--
-- Table structure for table `#__beseated_chauffeur_booking`
--

DROP TABLE IF EXISTS `#__beseated_chauffeur_booking`;
CREATE TABLE IF NOT EXISTS `#__beseated_chauffeur_booking` (
  `chauffeur_booking_id` int(11) NOT NULL AUTO_INCREMENT,
  `chauffeur_id` int(11) NOT NULL,
  `service_id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `booking_date` date NOT NULL,
  `booking_time` varchar(255) NOT NULL,
  `pickup_location` varchar(255) NOT NULL,
  `dropoff_location` varchar(255) NOT NULL,
  `user_status` int(11) NOT NULL,
  `chauffeur_status` int(11) NOT NULL,
  `total_price` decimal(12,2) NOT NULL,
  `booking_currency_code` varchar(255) NOT NULL,
  `booking_currency_sign` varchar(255) NOT NULL,
  `request_date_time` datetime NOT NULL,
  `respone_date_time` datetime NOT NULL,
  `time_stamp` bigint(20) NOT NULL,
  `created` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  KEY `chauffeur_id` (`chauffeur_id`),
  KEY `service_id` (`service_id`),
  KEY `user_id` (`user_id`),
  KEY `booking_date` (`booking_date`),
  KEY `user_status` (`user_status`),
  KEY `chauffeur_status` (`chauffeur_status`),
  PRIMARY KEY (`chauffeur_booking_id`)
)
ENGINE=InnoDB
DEFAULT CHARSET=utf8
COMMENT='Store Beseated Chauffeur services bookings'
AUTO_INCREMENT=1 ;

-- --------------------------------------------------------

--
-- Table structure for table `#__beseated_chauffeur_booking_split`
--

DROP TABLE IF EXISTS `#__beseated_chauffeur_booking_split`;
CREATE TABLE IF NOT EXISTS `#__beseated_chauffeur_booking_split` (
  `chauffeur_booking_split_id` int(11) NOT NULL AUTO_INCREMENT,
  `chauffeur_booking_id` int(11) NOT NULL,
  `chauffeur_id` int(11) NOT NULL,
  `service_id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `splitted_amount` decimal(12,2) NOT NULL,
  `split_payment_status` varchar(255) NOT NULL,
  `time_stamp` bigint(20) NOT NULL,
  `created` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  KEY `chauffeur_booking_id` (`chauffeur_booking_id`),
  KEY `chauffeur_id` (`chauffeur_id`),
  KEY `service_id` (`service_id`),
  KEY `user_id` (`user_id`),
  PRIMARY KEY (`chauffeur_booking_split_id`)
)
ENGINE=InnoDB
DEFAULT CHARSET=utf8
COMMENT='Store Beseated Chauffeur services Split detail'
AUTO_INCREMENT=1 ;

-- --------------------------------------------------------

--
-- Table structure for table `#__beseated_protection`
--

DROP TABLE IF EXISTS `#__beseated_protection`;
CREATE TABLE IF NOT EXISTS `#__beseated_protection` (
  `protection_id` int(11) NOT NULL AUTO_INCREMENT,
  `protection_name` varchar(255) NOT NULL,
  `location` varchar(255) NOT NULL,
  `city` varchar(255) NOT NULL,
  `currency_code` varchar(255) NOT NULL,
  `currency_sign` varchar(255) NOT NULL,
  `avg_ratting` decimal(6,2) NOT NULL,
  `latitude` varchar(255) NOT NULL,
  `longitude` varchar(255) NOT NULL,
  `has_service` int(11) NOT NULL,
  `published` int(11) NOT NULL,
  `time_stamp` bigint(20) NOT NULL,
  `created` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  KEY `protection_name` (`protection_name`),
  KEY `has_service` (`has_service`),
  KEY `published` (`published`),
  PRIMARY KEY (`protection_id`)
)
ENGINE=InnoDB
DEFAULT CHARSET=utf8
COMMENT='Store Beseated Protection company details'
AUTO_INCREMENT=1 ;

-- --------------------------------------------------------

--
-- Table structure for table `#__beseated_protection_services`
--

DROP TABLE IF EXISTS `#__beseated_protection_services`;
CREATE TABLE IF NOT EXISTS `#__beseated_protection_services` (
  `service_id` int(11) NOT NULL AUTO_INCREMENT,
  `protection_id` int(11) NOT NULL,
  `service_name` varchar(255) NOT NULL,
  `price_per_hours` decimal (12,2) NOT NULL,
  `thumb_image` varchar(255) NOT NULL,
  `image` varchar(255) NOT NULL,
  `published` int(11) NOT NULL,
  `time_stamp` bigint(20) NOT NULL,
  `created` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  KEY `protection_id` (`protection_id`),
  KEY `published` (`published`),
  PRIMARY KEY (`service_id`)
)
ENGINE=InnoDB
DEFAULT CHARSET=utf8
COMMENT='Store Beseated Protection services'
AUTO_INCREMENT=1 ;

-- --------------------------------------------------------

--
-- Table structure for table `#__beseated_protection_booking`
--

DROP TABLE IF EXISTS `#__beseated_protection_booking`;
CREATE TABLE IF NOT EXISTS `#__beseated_protection_booking` (
  `protection_booking_id` int(11) NOT NULL AUTO_INCREMENT,
  `protection_id` int(11) NOT NULL,
  `service_id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `booking_date` date NOT NULL,
  `booking_time` varchar(255) NOT NULL,
  `meetup_location` varchar(255) NOT NULL,
  `total_guest` int(11) NOT NULL,
  `male_guest` int(11) NOT NULL,
  `female_guest` int(11) NOT NULL,
  `total_hours` int(11) NOT NULL,
  `price_per_hours` decimal(12,2) NOT NULL,
  `total_price` decimal(12,2) NOT NULL,
  `user_status` int(11) NOT NULL,
  `protection_status` int(11) NOT NULL,
  `booking_currency_code` varchar(255) NOT NULL,
  `booking_currency_sign` varchar(255) NOT NULL,
  `request_date_time` datetime NOT NULL,
  `respone_date_time` datetime NOT NULL,
  `time_stamp` bigint(20) NOT NULL,
  `created` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  KEY `protection_id` (`protection_id`),
  KEY `service_id` (`service_id`),
  KEY `user_id` (`user_id`),
  KEY `booking_date` (`booking_date`),
  KEY `user_status` (`user_status`),
  KEY `protection_status` (`protection_status`),
  PRIMARY KEY (`protection_booking_id`)
)
ENGINE=InnoDB
DEFAULT CHARSET=utf8
COMMENT='Store Beseated Protection services bookings'
AUTO_INCREMENT=1 ;

-- --------------------------------------------------------

--
-- Table structure for table `#__beseated_protection_booking_split`
--

DROP TABLE IF EXISTS `#__beseated_protection_booking_split`;
CREATE TABLE IF NOT EXISTS `#__beseated_protection_booking_split` (
  `protection_booking_split_id` int(11) NOT NULL AUTO_INCREMENT,
  `protection_booking_id` int(11) NOT NULL,
  `protection_id` int(11) NOT NULL,
  `service_id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `splitted_amount` varchar(255) NOT NULL,
  `split_payment_status` varchar(255) NOT NULL,
  `time_stamp` bigint(20) NOT NULL,
  `created` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  KEY `protection_booking_id` (`protection_booking_id`),
  KEY `protection_id` (`protection_id`),
  KEY `service_id` (`service_id`),
  KEY `user_id` (`user_id`),
  PRIMARY KEY (`protection_booking_split_id`)
)
ENGINE=InnoDB
DEFAULT CHARSET=utf8
COMMENT='Store Beseated Protection services Split detail'
AUTO_INCREMENT=1 ;

-- --------------------------------------------------------

--
-- Table structure for table `#__beseated_venue`
--

DROP TABLE IF EXISTS `#__beseated_venue`;
CREATE TABLE IF NOT EXISTS `#__beseated_venue` (
  `venue_id` int(11) NOT NULL AUTO_INCREMENT,
  `venue_name` varchar(255) NOT NULL,
  `description` text NOT NULL,
  `working_days` varchar(255) NOT NULL,
  `music` varchar(255) NOT NULL,
  `atmosphere` varchar(255) NOT NULL,
  `location` varchar(255) NOT NULL,
  `city` varchar(255) NOT NULL,
  `currency_code` varchar(255) NOT NULL,
  `currency_sign` varchar(255) NOT NULL,
  `avg_ratting` decimal(6,2) NOT NULL,
  `latitude` varchar(255) NOT NULL,
  `longitude` varchar(255) NOT NULL,
  `has_table` int(11) NOT NULL,
  `published` int(11) NOT NULL,
  `time_stamp` bigint(20) NOT NULL,
  `created` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  KEY `venue_name` (`venue_name`),
  KEY `has_table` (`has_table`),
  KEY `published` (`published`),
  PRIMARY KEY (`venue_id`)
)
ENGINE=InnoDB
DEFAULT CHARSET=utf8
COMMENT='Store Beseated Venue details'
AUTO_INCREMENT=1 ;

-- --------------------------------------------------------

--
-- Table structure for table `#__beseated_venue_table`
--

DROP TABLE IF EXISTS `#__beseated_venue_table`;
CREATE TABLE IF NOT EXISTS `#__beseated_venue_table` (
  `table_id` int(11) NOT NULL AUTO_INCREMENT,
  `venue_id` int(11) NOT NULL,
  `table_name` varchar(255) NOT NULL,
  `table_type` varchar(255) NOT NULL,
  `premium_table_id` int(11) NOT NULL,
  `min_price` decimal (12,2) NOT NULL,
  `capacity` int(11) NOT NULL,
  `thumb_image` varchar(255) NOT NULL,
  `image` varchar(255) NOT NULL,
  `published` int(11) NOT NULL,
  `time_stamp` bigint(20) NOT NULL,
  `created` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  KEY `venue_id` (`venue_id`),
  KEY `published` (`published`),
  PRIMARY KEY (`table_id`)
)
ENGINE=InnoDB
DEFAULT CHARSET=utf8
COMMENT='Store Beseated Venue tables'
AUTO_INCREMENT=1 ;

-- --------------------------------------------------------

--
-- Table structure for table `#__beseated_venue_bottle`
--

DROP TABLE IF EXISTS `#__beseated_venue_bottle`;
CREATE TABLE IF NOT EXISTS `#__beseated_venue_bottle` (
  `bottle_id` int(11) NOT NULL AUTO_INCREMENT,
  `venue_id` int(11) NOT NULL,
  `brand_name` varchar(255) NOT NULL,
  `size` varchar(255) NOT NULL,
  `price` decimal (12,2) NOT NULL,
  `thumb_image` varchar(255) NOT NULL,
  `image` varchar(255) NOT NULL,
  `published` int(11) NOT NULL,
  `time_stamp` bigint(20) NOT NULL,
  `created` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  KEY `venue_id` (`venue_id`),
  KEY `published` (`published`),
  PRIMARY KEY (`bottle_id`)
)
ENGINE=InnoDB
DEFAULT CHARSET=utf8
COMMENT='Store Beseated Venue Bottle details'
AUTO_INCREMENT=1 ;

-- --------------------------------------------------------

--
-- Table structure for table `#__beseated_venue_table_booking`
--

DROP TABLE IF EXISTS `#__beseated_venue_table_booking`;
CREATE TABLE IF NOT EXISTS `#__beseated_venue_table_booking` (
  `venue_table_booking_id` int(11) NOT NULL AUTO_INCREMENT,
  `venue_id` int(11) NOT NULL,
  `table_id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `booking_date` date NOT NULL,
  `booking_time` varchar(255) NOT NULL,
  `privacy` varchar(255) NOT NULL,
  `passkey` varchar(255) NOT NULL,
  `total_guest` int(11) NOT NULL,
  `male_guest` int(11) NOT NULL,
  `female_guest` int(11) NOT NULL,
  `total_hours` int(11) NOT NULL,
  `total_price` decimal(12,2) NOT NULL,
  `user_status` int(11) NOT NULL,
  `venue_status` int(11) NOT NULL,
  `booking_currency_code` varchar(255) NOT NULL,
  `booking_currency_sign` varchar(255) NOT NULL,
  `booking_user_paid` int(11) NOT NULL,
  `has_bottle` int(11) NOT NULL,
  `total_bottle_price` decimal(12,2) NOT NULL,
  `final_price` decimal(12,2) NOT NULL,
  `request_date_time` datetime NOT NULL,
  `respone_date_time` datetime NOT NULL,
  `time_stamp` bigint(20) NOT NULL,
  `created` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  KEY `venue_id` (`venue_id`),
  KEY `table_id` (`table_id`),
  KEY `user_id` (`user_id`),
  KEY `booking_date` (`booking_date`),
  KEY `user_status` (`user_status`),
  KEY `venue_status` (`venue_status`),
  PRIMARY KEY (`venue_table_booking_id`)
)
ENGINE=InnoDB
DEFAULT CHARSET=utf8
COMMENT='Store Beseated Venue table bookings'
AUTO_INCREMENT=1 ;

-- --------------------------------------------------------

--
-- Table structure for table `#__beseated_venue_bottle_booking`
--

DROP TABLE IF EXISTS `#__beseated_venue_bottle_booking`;
CREATE TABLE IF NOT EXISTS `#__beseated_venue_bottle_booking` (
  `venue_bottle_booking_id` int(11) NOT NULL AUTO_INCREMENT,
  `bottle_id` int(11) NOT NULL,
  `venue_table_booking_id` int(11) NOT NULL,
  `venue_id` int(11) NOT NULL,
  `table_id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `qty` int(11) NOT NULL,
  `price` decimal(12,2) NOT NULL,
  `total_price` decimal(12,2) NOT NULL,
  `time_stamp` bigint(20) NOT NULL,
  `created` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  KEY `bottle_id` (`bottle_id`),
  KEY `venue_table_booking_id` (`venue_table_booking_id`),
  KEY `venue_id` (`venue_id`),
  KEY `table_id` (`table_id`),
  KEY `user_id` (`user_id`),
  PRIMARY KEY (`venue_bottle_booking_id`)
)
ENGINE=InnoDB
DEFAULT CHARSET=utf8
COMMENT='Store Beseated Venue Bottle bookings'
AUTO_INCREMENT=1 ;

-- --------------------------------------------------------

--
-- Table structure for table `#__beseated_venue_booking_split`
--

DROP TABLE IF EXISTS `#__beseated_venue_booking_split`;
CREATE TABLE IF NOT EXISTS `#__beseated_venue_booking_split` (
  `venue_booking_split_id` int(11) NOT NULL AUTO_INCREMENT,
  `venue_table_booking_id` int(11) NOT NULL,
  `venue_id` int(11) NOT NULL,
  `table_id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `splitted_amount` decimal(12,2) NOT NULL,
  `split_payment_status` varchar(255) NOT NULL,
  `time_stamp` bigint(20) NOT NULL,
  `created` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  KEY `venue_table_booking_id` (`venue_table_booking_id`),
  KEY `venue_id` (`venue_id`),
  KEY `table_id` (`table_id`),
  KEY `user_id` (`user_id`),
  PRIMARY KEY (`venue_booking_split_id`)
)
ENGINE=InnoDB
DEFAULT CHARSET=utf8
COMMENT='Store Beseated Venue table Split detail'
AUTO_INCREMENT=1 ;

-- --------------------------------------------------------

--
-- Table structure for table `#__beseated_yacht`
--

DROP TABLE IF EXISTS `#__beseated_yacht`;
CREATE TABLE IF NOT EXISTS `#__beseated_yacht` (
  `yacht_id` int(11) NOT NULL AUTO_INCREMENT,
  `yacht_name` varchar(255) NOT NULL,
  `location` varchar(255) NOT NULL,
  `city` varchar(255) NOT NULL,
  `currency_code` varchar(255) NOT NULL,
  `currency_sign` varchar(255) NOT NULL,
  `avg_ratting` decimal(6,2) NOT NULL,
  `latitude` varchar(255) NOT NULL,
  `longitude` varchar(255) NOT NULL,
  `has_service` int(11) NOT NULL,
  `published` int(11) NOT NULL,
  `time_stamp` bigint(20) NOT NULL,
  `created` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  KEY `yacht_name` (`yacht_name`),
  KEY `has_service` (`has_service`),
  KEY `published` (`published`),
  PRIMARY KEY (`yacht_id`)
)
ENGINE=InnoDB
DEFAULT CHARSET=utf8
COMMENT='Store Beseated Yacht company details'
AUTO_INCREMENT=1 ;

-- --------------------------------------------------------

--
-- Table structure for table `#__beseated_yacht_services`
--

DROP TABLE IF EXISTS `#__beseated_yacht_services`;
CREATE TABLE IF NOT EXISTS `#__beseated_yacht_services` (
  `service_id` int(11) NOT NULL AUTO_INCREMENT,
  `yacht_id` int(11) NOT NULL,
  `service_name` varchar(255) NOT NULL,
  `service_type` varchar(255) NOT NULL,
  `dock` varchar(255) NOT NULL,
  `price_per_hours` decimal (12,2) NOT NULL,
  `capacity` int(11) NOT NULL,
  `thumb_image` varchar(255) NOT NULL,
  `image` varchar(255) NOT NULL,
  `published` int(11) NOT NULL,
  `time_stamp` bigint(20) NOT NULL,
  `created` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  KEY `yacht_id` (`yacht_id`),
  KEY `published` (`published`),
  PRIMARY KEY (`service_id`)
)
ENGINE=InnoDB
DEFAULT CHARSET=utf8
COMMENT='Store Beseated Yacht services details'
AUTO_INCREMENT=1 ;

-- --------------------------------------------------------

--
-- Table structure for table `#__beseated_yacht_booking`
--

DROP TABLE IF EXISTS `#__beseated_yacht_booking`;
CREATE TABLE IF NOT EXISTS `#__beseated_yacht_booking` (
  `yacht_booking_id` int(11) NOT NULL AUTO_INCREMENT,
  `yacht_id` int(11) NOT NULL,
  `service_id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `booking_date` date NOT NULL,
  `booking_time` varchar(255) NOT NULL,
  `total_hours` int(11) NOT NULL,
  `price_per_hours` decimal(12,2) NOT NULL,
  `total_price` decimal(12,2) NOT NULL,
  `user_status` int(11) NOT NULL,
  `yacht_status` int(11) NOT NULL,
  `booking_currency_code` varchar(255) NOT NULL,
  `booking_currency_sign` varchar(255) NOT NULL,
  `request_date_time` datetime NOT NULL,
  `respone_date_time` datetime NOT NULL,
  `time_stamp` bigint(20) NOT NULL,
  `created` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  KEY `yacht_id` (`yacht_id`),
  KEY `service_id` (`service_id`),
  KEY `user_id` (`user_id`),
  KEY `booking_date` (`booking_date`),
  KEY `user_status` (`user_status`),
  KEY `yacht_status` (`yacht_status`),
  PRIMARY KEY (`yacht_booking_id`)
)
ENGINE=InnoDB
DEFAULT CHARSET=utf8
COMMENT='Store Beseated Yacht services bookings'
AUTO_INCREMENT=1 ;

-- --------------------------------------------------------

--
-- Table structure for table `#__beseated_yacht_booking_split`
--

DROP TABLE IF EXISTS `#__beseated_yacht_booking_split`;
CREATE TABLE IF NOT EXISTS `#__beseated_yacht_booking_split` (
  `yatch_booking_split_id` int(11) NOT NULL AUTO_INCREMENT,
  `yacht_booking_id` int(11) NOT NULL,
  `yacht_id` int(11) NOT NULL,
  `service_id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `splitted_amount` decimal(12,2) NOT NULL,
  `split_payment_status` varchar(255) NOT NULL,
  `time_stamp` bigint(20) NOT NULL,
  `created` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  KEY `yacht_booking_id` (`yacht_booking_id`),
  KEY `yacht_id` (`yacht_id`),
  KEY `service_id` (`service_id`),
  KEY `user_id` (`user_id`),
  PRIMARY KEY (`yatch_booking_split_id`)
)
ENGINE=InnoDB
DEFAULT CHARSET=utf8
COMMENT='Store Beseated Yacht service bookings Split detail'
AUTO_INCREMENT=1 ;

-- --------------------------------------------------------

--
-- Table structure for table `#__beseated_private_jet`
--

DROP TABLE IF EXISTS `#__beseated_private_jet`;
CREATE TABLE IF NOT EXISTS `#__beseated_private_jet` (
  `private_jet_id` int(11) NOT NULL AUTO_INCREMENT,
  `company_name` varchar(255) NOT NULL,
  `location` varchar(255) NOT NULL,
  `city` varchar(255) NOT NULL,
  `avg_ratting` decimal(6,2) NOT NULL,
  `latitude` varchar(255) NOT NULL,
  `longitude` varchar(255) NOT NULL,
  `published` int(11) NOT NULL,
  `time_stamp` bigint(20) NOT NULL,
  `created` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  KEY `company_name` (`company_name`),
  KEY `published` (`published`),
  PRIMARY KEY (`private_jet_id`)
)
ENGINE=InnoDB
DEFAULT CHARSET=utf8
COMMENT='Store Beseated Private jet informations'
AUTO_INCREMENT=1 ;

-- --------------------------------------------------------

--
-- Table structure for table `#__beseated_private_jet_booking`
--

DROP TABLE IF EXISTS `#__beseated_private_jet_booking`;
CREATE TABLE IF NOT EXISTS `#__beseated_private_jet_booking` (
  `private_jet_booking_id` int(11) NOT NULL AUTO_INCREMENT,
  `private_jet_id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `flight_date` date NOT NULL,
  `flight_time` varchar(255) NOT NULL,
  `from_location` varchar(255) NOT NULL,
  `to_location` varchar(255) NOT NULL,
  `total_guest` int(11) NOT NULL,
  `male_guest` int(11) NOT NULL,
  `female_guest` int(11) NOT NULL,
  `person_name` varchar(255) NOT NULL,
  `email` varchar(255) NOT NULL,
  `phone`varchar(255) NOT NULL,
  `extra_information` text NOT NULL,
  `time_stamp` bigint(20) NOT NULL,
  `created` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  KEY `private_jet_id` (`private_jet_id`),
  KEY `user_id` (`user_id`),
  KEY `flight_date` (`flight_date`),
  PRIMARY KEY (`private_jet_booking_id`)
)
ENGINE=InnoDB
DEFAULT CHARSET=utf8
COMMENT='Store Beseated Private jet bookings'
AUTO_INCREMENT=1 ;

-- --------------------------------------------------------

--
-- Table structure for table `#__beseated_event`
--

DROP TABLE IF EXISTS `#__beseated_event`;
CREATE TABLE IF NOT EXISTS `#__beseated_event` (
  `event_id` int(11) NOT NULL AUTO_INCREMENT,
  `event_name` varchar(255) NOT NULL,
  `location` varchar(255) NOT NULL,
  `city` varchar(255) NOT NULL,
  `event_date` date NOT NULL,
  `event_time` varchar(255) NOT NULL,
  `currency_code` varchar(255) NOT NULL,
  `currency_sign` varchar(255) NOT NULL,
  `latitude` varchar(255) NOT NULL,
  `longitude` varchar(255) NOT NULL,
  `published` int(11) NOT NULL,
  `time_stamp` bigint(20) NOT NULL,
  `created` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  KEY `event_name` (`event_name`),
  KEY `event_date` (`event_date`),
  KEY `published` (`published`),
  PRIMARY KEY (`event_id`)
)
ENGINE=InnoDB
DEFAULT CHARSET=utf8
COMMENT='Store Beseated Events detail'
AUTO_INCREMENT=1 ;

-- --------------------------------------------------------

--
-- Table structure for table `#__beseated_event_ticket`
--

DROP TABLE IF EXISTS `#__beseated_event_ticket`;
CREATE TABLE IF NOT EXISTS `#__beseated_event_ticket` (
  `ticket_id` int(11) NOT NULL AUTO_INCREMENT,
  `event_id` int(11) NOT NULL,
  `tickte_name` varchar(255) NOT NULL,
  `total_ticket` int(11) NOT NULL,
  `available_ticket` int(11) NOT NULL,
  `tickte_price` decimal(12,2) NOT NULL,
  `published` int(11) NOT NULL,
  `time_stamp` bigint(20) NOT NULL,
  `created` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  KEY `event_id` (`event_id`),
  KEY `published` (`published`),
  PRIMARY KEY (`ticket_id`)
)
ENGINE=InnoDB
DEFAULT CHARSET=utf8
COMMENT='Store Beseated Event tickets'
AUTO_INCREMENT=1 ;

-- --------------------------------------------------------

--
-- Table structure for table `#__beseated_event_ticket_booking`
--

DROP TABLE IF EXISTS `#__beseated_event_ticket_booking`;
CREATE TABLE IF NOT EXISTS `#__beseated_event_ticket_booking` (
  `event_ticket_booking_id` int(11) NOT NULL AUTO_INCREMENT,
  `ticket_id` int(11) NOT NULL,
  `event_id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `booking_currency_code` varchar(255) NOT NULL,
  `booking_currency_sign` varchar(255) NOT NULL,
  `qty` int(11) NOT NULL,
  `ticket_price` decimal(12,2) NOT NULL,
  `total_price` decimal(12,2) NOT NULL,
  `time_stamp` bigint(20) NOT NULL,
  `created` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  KEY `event_id` (`event_id`),
  KEY `ticket_id` (`ticket_id`),
  KEY `user_id` (`user_id`),
  PRIMARY KEY (`event_ticket_booking_id`)
)
ENGINE=InnoDB
DEFAULT CHARSET=utf8
COMMENT='Store Beseated Event ticket bookings'
AUTO_INCREMENT=1 ;

-- --------------------------------------------------------

--
-- Table structure for table `#__beseated_event_ticket_booking_invite`
--

DROP TABLE IF EXISTS `#__beseated_event_ticket_booking_invite`;
CREATE TABLE IF NOT EXISTS `#__beseated_event_ticket_booking_invite` (
  `invite_id` int(11) NOT NULL AUTO_INCREMENT,
  `event_ticket_booking_id` int(11) NOT NULL,
  `ticket_id` int(11) NOT NULL,
  `event_id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `invited_user_id` int(11) NOT NULL,
  `invited_email` varchar(255) NOT NULL,
  `invited_fb_id` varchar(255) NOT NULL,
  `time_stamp` bigint(20) NOT NULL,
  `created` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  KEY `event_ticket_booking_id` (`event_ticket_booking_id`),
  KEY `ticket_id` (`ticket_id`),
  KEY `event_id` (`event_id`),
  KEY `user_id` (`user_id`),
  PRIMARY KEY (`invite_id`)
)
ENGINE=InnoDB
DEFAULT CHARSET=utf8
COMMENT='Store Beseated Event ticket booking invite'
AUTO_INCREMENT=1 ;

-- --------------------------------------------------------

--
-- Table structure for table `#__beseated_user_profile`
--

DROP TABLE IF EXISTS `#__beseated_user_profile`;
CREATE TABLE IF NOT EXISTS `#__beseated_user_profile` (
  `user_id` int(11) NOT NULL,
  `full_name` varchar(255) NOT NULL,
  `email`  varchar(255) NOT NULL,
  `phone`  varchar(255) NOT NULL,
  `birthdate` date NOT NULL,
  `city` varchar(255) NOT NULL,
  `latitude` varchar(255) NOT NULL,
  `longitude` varchar(255) NOT NULL,
  `is_fb_user` int(11) NOT NULL,
  `fb_id` varchar(255) NOT NULL,
  `is_deleted` int(11) NOT NULL,
  `time_stamp` bigint(20) NOT NULL,
  `created` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  KEY `email` (`email`),
  KEY `fb_id` (`fb_id`),
  KEY `is_fb_user` (`is_fb_user`),
  KEY `is_deleted` (`is_deleted`),
  PRIMARY KEY (`user_id`)
)
ENGINE=InnoDB
DEFAULT CHARSET=utf8
COMMENT='Store Beseated Guest users'
AUTO_INCREMENT=1 ;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
