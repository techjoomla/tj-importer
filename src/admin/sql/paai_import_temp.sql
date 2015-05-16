-- phpMyAdmin SQL Dump
-- version 3.4.10.1deb1
-- http://www.phpmyadmin.net
--
-- Host: localhost
-- Generation Time: May 16, 2015 at 05:03 PM
-- Server version: 5.5.43
-- PHP Version: 5.3.10-1ubuntu3.18

SET SQL_MODE="NO_AUTO_VALUE_ON_ZERO";
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8 */;

--
-- Database: `osian`
--

-- --------------------------------------------------------

--
-- Table structure for table `paai_import_temp`
--

CREATE TABLE IF NOT EXISTS `paai_import_temp` (
  `id` int(10) NOT NULL AUTO_INCREMENT,
  `data` text NOT NULL,
  `title` varchar(250) NOT NULL,
  `validated` tinyint(1) NOT NULL,
  `imported` tinyint(1) NOT NULL,
  `batch_id` int(10) NOT NULL,
  `content_id` varchar(25) NOT NULL,
  `invalid_columns` text NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB  DEFAULT CHARSET=latin1 AUTO_INCREMENT=87 ;


