-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: Jan 13, 2026 at 10:18 AM
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
-- Database: `db_gcioj`
--

-- --------------------------------------------------------

--
-- Table structure for table `contest`
--

CREATE TABLE `contest` (
  `id` int(11) NOT NULL,
  `name` varchar(100) NOT NULL,
  `course` varchar(100) DEFAULT NULL,
  `is_active` tinyint(1) NOT NULL DEFAULT 0,
  `is_public` tinyint(1) NOT NULL DEFAULT 0,
  `start_time` datetime DEFAULT NULL,
  `end_time` datetime DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `contest`
--

INSERT INTO `contest` (`id`, `name`, `course`, `is_active`, `is_public`, `start_time`, `end_time`) VALUES
(14, 'WarmUp', 'AL_114', 1, 1, '2026-01-20 21:45:00', '2026-01-20 12:00:00'),
(15, 'WarmUp', 'PY_114', 1, 1, '2026-01-21 21:45:00', '2026-01-21 12:46:00');

-- --------------------------------------------------------

--
-- Table structure for table `contest_problem`
--

CREATE TABLE `contest_problem` (
  `contest_id` int(11) NOT NULL,
  `problem_id` int(11) NOT NULL,
  `problem_order` int(11) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `contest_problem`
--

INSERT INTO `contest_problem` (`contest_id`, `problem_id`, `problem_order`) VALUES
(14, 713, 0),
(14, 714, 0),
(14, 715, 0),
(15, 716, 0),
(15, 717, 0);

-- --------------------------------------------------------

--
-- Table structure for table `course`
--

CREATE TABLE `course` (
  `id` int(11) NOT NULL,
  `name` varchar(255) NOT NULL,
  `code` varchar(5) DEFAULT NULL,
  `year` int(11) NOT NULL,
  `semester` varchar(50) NOT NULL,
  `department` varchar(100) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `course`
--

INSERT INTO `course` (`id`, `name`, `code`, `year`, `semester`, `department`) VALUES
(1, 'Advanced Python Programming', 'APP', 114, 'Fall', 'IAI'),
(2, 'Algorithms', 'AL', 114, 'Spring', 'IAI'),
(3, 'Python Programming', 'PY', 114, 'Spring', 'IAI'),
(4, 'Windows Programming Design', 'WD', 115, 'Fall', 'IAI'),
(5, 'Data Structures', 'DS', 114, 'Fall', 'IAI');

-- --------------------------------------------------------

--
-- Table structure for table `problem`
--

CREATE TABLE `problem` (
  `id` int(11) NOT NULL,
  `code` varchar(20) NOT NULL,
  `leetcode_id` varchar(50) DEFAULT NULL,
  `leetcode_link` varchar(255) DEFAULT NULL,
  `title` varchar(255) DEFAULT NULL,
  `input_type` varchar(10) NOT NULL DEFAULT 'arr',
  `output_type` enum('screen','value') NOT NULL DEFAULT 'value',
  `grading_type` enum('algorithm','test') NOT NULL,
  `description` text DEFAULT NULL,
  `level` enum('Easy','Medium','Hard') DEFAULT 'Easy',
  `time_limit_ms` int(11) DEFAULT 1000,
  `memory_limit_mb` int(11) DEFAULT 256,
  `tag` varchar(255) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `problem`
--

INSERT INTO `problem` (`id`, `code`, `leetcode_id`, `leetcode_link`, `title`, `input_type`, `output_type`, `grading_type`, `description`, `level`, `time_limit_ms`, `memory_limit_mb`, `tag`) VALUES
(713, 'G001_ERYIIN', 'None', 'None', 'Find Max Value in The Array', 'arr', 'screen', 'test', NULL, 'Easy', 1000, 256, 'number'),
(714, 'G714_EMNRMA', 'None', 'None', 'Find Minimun Value in Array', 'arr', 'value', 'test', NULL, 'Easy', 1000, 256, 'number'),
(715, 'G715_NAFMIX', 'None', 'None', 'Find Max', 'arr', 'value', 'test', NULL, 'Easy', 1000, 256, 'number'),
(716, 'G716_LOOREL', 'None', 'None', 'Hello World!', 'arr', 'screen', 'test', NULL, 'Easy', 1000, 256, 'string'),
(717, 'G717_NETN2M', 'None', 'None', 'Find Max Between 2', 'arr', 'screen', 'test', NULL, 'Easy', 1000, 256, 'number'),
(718, 'G718_TUUMMO', 'None', 'None', 'Sum Two Number', 'arr', 'screen', 'test', NULL, 'Easy', 1000, 256, 'string');

-- --------------------------------------------------------

--
-- Table structure for table `registration`
--

CREATE TABLE `registration` (
  `id` int(11) NOT NULL,
  `student_id` int(11) NOT NULL,
  `course_id` int(11) NOT NULL,
  `registered_at` datetime DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `registration`
--

INSERT INTO `registration` (`id`, `student_id`, `course_id`, `registered_at`) VALUES
(1, 109, 1, '2026-01-11 00:18:35'),
(2, 119, 1, '2026-01-11 00:18:35'),
(3, 93, 1, '2026-01-11 00:18:35'),
(4, 84, 1, '2026-01-11 00:18:35'),
(5, 77, 1, '2026-01-11 00:18:35'),
(6, 105, 1, '2026-01-11 00:18:35'),
(7, 73, 1, '2026-01-11 00:18:35'),
(8, 69, 1, '2026-01-11 00:18:35'),
(9, 78, 1, '2026-01-11 00:18:35'),
(10, 74, 1, '2026-01-11 00:18:35'),
(11, 88, 1, '2026-01-11 00:18:35'),
(12, 95, 1, '2026-01-11 00:18:35'),
(13, 75, 1, '2026-01-11 00:18:35'),
(14, 76, 1, '2026-01-11 00:18:35'),
(15, 90, 1, '2026-01-11 00:18:35'),
(16, 106, 1, '2026-01-11 00:18:35'),
(17, 100, 1, '2026-01-11 00:18:35'),
(18, 67, 1, '2026-01-11 00:18:35'),
(19, 71, 1, '2026-01-11 00:18:35'),
(20, 68, 1, '2026-01-11 00:18:35'),
(21, 98, 1, '2026-01-11 00:18:35'),
(22, 91, 1, '2026-01-11 00:18:35'),
(23, 87, 1, '2026-01-11 00:18:35'),
(24, 82, 1, '2026-01-11 00:18:35'),
(25, 101, 1, '2026-01-11 00:18:35'),
(26, 72, 1, '2026-01-11 00:18:35'),
(27, 79, 1, '2026-01-11 00:18:35'),
(28, 102, 1, '2026-01-11 00:18:35'),
(29, 89, 1, '2026-01-11 00:18:35'),
(30, 85, 1, '2026-01-11 00:18:35'),
(31, 94, 1, '2026-01-11 00:18:35'),
(32, 70, 1, '2026-01-11 00:18:35'),
(33, 96, 1, '2026-01-11 00:18:35'),
(34, 108, 1, '2026-01-11 00:18:35'),
(35, 80, 1, '2026-01-11 00:18:35'),
(36, 92, 1, '2026-01-11 00:18:35'),
(37, 86, 1, '2026-01-11 00:18:35'),
(38, 107, 1, '2026-01-11 00:18:35'),
(77, 111, 5, '2026-01-11 00:19:18'),
(78, 109, 5, '2026-01-11 00:19:18'),
(79, 117, 5, '2026-01-11 00:19:18'),
(80, 118, 5, '2026-01-11 00:19:18'),
(81, 113, 5, '2026-01-11 00:19:18'),
(82, 119, 5, '2026-01-11 00:19:18'),
(83, 93, 5, '2026-01-11 00:19:18'),
(84, 84, 5, '2026-01-11 00:19:18'),
(85, 77, 5, '2026-01-11 00:19:18'),
(86, 99, 5, '2026-01-11 00:19:18'),
(87, 105, 5, '2026-01-11 00:19:18'),
(88, 73, 5, '2026-01-11 00:19:18'),
(89, 69, 5, '2026-01-11 00:19:19'),
(90, 78, 5, '2026-01-11 00:19:19'),
(91, 74, 5, '2026-01-11 00:19:19'),
(92, 88, 5, '2026-01-11 00:19:19'),
(93, 120, 5, '2026-01-11 00:19:19'),
(94, 95, 5, '2026-01-11 00:19:19'),
(95, 75, 5, '2026-01-11 00:19:19'),
(96, 76, 5, '2026-01-11 00:19:19'),
(97, 90, 5, '2026-01-11 00:19:19'),
(98, 106, 5, '2026-01-11 00:19:19'),
(99, 100, 5, '2026-01-11 00:19:19'),
(100, 67, 5, '2026-01-11 00:19:19'),
(101, 71, 5, '2026-01-11 00:19:19'),
(102, 114, 5, '2026-01-11 00:19:19'),
(103, 116, 5, '2026-01-11 00:19:19'),
(104, 68, 5, '2026-01-11 00:19:19'),
(105, 98, 5, '2026-01-11 00:19:19'),
(106, 112, 5, '2026-01-11 00:19:19'),
(107, 91, 5, '2026-01-11 00:19:19'),
(108, 87, 5, '2026-01-11 00:19:19'),
(109, 82, 5, '2026-01-11 00:19:19'),
(110, 115, 5, '2026-01-11 00:19:19'),
(111, 101, 5, '2026-01-11 00:19:19'),
(112, 72, 5, '2026-01-11 00:19:19'),
(113, 79, 5, '2026-01-11 00:19:19'),
(114, 102, 5, '2026-01-11 00:19:19'),
(115, 89, 5, '2026-01-11 00:19:19'),
(116, 85, 5, '2026-01-11 00:19:19'),
(117, 94, 5, '2026-01-11 00:19:19'),
(118, 70, 5, '2026-01-11 00:19:19'),
(119, 96, 5, '2026-01-11 00:19:19'),
(120, 108, 5, '2026-01-11 00:19:19'),
(121, 80, 5, '2026-01-11 00:19:19'),
(122, 92, 5, '2026-01-11 00:19:19'),
(123, 86, 5, '2026-01-11 00:19:19'),
(124, 81, 5, '2026-01-11 00:19:19'),
(125, 107, 5, '2026-01-11 00:19:19'),
(126, 275, 5, '2026-01-11 00:19:27'),
(127, 275, 1, '2026-01-11 00:19:32');

-- --------------------------------------------------------

--
-- Table structure for table `student`
--

CREATE TABLE `student` (
  `id` int(11) NOT NULL,
  `student_id` varchar(50) NOT NULL,
  `password` varchar(20) NOT NULL DEFAULT 'pass',
  `name` varchar(100) DEFAULT NULL,
  `english_name` varchar(100) DEFAULT NULL,
  `class` varchar(50) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `student`
--

INSERT INTO `student` (`id`, `student_id`, `password`, `name`, `english_name`, `class`) VALUES
(1, 'U12627000', 'pass', 'X', 'Test User', 'MCUT_CLASS'),
(2, 'U12627016', 'dog', '徐忨新', 'Chiout', 'MCUT_CLASS'),
(3, 'U12627902', 'love', '謝博閔', 'Antonio', 'MCUT_CLASS'),
(4, 'U12627031', 'time', '黃一展', 'David', 'MCUT_CLASS'),
(5, 'U12627032', 'star', '黃渝淇', 'Yuki', 'MCUT_CLASS'),
(6, 'U12627026', 'book', '陳柏翰', 'han', 'MCUT_CLASS'),
(7, 'U12627011', 'love', '李庭安', 'Andy', 'MCUT_CLASS'),
(8, 'U12627901', 'drum', '黃章盛', 'Thịnh', 'MCUT_CLASS'),
(9, 'U12627039', 'dog', '蔡昇穆', 'Sam', 'MCUT_CLASS'),
(10, 'U12627014', 'drum', '林孟軒', 'Mark', 'MCUT_CLASS'),
(11, 'U12627017', 'love', '徐淳葳', 'Vivian', 'MCUT_CLASS'),
(12, 'U12627018', 'book', '高浩倫', 'Alan', 'MCUT_CLASS'),
(13, 'U12627010', 'car', '李承勳', 'Ryan', 'MCUT_CLASS'),
(14, 'U12627012', 'book', '周詮彰', 'Kan', 'MCUT_CLASS'),
(15, 'U12627004', 'book', '王若宇', 'Roy', 'MCUT_CLASS'),
(16, 'U12627015', 'drum', '邱冠傑', 'Eric', 'MCUT_CLASS'),
(17, 'U12627013', 'book', '林正皓', 'Howard', 'MCUT_CLASS'),
(18, 'U12627027', 'game', '彭聖凱', 'kyle', 'MCUT_CLASS'),
(19, 'U12627022', 'book', '陳心如', 'Kelly', 'MCUT_CLASS'),
(20, 'U12627030', 'book', '閔子豪', 'David', 'MCUT_CLASS'),
(21, 'U12627021', 'tree', '張馨勻', 'Anita', 'MCUT_CLASS'),
(22, 'U12627044', 'game', '蕭宇辰', 'Diego', 'MCUT_CLASS'),
(23, 'U12627040', 'pokemon', '蔡杰勳', 'Leo_Tsai', 'MCUT_CLASS'),
(24, 'U12627042', 'book', '鄭軍', 'Jerry', 'MCUT_CLASS'),
(25, 'U12627008', 'pikachu', '呂承洋', 'Daniel', 'MCUT_CLASS'),
(26, 'U12627006', 'drum', '石浚維', 'shijunwei', 'MCUT_CLASS'),
(27, 'U12627028', 'book', '曾瑋聖', 'Vilen', 'MCUT_CLASS'),
(28, 'U12627002', 'home', '王垠錚', 'yinzheng', 'MCUT_CLASS'),
(29, 'U12627029', 'love', '曾慶元', 'white', 'MCUT_CLASS'),
(30, 'U12627019', 'book', '張威得', 'NOIDEA', 'MCUT_CLASS'),
(31, 'U12627035', 'love', '楊育賓', 'Ben', 'MCUT_CLASS'),
(32, 'U12627038', 'game', '劉恆佑', 'Hengyou', 'MCUT_CLASS'),
(33, 'U12627034', 'fire', '黃鉉淙', 'Hsuan', 'MCUT_CLASS'),
(34, 'U12627020', 'car', '張峻毓', 'Benny', 'MCUT_CLASS'),
(35, 'U12627009', 'time', '李宙軒', 'melo', 'MCUT_CLASS'),
(36, 'U12627041', 'game', '蔣昌哲', 'jonathan', 'MCUT_CLASS'),
(37, 'U12627036', 'game', '楊東霖', 'Tony', 'MCUT_CLASS'),
(38, 'U12627037', 'game', '劉芳境', 'Ivan', 'MCUT_CLASS'),
(39, 'U12627033', 'game', '黃暐宸', 'wei', 'MCUT_CLASS'),
(40, 'U12627023', 'home', '陳宏哲', 'CHEN_HONG_JHE', 'MCUT_CLASS'),
(41, 'U12627025', 'love', '陳建愷', 'CHEN-JIAN-KAI', 'MCUT_CLASS'),
(42, 'U12627007', 'book', '江俞廷', 'Louis', 'MCUT_CLASS'),
(43, 'U12627003', 'car', '王威翔', 'Will', 'MCUT_CLASS'),
(44, 'U13227207', 'ball', '阮團慶玲', 'Max', 'MCUT_CLASS'),
(45, 'U13227211', 'green', '馮氏燕薇', 'Winna', 'MCUT_CLASS'),
(46, 'U13227202', 'pink', '吳何清香', 'Raya', 'MCUT_CLASS'),
(47, 'U13227206', 'game', '阮梅翔薇', 'Victoria', 'MCUT_CLASS'),
(48, 'U13227203', 'duck', '杜黃夜草', 'Da Thao', 'MCUT_CLASS'),
(49, 'U13227210', 'love', '陽寶玉', 'Iris', 'MCUT_CLASS'),
(50, 'U13227212', 'duck', '黃明河', 'Hoang Minh Ha (Jessica)', 'MCUT_CLASS'),
(51, 'U13227208', 'pink', '陳芳玲', 'Jennifer', 'MCUT_CLASS'),
(52, 'U13227201', 'plan', '宇清草', 'Sally', 'MCUT_CLASS'),
(53, 'U13227204', 'home', '阮氏瓊娥', 'Rose', 'MCUT_CLASS'),
(54, 'U13227213', 'book', '楊庭德', 'Tak', 'MCUT_CLASS'),
(55, 'U13227214', 'ball', '鄧泰楊', 'Tommy', 'MCUT_CLASS'),
(56, 'U13227205', 'time', '阮世日輝', 'Nguyen The Nhat Huy', 'MCUT_CLASS'),
(57, 'U13227209', 'game', '凱菲德', 'Fidel', 'MCUT_CLASS'),
(58, 'U12227213', 'car', '潘氏茗英', 'Mindy', 'MCUT_CLASS'),
(59, 'U12227204', 'love', '阮俊勇', 'Oliver', 'MCUT_CLASS'),
(60, 'U12227201', 'blue', '石秋雨', 'Winnie', 'MCUT_CLASS'),
(61, 'U12227203', 'game', '阮世日明', 'Cedric', 'MCUT_CLASS'),
(62, 'U12227215', 'book', '黎梅方', 'Annie', 'MCUT_CLASS'),
(63, 'U12227216', 'red', '黎範英書', 'Carly', 'MCUT_CLASS'),
(64, 'U12227209', 'pikachu', '阮碧鳳', 'Julie', 'MCUT_CLASS'),
(65, 'U12227210', 'love', '阮裴雲玉', 'Wulix', 'MCUT_CLASS'),
(66, 'U12227211', 'pokemon', '梁氏玄', 'Helene', 'MCUT_CLASS'),
(67, 'U13627021', 'book', '高碩謙', 'Andy', 'MCUT_CLASS'),
(68, 'U13627025', 'book', '郭光智', 'justin', 'MCUT_CLASS'),
(69, 'U13627010', 'book', '卓品誌', 'Tom', 'MCUT_CLASS'),
(70, 'U13627039', 'love', '劉玟均', 'Liu Wen Chun', 'MCUT_CLASS'),
(71, 'U13627022', 'star', '張羽辰', 'Chang Yu Chen', 'MCUT_CLASS'),
(72, 'U13627033', 'love', '陳翊如', 'Chen I-JU', 'MCUT_CLASS'),
(73, 'U13627008', 'book', '李易修', 'Lee Yi Shiu', 'MCUT_CLASS'),
(74, 'U13627012', 'book', '林彣璇', 'WUN-SYUAN LIN', 'MCUT_CLASS'),
(75, 'U13627016', 'duck', '姚伊潔', 'jamie', 'MCUT_CLASS'),
(76, 'U13627017', 'love', '施姵亘', 'Rita', 'MCUT_CLASS'),
(77, 'U13627003', 'book', '成和融', 'Cheng He Rong', 'MCUT_CLASS'),
(78, 'U13627011', 'pokemon', '周哲維', 'Mike', 'MCUT_CLASS'),
(79, 'U13627034', 'sock', '陳詩涵', 'Meghan', 'MCUT_CLASS'),
(80, 'U13627042', 'game', '鄭頤遠', 'Ian', 'MCUT_CLASS'),
(81, 'U13627045', 'home', '謝鎮鴻', 'Alen', 'MCUT_CLASS'),
(82, 'U13627030', 'book', '陳俊龍', 'Jimmy', 'MCUT_CLASS'),
(83, 'U13627009', 'dog', '李楷珩', 'toby', 'MCUT_CLASS'),
(84, 'U13627002', 'game', '王芊嵐', 'Annie', 'MCUT_CLASS'),
(85, 'U13627037', 'game', '楊祺森', 'chison', 'MCUT_CLASS'),
(86, 'U13627044', 'game', '謝玉麟', 'David', 'MCUT_CLASS'),
(87, 'U13627029', 'game', '陳季遠', 'Neal', 'MCUT_CLASS'),
(88, 'U13627013', 'blue', '林志芯', 'Jason Lin', 'MCUT_CLASS'),
(89, 'U13627036', 'wind', '黃紹銨', 'Sean', 'MCUT_CLASS'),
(90, 'U13627018', 'book', '皇甫駿睿', 'Jerry', 'MCUT_CLASS'),
(91, 'U13627028', 'star', '陳立倫', 'Eric', 'MCUT_CLASS'),
(92, 'U13627043', 'car', '戴佑恩', 'Apple', 'MCUT_CLASS'),
(93, 'U13627001', 'book', '王羽珊', 'Sandy', 'MCUT_CLASS'),
(94, 'U13627038', 'book', '劉宇軒', 'alex', 'MCUT_CLASS'),
(95, 'U13627015', 'star', '林鈺淞', 'Ryan', 'MCUT_CLASS'),
(96, 'U13627040', 'green', '蔡其聖', 'hanns', 'MCUT_CLASS'),
(97, 'U13627004', 'game', '朱業恆', 'Chew Ye Heng', 'MCUT_CLASS'),
(98, 'U13627026', 'book', '郭奕顯', 'eason', 'MCUT_CLASS'),
(99, 'U13627006', 'game', '巫承翰', 'Brian', 'MCUT_CLASS'),
(100, 'U13627020', 'book', '徐崇恩', 'Handsome', 'MCUT_CLASS'),
(101, 'U13627032', 'book', '陳奕辛', 'Eric', 'MCUT_CLASS'),
(102, 'U13627035', 'car', '黃子峻', 'xiaoma', 'MCUT_CLASS'),
(103, 'F131702688', 'tree', '', 'Handsome', 'MCUT_CLASS'),
(104, 'U12227208', 'love', '阮雄英', 'Jason', 'MCUT_CLASS'),
(105, 'U13627007', 'game', '李元祺', 'Michael Lee', 'MCUT_CLASS'),
(106, 'U13627019', 'book', '徐晟崴', 'Willy', 'MCUT_CLASS'),
(107, 'U13627901', 'home', '陳威佑', 'WEIYOU CHEN', 'MCUT_CLASS'),
(108, 'U13627041', 'book', '鄭任博', 'Jamie', 'MCUT_CLASS'),
(109, 'U11127043', 'drum', '賴俊辰', 'Ben', 'MCUT_CLASS'),
(110, 'U14227216', 'pink', '潘玉南珍', 'Amelia', 'MCUT_CLASS'),
(111, 'U10187156', 'star', '顏誠昊', 'Eric', 'MCUT_CLASS'),
(112, 'U13627027', 'love', '陳又愷', 'kevin', 'MCUT_CLASS'),
(113, 'U11627034', 'book', '黃冠勛', 'Tommy', 'MCUT_CLASS'),
(114, 'u13627023', '樹', '', 'yuting', 'MCUT_CLASS'),
(115, 'U13627031', 'book', '陳品叡', 'Eric', 'MCUT_CLASS'),
(116, 'U13627024', 'game', '郭加興', 'Kuo', 'MCUT_CLASS'),
(117, 'U11627013', 'game', '林廉家', 'LianChia', 'MCUT_CLASS'),
(118, 'u11627032', 'love', '', 'Young', 'MCUT_CLASS'),
(119, 'U12627024', 'book', '陳宥伯', 'U12627024', 'MCUT_CLASS'),
(120, 'U13627014', 'tree', '林詠欽', 'Yung Chin', 'MCUT_CLASS'),
(121, 'U14227213', 'game', '張晉勇', 'Alvin', 'MCUT_CLASS'),
(122, 'U14227217', 'game', '黎燈豪', 'Messi', 'MCUT_CLASS'),
(123, 'U14227202', 'fire', '阮芷葳', 'Nguyen Thuy Vi', 'MCUT_CLASS'),
(124, 'U14227204', 'fire', '阮寶江', 'Selena', 'MCUT_CLASS'),
(125, 'U14227206', 'fire', '武秋娟', 'nic', 'MCUT_CLASS'),
(126, 'U14227203', 'love', '阮青心', 'Sophia', 'MCUT_CLASS'),
(127, 'U142272122', 'book', '', 'Tracy', 'MCUT_CLASS'),
(128, 'U14227205', 'road', '林家寶', 'Brian', 'MCUT_CLASS'),
(129, 'U14227208', 'book', '武國泰', 'Thai', 'MCUT_CLASS'),
(130, 'U14227207', 'book', '武美妍', 'Mary', 'MCUT_CLASS'),
(131, 'U14227210', 'road', '武嘉希', 'Elysia', 'MCUT_CLASS'),
(132, 'U14227209', 'love', '武登輝', 'Vu Dang Huy', 'MCUT_CLASS'),
(133, 'U14227214', 'star', '雷柏安', 'Andrew', 'MCUT_CLASS'),
(134, 'U14227215', 'love', '裴有英德', 'Andy', 'MCUT_CLASS'),
(135, 'U14227212', 'book', '范秋莊', 'Tracy', 'MCUT_CLASS'),
(136, 'U11227010', 'yellow', '阮氏明香', 'Lia', 'MCUT_CLASS'),
(137, 'U14227201', 'foot', '阮光輝', 'Noah', 'MCUT_CLASS'),
(275, 'chgiang', 'khongtontai', 'chgiang', 'chgiang', NULL),
(415, 'u12627111', 'pass', '高', NULL, NULL);

-- --------------------------------------------------------

--
-- Table structure for table `submission`
--

CREATE TABLE `submission` (
  `id` int(11) NOT NULL,
  `student_id` int(11) DEFAULT NULL,
  `contest_id` int(11) DEFAULT NULL,
  `problem_id` int(11) DEFAULT NULL,
  `language` varchar(20) NOT NULL,
  `status` enum('Pending','Accepted','Wrong Answer','Time Limit Exceeded','Compilation Error') DEFAULT 'Pending',
  `score` int(3) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `submission`
--

INSERT INTO `submission` (`id`, `student_id`, `contest_id`, `problem_id`, `language`, `status`, `score`, `created_at`) VALUES
(161, 275, 14, 715, 'python', 'Accepted', 100, '2026-01-10 14:44:03'),
(162, 275, 14, 713, 'python', 'Wrong Answer', 0, '2026-01-10 14:48:54'),
(163, 275, 15, 716, 'python', 'Wrong Answer', 0, '2026-01-10 14:55:41'),
(164, 275, 15, 716, 'python', 'Wrong Answer', 0, '2026-01-10 14:55:46'),
(165, 275, 15, 716, 'python', 'Accepted', 100, '2026-01-10 14:55:50'),
(166, 275, 15, 717, 'python', 'Wrong Answer', 0, '2026-01-10 14:58:05'),
(167, 275, 15, 717, 'python', '', 0, '2026-01-10 14:58:14'),
(168, 275, 15, 717, 'python', 'Wrong Answer', 0, '2026-01-10 14:58:23'),
(169, 275, 15, 717, 'python', 'Accepted', 100, '2026-01-10 14:58:25'),
(170, 275, 15, 717, 'python', 'Accepted', 100, '2026-01-10 14:58:32'),
(171, 275, 15, 717, 'python', 'Wrong Answer', 0, '2026-01-10 14:58:36'),
(172, 275, 15, 717, 'python', 'Wrong Answer', 0, '2026-01-10 15:05:05'),
(173, 275, 15, 717, 'python', 'Wrong Answer', 0, '2026-01-10 15:05:13'),
(174, 275, 15, 717, 'python', 'Wrong Answer', 0, '2026-01-10 15:05:20'),
(175, 275, 15, 717, 'python', '', 0, '2026-01-10 15:05:22'),
(176, 275, 15, 717, 'python', 'Wrong Answer', 0, '2026-01-10 15:08:38'),
(177, 275, 15, 717, 'python', '', 0, '2026-01-10 15:08:40'),
(178, 275, 15, 717, 'python', 'Accepted', 100, '2026-01-10 15:08:50'),
(179, 275, 15, 717, 'python', 'Accepted', 100, '2026-01-10 15:11:10'),
(180, 275, 15, 717, 'python', 'Accepted', 100, '2026-01-10 15:11:24'),
(181, 275, 15, 717, 'python', 'Accepted', 100, '2026-01-10 15:11:30'),
(182, 275, 15, 717, 'python', 'Accepted', 100, '2026-01-10 15:12:53'),
(183, 275, 15, 717, 'python', 'Accepted', 100, '2026-01-10 15:13:18'),
(184, 275, 15, 717, 'python', 'Accepted', 100, '2026-01-10 15:13:31'),
(185, 275, 15, 717, 'python', 'Accepted', 100, '2026-01-10 15:13:37'),
(186, 275, 15, 717, 'python', 'Wrong Answer', 0, '2026-01-10 15:13:51'),
(187, 275, 15, 717, 'python', 'Accepted', 100, '2026-01-10 15:13:53'),
(188, 137, 15, 716, 'python', 'Wrong Answer', 0, '2026-01-10 17:18:05'),
(189, 137, 15, 716, 'python', 'Accepted', 100, '2026-01-10 17:18:06'),
(190, 137, 15, 717, 'python', 'Wrong Answer', 0, '2026-01-10 17:18:23'),
(191, 137, 15, 717, 'python', 'Accepted', 100, '2026-01-10 17:18:25'),
(192, 137, 15, 716, 'python', 'Accepted', 100, '2026-01-10 18:02:31'),
(193, 137, 15, 716, 'python', 'Wrong Answer', 0, '2026-01-10 18:02:32'),
(194, 137, 15, 716, 'python', 'Accepted', 100, '2026-01-10 18:02:33');

--
-- Indexes for dumped tables
--

--
-- Indexes for table `contest`
--
ALTER TABLE `contest`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `contest_problem`
--
ALTER TABLE `contest_problem`
  ADD PRIMARY KEY (`contest_id`,`problem_id`),
  ADD KEY `problem_id` (`problem_id`);

--
-- Indexes for table `course`
--
ALTER TABLE `course`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `problem`
--
ALTER TABLE `problem`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `code` (`code`);

--
-- Indexes for table `registration`
--
ALTER TABLE `registration`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `unique_enrollment` (`student_id`,`course_id`),
  ADD KEY `course_id` (`course_id`);

--
-- Indexes for table `student`
--
ALTER TABLE `student`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `username` (`student_id`);

--
-- Indexes for table `submission`
--
ALTER TABLE `submission`
  ADD PRIMARY KEY (`id`),
  ADD KEY `submission_ibfk_1` (`student_id`),
  ADD KEY `submission_ibfk_2` (`contest_id`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `contest`
--
ALTER TABLE `contest`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=16;

--
-- AUTO_INCREMENT for table `course`
--
ALTER TABLE `course`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;

--
-- AUTO_INCREMENT for table `problem`
--
ALTER TABLE `problem`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=719;

--
-- AUTO_INCREMENT for table `registration`
--
ALTER TABLE `registration`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=128;

--
-- AUTO_INCREMENT for table `student`
--
ALTER TABLE `student`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=416;

--
-- AUTO_INCREMENT for table `submission`
--
ALTER TABLE `submission`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=195;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `contest_problem`
--
ALTER TABLE `contest_problem`
  ADD CONSTRAINT `contest_problem_ibfk_1` FOREIGN KEY (`contest_id`) REFERENCES `contest` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `contest_problem_ibfk_2` FOREIGN KEY (`problem_id`) REFERENCES `problem` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `registration`
--
ALTER TABLE `registration`
  ADD CONSTRAINT `registration_ibfk_1` FOREIGN KEY (`student_id`) REFERENCES `student` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `registration_ibfk_2` FOREIGN KEY (`course_id`) REFERENCES `course` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `submission`
--
ALTER TABLE `submission`
  ADD CONSTRAINT `submission_ibfk_1` FOREIGN KEY (`student_id`) REFERENCES `student` (`id`),
  ADD CONSTRAINT `submission_ibfk_2` FOREIGN KEY (`contest_id`) REFERENCES `contest` (`id`);
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
