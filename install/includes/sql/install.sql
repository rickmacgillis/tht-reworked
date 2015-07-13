SET SQL_MODE="NO_AUTO_VALUE_ON_ZERO";
SET time_zone = "+00:00";
-- --------------------------------------------------------

--
-- Table structure for table `%PRE%acpnav`
--

CREATE TABLE IF NOT EXISTS `%PRE%acpnav` (
  `id` mediumint(9) NOT NULL AUTO_INCREMENT,
  `visual` varchar(255) NOT NULL,
  `icon` varchar(50) NOT NULL,
  `link` varchar(20) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 AUTO_INCREMENT=19 ;

--
-- Dumping data for table `%PRE%acpnav`
--

INSERT INTO `%PRE%acpnav` (`id`, `visual`, `icon`, `link`) VALUES
(1, 'Home', 'house.png', 'home'),
(2, 'General Settings', 'cog.png', 'settings'),
(3, 'Servers', 'server.png', 'servers'),
(4, 'Packages', 'package_green.png', 'packages'),
(5, 'Staff Accounts', 'user_gray.png', 'staff'),
(6, 'Subdomains', 'link.png', 'sub'),
(7, 'Clients', 'group.png', 'users'),
(8, 'Change Password', 'shield.png', 'pass'),
(9, 'Server Status', 'computer.png', 'status'),
(10, 'Mail Center', 'email_open.png', 'email'),
(11, 'Client Importer', 'user_orange.png', 'import'),
(12, 'Tickets', 'page_white_text.png', 'tickets'),
(13, 'Knowledge Base', 'folder.png', 'kb'),
(14, 'Look & Feel', 'rainbow.png', 'lof'),
(15, 'Invoice Management', 'script.png', 'invoices'),
(16, 'Logs', 'report.png', 'logs'),
(17, 'AutoMod', 'box.png', 'automod'),
(18, 'Coupons', 'award_star_silver_3.png', 'navens_coupons');

-- --------------------------------------------------------

--
-- Table structure for table `%PRE%articles`
--

CREATE TABLE IF NOT EXISTS `%PRE%articles` (
  `id` mediumint(9) NOT NULL AUTO_INCREMENT,
  `catid` mediumint(9) NOT NULL,
  `name` varchar(100) NOT NULL,
  `content` text NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 AUTO_INCREMENT=1 ;

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
-- Table structure for table `%PRE%cats`
--

CREATE TABLE IF NOT EXISTS `%PRE%cats` (
  `id` mediumint(9) NOT NULL AUTO_INCREMENT,
  `name` varchar(50) NOT NULL,
  `description` text NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 AUTO_INCREMENT=1 ;

-- --------------------------------------------------------

--
-- Table structure for table `%PRE%clientnav`
--

CREATE TABLE IF NOT EXISTS `%PRE%clientnav` (
  `id` mediumint(9) NOT NULL AUTO_INCREMENT,
  `visual` varchar(20) NOT NULL,
  `icon` varchar(50) NOT NULL,
  `link` varchar(20) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 AUTO_INCREMENT=9 ;

--
-- Dumping data for table `%PRE%clientnav`
--

INSERT INTO `%PRE%clientnav` (`id`, `visual`, `icon`, `link`) VALUES
(1, 'Home', 'house.png', 'home'),
(2, 'Announcements', 'bell.png', 'notices'),
(3, 'View Package', 'package.png', 'view'),
(4, 'Edit Details', 'user_edit.png', 'details'),
(5, 'Delete Account', 'cross.png', 'delete'),
(6, 'Invoices', 'script.png', 'invoices'),
(7, 'Tickets', 'page_white_text.png', 'tickets'),
(8, 'Upgrade', 'award_star_silver_3.png', 'upgrade');

-- --------------------------------------------------------

--
-- Table structure for table `%PRE%config`
--

CREATE TABLE IF NOT EXISTS `%PRE%config` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `name` varchar(50) NOT NULL,
  `value` text NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 AUTO_INCREMENT=42 ;

--
-- Dumping data for table `%PRE%config`
--

INSERT INTO `%PRE%config` (`id`, `name`, `value`) VALUES
(1, 'url', 'http://thehostingtool.com/'),
(2, 'version', '1.3.5 Reworked'),
(3, 'smtp_user', 'user'),
(4, 'senabled', '1'),
(5, 'whm-ssl', '1'),
(6, 'paypalemail', 'your@email.com'),
(7, 'default', 'order'),
(8, 'theme', 'bluelust'),
(9, 'name', 'The Hosting Tool'),
(10, 'tos', '<p><span style="font-weight: bold;"><span style="color: #333333; font-family: Tahoma; font-size: 11px; font-weight: normal;"><span style="color: #ff0000;">The following content is prohibited on our servers:</span><ol>\r\n<li><strong>Illegal use</strong></li>\r\n<li><strong>Threatening Material</strong></li>\r\n<li><strong>Fraudulent Content</strong></li>\r\n<li><strong>Forgery or Impersonation</strong></li>\r\n<li><strong>Unsolicited Content</strong></li>\r\n<li><strong>Copyright Infringements</strong></li>\r\n<li><strong>Collection of Private Date (Unless DPA Registered)</strong></li>\r\n<li><strong>Viruses</strong></li>\r\n<li><strong>IRC Networks (Including all IRC Material)</strong></li>\r\n<li><strong>Peer to Peer software&nbsp;<br /></strong></li>\r\n<li><strong>Any Adult Content</strong></li>\r\n<li><strong>Non-english Content</strong></li>\r\n</ol></span></span></p>'),
(11, 'multiple', '1'),
(12, 'general', '1'),
(13, 'message', '<p>Signups are currently disabled. Contact your host for more information.</p>'),
(14, 'delacc', '1'),
(15, 'cenabled', '1'),
(16, 'cmessage', '<p>Client area isn''t enabled. Sorry, contact your host for more details.</p>'),
(17, 'emailmethod', 'php'),
(18, 'emailfrom', 'The Hosting Tool <admin@thehostingtool.com>'),
(19, 'smtp_host', 'localhost'),
(20, 'smtp_user', 'user'),
(21, 'smtp_password', 'password'),
(22, 'show_version_id', '0'),
(23, 'show_acp_menu', '0'),
(24, 'show_page_gentime', '1'),
(25, 'alerts', ''),
(26, 'p2hcheck', ''),
(27, 'show_footer', '0'),
(28, 'senabled', '1'),
(29, 'smessage', '<p>Support area isn\'t enabled. Sorry, contact your host for more details.</p>'),
(30, 'terminationdays', '14'),
(31, 'suspensiondays', '14'),
(32, 'tldonly', '0'),
(33, 'currency', 'USD'),
(34, 'ui-theme', 'cupertino'),
(35, 'paypalmode', 'sandbox'),
(36, 'paypalsandemail', ''),
(37, 'useakismet', '0'),
(38, 'akismetkey', ''),
(39, 'emailoncron', '1'),
(40, 'p2hwarndate', '20'),
(41, 'automodvers', '1.0');

-- --------------------------------------------------------

--
-- Table structure for table `%PRE%invoices`
--

CREATE TABLE IF NOT EXISTS `%PRE%invoices` (
  `id` int(255) NOT NULL AUTO_INCREMENT,
  `uid` int(255) NOT NULL,
  `amount` varchar(255) NOT NULL,
  `is_paid` int(1) NOT NULL DEFAULT '0',
  `created` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `due` text NOT NULL,
  `notes` text NOT NULL,
  `uniqueid` varchar(255) NOT NULL,
  `datepaid` text NOT NULL,
  `txn` text NOT NULL,
  `amt_paid` text NOT NULL,
  `gateway` text NOT NULL,
  `pay_now` varchar(255) NOT NULL,
  `locked` int(11) NOT NULL,
  `pid` int(11) NOT NULL,
  `hadcoupons` text NOT NULL,
  `couponvals` text NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 AUTO_INCREMENT=1 ;

-- --------------------------------------------------------

--
-- Table structure for table `%PRE%logs`
--

CREATE TABLE IF NOT EXISTS `%PRE%logs` (
  `id` mediumint(9) NOT NULL AUTO_INCREMENT,
  `uid` varchar(5) NOT NULL,
  `loguser` varchar(50) NOT NULL,
  `logtime` varchar(20) NOT NULL,
  `message` text NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 AUTO_INCREMENT=1 ;

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

INSERT INTO `%PRE%mod_navens_coupons_config` (`id`, `configname`, `configvalue`) VALUES
(1, 'multicoupons', '0'),
(2, 'p2hgraceperiod', '5');

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
-- Table structure for table `%PRE%navbar`
--

CREATE TABLE IF NOT EXISTS `%PRE%navbar` (
  `id` smallint(6) NOT NULL AUTO_INCREMENT,
  `icon` varchar(20) NOT NULL,
  `visual` varchar(70) NOT NULL,
  `link` varchar(20) NOT NULL,
  `order` int(11) NOT NULL DEFAULT '0',
  PRIMARY KEY (`id`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 AUTO_INCREMENT=5 ;

--
-- Dumping data for table `%PRE%navbar`
--

INSERT INTO `%PRE%navbar` (`id`, `icon`, `visual`, `link`, `order`) VALUES
(1, 'cart.png', 'Order Form', 'order', 1),
(2, 'user.png', 'Client Area', 'client', 0),
(3, 'key.png', 'Admin Area', 'venus', 2),
(4, 'report_magnify.png', 'Knowledge Base', 'support', 3);

-- --------------------------------------------------------

--
-- Table structure for table `%PRE%orderfields`
--

CREATE TABLE IF NOT EXISTS `%PRE%orderfields` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `order` int(11) NOT NULL DEFAULT '0',
  `title` varchar(255) NOT NULL,
  `type` varchar(255) NOT NULL,
  `default` text NOT NULL,
  `description` text NOT NULL,
  `required` tinyint(1) NOT NULL DEFAULT '0',
  `regex` text NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 AUTO_INCREMENT=1 ;

-- --------------------------------------------------------

--
-- Table structure for table `%PRE%packages`
--

CREATE TABLE IF NOT EXISTS `%PRE%packages` (
  `id` mediumint(9) NOT NULL AUTO_INCREMENT,
  `name` varchar(255) NOT NULL,
  `backend` varchar(50) NOT NULL,
  `description` text NOT NULL,
  `type` varchar(10) NOT NULL,
  `server` varchar(20) NOT NULL,
  `admin` tinyint(1) NOT NULL,
  `reseller` tinyint(4) NOT NULL,
  `additional` text NOT NULL,
  `order` int(11) NOT NULL DEFAULT '0',
  `is_hidden` int(1) NOT NULL,
  `is_disabled` int(1) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 AUTO_INCREMENT=1 ;

-- --------------------------------------------------------

--
-- Table structure for table `%PRE%resources`
--

CREATE TABLE IF NOT EXISTS `%PRE%resources` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `resource_name` varchar(20) NOT NULL,
  `resource_value` text NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 AUTO_INCREMENT=2 ;

--
-- Dumping data for table `%PRE%resources`
--

INSERT INTO `%PRE%resources` (`id`, `resource_name`, `resource_value`) VALUES
(1, 'admin_notes', '<p><strong>Welcome to your TheHostingTool v1.3.5 Reworked Installation!</strong></p>\r\n<p>We hope that you like TheHostingTool and you have a good time with our script. If you need any help, you can ask at the THT Community, or contact us directly. Thanks for using TheHostingTool, and good luck on your hosting service!</p>\r\n<p>- The THT Team</p>');

-- --------------------------------------------------------

--
-- Table structure for table `%PRE%servers`
--

CREATE TABLE IF NOT EXISTS `%PRE%servers` (
  `id` mediumint(9) NOT NULL AUTO_INCREMENT,
  `name` varchar(20) NOT NULL,
  `host` varchar(50) NOT NULL,
  `user` varchar(255) NOT NULL,
  `accesshash` text NOT NULL,
  `type` varchar(10) NOT NULL,
  `port` varchar(5) NOT NULL,
  `whmport` varchar(5) NOT NULL,
  `nameservers` text NOT NULL,
  `ip` varchar(50) NOT NULL,
  `dnstemplate` varchar(255) NOT NULL,
  `welcome` int(11) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 AUTO_INCREMENT=1 ;

-- --------------------------------------------------------

--
-- Table structure for table `%PRE%staff`
--

CREATE TABLE IF NOT EXISTS `%PRE%staff` (
  `id` mediumint(9) NOT NULL AUTO_INCREMENT,
  `user` varchar(20) NOT NULL,
  `email` varchar(50) NOT NULL,
  `name` varchar(50) NOT NULL,
  `password` text NOT NULL,
  `salt` text NOT NULL,
  `perms` text NOT NULL,
  `tzadjust` text NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 AUTO_INCREMENT=1 ;

-- --------------------------------------------------------

--
-- Table structure for table `%PRE%subdomains`
--

CREATE TABLE IF NOT EXISTS `%PRE%subdomains` (
  `id` mediumint(9) NOT NULL AUTO_INCREMENT,
  `subdomain` varchar(50) NOT NULL,
  `server` varchar(5) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 AUTO_INCREMENT=1 ;

-- --------------------------------------------------------

--
-- Table structure for table `%PRE%supportnav`
--

CREATE TABLE IF NOT EXISTS `%PRE%supportnav` (
  `id` mediumint(9) NOT NULL AUTO_INCREMENT,
  `visual` varchar(50) NOT NULL,
  `icon` varchar(20) NOT NULL,
  `link` varchar(20) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 AUTO_INCREMENT=4 ;

--
-- Dumping data for table `%PRE%supportnav`
--

INSERT INTO `%PRE%supportnav` (`id`, `visual`, `icon`, `link`) VALUES
(1, 'Home', 'house.png', 'home'),
(2, 'Tickets', 'page_white_text.png', 'tickets'),
(3, 'Knowledgebase', 'folder_explore.png', 'kb');

-- --------------------------------------------------------

--
-- Table structure for table `%PRE%templates`
--

CREATE TABLE IF NOT EXISTS `%PRE%templates` (
  `id` mediumint(9) NOT NULL AUTO_INCREMENT,
  `name` varchar(50) NOT NULL,
  `acpvisual` varchar(255) NOT NULL,
  `subject` varchar(250) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 AUTO_INCREMENT=31 ;

--
-- Dumping data for table `%PRE%templates`
--

INSERT INTO `%PRE%templates` (`id`, `name`, `acpvisual`, `subject`) VALUES
(1, 'reset', 'Client - Reset Password', 'New Password'),
(2, 'newacc', 'Client - New Hosting Account', 'Your Hosting Account'),
(3, 'termacc', 'Client - Client Terminated', 'Termination'),
(4, 'suspendacc', 'Client - Suspended Account', 'Suspended'),
(5, 'unsusacc', 'Client - Unsuspended Account', 'Unsuspended'),
(6, 'newaccadmin', 'Client - Awaiting Validation', 'Awaiting Admin'),
(7, 'adminval', 'Admin - User Needs Validating', 'User Awaiting Validation'),
(8, 'approvedacc', 'Client - Account Approved', 'Account Approved'),
(9, 'declinedacc', 'Client - Declined Account', 'Account Declined'),
(10, 'p2hwarning', 'Client - Post 2 Host - Posts Warning', 'Monthly Posts Warning'),
(11, 'newticket', 'Admin - New Ticket', 'New Ticket'),
(12, 'newresponse', 'Admin - New Ticket Response', 'New Ticket Response'),
(13, 'clientresponse', 'Client - New Ticket Response', 'New Ticket Response'),
(14, 'areset', 'Admin - Admin Reset Password', 'New ACP Password!'),
(15, 'newinvoice', 'Client - New Invoice', 'New Invoice'),
(16, 'cancelacc', 'Client - Account Cancelled', 'Cancelled'),
(17, 'newreselleracc', 'Client - New Reseller Hosting Account', 'Your Reseller Hosting Account'),
(18, 'newreselleraccadmin', 'Client - Reseller Awaiting Validation', 'Awaiting Admin'),
(19, 'upgrade_welcome', 'Upgrade - Client - Upgraded', 'Your Hosting Account'),
(20, 'admin_manual_upgrade', 'Upgrade - Admin - Manual User Upgrade Required', 'A user needs to be manually upgraded.'),
(21, 'upgrade_welcome_resell', 'Upgrade - Client - Upgraded To Reseller', 'Your Reseller Hosting Account'),
(22, 'upgrade_welcome_newserv', 'Upgrade - Client - Upgraded (New Server)', 'Your Hosting Account'),
(23, 'upgrade_welcome_newserv_resell', 'Upgrade - Client - Upgraded To Reseller (New Server)', 'Your Reseller Hosting Account'),
(24, 'admin_notify_newserv', 'Upgrade - Admin - User Switched Servers', 'A user has switched servers.'),
(25, 'adminval_upgrade', 'Upgrade - Admin - Upgraded User Needs Approval', 'A user has upgraded and requires approval.'),
(26, 'admin_inform_new_upgrade', 'Upgrade - Admin - User Upgraded', 'A user has upgraded their hosting plan.'),
(27, 'admin_inform_new_upgrade', 'Upgrade - Admin - User Upgraded', 'A user has upgraded their hosting plan.'),
(28, 'admin_inform_new_upgrade', 'Upgrade - Admin - User Upgraded', 'A user has upgraded their hosting plan.'),
(29, 'adminval_upgrade_newserv', 'Upgrade - Admin - Upgraded User Needs Approval (New Server)', 'A user has upgraded and requires approval.'),
(30, 'upgrade_denied', 'Upgrade - Client - Upgrade Denied', 'Your hosting plan change has been denied.');

-- --------------------------------------------------------

--
-- Table structure for table `%PRE%tickets`
--

CREATE TABLE IF NOT EXISTS `%PRE%tickets` (
  `id` mediumint(9) NOT NULL AUTO_INCREMENT,
  `title` varchar(100) NOT NULL,
  `content` text NOT NULL,
  `urgency` varchar(50) NOT NULL,
  `time` varchar(20) NOT NULL,
  `reply` mediumint(9) NOT NULL,
  `ticketid` mediumint(9) NOT NULL,
  `staff` mediumint(9) NOT NULL,
  `userid` mediumint(9) NOT NULL,
  `status` mediumint(9) NOT NULL DEFAULT '1',
  PRIMARY KEY (`id`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 AUTO_INCREMENT=1 ;

-- --------------------------------------------------------

--
-- Table structure for table `%PRE%types`
--

CREATE TABLE IF NOT EXISTS `%PRE%types` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `name` varchar(15) NOT NULL,
  `visual` varchar(20) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 AUTO_INCREMENT=4 ;

--
-- Dumping data for table `%PRE%types`
--

INSERT INTO `%PRE%types` (`id`, `name`, `visual`) VALUES
(1, 'free', 'Free'),
(2, 'p2h', 'Post2Host'),
(3, 'paid', 'Paid');

-- --------------------------------------------------------

--
-- Table structure for table `%PRE%users`
--

CREATE TABLE IF NOT EXISTS `%PRE%users` (
  `id` mediumint(9) NOT NULL AUTO_INCREMENT,
  `user` varchar(50) NOT NULL,
  `email` varchar(50) NOT NULL,
  `password` varchar(50) NOT NULL,
  `salt` varchar(50) NOT NULL,
  `signup` varchar(20) NOT NULL,
  `ip` text NOT NULL,
  `firstname` varchar(50) NOT NULL,
  `lastname` varchar(50) NOT NULL,
  `address` varchar(50) NOT NULL,
  `city` varchar(50) NOT NULL,
  `zip` varchar(7) NOT NULL,
  `state` varchar(55) NOT NULL,
  `country` varchar(2) NOT NULL,
  `phone` varchar(15) NOT NULL,
  `status` varchar(1) NOT NULL DEFAULT '0',
  `tzadjust` text NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 AUTO_INCREMENT=1 ;

-- --------------------------------------------------------

--
-- Table structure for table `%PRE%users_bak`
--

CREATE TABLE IF NOT EXISTS `%PRE%users_bak` (
  `id` mediumint(9) NOT NULL AUTO_INCREMENT,
  `uid` int(11) NOT NULL,
  `user` varchar(50) NOT NULL,
  `email` varchar(50) NOT NULL,
  `password` varchar(50) NOT NULL,
  `salt` varchar(50) NOT NULL,
  `signup` varchar(20) NOT NULL,
  `ip` text NOT NULL,
  `firstname` varchar(50) NOT NULL,
  `lastname` varchar(50) NOT NULL,
  `address` varchar(50) NOT NULL,
  `city` varchar(50) NOT NULL,
  `zip` varchar(7) NOT NULL,
  `state` varchar(55) NOT NULL,
  `country` varchar(2) NOT NULL,
  `phone` varchar(15) NOT NULL,
  `status` varchar(1) NOT NULL DEFAULT '0',
  PRIMARY KEY (`id`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 AUTO_INCREMENT=1 ;

-- --------------------------------------------------------

--
-- Table structure for table `%PRE%user_packs`
--

CREATE TABLE IF NOT EXISTS `%PRE%user_packs` (
  `id` mediumint(9) NOT NULL AUTO_INCREMENT,
  `userid` varchar(5) NOT NULL,
  `username` varchar(50) NOT NULL,
  `domain` varchar(50) NOT NULL,
  `pid` varchar(5) NOT NULL,
  `signup` varchar(20) NOT NULL,
  `status` varchar(1) NOT NULL,
  `additional` text NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 AUTO_INCREMENT=1 ;

-- --------------------------------------------------------

--
-- Table structure for table `%PRE%user_packs_bak`
--

CREATE TABLE IF NOT EXISTS `%PRE%user_packs_bak` (
  `id` mediumint(9) NOT NULL AUTO_INCREMENT,
  `userid` varchar(5) NOT NULL,
  `username` varchar(50) NOT NULL,
  `domain` varchar(50) NOT NULL,
  `pid` varchar(5) NOT NULL,
  `signup` varchar(20) NOT NULL,
  `status` varchar(1) NOT NULL,
  `additional` text NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 AUTO_INCREMENT=1 ;
