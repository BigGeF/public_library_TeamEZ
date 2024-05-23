-- phpMyAdmin SQL Dump
-- version 3.3.8.1
-- http://www.phpmyadmin.net
--
-- Host: localhost
-- Generation Time: Oct 28, 2014 at 11:13 AM
-- Server version: 5.1.73
-- PHP Version: 5.3.2-1ubuntu4.27

SET SQL_MODE="NO_AUTO_VALUE_ON_ZERO";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8 */;

--
-- Database: `library`
--

-- Drop existing tables

DROP TABLE IF EXISTS books;
DROP TABLE IF EXISTS magazines;
DROP TABLE IF EXISTS users;


-- --------------------------------------------------------

--
-- Table structure for table `users`
--

CREATE TABLE IF NOT EXISTS `users` (
  `id` int(3) NOT NULL AUTO_INCREMENT,
  `role` int(1) DEFAULT 0,
  `first` varchar(30) DEFAULT NULL,
  `last` varchar(30) DEFAULT NULL,
  `email` varchar(30) DEFAULT NULL,
  `password` varchar(250) DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

--
-- Dumping data for table `users`
--

INSERT INTO `users` (`id`, `role`, `first`, `last`, `email`, `password`) VALUES
(101, 0, 'John', 'Doe', 'john.doe@example.com', 'password123'),
(102, 0, 'Jane', 'Smith', 'jane.smith@example.com', 'password456'),
(103, 1, 'Alice', 'Johnson', 'alice.johnson@example.com', 'password789'),
(104, 0, 'Bob', 'Brown', 'bob.brown@example.com', 'password101'),
(105, 1, 'Charlie', 'Davis', 'charlie.davis@example.com', 'password202'),
(106, 0, 'David', 'Miller', 'david.miller@example.com', 'password303'),
(107, 0, 'Eva', 'Garcia', 'eva.garcia@example.com', 'password404'),
(108, 1, 'Frank', 'Martinez', 'frank.martinez@example.com', 'password505'),
(109, 0, 'Grace', 'Wilson', 'grace.wilson@example.com', 'password606'),
(110, 0, 'Henry', 'Taylor', 'henry.taylor@example.com', 'password707');



-- --------------------------------------------------------

--
-- Table structure for table `books`
--

CREATE TABLE IF NOT EXISTS `books` (
  `id` int(3) NOT NULL AUTO_INCREMENT,
  `title` varchar(50) DEFAULT NULL,
  `description` varchar(250) DEFAULT NULL,
  `available` BOOLEAN DEFAULT TRUE,
  `publishedDate` DATE DEFAULT NULL,
  `borrowedDate` DATE DEFAULT NULL,
  `returnDate` DATE DEFAULT NULL,
  `user_id` int(3) DEFAULT NULL,
  `author` varchar(60) DEFAULT NULL,
  `pages` int(4) DEFAULT NULL,
  `isbn` bigint(13) DEFAULT NULL,
  `cover` varchar(500) DEFAULT NULL,
  PRIMARY KEY (`id`),
  FOREIGN KEY (`user_id`) REFERENCES users(`id`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

--
-- Dumping data for table `books`
--

INSERT INTO `books` (`id`, `title`, `description`, `available`, `publishedDate`, `borrowedDate`, `returnDate`, `user_id`, `author`, `pages`, `isbn`, `cover`) VALUES
(1, 'The Great Gatsby', 'A novel set in the Roaring Twenties', TRUE, '1925-04-10', NULL, NULL, NULL, 'F. Scott Fitzgerald', 218, 9780743273565, 'https://example.com/covers/gatsby.jpg'),
(2, '1984', 'Dystopian social science fiction novel', FALSE, '1949-06-08', '2024-05-01', '2024-05-15', 101, 'George Orwell', 328, 9780451524935, 'https://example.com/covers/1984.jpg'),
(3, 'To Kill a Mockingbird', 'A novel about racial injustice', TRUE, '1960-07-11', NULL, NULL, NULL, 'Harper Lee', 281, 9780060935467, 'https://example.com/covers/mockingbird.jpg'),
(4, 'Pride and Prejudice', 'A romantic novel of manners', TRUE, '1813-01-28', NULL, NULL, NULL, 'Jane Austen', 279, 9781503290563, 'https://example.com/covers/pride.jpg'),
(5, 'The Catcher in the Rye', 'A novel about teenage rebellion', FALSE, '1951-07-16', '2024-04-20', '2024-05-05', 102, 'J.D. Salinger', 214, 9780316769488, 'https://example.com/covers/catcher.jpg'),
(6, 'The Hobbit', 'Fantasy novel', TRUE, '1937-09-21', NULL, NULL, NULL, 'J.R.R. Tolkien', 310, 9780547928227, 'https://example.com/covers/hobbit.jpg'),
(7, 'Fahrenheit 451', 'Dystopian novel about book burning', FALSE, '1953-10-19', '2024-05-10', '2024-05-25', 103, 'Ray Bradbury', 194, 9781451673319, 'https://example.com/covers/fahrenheit.jpg'),
(8, 'Jane Eyre', 'A novel about an orphaned girl', TRUE, '1847-10-16', NULL, NULL, NULL, 'Charlotte Brontë', 500, 9780141441146, 'https://example.com/covers/jane.jpg'),
(9, 'Brave New World', 'Dystopian novel about a futuristic society', TRUE, '1932-08-30', NULL, NULL, NULL, 'Aldous Huxley', 268, 9780060850524, 'https://example.com/covers/brave.jpg'),
(10, 'Moby-Dick', 'A novel about the voyage of the whaling ship Pequod', FALSE, '1851-11-14', '2024-04-01', '2024-04-20', 104, 'Herman Melville', 635, 9781503280786, 'https://example.com/covers/mobydick.jpg');

-- --------------------------------------------------------

--
-- Table structure for table `magazines`
--

CREATE TABLE IF NOT EXISTS `magazines` (
  `id` int(3) NOT NULL AUTO_INCREMENT,
  `title` varchar(50) DEFAULT NULL,
  `description` varchar(250) DEFAULT NULL,
  `available` BOOLEAN DEFAULT TRUE,
  `publishedDate` DATE DEFAULT NULL,
  `borrowedDate` DATE DEFAULT NULL,
  `returnDate` DATE DEFAULT NULL,
  `user_id` int(3) DEFAULT NULL,
  `pages` int(4) DEFAULT NULL,
  `cover` varchar(500) DEFAULT NULL,
  PRIMARY KEY (`id`),
  FOREIGN KEY (`user_id`) REFERENCES users(`id`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

--
-- Dumping data for table `magazines`
--

INSERT INTO `magazines` (`id`, `title`, `description`, `available`, `publishedDate`, `borrowedDate`, `returnDate`, `user_id`, `pages`, `cover`) VALUES
(1, 'National Geographic', 'A monthly magazine about science, geography, history, and world culture', TRUE, '2024-05-01', NULL, NULL, 101, 120, 'https://example.com/covers/natgeo.jpg'),
(2, 'TIME', 'A weekly news magazine', FALSE, '2024-04-28', '2024-05-10', '2024-05-20', 102, 75, 'https://example.com/covers/time.jpg'),
(3, 'The Economist', 'A weekly magazine focusing on current affairs, international business, politics, technology, and culture', TRUE, '2024-05-15', NULL, NULL, 103, 95, 'https://example.com/covers/economist.jpg'),
(4, 'Forbes', 'Bi-weekly business magazine', TRUE, '2024-05-05', NULL, NULL, 104, 85, 'https://example.com/covers/forbes.jpg'),
(5, 'Scientific American', 'Monthly magazine bringing scientific discoveries and research to the public', FALSE, '2024-04-20', '2024-04-30', '2024-05-15', 105, 110, 'https://example.com/covers/scientific.jpg'),
(6, 'Popular Science', 'Monthly magazine on current science and technology news', TRUE, '2024-05-10', NULL, NULL, 106, 90, 'https://example.com/covers/popularscience.jpg'),
(7, 'Vogue', 'Monthly fashion and lifestyle magazine', FALSE, '2024-04-15', '2024-05-01', '2024-05-10', 107, 150, 'https://example.com/covers/vogue.jpg'),
(8, 'National Geographic Traveler', 'A bi-monthly travel magazine', TRUE, '2024-05-12', NULL, NULL, 108, 130, 'https://example.com/covers/natgeotravel.jpg'),
(9, 'Wired', 'Monthly magazine focusing on how emerging technologies affect culture, the economy, and politics', TRUE, '2024-05-08', NULL, NULL, 109, 80, 'https://example.com/covers/wired.jpg'),
(10, 'Fortune', 'Bi-weekly magazine covering business, investing, technology, and management', FALSE, '2024-04-25', '2024-05-05', '2024-05-18', 110, 100, 'https://example.com/covers/fortune.jpg');


