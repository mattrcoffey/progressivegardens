<?php

$installer = $this;

$installer->startSetup();

$installer->run(

"CREATE TABLE IF NOT EXISTS `".Mage::getSingleton('core/resource')->getTableName('connect_config')."` (
  `id` int(50) NOT NULL AUTO_INCREMENT,
  `email` varchar(255) DEFAULT NULL,
  `token` varchar(255) NOT NULL,
  `store_id` varchar(255) NOT NULL,
  `wcstoremodule` varchar(255) NOT NULL,
  `status` varchar(255) NOT NULL,
  `created_time` varchar(255) DEFAULT NULL,
  `update_time` varchar(255) DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM  DEFAULT CHARSET=latin1 AUTO_INCREMENT=2 ;"

);

$installer->run("CREATE TABLE IF NOT EXISTS `".Mage::getSingleton('core/resource')->getTableName('connect_qb_orders')."` (
  `id` int(4) NOT NULL AUTO_INCREMENT,
  `orderid` varchar(20) NOT NULL,
  `profile_id` varchar(50) DEFAULT NULL,
  `qb_status` varchar(50) NOT NULL,
  `qb_posted` varchar(20) DEFAULT NULL,
  `qb_posted_date` varchar(20) DEFAULT NULL,
  `transaction_type` varchar(50) DEFAULT NULL,
  `qb_transactionNumber` varchar(255) DEFAULT NULL,
  `TransactionMsg` text,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM  DEFAULT CHARSET=latin1 AUTO_INCREMENT=33 ;");



/*
$installer->run(

"CREATE TABLE IF NOT EXISTS `{$this->getTable('connect_config')}` (
  `id` int(50) NOT NULL AUTO_INCREMENT,
  `email` varchar(255) DEFAULT NULL,
  `token` varchar(255) NOT NULL,
  `store_id` varchar(255) NOT NULL,
  `wcstoremodule` varchar(255) NOT NULL,
  `status` varchar(255) NOT NULL,
  `created_time` varchar(255) DEFAULT NULL,
  `update_time` varchar(255) DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM  DEFAULT CHARSET=latin1 AUTO_INCREMENT=2 ;"

);

$installer->run("CREATE TABLE IF NOT EXISTS `{$this->getTable('connect_qb_orders')}` (
  `id` int(4) NOT NULL AUTO_INCREMENT,
  `orderid` varchar(20) NOT NULL,
  `profile_id` varchar(50) DEFAULT NULL,
  `qb_status` varchar(50) NOT NULL,
  `qb_posted` varchar(20) DEFAULT NULL,
  `qb_posted_date` varchar(20) DEFAULT NULL,
  `transaction_type` varchar(50) DEFAULT NULL,
  `qb_transactionNumber` varchar(255) DEFAULT NULL,
  `TransactionMsg` text,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM  DEFAULT CHARSET=latin1 AUTO_INCREMENT=33 ;");
*/
/*$installer->run("CREATE TABLE IF NOT EXISTS {$this->getTable('connect_config')} (
  `ecc_id` int(11) unsigned NOT NULL auto_increment,
  `title` varchar(255) NOT NULL default '',
  `content` text NOT NULL default '',
  `status` smallint(6) NOT NULL default '0',
  `created_time` datetime NULL,
  `update_time` datetime NULL,
  PRIMARY KEY (`ecc_id`)
)ENGINE=MyISAM  DEFAULT CHARSET=latin1 AUTO_INCREMENT=37; ");*/
$installer->endSetup();

