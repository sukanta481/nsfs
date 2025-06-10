-- phpMyAdmin SQL Dump
-- version 4.9.4
-- https://www.phpmyadmin.net/
--
-- Host: localhost:3306
-- Generation Time: Feb 05, 2021 at 05:19 AM
-- Server version: 10.1.48-MariaDB
-- PHP Version: 7.3.6

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
SET AUTOCOMMIT = 0;
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Database: `workinprogco88_hindustan`
--

-- --------------------------------------------------------

--
-- Table structure for table `tbl_administrator`
--

CREATE TABLE `tbl_administrator` (
  `id` int(11) NOT NULL,
  `adminname` varchar(256) NOT NULL DEFAULT '',
  `adminpassword` varchar(256) NOT NULL DEFAULT '',
  `admin_email` varchar(256) NOT NULL,
  `login_status` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

--
-- Dumping data for table `tbl_administrator`
--

INSERT INTO `tbl_administrator` (`id`, `adminname`, `adminpassword`, `admin_email`, `login_status`) VALUES
(1, 'admin', 'ea14bad49556d3c2df116f775de7bf1a', 'nsdeveloper.2014@gmail.com', 1);

-- --------------------------------------------------------

--
-- Table structure for table `tbl_post`
--

CREATE TABLE `tbl_post` (
  `post_id` int(11) NOT NULL,
  `post_title` varchar(512) COLLATE utf8_unicode_ci NOT NULL,
  `alise` varchar(512) COLLATE utf8_unicode_ci NOT NULL,
  `meta_title` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `meta_keyword` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `meta_desc` text COLLATE utf8_unicode_ci NOT NULL,
  `post_image` varchar(256) COLLATE utf8_unicode_ci NOT NULL,
  `post_image_webp` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `post_date` date NOT NULL,
  `post_srt_desc` text COLLATE utf8_unicode_ci NOT NULL,
  `post_desc` text COLLATE utf8_unicode_ci NOT NULL,
  `post_link` text COLLATE utf8_unicode_ci NOT NULL,
  `show_home` int(11) NOT NULL
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

--
-- Dumping data for table `tbl_post`
--

INSERT INTO `tbl_post` (`post_id`, `post_title`, `alise`, `meta_title`, `meta_keyword`, `meta_desc`, `post_image`, `post_image_webp`, `post_date`, `post_srt_desc`, `post_desc`, `post_link`, `show_home`) VALUES
(10, 'MBA & BBA Industrial Visit', 'mba--bba-industrial-visit', 'MBA & BBA Industrial Visit', 'MBA & BBA Industrial Visit', 'MBA & BBA Industrial Visit', '1578583317MBA.jpg', '1578583317MBA.jpg', '2019-12-23', '<p>Apart from other tools of learning, one way of learning business is by action and participation in research. Following this chain of practical learning through Industrial visits, students of MBA & BBA department of Dayanand Academy of Management Studies are made to visit different industries every year.</p>', '<div style=\"padding: 0px; margin: 0px 0px 0px 4px; color: rgb(51, 51, 51); font-family: Arial, Helvetica, sans-serif;\">\r\n<p style=\"padding: 0px; margin: 10px 0px; text-align: justify;\">Apart from other tools of learning, one way of learning business is by action and participation in research. Following this chain of practical learning through Industrial visits, students of MBA &amp; BBA department of Dayanand Academy of Management Studie are made to visit different industries every year. Our students accepted this opportunity with both hands and made the optimum utilization of that. Students have visited Bajaj Auto, Britannia, TCPL, Tata Motors, Cello, Rotomac, Havells etc. Aim of this visit was to ensure student interaction with best Professionals &amp; Leaders in their respective field. These visits are part of curriculum of management programmes at DAMS.</p>\r\n<div>&nbsp;</div>\r\n</div>', '#', 0),
(11, 'Seminars', 'seminars', 'Seminars DAMS', 'Seminars DAMS', 'Seminars', '1578288264eventsPic3.jpg', '1578288264eventsPic3.webp', '2020-01-03', '<p>To enhance the upcoming knowledge of the latest issues which spellbound our universe, been showered by the eminent panel of dignitaries and well renowned speakers.</p>', '<div style=\"padding: 0px; margin: 0px 0px 0px 4px; color: rgb(51, 51, 51); font-family: Arial, Helvetica, sans-serif;\">\r\n<p style=\"padding: 0px; margin: 10px 0px; text-align: justify;\">To enhance the upcoming knowledge of the latest issues which spellbound our universe, been showered by the eminent panel of dignitories and well renowned speakers. The dew drop of such information creates a soothing affect on the petals of our life. Continuous conduction of such events brings together the justified and refreshing views and thoughts of the stalwarts of different fields.</p>\r\n<div>&nbsp;</div>\r\n</div>', '#', 1),
(12, 'Swarup\'s Mega Job Fair', 'swarups-mega-job-fair', 'Swarup\'s Mega Job Fair', 'Swarup\'s Mega Job Fair - DAMS', 'Swarup\'s Mega Job Fair, DAMS', '1578288018eventsPic2.jpg', '1578288018eventsPic2.webp', '2020-01-03', '<p>With a positive attitude, SWARUP\'s have been trying to achieve a big felicitation from the corporate by organizing A MEGA JOB FAIR once in every two years .</p>', '<p><span style=\"color: rgb(51, 51, 51); font-family: Arial, Helvetica, sans-serif; text-align: justify;\">With a positive attitude, SWARUP\'s have been trying to achieve a big felicitation from the corporate by organizing A MEGA JOB FAIR once in every two years. With the motive that the learner should earn from most of the educational fraternity we just try to become a bridge between the high profile companies and the job seekers. We hope that it will be a great &amp; never ending relationship with these companies namely HCL, VODAFONE, BAJAJ ALLIANCE, and many more such companies to continue with. We feel highly privileged to fill the faces with satisfaction &amp; happiness of thousands &amp; thousands of well deserving candidates.</span></p>', '#', 1),
(13, 'Srijan - An Innovation to Creativity', 'srijan---an-innovation-to-creativity', ' Srijan - An Innovation to Creativity', ' Srijan - An Innovation to Creativity', 'Srijan - An Innovation to Creativity', '1578150434eventsPic1.jpg', '1578287819eventsPic1.webp', '2020-01-05', '<p>Ever since the advent of human beings, they have been ornamented with an attribute called creativity.</p>\r\n<p>Its a compendium of courage, valour, talent, dance, drama, singing, innovation to new ideas, are been portrayed by our</p>', '<p><strong>Ever since the advent of human beings, they have been ornamented with an attribute called creativity.&nbsp;</strong></p>\r\n<p>Its a compendium of courage, valour, talent, dance, drama, singing, innovation to new ideas, are been portrayed by our students. The extravagant show, been nurtured by sheer talent and enthusiasm live up to the expectations of the spectators. The stage is all set to have a larger than life experience. Students, staff, faculties &amp; the management whole heartedly give their best to make it a mega success</p>', '#', 1),
(16, 'Energy - The Athletic Meet', 'energy---the-athletic-meet', 'Energy - The Athletic Meet', 'Energy - The Athletic Meet', 'Energy - The Athletic Meet', '1578583414Energy - The Athletic Meet.jpg', '1578583631Energy - The Athletic Meet.jpg', '2020-01-09', '<p>VSEF organizes two-day athletic meet - Energy providing students with the opportunity to relax & rejuvenate exploring an untouched realm in their lives - sports & athletics. </p>', '<p>&nbsp;<span style=\"text-align: justify; color: rgb(51, 51, 51); font-family: Arial, Helvetica, sans-serif;\">VSEF organizes two-day athletic meet - Energy</span><span style=\"text-align: justify; color: rgb(51, 51, 51); font-family: Arial, Helvetica, sans-serif;\">&nbsp;</span><span style=\"text-align: justify; color: rgb(51, 51, 51); font-family: Arial, Helvetica, sans-serif;\">providing students with the opportunity to relax &amp; rejuvenate exploring an untouched realm in their lives - sports &amp; athletics. The two day event was marked by plethora of track &amp; field events participated by eight teams of VSEF with immense vigour &amp; fervour.</span></p>\r\n<div><span style=\"text-align: justify; color: rgb(51, 51, 51); font-family: Arial, Helvetica, sans-serif;\"><br />\r\n</span></div>', '', 0);

-- --------------------------------------------------------

--
-- Table structure for table `tbl_sessions`
--

CREATE TABLE `tbl_sessions` (
  `sesskey` varchar(64) NOT NULL DEFAULT '',
  `expiry` int(11) UNSIGNED NOT NULL DEFAULT '0',
  `value` text NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

--
-- Dumping data for table `tbl_sessions`
--

INSERT INTO `tbl_sessions` (`sesskey`, `expiry`, `value`) VALUES
('mtscln2dthafn4h3lse0r1bu45', 1580984888, 'admin|a:2:{s:2:\"id\";s:1:\"1\";s:9:\"adminname\";s:5:\"admin\";}'),
('ac2jbeqt41f2st5gkh6gm1q392', 1581150377, 'admin|a:2:{s:2:\"id\";s:1:\"1\";s:9:\"adminname\";s:5:\"admin\";}'),
('c881b8717765b0c6789f43179a56a686', 1609872803, 'admin|a:2:{s:2:\"id\";s:1:\"1\";s:9:\"adminname\";s:5:\"admin\";}'),
('488a1ab7b1476727d6b43f6cb1ee3dc2', 1609957387, 'admin|a:2:{s:2:\"id\";s:1:\"1\";s:9:\"adminname\";s:5:\"admin\";}'),
('b9c459ed31543cdaf8e4cb3df1804573', 1610108126, 'admin|a:2:{s:2:\"id\";s:1:\"1\";s:9:\"adminname\";s:5:\"admin\";}'),
('974f6e5cd97ba71e5372b7cc3a31cd59', 1610171403, 'admin|a:2:{s:2:\"id\";s:1:\"1\";s:9:\"adminname\";s:5:\"admin\";}'),
('59dba8a0626418508d414f96ecff98fd', 1610298877, 'redirect_origin|N;'),
('a38c404685ffbb039204cb3a05f2875d', 1610477925, 'admin|a:2:{s:2:\"id\";s:1:\"1\";s:9:\"adminname\";s:5:\"admin\";}'),
('2b43cda4b79bddaeb9d2c56ac9a21755', 1610538033, 'admin|a:2:{s:2:\"id\";s:1:\"1\";s:9:\"adminname\";s:5:\"admin\";}'),
('a967862210cae2d6d726d4c5d76cc4cd', 1611205073, 'redirect_origin|N;'),
('5ff76a440936999d522b064a0bd0e538', 1611205073, ''),
('b1d8226d5abf43a2600dbfa2ef1dccd8', 1611214919, 'admin|a:2:{s:2:\"id\";s:1:\"1\";s:9:\"adminname\";s:5:\"admin\";}'),
('1587370162be0e1e5d483388bde2c882', 1611339769, 'admin|a:2:{s:2:\"id\";s:1:\"1\";s:9:\"adminname\";s:5:\"admin\";}');

--
-- Indexes for dumped tables
--

--
-- Indexes for table `tbl_administrator`
--
ALTER TABLE `tbl_administrator`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `tbl_post`
--
ALTER TABLE `tbl_post`
  ADD PRIMARY KEY (`post_id`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `tbl_administrator`
--
ALTER TABLE `tbl_administrator`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT for table `tbl_post`
--
ALTER TABLE `tbl_post`
  MODIFY `post_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=17;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
