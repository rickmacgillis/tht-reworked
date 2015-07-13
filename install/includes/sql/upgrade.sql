SET SQL_MODE="NO_AUTO_VALUE_ON_ZERO";
SET time_zone = "+00:00";
-- --------------------------------------------------------

DELETE FROM `%PRE%acpnav`;

INSERT INTO `%PRE%acpnav` (`id`, `visual`, `icon`, `link`) VALUES
(1, 'Home', 'house.png', 'home'),
(3, 'General Settings', 'cog.png', 'settings'),
(5, 'Servers', 'server.png', 'servers'),
(6, 'Packages', 'package_green.png', 'packages'),
(12, 'Staff Accounts', 'user_gray.png', 'staff'),
(7, 'Subdomains', 'link.png', 'sub'),
(10, 'Clients', 'group.png', 'users'),
(2, 'Change Password', 'shield.png', 'pass'),
(14, 'Mail Center', 'email_open.png', 'email'),
(15, 'Tickets', 'page_white_text.png', 'tickets'),
(16, 'Knowledge Base', 'folder.png', 'kb'),
(17, 'Look & Feel', 'rainbow.png', 'lof'),
(11, 'Invoice Management', 'script.png', 'invoices'),
(18, 'Logs', 'report.png', 'logs'),
(19, 'AutoMod', 'box.png', 'automod'),
(9, 'Coupons', 'award_star_silver_3.png', 'coupons'),
(4, 'System Tools', 'error.png', 'system');

-- --------------------------------------------------------

--
-- Table structure for table `%PRE%admin_notes`
--

CREATE TABLE IF NOT EXISTS `%PRE%admin_notes` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `notes` text NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 AUTO_INCREMENT=2 ;

--
-- Dumping data for table `%PRE%admin_notes`
--

INSERT INTO `%PRE%admin_notes` (`id`, `notes`) VALUES
(1, '<p><strong>Welcome to your TheHostingTool v1.3.10 Reworked Installation!</strong></p>\n<p>We hope that you like TheHostingTool and you have a good time with our script. If you need any help, you can ask at the THT Community, or contact us directly. Thanks for using TheHostingTool, and good luck on your hosting service!</p>\n<p>- The THT Team</p>');

-- --------------------------------------------------------

--
-- Dumping data for table `%PRE%config`
--

UPDATE `%PRE%config` SET value = '1.3.10 Reworked' WHERE name = 'version';
UPDATE `%PRE%config` SET value = 'black-tie' WHERE name = 'ui-theme';
UPDATE `%PRE%config` SET name = 'default_page' WHERE name = 'default';

INSERT INTO `%PRE%config` SET name = 'smtp_port', value = '25';
INSERT INTO `%PRE%config` SET name = 'multicoupons', value = '0';
INSERT INTO `%PRE%config` SET name = 'last_tld_update', value = 'never';
INSERT INTO `%PRE%config` SET name = 'tld_update_days', value = '30';
INSERT INTO `%PRE%config` SET name = 'show_errors', value = '0';
INSERT INTO `%PRE%config` SET name = 'currency_format', value = '.';
INSERT INTO `%PRE%config` SET name = 'session_timeout', value = '30';
INSERT INTO `%PRE%config` SET name = 'email_for_cron', value = 'email@example.com';
INSERT INTO `%PRE%config` SET name = 'p2hgraceperiod', value = '5';

DELETE FROM `%PRE%config` WHERE name = 'whm-ssl';
DELETE FROM `%PRE%config` WHERE name = 'show_acp_menu';
DELETE FROM `%PRE%config` WHERE name = 'automodvers';
DELETE FROM `%PRE%config` WHERE name = 'useakismet';
DELETE FROM `%PRE%config` WHERE name = 'akismetkey';
DELETE FROM `%PRE%config` WHERE name = 'senabled' LIMIT 1;  -- Delete the duplicate
DELETE FROM `%PRE%config` WHERE name = 'smtp_user' LIMIT 1; -- Delete the duplicate

-- --------------------------------------------------------

--
-- Table structure for table `%PRE%countries`
--

CREATE TABLE IF NOT EXISTS `%PRE%countries` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `code` varchar(2) NOT NULL,
  `country` varchar(70) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 AUTO_INCREMENT=245 ;

--
-- Dumping data for table `%PRE%countries`
--

INSERT INTO `%PRE%countries` (`id`, `code`, `country`) VALUES
(1, 'af', 'Afghanistan '),
(2, 'ax', 'Aland Islands '),
(3, 'al', 'Albania '),
(4, 'dz', 'Algeria '),
(5, 'as', 'American Samoa '),
(6, 'ad', 'Andorra '),
(7, 'ao', 'Angola '),
(8, 'ai', 'Anguilla '),
(9, 'aq', 'Antarctica '),
(10, 'ag', 'Antigua and Barbuda '),
(11, 'ar', 'Argentina '),
(12, 'am', 'Armenia '),
(13, 'aw', 'Aruba '),
(14, 'au', 'Australia '),
(15, 'at', 'Austria '),
(16, 'az', 'Azerbaijan '),
(17, 'bs', 'Bahamas '),
(18, 'bh', 'Bahrain '),
(19, 'bd', 'Bangladesh '),
(20, 'bb', 'Barbados '),
(21, 'by', 'Belarus '),
(22, 'be', 'Belgium '),
(23, 'bz', 'Belize '),
(24, 'bj', 'Benin '),
(25, 'bm', 'Bermuda '),
(26, 'bt', 'Bhutan '),
(27, 'bo', 'Bolivia '),
(28, 'ba', 'Bosnia and Herzegovina '),
(29, 'bw', 'Botswana '),
(30, 'bv', 'Bouvet Island '),
(31, 'br', 'Brazil '),
(32, 'io', 'British Indian Ocean Territory '),
(33, 'bn', 'Brunei Darussalam '),
(34, 'bg', 'Bulgaria '),
(35, 'bf', 'Burkina Faso '),
(36, 'bi', 'Burundi '),
(37, 'kh', 'Cambodia '),
(38, 'cm', 'Cameroon '),
(39, 'ca', 'Canada '),
(40, 'cv', 'Cape Verde '),
(41, 'ky', 'Cayman Islands '),
(42, 'cf', 'Central African Republic '),
(43, 'td', 'Chad '),
(44, 'cl', 'Chile '),
(45, 'cn', 'China '),
(46, 'cx', 'Christmas Island '),
(47, 'cc', 'Cocos (Keeling) Islands '),
(48, 'co', 'Colombia '),
(49, 'km', 'Comoros '),
(50, 'cg', 'Congo '),
(51, 'cd', 'Congo, The Democratic Republic of The '),
(52, 'ck', 'Cook Islands '),
(53, 'cr', 'Costa Rica '),
(54, 'ci', 'Cote D''ivoire '),
(55, 'hr', 'Croatia '),
(56, 'cu', 'Cuba '),
(57, 'cy', 'Cyprus '),
(58, 'cz', 'Czech Republic '),
(59, 'dk', 'Denmark '),
(60, 'dj', 'Djibouti '),
(61, 'dm', 'Dominica '),
(62, 'do', 'Dominican Republic '),
(63, 'ec', 'Ecuador '),
(64, 'eg', 'Egypt '),
(65, 'sv', 'El Salvador '),
(66, 'gq', 'Equatorial Guinea '),
(67, 'er', 'Eritrea '),
(68, 'ee', 'Estonia '),
(69, 'et', 'Ethiopia '),
(70, 'fk', 'Falkland Islands (Malvinas) '),
(71, 'fo', 'Faroe Islands '),
(72, 'fj', 'Fiji '),
(73, 'fi', 'Finland '),
(74, 'fr', 'France '),
(75, 'gf', 'French Guiana '),
(76, 'pf', 'French Polynesia '),
(77, 'tf', 'French Southern Territories '),
(78, 'ga', 'Gabon '),
(79, 'gm', 'Gambia '),
(80, 'ge', 'Georgia '),
(81, 'de', 'Germany '),
(82, 'gh', 'Ghana '),
(83, 'gi', 'Gibraltar '),
(84, 'gr', 'Greece '),
(85, 'gl', 'Greenland '),
(86, 'gd', 'Grenada '),
(87, 'gp', 'Guadeloupe '),
(88, 'gu', 'Guam '),
(89, 'gt', 'Guatemala '),
(90, 'gg', 'Guernsey '),
(91, 'gn', 'Guinea '),
(92, 'gw', 'Guinea-bissau '),
(93, 'gy', 'Guyana '),
(94, 'ht', 'Haiti '),
(95, 'hm', 'Heard Island and Mcdonald Islands '),
(96, 'va', 'Holy See (Vatican City State) '),
(97, 'hn', 'Honduras '),
(98, 'hk', 'Hong Kong '),
(99, 'hu', 'Hungary '),
(100, 'is', 'Iceland '),
(101, 'in', 'India '),
(102, 'id', 'Indonesia '),
(103, 'ir', 'Iran, Islamic Republic of '),
(104, 'iq', 'Iraq '),
(105, 'ie', 'Ireland '),
(106, 'im', 'Isle of Man '),
(107, 'il', 'Israel '),
(108, 'it', 'Italy '),
(109, 'jm', 'Jamaica '),
(110, 'jp', 'Japan '),
(111, 'je', 'Jersey '),
(112, 'jo', 'Jordan '),
(113, 'kz', 'Kazakhstan '),
(114, 'ke', 'Kenya '),
(115, 'ki', 'Kiribati '),
(116, 'kp', 'Korea, Democratic People''s Republic of '),
(117, 'kr', 'Korea, Republic of '),
(118, 'kw', 'Kuwait '),
(119, 'kg', 'Kyrgyzstan '),
(120, 'la', 'Lao People''s Democratic Republic '),
(121, 'lv', 'Latvia '),
(122, 'lb', 'Lebanon '),
(123, 'ls', 'Lesotho '),
(124, 'lr', 'Liberia '),
(125, 'ly', 'Libyan Arab Jamahiriya '),
(126, 'li', 'Liechtenstein '),
(127, 'lt', 'Lithuania '),
(128, 'lu', 'Luxembourg '),
(129, 'mo', 'Macao '),
(130, 'mk', 'Macedonia, The Former Yugoslav Republic of '),
(131, 'mg', 'Madagascar '),
(132, 'mw', 'Malawi '),
(133, 'my', 'Malaysia '),
(134, 'mv', 'Maldives '),
(135, 'ml', 'Mali '),
(136, 'mt', 'Malta '),
(137, 'mh', 'Marshall Islands '),
(138, 'mq', 'Martinique '),
(139, 'mr', 'Mauritania '),
(140, 'mu', 'Mauritius '),
(141, 'yt', 'Mayotte '),
(142, 'mx', 'Mexico '),
(143, 'fm', 'Micronesia, Federated States of '),
(144, 'md', 'Moldova, Republic of '),
(145, 'mc', 'Monaco '),
(146, 'mn', 'Mongolia '),
(147, 'me', 'Montenegro '),
(148, 'ms', 'Montserrat '),
(149, 'ma', 'Morocco '),
(150, 'mz', 'Mozambique '),
(151, 'mm', 'Myanmar '),
(152, 'na', 'Namibia '),
(153, 'nr', 'Nauru '),
(154, 'np', 'Nepal '),
(155, 'nl', 'Netherlands '),
(156, 'an', 'Netherlands Antilles '),
(157, 'nc', 'New Caledonia '),
(158, 'nz', 'New Zealand '),
(159, 'ni', 'Nicaragua '),
(160, 'ne', 'Niger '),
(161, 'ng', 'Nigeria '),
(162, 'nu', 'Niue '),
(163, 'nf', 'Norfolk Island '),
(164, 'mp', 'Northern Mariana Islands '),
(165, 'no', 'Norway '),
(166, 'om', 'Oman '),
(167, 'pk', 'Pakistan '),
(168, 'pw', 'Palau '),
(169, 'ps', 'Palestinian Territory, Occupied '),
(170, 'pa', 'Panama '),
(171, 'pg', 'Papua New Guinea '),
(172, 'py', 'Paraguay '),
(173, 'pe', 'Peru '),
(174, 'ph', 'Philippines '),
(175, 'pn', 'Pitcairn '),
(176, 'pl', 'Poland '),
(177, 'pt', 'Portugal '),
(178, 'pr', 'Puerto Rico '),
(179, 'qa', 'Qatar '),
(180, 're', 'Reunion '),
(181, 'ro', 'Romania '),
(182, 'ru', 'Russian Federation '),
(183, 'rw', 'Rwanda '),
(184, 'sh', 'Saint Helena '),
(185, 'kn', 'Saint Kitts and Nevis '),
(186, 'lc', 'Saint Lucia '),
(187, 'pm', 'Saint Pierre and Miquelon '),
(188, 'vc', 'Saint Vincent and The Grenadines '),
(189, 'ws', 'Samoa '),
(190, 'sm', 'San Marino '),
(191, 'st', 'Sao Tome and Principe '),
(192, 'sa', 'Saudi Arabia '),
(193, 'sn', 'Senegal '),
(194, 'rs', 'Serbia '),
(195, 'sc', 'Seychelles '),
(196, 'sl', 'Sierra Leone '),
(197, 'sg', 'Singapore '),
(198, 'sk', 'Slovakia '),
(199, 'si', 'Slovenia '),
(200, 'sb', 'Solomon Islands '),
(201, 'so', 'Somalia '),
(202, 'za', 'South Africa '),
(203, 'gs', 'South Georgia and The South Sandwich Islands '),
(204, 'es', 'Spain '),
(205, 'lk', 'Sri Lanka '),
(206, 'sd', 'Sudan '),
(207, 'sr', 'Suriname '),
(208, 'sj', 'Svalbard and Jan Mayen '),
(209, 'sz', 'Swaziland '),
(210, 'se', 'Sweden '),
(211, 'ch', 'Switzerland '),
(212, 'sy', 'Syrian Arab Republic '),
(213, 'tw', 'Taiwan, Province of China '),
(214, 'tj', 'Tajikistan '),
(215, 'tz', 'Tanzania, United Republic of '),
(216, 'th', 'Thailand '),
(217, 'tl', 'Timor-leste '),
(218, 'tg', 'Togo '),
(219, 'tk', 'Tokelau '),
(220, 'to', 'Tonga '),
(221, 'tt', 'Trinidad and Tobago '),
(222, 'tn', 'Tunisia '),
(223, 'tr', 'Turkey '),
(224, 'tm', 'Turkmenistan '),
(225, 'tc', 'Turks and Caicos Islands '),
(226, 'tv', 'Tuvalu '),
(227, 'ug', 'Uganda '),
(228, 'ua', 'Ukraine '),
(229, 'ae', 'United Arab Emirates '),
(230, 'gb', 'United Kingdom '),
(231, 'us', 'United States '),
(232, 'um', 'United States Minor Outlying Islands '),
(233, 'uy', 'Uruguay '),
(234, 'uz', 'Uzbekistan '),
(235, 'vu', 'Vanuatu '),
(236, 've', 'Venezuela '),
(237, 'vn', 'Viet Nam '),
(238, 'vg', 'Virgin Islands, British '),
(239, 'vi', 'Virgin Islands, U.S. '),
(240, 'wf', 'Wallis and Futuna '),
(241, 'eh', 'Western Sahara '),
(242, 'ye', 'Yemen '),
(243, 'zm', 'Zambia '),
(244, 'zw', 'Zimbabwe ');

-- --------------------------------------------------------

ALTER TABLE `%PRE%mod_navens_coupons` RENAME `%PRE%coupons`;
ALTER TABLE `%PRE%mod_navens_coupons_p2h` RENAME `%PRE%coupons_p2h`;
ALTER TABLE `%PRE%mod_navens_coupons_used` RENAME `%PRE%coupons_used`;
DROP TABLE `%PRE%mod_navens_coupons_config`;

ALTER TABLE `%PRE%invoices` CHANGE `created` `created` VARCHAR( 11 ) NOT NULL;
ALTER TABLE `%PRE%invoices` CHANGE `due` `due` VARCHAR( 11 ) NOT NULL;
ALTER TABLE `%PRE%invoices` DROP `locked`;
ALTER TABLE `%PRE%invoices` ADD `changed_plan` INT( 11 ) NOT NULL;

ALTER TABLE `%PRE%logs` ADD `logtype` varchar( 50 ) NOT NULL;

ALTER TABLE `%PRE%navbar` CHANGE `order` `sortorder` INT( 11 ) NOT NULL;
ALTER TABLE `%PRE%navbar` CHANGE `link` `link` VARCHAR( 150 ) NOT NULL;

ALTER TABLE `%PRE%orderfields` CHANGE `order` `sortorder` INT( 11 ) NOT NULL;

-- --------------------------------------------------------

--
-- Table structure for table `%PRE%p2h`
--

CREATE TABLE IF NOT EXISTS `%PRE%p2h` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `forumname` varchar(50) NOT NULL,
  `username` varchar(25) NOT NULL,
  `password` varchar(50) NOT NULL,
  `forumdb` varchar(25) NOT NULL,
  `hostname` varchar(100) NOT NULL,
  `prefix` varchar(20) NOT NULL,
  `forumtype` varchar(20) NOT NULL,
  `url` text NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 AUTO_INCREMENT=1 ;

-- --------------------------------------------------------

ALTER TABLE `%PRE%packages` ADD `groupid` INT( 6 ) NOT NULL;
ALTER TABLE `%PRE%packages` ADD `send_email` INT( 1 ) NOT NULL;
ALTER TABLE `%PRE%packages` ADD `email_subject` VARCHAR( 255 ) NOT NULL;
ALTER TABLE `%PRE%packages` ADD `email_body` TEXT NOT NULL;
ALTER TABLE `%PRE%packages` CHANGE `order` `sortorder` INT( 11 ) NOT NULL;

DROP TABLE `%PRE%resources`;

ALTER TABLE `%PRE%servers` ADD `reseller_id` INT( 6 ) NOT NULL;
ALTER TABLE `%PRE%servers` ADD `pass` VARCHAR( 255 ) NOT NULL;
ALTER TABLE `%PRE%servers` ADD `https` INT( 11 ) NOT NULL;
ALTER TABLE `%PRE%servers` ADD `apiport` INT( 11 ) NOT NULL;
ALTER TABLE `%PRE%servers` CHANGE `whmport` `resellerport` VARCHAR( 5 ) NOT NULL;


ALTER TABLE `%PRE%staff` CHANGE `password` `password` VARCHAR( 255 ) NOT NULL;
ALTER TABLE `%PRE%staff` CHANGE `salt` `salt` VARCHAR( 255 ) NOT NULL;

ALTER TABLE `%PRE%subdomains` CHANGE `subdomain` `domain` VARCHAR( 50 ) NOT NULL;

ALTER TABLE `%PRE%templates` ADD `dir` VARCHAR( 50 ) NOT NULL;

DELETE FROM `%PRE%templates`;

-- --------------------------------------------------------

--
-- Dumping data for table `%PRE%templates`
--

INSERT INTO `%PRE%templates` (`id`, `name`, `acpvisual`, `subject`, `dir`) VALUES
(1, 'client-password-reset', 'Client - Reset Password', 'New Password', 'client/client-password-reset'),
(2, 'new-account', 'Client - New Hosting Account', 'Your Hosting Account', 'client/welcome/new-account'),
(3, 'account-terminated', 'Client - Client Terminated', 'Termination', 'client/status/account-terminated'),
(4, 'account-suspended', 'Client - Suspended Account', 'Suspended', 'client/status/account-suspended'),
(5, 'account-unsuspended', 'Client - Unsuspended Account', 'Unsuspended', 'client/status/account-unsuspended'),
(6, 'new-account-adminval', 'Client - Awaiting Validation', 'Awaiting Admin Validation', 'client/welcome/new-account-adminval'),
(7, 'admin-validation-requested', 'Admin - User Needs Validating', 'User Awaiting Validation', 'admin/admin-validation-requested'),
(8, 'account-approved', 'Client - Account Approved', 'Account Approved', 'client/status/account-approved'),
(9, 'account-declined', 'Client - Declined Account', 'Account Declined', 'client/status/account-declined'),
(10, 'p2h-low-post-warning', 'Client - Post 2 Host - Posts Warning', 'Monthly Posts Warning', 'client/p2h-low-post-warning'),
(11, 'new-ticket', 'Admin - New Ticket', 'New Ticket', 'admin/new-ticket'),
(12, 'ticket-client-responded', 'Admin - New Ticket Response', 'New Ticket Response', 'admin/ticket-client-responded'),
(13, 'ticket-staff-responded', 'Client - New Ticket Response', 'New Ticket Response', 'client/ticket-staff-responded'),
(14, 'admin-password-reset', 'Admin - Admin Reset Password', 'New ACP Password', 'admin/admin-password-reset'),
(15, 'new-invoice', 'Client - New Invoice', 'New Invoice', 'client/new-invoice'),
(16, 'account-canceled', 'Client - Account Cancelled', 'Cancelled', 'client/status/account-canceled'),
(17, 'new-reseller-account', 'Client - New Reseller Hosting Account', 'Your Reseller Hosting Account', 'client/welcome/new-reseller-account'),
(18, 'new-reseller-account-adminval', 'Client - Reseller Awaiting Validation', 'Awaiting Admin', 'client/welcome/new-reseller-account-adminval'),
(19, 'upgrade-welcome', 'Upgrade - Client - Upgraded', 'Your Hosting Account', 'client/upgrade/upgrade-welcome'),
(20, 'manual-upgrade-request', 'Upgrade - Admin - Manual User Upgrade Required', 'A user needs to be manually upgraded.', 'admin/upgrade/manual-upgrade-request'),
(21, 'upgrade-resell-welcome', 'Upgrade - Client - Upgraded To Reseller', 'Your Reseller Hosting Account', 'client/upgrade/upgrade-resell-welcome'),
(22, 'upgrade-newserv-welcome', 'Upgrade - Client - Upgraded (New Server)', 'Your Hosting Account', 'client/upgrade/upgrade-newserv-welcome'),
(23, 'upgrade-newserv-resell-welcome', 'Upgrade - Client - Upgraded To Reseller (New Server)', 'Your Reseller Hosting Account', 'client/upgrade/upgrade-newserv-resell-welcome'),
(24, 'notify-upgrade-new-server', 'Upgrade - Admin - User Switched Servers', 'A user has switched servers.', 'admin/upgrade/notify-upgrade-new-server'),
(25, 'upgrade-adminval', 'Upgrade - Admin - Upgraded User Needs Approval', 'A user has upgraded and requires approval.', 'admin/upgrade/upgrade-adminval'),
(26, 'notify-upgrade', 'Upgrade - Admin - User Upgraded', 'A user has upgraded their hosting plan.', 'admin/upgrade/notify-upgrade'),
(29, 'upgrade-newserv-adminval', 'Upgrade - Admin - Upgraded User Needs Approval (New Server)', 'A user has upgraded and requires approval.', 'admin/upgrade/upgrade-newserv-adminval'),
(30, 'client-upgrade-denied', 'Upgrade - Client - Upgrade Denied', 'Your hosting plan change has been denied.', 'client/upgrade/denied'),
(34, 'notify-admin-of-termination', 'Admin - Notification of a terminated account', 'A user''s account was terminated.', 'admin/notify/notify-admin-of-termination'),
(35, 'notify-admin-of-cancellation', 'Admin - Notification of a canceled account', 'A user''s account was canceled.', 'admin/notify/notify-admin-of-cancellation'),
(36, 'notify-admin-of-suspension', 'Admin - Notification of a suspended account', 'A user''s account was suspended.', 'admin/notify/notify-admin-of-suspension'),
(37, 'notify-admin-of-unsuspension', 'Admin - Notification of an unsuspended account', 'A user''s account was unsuspended.', 'admin/notify/notify-admin-of-unsuspension');

-- --------------------------------------------------------

--
-- Table structure for table `%PRE%tld`
--

CREATE TABLE IF NOT EXISTS `%PRE%tld` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `tld` varchar(50) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 AUTO_INCREMENT=958 ;

--
-- Dumping data for table `%PRE%tld`
--

INSERT INTO `%PRE%tld` (`id`, `tld`) VALUES
(1, 'AC'),
(2, 'AD'),
(3, 'AE'),
(4, 'AERO'),
(5, 'AF'),
(6, 'AG'),
(7, 'AI'),
(8, 'AL'),
(9, 'AM'),
(10, 'AN'),
(11, 'AO'),
(12, 'AQ'),
(13, 'AR'),
(14, 'ARPA'),
(15, 'AS'),
(16, 'ASIA'),
(17, 'AT'),
(18, 'AU'),
(19, 'AW'),
(20, 'AX'),
(21, 'AZ'),
(22, 'BA'),
(23, 'BB'),
(24, 'BD'),
(25, 'BE'),
(26, 'BF'),
(27, 'BG'),
(28, 'BH'),
(29, 'BI'),
(30, 'BIZ'),
(31, 'BJ'),
(32, 'BM'),
(33, 'BN'),
(34, 'BO'),
(35, 'BR'),
(36, 'BS'),
(37, 'BT'),
(38, 'BV'),
(39, 'BW'),
(40, 'BY'),
(41, 'BZ'),
(42, 'CA'),
(43, 'CAT'),
(44, 'CC'),
(45, 'CD'),
(46, 'CF'),
(47, 'CG'),
(48, 'CH'),
(49, 'CI'),
(50, 'CK'),
(51, 'CL'),
(52, 'CM'),
(53, 'CN'),
(54, 'CO'),
(55, 'COM'),
(56, 'COOP'),
(57, 'CR'),
(58, 'CU'),
(59, 'CV'),
(60, 'CW'),
(61, 'CX'),
(62, 'CY'),
(63, 'CZ'),
(64, 'DE'),
(65, 'DJ'),
(66, 'DK'),
(67, 'DM'),
(68, 'DO'),
(69, 'DZ'),
(70, 'EC'),
(71, 'EDU'),
(72, 'EE'),
(73, 'EG'),
(74, 'ER'),
(75, 'ES'),
(76, 'ET'),
(77, 'EU'),
(78, 'FI'),
(79, 'FJ'),
(80, 'FK'),
(81, 'FM'),
(82, 'FO'),
(83, 'FR'),
(84, 'GA'),
(85, 'GB'),
(86, 'GD'),
(87, 'GE'),
(88, 'GF'),
(89, 'GG'),
(90, 'GH'),
(91, 'GI'),
(92, 'GL'),
(93, 'GM'),
(94, 'GN'),
(95, 'GOV'),
(96, 'GP'),
(97, 'GQ'),
(98, 'GR'),
(99, 'GS'),
(100, 'GT'),
(101, 'GU'),
(102, 'GW'),
(103, 'GY'),
(104, 'HK'),
(105, 'HM'),
(106, 'HN'),
(107, 'HR'),
(108, 'HT'),
(109, 'HU'),
(110, 'ID'),
(111, 'IE'),
(112, 'IL'),
(113, 'IM'),
(114, 'IN'),
(115, 'INFO'),
(116, 'INT'),
(117, 'IO'),
(118, 'IQ'),
(119, 'IR'),
(120, 'IS'),
(121, 'IT'),
(122, 'JE'),
(123, 'JM'),
(124, 'JO'),
(125, 'JOBS'),
(126, 'JP'),
(127, 'KE'),
(128, 'KG'),
(129, 'KH'),
(130, 'KI'),
(131, 'KM'),
(132, 'KN'),
(133, 'KP'),
(134, 'KR'),
(135, 'KW'),
(136, 'KY'),
(137, 'KZ'),
(138, 'LA'),
(139, 'LB'),
(140, 'LC'),
(141, 'LI'),
(142, 'LK'),
(143, 'LR'),
(144, 'LS'),
(145, 'LT'),
(146, 'LU'),
(147, 'LV'),
(148, 'LY'),
(149, 'MA'),
(150, 'MC'),
(151, 'MD'),
(152, 'ME'),
(153, 'MG'),
(154, 'MH'),
(155, 'MIL'),
(156, 'MK'),
(157, 'ML'),
(158, 'MM'),
(159, 'MN'),
(160, 'MO'),
(161, 'MOBI'),
(162, 'MP'),
(163, 'MQ'),
(164, 'MR'),
(165, 'MS'),
(166, 'MT'),
(167, 'MU'),
(168, 'MUSEUM'),
(169, 'MV'),
(170, 'MW'),
(171, 'MX'),
(172, 'MY'),
(173, 'MZ'),
(174, 'NA'),
(175, 'NAME'),
(176, 'NC'),
(177, 'NE'),
(178, 'NET'),
(179, 'NF'),
(180, 'NG'),
(181, 'NI'),
(182, 'NL'),
(183, 'NO'),
(184, 'NP'),
(185, 'NR'),
(186, 'NU'),
(187, 'NZ'),
(188, 'OM'),
(189, 'ORG'),
(190, 'PA'),
(191, 'PE'),
(192, 'PF'),
(193, 'PG'),
(194, 'PH'),
(195, 'PK'),
(196, 'PL'),
(197, 'PM'),
(198, 'PN'),
(199, 'POST'),
(200, 'PR'),
(201, 'PRO'),
(202, 'PS'),
(203, 'PT'),
(204, 'PW'),
(205, 'PY'),
(206, 'QA'),
(207, 'RE'),
(208, 'RO'),
(209, 'RS'),
(210, 'RU'),
(211, 'RW'),
(212, 'SA'),
(213, 'SB'),
(214, 'SC'),
(215, 'SD'),
(216, 'SE'),
(217, 'SG'),
(218, 'SH'),
(219, 'SI'),
(220, 'SJ'),
(221, 'SK'),
(222, 'SL'),
(223, 'SM'),
(224, 'SN'),
(225, 'SO'),
(226, 'SR'),
(227, 'ST'),
(228, 'SU'),
(229, 'SV'),
(230, 'SX'),
(231, 'SY'),
(232, 'SZ'),
(233, 'TC'),
(234, 'TD'),
(235, 'TEL'),
(236, 'TF'),
(237, 'TG'),
(238, 'TH'),
(239, 'TJ'),
(240, 'TK'),
(241, 'TL'),
(242, 'TM'),
(243, 'TN'),
(244, 'TO'),
(245, 'TP'),
(246, 'TR'),
(247, 'TRAVEL'),
(248, 'TT'),
(249, 'TV'),
(250, 'TW'),
(251, 'TZ'),
(252, 'UA'),
(253, 'UG'),
(254, 'UK'),
(255, 'US'),
(256, 'UY'),
(257, 'UZ'),
(258, 'VA'),
(259, 'VC'),
(260, 'VE'),
(261, 'VG'),
(262, 'VI'),
(263, 'VN'),
(264, 'VU'),
(265, 'WF'),
(266, 'WS'),
(267, 'XN--0ZWM56D'),
(268, 'XN--11B5BS3A9AJ6G'),
(269, 'XN--3E0B707E'),
(270, 'XN--45BRJ9C'),
(271, 'XN--80AKHBYKNJ4F'),
(272, 'XN--80AO21A'),
(273, 'XN--90A3AC'),
(274, 'XN--9T4B11YI5A'),
(275, 'XN--CLCHC0EA0B2G2A9GCD'),
(276, 'XN--DEBA0AD'),
(277, 'XN--FIQS8S'),
(278, 'XN--FIQZ9S'),
(279, 'XN--FPCRJ9C3D'),
(280, 'XN--FZC2C9E2C'),
(281, 'XN--G6W251D'),
(282, 'XN--GECRJ9C'),
(283, 'XN--H2BRJ9C'),
(284, 'XN--HGBK6AJ7F53BBA'),
(285, 'XN--HLCJ6AYA9ESC7A'),
(286, 'XN--J1AMH'),
(287, 'XN--J6W193G'),
(288, 'XN--JXALPDLP'),
(289, 'XN--KGBECHTV'),
(290, 'XN--KPRW13D'),
(291, 'XN--KPRY57D'),
(292, 'XN--L1ACC'),
(293, 'XN--LGBBAT1AD8J'),
(294, 'XN--MGB9AWBF'),
(295, 'XN--MGBAAM7A8H'),
(296, 'XN--MGBAYH7GPA'),
(297, 'XN--MGBBH1A71E'),
(298, 'XN--MGBC0A9AZCG'),
(299, 'XN--MGBERP4A5D4AR'),
(300, 'XN--MGBX4CD0AB'),
(301, 'XN--O3CW4H'),
(302, 'XN--OGBPF8FL'),
(303, 'XN--P1AI'),
(304, 'XN--PGBS0DH'),
(305, 'XN--S9BRJ9C'),
(306, 'XN--WGBH1C'),
(307, 'XN--WGBL6A'),
(308, 'XN--XKC2AL3HYE2A'),
(309, 'XN--XKC2DL3A5EE0H'),
(310, 'XN--YFRO4I67O'),
(311, 'XN--YGBI2AMMX'),
(312, 'XN--ZCKZAH'),
(313, 'XXX'),
(314, 'YE'),
(315, 'YT'),
(316, 'ZA'),
(317, 'ZM'),
(318, 'ZW');

-- --------------------------------------------------------

DROP TABLE `%PRE%types`;

ALTER TABLE `%PRE%mod_navens_upgrade` RENAME `%PRE%upgrade`;

ALTER TABLE `%PRE%users` ADD `zpanel_uid` INT( 6 ) NOT NULL;
ALTER TABLE `%PRE%users` ADD `domain` VARCHAR( 50 ) NOT NULL;
ALTER TABLE `%PRE%users` ADD `pid` VARCHAR( 5 ) NOT NULL;
ALTER TABLE `%PRE%users` ADD `additional` TEXT NOT NULL;
ALTER TABLE `%PRE%users` ADD `freeuser` INT( 11 ) NOT NULL;
ALTER TABLE `%PRE%users` CHANGE `password` `password` VARCHAR( 255 ) NOT NULL;
ALTER TABLE `%PRE%users` CHANGE `salt` `salt` VARCHAR( 255 ) NOT NULL;

ALTER TABLE `%PRE%users_bak` ADD `zpanel_uid` INT( 6 ) NOT NULL;
ALTER TABLE `%PRE%users_bak` ADD `tzadjust` TEXT NOT NULL;
ALTER TABLE `%PRE%users_bak` ADD `domain` VARCHAR( 50 ) NOT NULL;
ALTER TABLE `%PRE%users_bak` ADD `pid` VARCHAR( 5 ) NOT NULL;
ALTER TABLE `%PRE%users_bak` ADD `additional` TEXT NOT NULL;
ALTER TABLE `%PRE%users_bak` CHANGE `password` `password` VARCHAR( 255 ) NOT NULL;
ALTER TABLE `%PRE%users_bak` CHANGE `salt` `salt` VARCHAR( 255 ) NOT NULL;

DROP TABLE `%PRE%user_packs`;
DROP TABLE `%PRE%user_packs_bak`;
