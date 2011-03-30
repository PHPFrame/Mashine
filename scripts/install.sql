-- phpMyAdmin SQL Dump
-- version 3.3.9
-- http://www.phpmyadmin.net
--
-- Host: localhost
-- Generation Time: Mar 29, 2011 at 09:32 PM
-- Server version: 5.1.54
-- PHP Version: 5.3.4

SET SQL_MODE="NO_AUTO_VALUE_ON_ZERO";

--
-- Database: `mashine`
--

-- --------------------------------------------------------

--
-- Table structure for table `api_methods`
--

CREATE TABLE IF NOT EXISTS `api_methods` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `method` varchar(100) COLLATE utf8_unicode_ci NOT NULL,
  `oauth` enum('0','1','2','3') COLLATE utf8_unicode_ci NOT NULL DEFAULT '0' COMMENT '0 = No, 1= both, 2 = 2-legged, 3 = 3-legged',
  `cookie` enum('0','1','2') COLLATE utf8_unicode_ci NOT NULL DEFAULT '0',
  PRIMARY KEY (`id`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci AUTO_INCREMENT=18 ;

--
-- Dumping data for table `api_methods`
--

INSERT INTO `api_methods` (`id`, `method`, `oauth`, `cookie`) VALUES
(1, 'session/login', '2', '2'),
(2, 'session/logout', '0', '1'),
(3, 'oauth/save_method_auth', '0', '2'),
(4, 'users/get', '1', '1'),
(5, 'users/post', '1', '2'),
(6, 'content/get', '1', '1'),
(7, 'content/post', '1', '2'),
(8, 'content/delete', '1', '1'),
(9, 'notifications/get', '0', '1'),
(10, 'notifications/delete', '0', '1'),
(11, 'plugins/get', '0', '1'),
(12, 'plugins/post', '0', '2'),
(13, 'plugins/delete', '0', '1'),
(14, 'users/delete', '2', '1'),
(15, 'users/search', '1', '1'),
(16, 'oauth/access_token', '0', '1'),
(17, 'oauth/request_token', '0', '1');

-- --------------------------------------------------------

--
-- Table structure for table `contacts`
--

CREATE TABLE IF NOT EXISTS `contacts` (
  `org_name` varchar(100) COLLATE utf8_unicode_ci DEFAULT NULL,
  `first_name` varchar(50) COLLATE utf8_unicode_ci NOT NULL,
  `last_name` varchar(50) COLLATE utf8_unicode_ci NOT NULL,
  `address1` varchar(100) COLLATE utf8_unicode_ci NOT NULL,
  `address2` varchar(100) COLLATE utf8_unicode_ci DEFAULT NULL,
  `city` varchar(50) COLLATE utf8_unicode_ci NOT NULL,
  `post_code` varchar(15) COLLATE utf8_unicode_ci NOT NULL,
  `county` varchar(50) COLLATE utf8_unicode_ci NOT NULL,
  `country` varchar(2) COLLATE utf8_unicode_ci NOT NULL DEFAULT 'GB',
  `phone` varchar(20) COLLATE utf8_unicode_ci NOT NULL,
  `email` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `fax` varchar(20) COLLATE utf8_unicode_ci DEFAULT NULL,
  `preferred` tinyint(1) DEFAULT '0',
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `ctime` int(11) DEFAULT NULL,
  `mtime` int(11) DEFAULT NULL,
  `owner` int(11) NOT NULL DEFAULT '0',
  `group` int(11) NOT NULL DEFAULT '2',
  `perms` int(11) NOT NULL DEFAULT '660',
  PRIMARY KEY (`id`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci AUTO_INCREMENT=2 ;

--
-- Dumping data for table `contacts`
--

INSERT INTO `contacts` (`org_name`, `first_name`, `last_name`, `address1`, `address2`, `city`, `post_code`, `county`, `country`, `phone`, `email`, `fax`, `preferred`, `id`, `ctime`, `mtime`, `owner`, `group`, `perms`) VALUES
(NULL, 'Root', 'User', 'Some Street', '', 'Some city', '000000', 'Some county', 'GB', '0123456789', 'root@example.com', NULL, 1, 1, 1301429845, 1301429845, 1, 1, 440);

-- --------------------------------------------------------

--
-- Table structure for table `content`
--

CREATE TABLE IF NOT EXISTS `content` (
  `parent_id` int(11) NOT NULL DEFAULT '0',
  `slug` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `title` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `short_title` varchar(50) COLLATE utf8_unicode_ci DEFAULT NULL,
  `pub_date` datetime DEFAULT NULL,
  `status` tinyint(4) DEFAULT NULL,
  `robots_index` tinyint(4) DEFAULT NULL,
  `robots_follow` tinyint(4) DEFAULT NULL,
  `type` varchar(100) COLLATE utf8_unicode_ci NOT NULL,
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `ctime` int(11) DEFAULT NULL,
  `mtime` int(11) DEFAULT NULL,
  `owner` int(11) NOT NULL DEFAULT '0',
  `group` int(11) NOT NULL DEFAULT '0',
  `perms` int(11) NOT NULL DEFAULT '664',
  PRIMARY KEY (`id`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci AUTO_INCREMENT=105 ;

--
-- Dumping data for table `content`
--

INSERT INTO `content` (`parent_id`, `slug`, `title`, `short_title`, `pub_date`, `status`, `robots_index`, `robots_follow`, `type`, `id`, `ctime`, `mtime`, `owner`, `group`, `perms`) VALUES
(0, 'home', 'Home', NULL, '2011-03-29 21:17:26', 1, 1, 1, 'PageContent', 1, 1301429846, 1301429846, 1, 2, 664),
(1, 'user/login', 'Log in', NULL, '1970-01-01 00:01:00', 1, 0, 0, 'MVCContent', 2, 1301429846, 1301429846, 1, 1, 644),
(1, 'user/logout', 'Log out', NULL, '1970-01-01 00:00:00', 1, 0, 0, 'MVCContent', 3, 1301429846, 1301429846, 1, 1, 644),
(1, 'user/signup', 'Sign up', NULL, '1970-01-01 00:00:30', 1, 0, 0, 'MVCContent', 4, 1301429846, 1301429846, 1, 1, 644),
(1, 'dashboard', 'Dashboard', NULL, '1970-01-01 00:01:00', 1, 0, 0, 'MVCContent', 5, 1301429846, 1301429846, 1, 3, 440),
(5, 'profile', 'User profile', NULL, '1970-01-01 00:04:00', 1, 0, 0, 'MVCContent', 6, 1301429846, 1301429846, 1, 3, 440),
(6, 'user/addcontact', 'Add contact', NULL, '2011-03-29 21:17:26', 1, 0, 0, 'MVCContent', 7, 1301429846, 1301429846, 1, 3, 440),
(6, 'user/editcontact', 'Modify contact', NULL, '2011-03-29 21:17:26', 1, 0, 0, 'MVCContent', 8, 1301429846, 1301429846, 1, 3, 440),
(5, 'admin/content', 'Manage content', NULL, '1970-01-01 00:03:30', 1, 0, 0, 'MVCContent', 9, 1301429846, 1301429846, 1, 1, 440),
(9, 'admin/content/form', 'Content form', NULL, '2011-03-29 21:17:26', 1, 0, 0, 'MVCContent', 10, 1301429846, 1301429846, 1, 1, 440),
(5, 'admin/media', 'Manage media', NULL, '1970-01-01 00:03:30', 1, 0, 0, 'MVCContent', 11, 1301429846, 1301429846, 1, 1, 644),
(5, 'admin/user', 'Manage users', NULL, '1970-01-01 00:03:00', 1, 0, 0, 'MVCContent', 12, 1301429846, 1301429846, 1, 2, 440),
(12, 'admin/user/form', 'User form', NULL, '2011-03-29 21:17:26', 1, 0, 0, 'MVCContent', 13, 1301429846, 1301429846, 1, 2, 440),
(5, 'admin/plugins', 'Plugins', NULL, '1970-01-01 00:02:30', 1, 0, 0, 'MVCContent', 14, 1301429846, 1301429846, 1, 1, 440),
(14, 'admin/plugins/options', 'Plugin Options', NULL, '2011-03-29 21:17:26', 1, 0, 0, 'MVCContent', 15, 1301429846, 1301429846, 1, 1, 440),
(5, 'admin/config', 'Config', NULL, '1970-01-01 00:02:00', 1, 0, 0, 'MVCContent', 16, 1301429846, 1301429846, 1, 1, 440),
(5, 'admin/system', 'System info', NULL, '1970-01-01 00:01:30', 1, 0, 0, 'MVCContent', 17, 1301429846, 1301429846, 1, 1, 440),
(5, 'admin/api', 'REST API', NULL, '1970-01-01 00:01:00', 1, 0, 0, 'MVCContent', 18, 1301429846, 1301429846, 1, 1, 440),
(5, 'admin/upgrade', 'Upgrade', NULL, '1970-01-01 00:00:30', 1, 0, 0, 'MVCContent', 19, 1301429846, 1301429846, 1, 1, 440),
(5, 'admin/backup', 'Backup', NULL, '1970-01-01 00:00:00', 1, 0, 0, 'MVCContent', 20, 1301429846, 1301429846, 1, 1, 440),
(1, 'sitemap', 'Sitemap', NULL, '1970-01-01 00:00:00', 1, 1, 1, 'PageContent', 21, 1301429846, 1301429846, 1, 2, 444);

-- --------------------------------------------------------

--
-- Table structure for table `content_data`
--

CREATE TABLE IF NOT EXISTS `content_data` (
  `content_id` int(11) NOT NULL,
  `description` text COLLATE utf8_unicode_ci,
  `keywords` text COLLATE utf8_unicode_ci,
  `body` text COLLATE utf8_unicode_ci,
  `params` text COLLATE utf8_unicode_ci,
  PRIMARY KEY (`content_id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

--
-- Dumping data for table `content_data`
--

INSERT INTO `content_data` (`content_id`, `description`, `keywords`, `body`, `params`) VALUES
(1, 'This is the home page!', 'home, page', 'Here we should show some posts using a short tag...', NULL),
(2, NULL, NULL, NULL, 'a:2:{s:10:"controller";s:4:"user";s:6:"action";s:5:"login";}'),
(3, NULL, NULL, NULL, 'a:2:{s:10:"controller";s:4:"user";s:6:"action";s:6:"logout";}'),
(4, NULL, NULL, NULL, 'a:2:{s:10:"controller";s:4:"user";s:6:"action";s:6:"signup";}'),
(5, 'Dashboard ...', NULL, NULL, 'a:2:{s:10:"controller";s:4:"user";s:6:"action";s:5:"index";}'),
(6, 'User profile...', NULL, NULL, 'a:2:{s:10:"controller";s:4:"user";s:6:"action";s:4:"form";}'),
(7, 'Add contact...', NULL, NULL, 'a:2:{s:10:"controller";s:4:"user";s:6:"action";s:11:"contactform";}'),
(8, 'Modify contact...', NULL, NULL, 'a:2:{s:10:"controller";s:4:"user";s:6:"action";s:11:"contactform";}'),
(9, NULL, NULL, NULL, 'a:2:{s:10:"controller";s:7:"content";s:6:"action";s:6:"manage";}'),
(10, NULL, NULL, NULL, 'a:2:{s:10:"controller";s:7:"content";s:6:"action";s:4:"form";}'),
(11, NULL, NULL, NULL, 'a:2:{s:10:"controller";s:5:"media";s:6:"action";s:6:"manage";}'),
(12, NULL, NULL, NULL, 'a:2:{s:10:"controller";s:4:"user";s:6:"action";s:6:"manage";}'),
(13, NULL, NULL, NULL, 'a:2:{s:10:"controller";s:4:"user";s:6:"action";s:4:"form";}'),
(14, NULL, NULL, NULL, 'a:2:{s:10:"controller";s:7:"plugins";s:6:"action";s:5:"index";}'),
(15, NULL, NULL, NULL, 'a:2:{s:10:"controller";s:7:"plugins";s:6:"action";s:7:"options";}'),
(16, NULL, NULL, NULL, 'a:2:{s:10:"controller";s:6:"system";s:6:"action";s:9:"sysconfig";}'),
(17, NULL, NULL, NULL, 'a:2:{s:10:"controller";s:6:"system";s:6:"action";s:5:"index";}'),
(18, NULL, NULL, NULL, 'a:2:{s:10:"controller";s:6:"system";s:6:"action";s:3:"api";}'),
(19, NULL, NULL, NULL, 'a:2:{s:10:"controller";s:6:"system";s:6:"action";s:7:"upgrade";}'),
(20, NULL, NULL, NULL, 'a:2:{s:10:"controller";s:6:"system";s:6:"action";s:6:"backup";}'),
(21, 'This is the site map', 'sitemap', NULL, 'a:1:{s:4:"view";s:7:"sitemap";}');

-- --------------------------------------------------------

--
-- Table structure for table `countries`
--

CREATE TABLE IF NOT EXISTS `countries` (
  `iso` char(2) COLLATE utf8_unicode_ci NOT NULL,
  `name` varchar(80) COLLATE utf8_unicode_ci NOT NULL,
  `printable_name` varchar(80) COLLATE utf8_unicode_ci NOT NULL,
  `iso3` char(3) COLLATE utf8_unicode_ci DEFAULT NULL,
  `numcode` smallint(6) DEFAULT NULL,
  PRIMARY KEY (`iso`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

--
-- Dumping data for table `countries`
--

INSERT INTO `countries` (`iso`, `name`, `printable_name`, `iso3`, `numcode`) VALUES
('AF', 'AFGHANISTAN', 'Afghanistan', 'AFG', 4),
('AL', 'ALBANIA', 'Albania', 'ALB', 8),
('DZ', 'ALGERIA', 'Algeria', 'DZA', 12),
('AS', 'AMERICAN SAMOA', 'American Samoa', 'ASM', 16),
('AD', 'ANDORRA', 'Andorra', 'AND', 20),
('AO', 'ANGOLA', 'Angola', 'AGO', 24),
('AI', 'ANGUILLA', 'Anguilla', 'AIA', 660),
('AQ', 'ANTARCTICA', 'Antarctica', NULL, NULL),
('AG', 'ANTIGUA AND BARBUDA', 'Antigua and Barbuda', 'ATG', 28),
('AR', 'ARGENTINA', 'Argentina', 'ARG', 32),
('AM', 'ARMENIA', 'Armenia', 'ARM', 51),
('AW', 'ARUBA', 'Aruba', 'ABW', 533),
('AU', 'AUSTRALIA', 'Australia', 'AUS', 36),
('AT', 'AUSTRIA', 'Austria', 'AUT', 40),
('AZ', 'AZERBAIJAN', 'Azerbaijan', 'AZE', 31),
('BS', 'BAHAMAS', 'Bahamas', 'BHS', 44),
('BH', 'BAHRAIN', 'Bahrain', 'BHR', 48),
('BD', 'BANGLADESH', 'Bangladesh', 'BGD', 50),
('BB', 'BARBADOS', 'Barbados', 'BRB', 52),
('BY', 'BELARUS', 'Belarus', 'BLR', 112),
('BE', 'BELGIUM', 'Belgium', 'BEL', 56),
('BZ', 'BELIZE', 'Belize', 'BLZ', 84),
('BJ', 'BENIN', 'Benin', 'BEN', 204),
('BM', 'BERMUDA', 'Bermuda', 'BMU', 60),
('BT', 'BHUTAN', 'Bhutan', 'BTN', 64),
('BO', 'BOLIVIA', 'Bolivia', 'BOL', 68),
('BA', 'BOSNIA AND HERZEGOVINA', 'Bosnia and Herzegovina', 'BIH', 70),
('BW', 'BOTSWANA', 'Botswana', 'BWA', 72),
('BV', 'BOUVET ISLAND', 'Bouvet Island', NULL, NULL),
('BR', 'BRAZIL', 'Brazil', 'BRA', 76),
('IO', 'BRITISH INDIAN OCEAN TERRITORY', 'British Indian Ocean Territory', NULL, NULL),
('BN', 'BRUNEI DARUSSALAM', 'Brunei Darussalam', 'BRN', 96),
('BG', 'BULGARIA', 'Bulgaria', 'BGR', 100),
('BF', 'BURKINA FASO', 'Burkina Faso', 'BFA', 854),
('BI', 'BURUNDI', 'Burundi', 'BDI', 108),
('KH', 'CAMBODIA', 'Cambodia', 'KHM', 116),
('CM', 'CAMEROON', 'Cameroon', 'CMR', 120),
('CA', 'CANADA', 'Canada', 'CAN', 124),
('CV', 'CAPE VERDE', 'Cape Verde', 'CPV', 132),
('KY', 'CAYMAN ISLANDS', 'Cayman Islands', 'CYM', 136),
('CF', 'CENTRAL AFRICAN REPUBLIC', 'Central African Republic', 'CAF', 140),
('TD', 'CHAD', 'Chad', 'TCD', 148),
('CL', 'CHILE', 'Chile', 'CHL', 152),
('CN', 'CHINA', 'China', 'CHN', 156),
('CX', 'CHRISTMAS ISLAND', 'Christmas Island', NULL, NULL),
('CC', 'COCOS (KEELING) ISLANDS', 'Cocos (Keeling) Islands', NULL, NULL),
('CO', 'COLOMBIA', 'Colombia', 'COL', 170),
('KM', 'COMOROS', 'Comoros', 'COM', 174),
('CG', 'CONGO', 'Congo', 'COG', 178),
('CD', 'CONGO, THE DEMOCRATIC REPUBLIC OF THE', 'Congo, the Democratic Republic of the', 'COD', 180),
('CK', 'COOK ISLANDS', 'Cook Islands', 'COK', 184),
('CR', 'COSTA RICA', 'Costa Rica', 'CRI', 188),
('CI', 'COTE DIVOIRE', 'Cote DIvoire', 'CIV', 384),
('HR', 'CROATIA', 'Croatia', 'HRV', 191),
('CU', 'CUBA', 'Cuba', 'CUB', 192),
('CY', 'CYPRUS', 'Cyprus', 'CYP', 196),
('CZ', 'CZECH REPUBLIC', 'Czech Republic', 'CZE', 203),
('DK', 'DENMARK', 'Denmark', 'DNK', 208),
('DJ', 'DJIBOUTI', 'Djibouti', 'DJI', 262),
('DM', 'DOMINICA', 'Dominica', 'DMA', 212),
('DO', 'DOMINICAN REPUBLIC', 'Dominican Republic', 'DOM', 214),
('EC', 'ECUADOR', 'Ecuador', 'ECU', 218),
('EG', 'EGYPT', 'Egypt', 'EGY', 818),
('SV', 'EL SALVADOR', 'El Salvador', 'SLV', 222),
('GQ', 'EQUATORIAL GUINEA', 'Equatorial Guinea', 'GNQ', 226),
('ER', 'ERITREA', 'Eritrea', 'ERI', 232),
('EE', 'ESTONIA', 'Estonia', 'EST', 233),
('ET', 'ETHIOPIA', 'Ethiopia', 'ETH', 231),
('FK', 'FALKLAND ISLANDS (MALVINAS)', 'Falkland Islands (Malvinas)', 'FLK', 238),
('FO', 'FAROE ISLANDS', 'Faroe Islands', 'FRO', 234),
('FJ', 'FIJI', 'Fiji', 'FJI', 242),
('FI', 'FINLAND', 'Finland', 'FIN', 246),
('FR', 'FRANCE', 'France', 'FRA', 250),
('GF', 'FRENCH GUIANA', 'French Guiana', 'GUF', 254),
('PF', 'FRENCH POLYNESIA', 'French Polynesia', 'PYF', 258),
('TF', 'FRENCH SOUTHERN TERRITORIES', 'French Southern Territories', NULL, NULL),
('GA', 'GABON', 'Gabon', 'GAB', 266),
('GM', 'GAMBIA', 'Gambia', 'GMB', 270),
('GE', 'GEORGIA', 'Georgia', 'GEO', 268),
('DE', 'GERMANY', 'Germany', 'DEU', 276),
('GH', 'GHANA', 'Ghana', 'GHA', 288),
('GI', 'GIBRALTAR', 'Gibraltar', 'GIB', 292),
('GR', 'GREECE', 'Greece', 'GRC', 300),
('GL', 'GREENLAND', 'Greenland', 'GRL', 304),
('GD', 'GRENADA', 'Grenada', 'GRD', 308),
('GP', 'GUADELOUPE', 'Guadeloupe', 'GLP', 312),
('GU', 'GUAM', 'Guam', 'GUM', 316),
('GT', 'GUATEMALA', 'Guatemala', 'GTM', 320),
('GN', 'GUINEA', 'Guinea', 'GIN', 324),
('GW', 'GUINEA-BISSAU', 'Guinea-Bissau', 'GNB', 624),
('GY', 'GUYANA', 'Guyana', 'GUY', 328),
('HT', 'HAITI', 'Haiti', 'HTI', 332),
('HM', 'HEARD ISLAND AND MCDONALD ISLANDS', 'Heard Island and Mcdonald Islands', NULL, NULL),
('VA', 'HOLY SEE (VATICAN CITY STATE)', 'Holy See (Vatican City State)', 'VAT', 336),
('HN', 'HONDURAS', 'Honduras', 'HND', 340),
('HK', 'HONG KONG', 'Hong Kong', 'HKG', 344),
('HU', 'HUNGARY', 'Hungary', 'HUN', 348),
('IS', 'ICELAND', 'Iceland', 'ISL', 352),
('IN', 'INDIA', 'India', 'IND', 356),
('ID', 'INDONESIA', 'Indonesia', 'IDN', 360),
('IR', 'IRAN, ISLAMIC REPUBLIC OF', 'Iran, Islamic Republic of', 'IRN', 364),
('IQ', 'IRAQ', 'Iraq', 'IRQ', 368),
('IE', 'IRELAND', 'Ireland', 'IRL', 372),
('IL', 'ISRAEL', 'Israel', 'ISR', 376),
('IT', 'ITALY', 'Italy', 'ITA', 380),
('JM', 'JAMAICA', 'Jamaica', 'JAM', 388),
('JP', 'JAPAN', 'Japan', 'JPN', 392),
('JO', 'JORDAN', 'Jordan', 'JOR', 400),
('KZ', 'KAZAKHSTAN', 'Kazakhstan', 'KAZ', 398),
('KE', 'KENYA', 'Kenya', 'KEN', 404),
('KI', 'KIRIBATI', 'Kiribati', 'KIR', 296),
('KP', 'KOREA, DEMOCRATIC PEOPLES REPUBLIC OF', 'Korea, Democratic Peoples Republic of', 'PRK', 408),
('KR', 'KOREA, REPUBLIC OF', 'Korea, Republic of', 'KOR', 410),
('KW', 'KUWAIT', 'Kuwait', 'KWT', 414),
('KG', 'KYRGYZSTAN', 'Kyrgyzstan', 'KGZ', 417),
('LA', 'LAO PEOPLES DEMOCRATIC REPUBLIC', 'Lao Peoples Democratic Republic', 'LAO', 418),
('LV', 'LATVIA', 'Latvia', 'LVA', 428),
('LB', 'LEBANON', 'Lebanon', 'LBN', 422),
('LS', 'LESOTHO', 'Lesotho', 'LSO', 426),
('LR', 'LIBERIA', 'Liberia', 'LBR', 430),
('LY', 'LIBYAN ARAB JAMAHIRIYA', 'Libyan Arab Jamahiriya', 'LBY', 434),
('LI', 'LIECHTENSTEIN', 'Liechtenstein', 'LIE', 438),
('LT', 'LITHUANIA', 'Lithuania', 'LTU', 440),
('LU', 'LUXEMBOURG', 'Luxembourg', 'LUX', 442),
('MO', 'MACAO', 'Macao', 'MAC', 446),
('MK', 'MACEDONIA, THE FORMER YUGOSLAV REPUBLIC OF', 'Macedonia, the Former Yugoslav Republic of', 'MKD', 807),
('MG', 'MADAGASCAR', 'Madagascar', 'MDG', 450),
('MW', 'MALAWI', 'Malawi', 'MWI', 454),
('MY', 'MALAYSIA', 'Malaysia', 'MYS', 458),
('MV', 'MALDIVES', 'Maldives', 'MDV', 462),
('ML', 'MALI', 'Mali', 'MLI', 466),
('MT', 'MALTA', 'Malta', 'MLT', 470),
('MH', 'MARSHALL ISLANDS', 'Marshall Islands', 'MHL', 584),
('MQ', 'MARTINIQUE', 'Martinique', 'MTQ', 474),
('MR', 'MAURITANIA', 'Mauritania', 'MRT', 478),
('MU', 'MAURITIUS', 'Mauritius', 'MUS', 480),
('YT', 'MAYOTTE', 'Mayotte', NULL, NULL),
('MX', 'MEXICO', 'Mexico', 'MEX', 484),
('FM', 'MICRONESIA, FEDERATED STATES OF', 'Micronesia, Federated States of', 'FSM', 583),
('MD', 'MOLDOVA, REPUBLIC OF', 'Moldova, Republic of', 'MDA', 498),
('MC', 'MONACO', 'Monaco', 'MCO', 492),
('MN', 'MONGOLIA', 'Mongolia', 'MNG', 496),
('MS', 'MONTSERRAT', 'Montserrat', 'MSR', 500),
('MA', 'MOROCCO', 'Morocco', 'MAR', 504),
('MZ', 'MOZAMBIQUE', 'Mozambique', 'MOZ', 508),
('MM', 'MYANMAR', 'Myanmar', 'MMR', 104),
('NA', 'NAMIBIA', 'Namibia', 'NAM', 516),
('NR', 'NAURU', 'Nauru', 'NRU', 520),
('NP', 'NEPAL', 'Nepal', 'NPL', 524),
('NL', 'NETHERLANDS', 'Netherlands', 'NLD', 528),
('AN', 'NETHERLANDS ANTILLES', 'Netherlands Antilles', 'ANT', 530),
('NC', 'NEW CALEDONIA', 'New Caledonia', 'NCL', 540),
('NZ', 'NEW ZEALAND', 'New Zealand', 'NZL', 554),
('NI', 'NICARAGUA', 'Nicaragua', 'NIC', 558),
('NE', 'NIGER', 'Niger', 'NER', 562),
('NG', 'NIGERIA', 'Nigeria', 'NGA', 566),
('NU', 'NIUE', 'Niue', 'NIU', 570),
('NF', 'NORFOLK ISLAND', 'Norfolk Island', 'NFK', 574),
('MP', 'NORTHERN MARIANA ISLANDS', 'Northern Mariana Islands', 'MNP', 580),
('NO', 'NORWAY', 'Norway', 'NOR', 578),
('OM', 'OMAN', 'Oman', 'OMN', 512),
('PK', 'PAKISTAN', 'Pakistan', 'PAK', 586),
('PW', 'PALAU', 'Palau', 'PLW', 585),
('PS', 'PALESTINIAN TERRITORY, OCCUPIED', 'Palestinian Territory, Occupied', NULL, NULL),
('PA', 'PANAMA', 'Panama', 'PAN', 591),
('PG', 'PAPUA NEW GUINEA', 'Papua New Guinea', 'PNG', 598),
('PY', 'PARAGUAY', 'Paraguay', 'PRY', 600),
('PE', 'PERU', 'Peru', 'PER', 604),
('PH', 'PHILIPPINES', 'Philippines', 'PHL', 608),
('PN', 'PITCAIRN', 'Pitcairn', 'PCN', 612),
('PL', 'POLAND', 'Poland', 'POL', 616),
('PT', 'PORTUGAL', 'Portugal', 'PRT', 620),
('PR', 'PUERTO RICO', 'Puerto Rico', 'PRI', 630),
('QA', 'QATAR', 'Qatar', 'QAT', 634),
('RE', 'REUNION', 'Reunion', 'REU', 638),
('RO', 'ROMANIA', 'Romania', 'ROM', 642),
('RU', 'RUSSIAN FEDERATION', 'Russian Federation', 'RUS', 643),
('RW', 'RWANDA', 'Rwanda', 'RWA', 646),
('SH', 'SAINT HELENA', 'Saint Helena', 'SHN', 654),
('KN', 'SAINT KITTS AND NEVIS', 'Saint Kitts and Nevis', 'KNA', 659),
('LC', 'SAINT LUCIA', 'Saint Lucia', 'LCA', 662),
('PM', 'SAINT PIERRE AND MIQUELON', 'Saint Pierre and Miquelon', 'SPM', 666),
('VC', 'SAINT VINCENT AND THE GRENADINES', 'Saint Vincent and the Grenadines', 'VCT', 670),
('WS', 'SAMOA', 'Samoa', 'WSM', 882),
('SM', 'SAN MARINO', 'San Marino', 'SMR', 674),
('ST', 'SAO TOME AND PRINCIPE', 'Sao Tome and Principe', 'STP', 678),
('SA', 'SAUDI ARABIA', 'Saudi Arabia', 'SAU', 682),
('SN', 'SENEGAL', 'Senegal', 'SEN', 686),
('CS', 'SERBIA AND MONTENEGRO', 'Serbia and Montenegro', NULL, NULL),
('SC', 'SEYCHELLES', 'Seychelles', 'SYC', 690),
('SL', 'SIERRA LEONE', 'Sierra Leone', 'SLE', 694),
('SG', 'SINGAPORE', 'Singapore', 'SGP', 702),
('SK', 'SLOVAKIA', 'Slovakia', 'SVK', 703),
('SI', 'SLOVENIA', 'Slovenia', 'SVN', 705),
('SB', 'SOLOMON ISLANDS', 'Solomon Islands', 'SLB', 90),
('SO', 'SOMALIA', 'Somalia', 'SOM', 706),
('ZA', 'SOUTH AFRICA', 'South Africa', 'ZAF', 710),
('GS', 'SOUTH GEORGIA AND THE SOUTH SANDWICH ISLANDS', 'South Georgia and the South Sandwich Islands', NULL, NULL),
('ES', 'SPAIN', 'Spain', 'ESP', 724),
('LK', 'SRI LANKA', 'Sri Lanka', 'LKA', 144),
('SD', 'SUDAN', 'Sudan', 'SDN', 736),
('SR', 'SURINAME', 'Suriname', 'SUR', 740),
('SJ', 'SVALBARD AND JAN MAYEN', 'Svalbard and Jan Mayen', 'SJM', 744),
('SZ', 'SWAZILAND', 'Swaziland', 'SWZ', 748),
('SE', 'SWEDEN', 'Sweden', 'SWE', 752),
('CH', 'SWITZERLAND', 'Switzerland', 'CHE', 756),
('SY', 'SYRIAN ARAB REPUBLIC', 'Syrian Arab Republic', 'SYR', 760),
('TW', 'TAIWAN, PROVINCE OF CHINA', 'Taiwan, Province of China', 'TWN', 158),
('TJ', 'TAJIKISTAN', 'Tajikistan', 'TJK', 762),
('TZ', 'TANZANIA, UNITED REPUBLIC OF', 'Tanzania, United Republic of', 'TZA', 834),
('TH', 'THAILAND', 'Thailand', 'THA', 764),
('TL', 'TIMOR-LESTE', 'Timor-Leste', NULL, NULL),
('TG', 'TOGO', 'Togo', 'TGO', 768),
('TK', 'TOKELAU', 'Tokelau', 'TKL', 772),
('TO', 'TONGA', 'Tonga', 'TON', 776),
('TT', 'TRINIDAD AND TOBAGO', 'Trinidad and Tobago', 'TTO', 780),
('TN', 'TUNISIA', 'Tunisia', 'TUN', 788),
('TR', 'TURKEY', 'Turkey', 'TUR', 792),
('TM', 'TURKMENISTAN', 'Turkmenistan', 'TKM', 795),
('TC', 'TURKS AND CAICOS ISLANDS', 'Turks and Caicos Islands', 'TCA', 796),
('TV', 'TUVALU', 'Tuvalu', 'TUV', 798),
('UG', 'UGANDA', 'Uganda', 'UGA', 800),
('UA', 'UKRAINE', 'Ukraine', 'UKR', 804),
('AE', 'UNITED ARAB EMIRATES', 'United Arab Emirates', 'ARE', 784),
('GB', 'UNITED KINGDOM', 'United Kingdom', 'GBR', 826),
('US', 'UNITED STATES', 'United States', 'USA', 840),
('UM', 'UNITED STATES MINOR OUTLYING ISLANDS', 'United States Minor Outlying Islands', NULL, NULL),
('UY', 'URUGUAY', 'Uruguay', 'URY', 858),
('UZ', 'UZBEKISTAN', 'Uzbekistan', 'UZB', 860),
('VU', 'VANUATU', 'Vanuatu', 'VUT', 548),
('VE', 'VENEZUELA', 'Venezuela', 'VEN', 862),
('VN', 'VIET NAM', 'Viet Nam', 'VNM', 704),
('VG', 'VIRGIN ISLANDS, BRITISH', 'Virgin Islands, British', 'VGB', 92),
('VI', 'VIRGIN ISLANDS, U.S.', 'Virgin Islands, U.s.', 'VIR', 850),
('WF', 'WALLIS AND FUTUNA', 'Wallis and Futuna', 'WLF', 876),
('EH', 'WESTERN SAHARA', 'Western Sahara', 'ESH', 732),
('YE', 'YEMEN', 'Yemen', 'YEM', 887),
('ZM', 'ZAMBIA', 'Zambia', 'ZMB', 894),
('ZW', 'ZIMBABWE', 'Zimbabwe', 'ZWE', 716);

-- --------------------------------------------------------

--
-- Table structure for table `groups`
--

CREATE TABLE IF NOT EXISTS `groups` (
  `name` varchar(50) COLLATE utf8_unicode_ci NOT NULL,
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `ctime` int(11) DEFAULT NULL,
  `mtime` int(11) DEFAULT NULL,
  `owner` int(11) NOT NULL DEFAULT '0',
  `group` int(11) NOT NULL DEFAULT '0',
  `perms` int(11) NOT NULL DEFAULT '664',
  PRIMARY KEY (`id`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci AUTO_INCREMENT=4 ;

--
-- Dumping data for table `groups`
--

INSERT INTO `groups` (`name`, `id`, `ctime`, `mtime`, `owner`, `group`, `perms`) VALUES
('wheel', 1, 1301429845, 1301429845, 1, 1, 444),
('staff', 2, 1301429845, 1301429845, 1, 1, 444),
('registered', 3, 1301429845, 1301429845, 1, 1, 444);

-- --------------------------------------------------------

--
-- Table structure for table `notifications`
--

CREATE TABLE IF NOT EXISTS `notifications` (
  `title` varchar(50) COLLATE utf8_unicode_ci NOT NULL,
  `body` varchar(140) COLLATE utf8_unicode_ci DEFAULT NULL,
  `type` enum('error','warning','notice','info','success') COLLATE utf8_unicode_ci NOT NULL DEFAULT 'info',
  `sticky` tinyint(1) NOT NULL DEFAULT '0',
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `ctime` int(11) DEFAULT NULL,
  `mtime` int(11) DEFAULT NULL,
  `owner` int(11) NOT NULL DEFAULT '0',
  `group` int(11) NOT NULL DEFAULT '0',
  `perms` int(11) NOT NULL DEFAULT '664',
  PRIMARY KEY (`id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci AUTO_INCREMENT=1 ;

--
-- Dumping data for table `notifications`
--


-- --------------------------------------------------------

--
-- Table structure for table `oauth_acl`
--

CREATE TABLE IF NOT EXISTS `oauth_acl` (
  `client_id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `resource` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `token` varchar(30) COLLATE utf8_unicode_ci NOT NULL,
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `ctime` int(11) DEFAULT NULL,
  `mtime` int(11) DEFAULT NULL,
  `owner` int(11) NOT NULL DEFAULT '0',
  `group` int(11) NOT NULL DEFAULT '0',
  `perms` int(11) NOT NULL DEFAULT '440',
  PRIMARY KEY (`id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci AUTO_INCREMENT=1 ;

--
-- Dumping data for table `oauth_acl`
--


-- --------------------------------------------------------

--
-- Table structure for table `oauth_clients`
--

CREATE TABLE IF NOT EXISTS `oauth_clients` (
  `name` varchar(50) COLLATE utf8_unicode_ci NOT NULL,
  `version` varchar(20) COLLATE utf8_unicode_ci NOT NULL,
  `vendor` varchar(100) COLLATE utf8_unicode_ci NOT NULL,
  `key` varchar(30) COLLATE utf8_unicode_ci NOT NULL,
  `secret` varchar(10) COLLATE utf8_unicode_ci NOT NULL,
  `hosted` tinyint(1) NOT NULL DEFAULT '0',
  `status` enum('active','throttled','blacklisted') COLLATE utf8_unicode_ci NOT NULL DEFAULT 'active',
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `ctime` int(11) DEFAULT NULL,
  `mtime` int(11) DEFAULT NULL,
  `owner` int(11) NOT NULL DEFAULT '0',
  `group` int(11) NOT NULL DEFAULT '2',
  `perms` int(11) NOT NULL DEFAULT '660',
  PRIMARY KEY (`id`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci AUTO_INCREMENT=2 ;

--
-- Dumping data for table `oauth_clients`
--

-- INSERT INTO `oauth_clients` (`name`, `version`, `vendor`, `key`, `secret`, `hosted`, `status`, `id`, `ctime`, `mtime`, `owner`, `group`, `perms`) VALUES
-- ('API Browser', '1.0', 'Mashine Project', '317515ec6ed7a62ad1f7e5eed4d41d', '614b419f10', 0, 'active', 1, 1301429845, 1301429845, 0, 2, 660);

-- --------------------------------------------------------

--
-- Table structure for table `oauth_tokens`
--

CREATE TABLE IF NOT EXISTS `oauth_tokens` (
  `key` varchar(30) COLLATE utf8_unicode_ci NOT NULL DEFAULT 'f644c6f34580c73e8dc2777555ad59',
  `secret` varchar(10) COLLATE utf8_unicode_ci NOT NULL DEFAULT '8f3abb0e62',
  `consumer_key` varchar(30) COLLATE utf8_unicode_ci NOT NULL,
  `type` enum('request','access') COLLATE utf8_unicode_ci NOT NULL,
  `status` enum('active','used','revoked') COLLATE utf8_unicode_ci NOT NULL DEFAULT 'active',
  `callback` varchar(100) COLLATE utf8_unicode_ci DEFAULT NULL,
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `ctime` int(11) DEFAULT NULL,
  `mtime` int(11) DEFAULT NULL,
  `owner` int(11) NOT NULL DEFAULT '0',
  `group` int(11) NOT NULL DEFAULT '0',
  `perms` int(11) NOT NULL DEFAULT '440',
  PRIMARY KEY (`id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci AUTO_INCREMENT=1 ;

--
-- Dumping data for table `oauth_tokens`
--


-- --------------------------------------------------------

--
-- Table structure for table `options`
--

CREATE TABLE IF NOT EXISTS `options` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `name` varchar(100) COLLATE utf8_unicode_ci NOT NULL,
  `value` text COLLATE utf8_unicode_ci NOT NULL,
  `autoload` tinyint(4) NOT NULL DEFAULT '1',
  PRIMARY KEY (`id`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci AUTO_INCREMENT=17 ;

--
-- Dumping data for table `options`
--

INSERT INTO `options` (`id`, `name`, `value`, `autoload`) VALUES
(1, 'mashineplugin_version', '0.1.7-beta', 1),
(2, 'mediaplugin_version', '1.0', 1),
(3, 'mediaplugin_upload_dir', 'media', 1),
(4, 'mediaplugin_mode', 'lightbox', 1),
(5, 'mediaplugin_order_by', 'filename', 1),
(6, 'mediaplugin_order_direction', 'ASC', 1),
(7, 'mediaplugin_max_width', '560', 1),
(8, 'mediaplugin_max_height', '400', 1),
(9, 'mediaplugin_thumb_width', '120', 1),
(10, 'mediaplugin_thumb_height', '90', 1),
(11, 'mediaplugin_imgcomp', '0', 1),
(12, 'contactformplugin_version', '1.0', 1),
(13, 'socialplugin_version', '1.0', 1),
(14, 'syntaxhighlighterplugin_version', '1.0', 1),
(15, 'syntaxhighlighterplugin_langs', 'a:0:{}', 1),
(16, 'syntaxhighlighterplugin_theme', 'Default', 1);

-- --------------------------------------------------------

--
-- Table structure for table `users`
--

CREATE TABLE IF NOT EXISTS `users` (
  `status` enum('pending','active','suspended','cancelled') COLLATE utf8_unicode_ci NOT NULL DEFAULT 'pending',
  `notifications` tinyint(1) NOT NULL DEFAULT '1',
  `activation` varchar(100) COLLATE utf8_unicode_ci DEFAULT NULL,
  `group_id` int(11) NOT NULL DEFAULT '0',
  `email` varchar(100) COLLATE utf8_unicode_ci NOT NULL,
  `password` varchar(100) COLLATE utf8_unicode_ci NOT NULL,
  `params` text COLLATE utf8_unicode_ci,
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `ctime` int(11) DEFAULT NULL,
  `mtime` int(11) DEFAULT NULL,
  `owner` int(11) NOT NULL DEFAULT '0',
  `group` int(11) NOT NULL DEFAULT '2',
  `perms` int(11) NOT NULL DEFAULT '664',
  PRIMARY KEY (`id`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci ;

--
-- Dumping data for table `users`
--

-- INSERT INTO `users` (`status`, `notifications`, `activation`, `group_id`, `email`, `password`, `params`, `id`, `ctime`, `mtime`, `owner`, `group`, `perms`) VALUES
-- ('active', 1, NULL, 1, 'root@example.com', '445ae8d1d9a1e2540b060ccca36466f2:665e7801b86468fd1f196e6ded74ff25', '', 1, 1301429845, 1301429845, 1, 1, 440);

-- --------------------------------------------------------

--
-- Table structure for table `users_social`
--

CREATE TABLE IF NOT EXISTS `users_social` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `user_id` int(11) NOT NULL,
  `facebook_id` int(11) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci AUTO_INCREMENT=1 ;

--
-- Dumping data for table `users_social`
--
