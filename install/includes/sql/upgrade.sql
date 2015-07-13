ALTER TABLE `%PRE%user_packs` CHANGE `id` `id` MEDIUMINT( 9 ) NOT NULL AUTO_INCREMENT ,
CHANGE `userid` `userid` MEDIUMINT( 9 ) NOT NULL;

ALTER TABLE `%PRE%invoices` CHANGE `amount` `amount` VARCHAR( 255 ) NOT NULL;
INSERT INTO `%PRE%config` (`name`, `value`) VALUES ('paypalmode', 'live'), ('paypalsandemail', ''), ('useakismet', '0'), ('akismetkey', ''), ('emailoncron', '0'), ('p2hwarndate', '20');
UPDATE `%PRE%config` SET value = 'bluelust' WHERE name = 'theme' LIMIT 1;
ALTER TABLE `%PRE%users` ADD `tzadjust` TEXT NOT NULL;
ALTER TABLE `%PRE%staff` ADD `tzadjust` TEXT NOT NULL;
ALTER TABLE `%PRE%acpnav` CHANGE `visual` `visual` VARCHAR( 255 ) NOT NULL;
DELETE FROM `%PRE%acpnav` WHERE visual = 'Order Form' LIMIT 1;
ALTER TABLE `%PRE%users_bak` ADD `uid` INT( 11 ) NOT NULL;

ALTER TABLE `%PRE%servers` ADD `port` VARCHAR( 5 ) NOT NULL,
ADD `whmport` VARCHAR( 5 ) NOT NULL,
ADD `nameservers` TEXT NOT NULL,
ADD `ip` VARCHAR( 50 ) NOT NULL;

DROP TABLE IF EXISTS `%PRE%templates`;
CREATE TABLE IF NOT EXISTS `%PRE%templates` (
  `id` mediumint(9) NOT NULL AUTO_INCREMENT,
  `name` varchar(50) NOT NULL,
  `acpvisual` varchar(50) NOT NULL,
  `subject` varchar(250) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 AUTO_INCREMENT=20;

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
