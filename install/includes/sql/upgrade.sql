INSERT INTO `%PRE%acpnav` (`visual`, `icon`, `link`) VALUES
('AutoMod', 'box.png', 'automod'),
('Coupons', 'award_star_silver_3.png', 'navens_coupons');

-- --------------------------------------------------------

--
-- Table structure for table `%PRE%automod_mods`
--

CREATE TABLE IF NOT EXISTS `%PRE%automod_mods` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `mod_install_dir` varchar(255) NOT NULL,
  `mod_name` varchar(255) NOT NULL,
  `mod_version` varchar(255) NOT NULL,
  `mod_thtversion` varchar(255) NOT NULL,
  `mod_descrip` text NOT NULL,
  `mod_author` varchar(255) NOT NULL,
  `mod_link` text NOT NULL,
  `mod_projectpage` text NOT NULL,
  `mod_updateurl` text NOT NULL,
  `mod_support` text NOT NULL,
  `mod_license` text NOT NULL,
  `mod_diy` text NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM  DEFAULT CHARSET=latin1 AUTO_INCREMENT=1 ;

-- --------------------------------------------------------

--
-- Dumping data for table `%PRE%clientnav`
--

INSERT INTO `%PRE%clientnav` (`visual`, `icon`, `link`) VALUES
('Upgrade', 'award_star_silver_3.png', 'upgrade');

-- --------------------------------------------------------

--
-- Table structure for table `%PRE%config`
--

ALTER TABLE `%PRE%config` ADD `id` INT NOT NULL AUTO_INCREMENT PRIMARY KEY FIRST;

--
-- Dumping data for table `%PRE%config`
--

UPDATE `%PRE%config` SET value = '1.3.5 Reworked' WHERE name = 'version' LIMIT 1;
INSERT INTO `%PRE%config` SET name = 'automodvers', value = '1.0';

-- --------------------------------------------------------

--
-- Table structure for table `%PRE%invoices`
--

ALTER TABLE `%PRE%invoices`
  ADD `datepaid` text NOT NULL,
  ADD `txn` text NOT NULL,
  ADD `amt_paid` text NOT NULL,
  ADD `gateway` text NOT NULL,
  ADD `pay_now` VARCHAR( 255 ) NOT NULL,
  ADD `locked` int(11) NOT NULL,
  ADD `pid` int(11) NOT NULL,
  ADD `hadcoupons` text NOT NULL,
  ADD `couponvals` text NOT NULL;

-- --------------------------------------------------------

--
-- Table structure for table `%PRE%mod_navens_coupons`
--

CREATE TABLE IF NOT EXISTS `%PRE%mod_navens_coupons` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `coupname` varchar(255) NOT NULL,
  `shortdesc` text NOT NULL,
  `coupcode` varchar(255) NOT NULL,
  `area` varchar(255) NOT NULL,
  `goodfor` varchar(255) NOT NULL,
  `monthsgoodfor` int(11) NOT NULL,
  `expiredate` varchar(255) NOT NULL,
  `limited` varchar(255) NOT NULL,
  `user` varchar(255) NOT NULL,
  `packages` text NOT NULL,
  `paiddisc` varchar(255) NOT NULL,
  `p2hinitdisc` int(11) NOT NULL,
  `p2hmonthlydisc` int(11) NOT NULL,
  `paidtype` int(11) NOT NULL,
  `p2hinittype` int(11) NOT NULL,
  `p2hmonthlytype` int(11) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM  DEFAULT CHARSET=latin1 AUTO_INCREMENT=1 ;

-- --------------------------------------------------------

--
-- Table structure for table `%PRE%mod_navens_coupons_config`
--

CREATE TABLE IF NOT EXISTS `%PRE%mod_navens_coupons_config` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `configname` varchar(255) NOT NULL,
  `configvalue` text NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM  DEFAULT CHARSET=latin1 AUTO_INCREMENT=3 ;

--
-- Dumping data for table `%PRE%mod_navens_coupons_config`
--

INSERT INTO `%PRE%mod_navens_coupons_config` (`configname`, `configvalue`) VALUES
('multicoupons', '0'),
('p2hgraceperiod', '5');

-- --------------------------------------------------------

--
-- Table structure for table `%PRE%mod_navens_coupons_p2h`
--

CREATE TABLE IF NOT EXISTS `%PRE%mod_navens_coupons_p2h` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `uid` int(11) NOT NULL,
  `amt_paid` text NOT NULL,
  `txn` text NOT NULL,
  `datepaid` text NOT NULL,
  `gateway` text NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM  DEFAULT CHARSET=latin1 AUTO_INCREMENT=1 ;

-- --------------------------------------------------------

--
-- Table structure for table `%PRE%mod_navens_coupons_used`
--

CREATE TABLE IF NOT EXISTS `%PRE%mod_navens_coupons_used` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `user` int(11) NOT NULL,
  `coupcode` varchar(255) NOT NULL,
  `timeapplied` int(11) NOT NULL,
  `packages` int(11) NOT NULL,
  `goodfor` varchar(255) NOT NULL,
  `monthsgoodfor` int(11) NOT NULL,
  `paiddisc` varchar(255) NOT NULL,
  `p2hmonthlydisc` int(11) NOT NULL,
  `disabled` int(11) NOT NULL,
  `datedisabled` int(11) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM  DEFAULT CHARSET=latin1 AUTO_INCREMENT=1 ;

-- --------------------------------------------------------

--
-- Table structure for table `%PRE%mod_navens_upgrade`
--

CREATE TABLE IF NOT EXISTS `%PRE%mod_navens_upgrade` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `uid` int(11) NOT NULL,
  `newpack` int(11) NOT NULL,
  `flags` int(11) NOT NULL,
  `created` int(11) NOT NULL,
  `coupcode` varchar(255) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM  DEFAULT CHARSET=latin1 AUTO_INCREMENT=1 ;

-- --------------------------------------------------------

--
-- Table structure for table `%PRE%resources`
--

ALTER TABLE `%PRE%resources` ADD `id` INT NOT NULL AUTO_INCREMENT PRIMARY KEY FIRST;

-- --------------------------------------------------------

--
-- Table structure for table `%PRE%servers`
--

ALTER TABLE `%PRE%servers`
  ADD `dnstemplate` varchar(255) NOT NULL,
  ADD `welcome` int(11) NOT NULL;
  
ALTER TABLE `%PRE%servers` CHANGE `user` `user` VARCHAR( 255 ) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL;

-- --------------------------------------------------------

--
-- Table structure for table `%PRE%templates`
--

ALTER TABLE `%PRE%templates` CHANGE `acpvisual` `acpvisual` VARCHAR( 255 ) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL;

--
-- Dumping data for table `%PRE%templates`
--

INSERT INTO `%PRE%templates` (`name`, `acpvisual`, `subject`) VALUES
('upgrade_welcome', 'Upgrade - Client - Upgraded', 'Your Hosting Account'),
('admin_manual_upgrade', 'Upgrade - Admin - Manual User Upgrade Required', 'A user needs to be manually upgraded.'),
('upgrade_welcome_resell', 'Upgrade - Client - Upgraded To Reseller', 'Your Reseller Hosting Account'),
('upgrade_welcome_newserv', 'Upgrade - Client - Upgraded (New Server)', 'Your Hosting Account'),
('upgrade_welcome_newserv_resell', 'Upgrade - Client - Upgraded To Reseller (New Server)', 'Your Reseller Hosting Account'),
('admin_notify_newserv', 'Upgrade - Admin - User Switched Servers', 'A user has switched servers.'),
('adminval_upgrade', 'Upgrade - Admin - Upgraded User Needs Approval', 'A user has upgraded and requires approval.'),
('admin_inform_new_upgrade', 'Upgrade - Admin - User Upgraded', 'A user has upgraded their hosting plan.'),
('admin_inform_new_upgrade', 'Upgrade - Admin - User Upgraded', 'A user has upgraded their hosting plan.'),
('admin_inform_new_upgrade', 'Upgrade - Admin - User Upgraded', 'A user has upgraded their hosting plan.'),
('adminval_upgrade_newserv', 'Upgrade - Admin - Upgraded User Needs Approval (New Server)', 'A user has upgraded and requires approval.'),
('upgrade_denied', 'Upgrade - Client - Upgrade Denied', 'Your hosting plan change has been denied.');
