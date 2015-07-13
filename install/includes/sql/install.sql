SET SQL_MODE="NO_AUTO_VALUE_ON_ZERO";
-- --------------------------------------------------------

--
-- Table structure for table `%PRE%acpnav`
--

CREATE TABLE IF NOT EXISTS `%PRE%acpnav` (
  `id` mediumint(9) NOT NULL auto_increment,
  `visual` varchar(255) NOT NULL,
  `icon` varchar(50) NOT NULL,
  `link` varchar(20) NOT NULL,
  PRIMARY KEY  (`id`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 AUTO_INCREMENT=20 ;

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
(16, 'Logs', 'report.png', 'logs');

-- --------------------------------------------------------

--
-- Table structure for table `%PRE%articles`
--

CREATE TABLE IF NOT EXISTS `%PRE%articles` (
  `id` mediumint(9) NOT NULL auto_increment,
  `catid` mediumint(9) NOT NULL,
  `name` varchar(100) NOT NULL,
  `content` text NOT NULL,
  PRIMARY KEY  (`id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 AUTO_INCREMENT=1 ;

--
-- Dumping data for table `%PRE%articles`
--


-- --------------------------------------------------------

--
-- Table structure for table `%PRE%cats`
--

CREATE TABLE IF NOT EXISTS `%PRE%cats` (
  `id` mediumint(9) NOT NULL auto_increment,
  `name` varchar(50) NOT NULL,
  `description` text NOT NULL,
  PRIMARY KEY  (`id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 AUTO_INCREMENT=1 ;

--
-- Dumping data for table `%PRE%cats`
--


-- --------------------------------------------------------

--
-- Table structure for table `%PRE%clientnav`
--

CREATE TABLE IF NOT EXISTS `%PRE%clientnav` (
  `id` mediumint(9) NOT NULL auto_increment,
  `visual` varchar(20) NOT NULL,
  `icon` varchar(50) NOT NULL,
  `link` varchar(20) NOT NULL,
  PRIMARY KEY  (`id`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 AUTO_INCREMENT=8 ;

--
-- Dumping data for table `%PRE%clientnav`
--

INSERT INTO `%PRE%clientnav` (`id`, `visual`, `icon`, `link`) VALUES
(1, 'Home', 'house.png', 'home'),
(3, 'Announcements', 'bell.png', 'notices'),
(4, 'View Package', 'package.png', 'view'),
(5, 'Edit Details', 'user_edit.png', 'details'),
(2, 'Delete Account', 'cross.png', 'delete'),
(7, 'Invoices', 'script.png', 'invoices'),
(8, 'Tickets', 'page_white_text.png', 'tickets');


-- --------------------------------------------------------

--
-- Table structure for table `%PRE%config`
--

CREATE TABLE IF NOT EXISTS `%PRE%config` (
  `name` varchar(50) NOT NULL,
  `value` text NOT NULL
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

--
-- Dumping data for table `%PRE%config`
--

INSERT INTO `%PRE%config` (`name`, `value`) VALUES
('url', 'http://thehostingtool.com/'),
('version', '1.3'),
('smtp_user', 'user'),
('senabled', '1'),
('whm-ssl', '0'),
('paypalemail', 'your@email.com'),
('default', 'order'),
('theme', 'bluelust'),
('name', 'TheHostingTool'),
('tos', '<p><span style="font-weight: bold;"><span style="color: #333333; font-family: Tahoma; font-size: 11px; font-weight: normal;"><span style="color: #ff0000;">The following content is prohibited on our servers:</span><ol>\r\n<li><strong>Illegal use</strong></li>\r\n<li><strong>Threatening Material</strong></li>\r\n<li><strong>Fraudulent Content</strong></li>\r\n<li><strong>Forgery or Impersonation</strong></li>\r\n<li><strong>Unsolicited Content</strong></li>\r\n<li><strong>Copyright Infringements</strong></li>\r\n<li><strong>Collection of Private Date (Unless DPA Registered)</strong></li>\r\n<li><strong>Viruses</strong></li>\r\n<li><strong>IRC Networks (Including all IRC Material)</strong></li>\r\n<li><strong>Peer to Peer software&nbsp;<br /></strong></li>\r\n<li><strong>Any Adult Content</strong></li>\r\n<li><strong>Non-english Content</strong></li>\r\n</ol></span></span></p>'),
('multiple', '1'),
('general', '1'),
('message', '<p>Signups are currently disabled. Contact your host for more information.</p>'),
('delacc', '1'),
('cenabled', '1'),
('cmessage', '<p>Client area isn''t enabled. Sorry, contact your host for more details.</p>'),
('emailmethod', 'php'),
('emailfrom', 'The Hosting Tool <admin@thehostingtool.com>'),
('smtp_host', 'localhost'),
('smtp_user', 'user'),
('smtp_password', 'password'),
('show_version_id', '1'),
('show_acp_menu', '1'),
('show_page_gentime', '1'),
('alerts', ''),
('p2hcheck', ''),
('show_footer', '0'),
('senabled', '1'),
('smessage', '<p>Support area isn''t enabled. Sorry, contact your host for more details.</p>'),
('terminationdays', '14'),
('suspensiondays', '14'),
('tldonly', '0'),
('currency', 'USD'),
('ui-theme', 'cupertino'),
('paypalmode', 'live'),
('paypalsandemail', ''),
('useakismet', '0'),
('akismetkey', ''),
('emailoncron', '0'),
('p2hwarndate', '20');

-- --------------------------------------------------------

--
-- Table structure for table `%PRE%invoices`
--

CREATE TABLE IF NOT EXISTS `%PRE%invoices` (
  `id` int(255) NOT NULL auto_increment,
  `uid` int(255) NOT NULL,
  `amount` varchar(255) NOT NULL,
  `is_paid` int(1) NOT NULL default '0',
  `created` timestamp NOT NULL default CURRENT_TIMESTAMP,
  `due` text NOT NULL,
  `notes` text NOT NULL,
  `uniqueid` varchar(255) NOT NULL,
  PRIMARY KEY  (`id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 AUTO_INCREMENT=1 ;

--
-- Dumping data for table `%PRE%invoices`
--


-- --------------------------------------------------------

--
-- Table structure for table `%PRE%navbar`
--

CREATE TABLE IF NOT EXISTS `%PRE%navbar` (
  `id` smallint(6) NOT NULL auto_increment,
  `icon` varchar(20) NOT NULL,
  `visual` varchar(70) NOT NULL,
  `link` varchar(20) NOT NULL,
  `order` int(11) NOT NULL default '0',
  PRIMARY KEY  (`id`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 AUTO_INCREMENT=17 ;

--
-- Dumping data for table `%PRE%navbar`
--

INSERT INTO `%PRE%navbar` (`id`, `icon`, `visual`, `link`, `order`) VALUES
(1, 'cart.png', 'Order Form', 'order', 1),
(2, 'user.png', 'Client Area', 'client', 0),
(3, 'key.png', 'Admin Area', 'admin', 2),
(4, 'report_magnify.png', 'Knowledge Base', 'support', 3);

-- --------------------------------------------------------

--
-- Table structure for table `%PRE%packages`
--

CREATE TABLE IF NOT EXISTS `%PRE%packages` (
  `id` mediumint(9) NOT NULL auto_increment,
  `name` varchar(255) NOT NULL,
  `backend` varchar(50) NOT NULL,
  `description` text NOT NULL,
  `type` varchar(10) NOT NULL,
  `server` varchar(20) NOT NULL,
  `admin` tinyint(1) NOT NULL,
  `reseller` tinyint(4) NOT NULL,
  `additional` text NOT NULL,
  `order` int(11) NOT NULL default '0',
  `is_hidden` int(1) NOT NULL,
  `is_disabled` int(1) NOT NULL,
  PRIMARY KEY  (`id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 AUTO_INCREMENT=1 ;

--
-- Dumping data for table `%PRE%packages`
--


-- --------------------------------------------------------

--
-- Table structure for table `%PRE%resources`
--

CREATE TABLE IF NOT EXISTS `%PRE%resources` (
  `resource_name` varchar(20) NOT NULL,
  `resource_value` text NOT NULL
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

--
-- Dumping data for table `%PRE%resources`
--

INSERT INTO `%PRE%resources` (`resource_name`, `resource_value`) VALUES
('admin_notes', '<p><strong>Welcome to your TheHostingTool v1.3 Installation!</strong></p>\r\n<p>We hope that you like TheHostingTool and you have a good time with our script. If you need any help, you can ask at the THT Community, or contact us directly. Thanks for using TheHostingTool, and good luck on your hosting service!</p>\r\n<p>- The THT Team</p>');

-- --------------------------------------------------------

--
-- Table structure for table `%PRE%servers`
--

CREATE TABLE IF NOT EXISTS `%PRE%servers` (
  `id` mediumint(9) NOT NULL auto_increment,
  `name` varchar(20) NOT NULL,
  `host` varchar(50) NOT NULL,
  `user` varchar(20) NOT NULL,
  `accesshash` text NOT NULL,
  `type` varchar(10) NOT NULL,
  `port` varchar(5) NOT NULL,
  `whmport` varchar(5) NOT NULL,
  `nameservers` text NOT NULL,
  `ip` varchar(50) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 AUTO_INCREMENT=1;

--
-- Dumping data for table `%PRE%servers`
--


-- --------------------------------------------------------

--
-- Table structure for table `%PRE%staff`
--

CREATE TABLE IF NOT EXISTS `%PRE%staff` (
  `id` mediumint(9) NOT NULL auto_increment,
  `user` varchar(20) NOT NULL,
  `email` varchar(50) NOT NULL,
  `name` varchar(50) NOT NULL,
  `password` text NOT NULL,
  `salt` text NOT NULL,
  `perms` text NOT NULL,
  `tzadjust` text NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 AUTO_INCREMENT=1;

--
-- Dumping data for table `%PRE%staff`
--

-- --------------------------------------------------------

--
-- Table structure for table `%PRE%subdomains`
--

CREATE TABLE IF NOT EXISTS `%PRE%subdomains` (
  `id` mediumint(9) NOT NULL auto_increment,
  `subdomain` varchar(50) NOT NULL,
  `server` varchar(5) NOT NULL,
  PRIMARY KEY  (`id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 AUTO_INCREMENT=1 ;

--
-- Dumping data for table `%PRE%subdomains`
--


-- --------------------------------------------------------

--
-- Table structure for table `%PRE%supportnav`
--

CREATE TABLE IF NOT EXISTS `%PRE%supportnav` (
  `id` mediumint(9) NOT NULL auto_increment,
  `visual` varchar(50) NOT NULL,
  `icon` varchar(20) NOT NULL,
  `link` varchar(20) NOT NULL,
  PRIMARY KEY  (`id`)
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
  `id` mediumint(9) NOT NULL auto_increment,
  `name` varchar(50) NOT NULL,
  `acpvisual` varchar(50) NOT NULL,
  `subject` varchar(250) NOT NULL,
  PRIMARY KEY  (`id`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 AUTO_INCREMENT=17 ;

--
-- Dumping data for table `%PRE%templates`

INSERT INTO `%PRE%templates` (`id`, `name`, `acpvisual`, `subject`) VALUES
(1, 'reset', 'Client - Reset Password', 'New Password'),
(3, 'newacc', 'Client - New Hosting Account', 'Your Hosting Account'),
(4, 'termacc', 'Client - Client Terminated', 'Termination'),
(5, 'suspendacc', 'Client - Suspended Account', 'Suspended'),
(6, 'unsusacc', 'Client - Unsuspended Account', 'Unsuspended'),
(7, 'newaccadmin', 'Client - Awaiting Validation', 'Awaiting Admin'),
(8, 'adminval', 'Admin - User Needs Validating', 'User Awaiting Validation'),
(9, 'approvedacc', 'Client - Account Approved', 'Account Approved'),
(10, 'declinedacc', 'Client - Declined Account', 'Account Declined'),
(11, 'p2hwarning', 'Client - Post 2 Host - Posts Warning', 'Monthly Posts Warning'),
(12, 'newticket', 'Admin - New Ticket', 'New Ticket'),
(13, 'newresponse', 'Admin - New Ticket Response', 'New Ticket Response'),
(14, 'clientresponse', 'Client - New Ticket Response', 'New Ticket Response'),
(15, 'areset', 'Admin - Admin Reset Password', 'New ACP Password!'),
(16, 'newinvoice', 'Client - New Invoice', 'New Invoice'),
(17, 'cancelacc', 'Client - Account Cancelled', 'Cancelled'),
(18, 'newreselleracc', 'Client - New Reseller Hosting Account', 'Your Reseller Hosting Account'),
(19, 'newreselleraccadmin', 'Client - Reseller Awaiting Validation', 'Awaiting Admin');

--
-- Table structure for table `%PRE%tickets`
--

CREATE TABLE IF NOT EXISTS `%PRE%tickets` (
  `id` mediumint(9) NOT NULL auto_increment,
  `title` varchar(100) NOT NULL,
  `content` text NOT NULL,
  `urgency` varchar(50) NOT NULL,
  `time` varchar(20) NOT NULL,
  `reply` mediumint(9) NOT NULL,
  `ticketid` mediumint(9) NOT NULL,
  `staff` mediumint(9) NOT NULL,
  `userid` mediumint(9) NOT NULL,
  `status` mediumint(9) NOT NULL default '1',
  PRIMARY KEY  (`id`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 AUTO_INCREMENT=16 ;

--
-- Dumping data for table `%PRE%tickets`
--


-- --------------------------------------------------------

--
-- Table structure for table `%PRE%types`
--

CREATE TABLE IF NOT EXISTS `%PRE%types` (
  `id` int(10) unsigned NOT NULL auto_increment,
  `name` varchar(15) NOT NULL,
  `visual` varchar(20) NOT NULL,
  PRIMARY KEY  (`id`)
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
  `id` mediumint(9) NOT NULL auto_increment,
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
  `status` varchar(1) NOT NULL default '0',
  `tzadjust` text NOT NULL,
  PRIMARY KEY  (`id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 AUTO_INCREMENT=1 ;

--
-- Dumping data for table `%PRE%users`
--


-- --------------------------------------------------------

--
-- Table structure for table `%PRE%user_packs`
--

CREATE TABLE IF NOT EXISTS `%PRE%user_packs` (
  `id` mediumint(9) NOT NULL auto_increment,
  `userid` varchar(5) NOT NULL,
  `username` varchar(50) NOT NULL,
  `domain` varchar(50) NOT NULL,
  `pid` varchar(5) NOT NULL,
  `signup` varchar(20) NOT NULL,
  `status` varchar(1) NOT NULL,
  `additional` text NOT NULL,
  PRIMARY KEY  (`id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 AUTO_INCREMENT=1 ;

--
-- Dumping data for table `%PRE%user_packs`
--

-- --------------------------------------------------------

--
-- Table structure for table `%PRE%users_bak`
--

CREATE TABLE IF NOT EXISTS `%PRE%users_bak` (
  `id` mediumint(9) NOT NULL auto_increment,
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
  `status` varchar(1) NOT NULL default '0',
  PRIMARY KEY  (`id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 AUTO_INCREMENT=1 ;

--
-- Dumping data for table `%PRE%users_bak`
--

-- --------------------------------------------------------

--
-- Table structure for table `%PRE%user_packs_bak`
--

CREATE TABLE IF NOT EXISTS `%PRE%user_packs_bak` (
  `id` mediumint(9) NOT NULL auto_increment,
  `userid` varchar(5) NOT NULL,
  `username` varchar(50) NOT NULL,
  `domain` varchar(50) NOT NULL,
  `pid` varchar(5) NOT NULL,
  `signup` varchar(20) NOT NULL,
  `status` varchar(1) NOT NULL,
  `additional` text NOT NULL,
  PRIMARY KEY  (`id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 AUTO_INCREMENT=1 ;

--
-- Dumping data for table `%PRE%user_packs_bak`
--

-- --------------------------------------------------------

--
-- Table structure for table `%PRE%logs`
--

CREATE TABLE IF NOT EXISTS `%PRE%logs` (
  `id` mediumint(9) NOT NULL auto_increment,
  `uid` varchar(5) NOT NULL,
  `loguser` varchar(50) NOT NULL,
  `logtime` varchar(20) NOT NULL,
  `message` text NOT NULL,
  PRIMARY KEY  (`id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 AUTO_INCREMENT=1 ;

--
-- Dumping data for table `%PRE%user_packs_bak`
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
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 AUTO_INCREMENT=1 ;
