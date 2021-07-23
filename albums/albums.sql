-- phpMyAdmin SQL Dump
-- version 5.1.1
-- https://www.phpmyadmin.net/
--
-- Host: localhost
-- Generation Time: Jul 23, 2021 at 02:51 PM
-- Server version: 10.4.19-MariaDB
-- PHP Version: 8.0.7

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Database: `bands`
--

-- --------------------------------------------------------

--
-- Table structure for table `albums`
--

CREATE TABLE `albums` (
  `id` int(11) NOT NULL,
  `album_title` varchar(255) DEFAULT NULL,
  `date_released` date DEFAULT NULL,
  `label` varchar(255) DEFAULT NULL,
  `picture` varchar(255) DEFAULT ''
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

--
-- Dumping data for table `albums`
--

INSERT INTO `albums` (`id`, `album_title`, `date_released`, `label`, `picture`) VALUES
(1, 'Please Please Me', '1963-03-22', 'Parlophone', 'please-please-me.jpeg'),
(2, 'Introducing... The Beatles', '1964-01-11', 'Vee-Jay', 'introducing-the-beatles.jpeg'),
(3, 'With The Beatles', '1963-11-23', 'Parlophone', 'with-the-beatles.jpeg'),
(4, 'Hard Days Night', '1964-06-26', 'Parlophone', 'hard-days-night.jpg'),
(5, 'Beatles For Sale', '1964-12-04', 'Parlophone', 'beatles-for-sale.jpeg'),
(6, 'Help', '1965-03-08', 'Capitol', 'help.jpeg'),
(7, 'Rubber Soul', '1965-08-13', 'Capitol', 'rubber-soul.jpg'),
(8, 'Revolver', '1966-08-05', 'Parlophone', 'revolver.jpeg'),
(9, 'Sgt. Pepper\'s Lonely Hearts Club Band', '1967-05-26', 'Parlophone', 'sgt-peppers.jpeg'),
(10, 'Magical Mystery Tour', '1967-11-26', 'Parlophone', 'magical-mystery-tour.jpeg'),
(11, 'Yellow Submarine', '1969-01-13', 'Apple', 'yellow-submarine.jpeg'),
(12, 'Abbey Road', '1969-09-26', 'Apple', 'abbey-road.jpeg'),
(13, 'Let It Be', '2021-05-08', 'Apple', 'let-it-be.jpg');

--
-- Indexes for dumped tables
--

--
-- Indexes for table `albums`
--
ALTER TABLE `albums`
  ADD PRIMARY KEY (`id`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `albums`
--
ALTER TABLE `albums`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=14;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
