-- $Id: mpac.sql,v 1.2 2005/01/26 22:38:11 atrommer Exp $
-- MySQL dump 9.10
--
-- Host: localhost    Database: mpac
-- ------------------------------------------------------
-- Server version	4.1.6-gamma-standard

--
-- Table structure for table `areas`
--

DROP TABLE IF EXISTS areas;
CREATE TABLE areas (
  area_id int(11) unsigned NOT NULL auto_increment,
  area_name varchar(10) NOT NULL default '',
  area_desc varchar(100) NOT NULL default '',
  area_templ varchar(255) default NULL,
  PRIMARY KEY  (area_id)
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

--
-- Table structure for table `areasuper`
--

DROP TABLE IF EXISTS areasuper;
CREATE TABLE areasuper (
  as_id int(11) unsigned NOT NULL auto_increment,
  as_area_id int(11) unsigned NOT NULL default '0',
  as_uid int(11) unsigned NOT NULL default '0',
  PRIMARY KEY  (as_id),
  KEY as_area_id (as_area_id),
  KEY as_uid (as_uid),
  CONSTRAINT areasuper_ibfk_1 FOREIGN KEY (as_area_id) REFERENCES areas (area_id) ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT areasuper_ibfk_2 FOREIGN KEY (as_uid) REFERENCES users (user_id) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

--
-- Table structure for table `assignments`
--

DROP TABLE IF EXISTS assignments;
CREATE TABLE assignments (
  assign_id int(10) unsigned NOT NULL auto_increment,
  assign_uid int(10) unsigned NOT NULL default '0',
  assign_pid int(10) unsigned NOT NULL default '0',
  assign_eid int(10) unsigned NOT NULL default '0',
  PRIMARY KEY  (assign_id),
  KEY assign_uid (assign_uid),
  KEY assign_pid (assign_pid),
  KEY assign_eid (assign_eid),
  CONSTRAINT assignments_ibfk_1 FOREIGN KEY (assign_uid) REFERENCES users (user_id) ON DELETE NO ACTION ON UPDATE CASCADE,
  CONSTRAINT assignments_ibfk_2 FOREIGN KEY (assign_pid) REFERENCES positions (pos_id) ON DELETE NO ACTION ON UPDATE CASCADE,
  CONSTRAINT assign_assign_eid_fk FOREIGN KEY (assign_eid) REFERENCES `events` (event_id) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

--
-- Table structure for table `availabletimes`
--

DROP TABLE IF EXISTS availabletimes;
CREATE TABLE availabletimes (
  avail_id int(10) unsigned NOT NULL auto_increment,
  avail_uid int(11) unsigned NOT NULL default '0',
  avail_day char(1) NOT NULL default '',
  avail_start time NOT NULL default '00:00:00',
  avail_end time NOT NULL default '00:00:00',
  PRIMARY KEY  (avail_id),
  KEY avail_uid (avail_uid),
  CONSTRAINT availabletimes_ibfk_1 FOREIGN KEY (avail_uid) REFERENCES users (user_id) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

--
-- Table structure for table `dayoff`
--

DROP TABLE IF EXISTS dayoff;
CREATE TABLE dayoff (
  day_id int(11) unsigned NOT NULL auto_increment,
  day_uid int(11) unsigned NOT NULL default '0',
  day_start date NOT NULL default '0000-00-00',
  day_end date NOT NULL default '0000-00-00',
  day_desc varchar(50) NOT NULL default '',
  PRIMARY KEY  (day_id),
  KEY day_uid (day_uid),
  CONSTRAINT dayoff_ibfk_1 FOREIGN KEY (day_uid) REFERENCES users (user_id) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

--
-- Table structure for table `events`
--

DROP TABLE IF EXISTS events;
CREATE TABLE `events` (
  event_id int(11) unsigned NOT NULL auto_increment,
  event_start time NOT NULL default '00:00:00',
  event_end time NOT NULL default '00:00:00',
  event_area_id int(11) unsigned NOT NULL default '0',
  event_name varchar(50) NOT NULL default '',
  event_comments text,
  event_date date NOT NULL default '0000-00-00',
  PRIMARY KEY  (event_id),
  KEY event_area_id (event_area_id),
  CONSTRAINT events_ibfk_1 FOREIGN KEY (event_area_id) REFERENCES areas (area_id) ON DELETE NO ACTION ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

--
-- Table structure for table `positions`
--

DROP TABLE IF EXISTS positions;
CREATE TABLE positions (
  pos_id int(10) unsigned NOT NULL auto_increment,
  pos_area_id int(10) unsigned NOT NULL default '0',
  pos_name varchar(20) NOT NULL default '',
  pos_desc varchar(100) NOT NULL default '',
  PRIMARY KEY  (pos_id),
  KEY pos_area_id (pos_area_id),
  CONSTRAINT positions_ibfk_1 FOREIGN KEY (pos_area_id) REFERENCES areas (area_id) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

--
-- Table structure for table `supervisors`
--

DROP TABLE IF EXISTS supervisors;
CREATE TABLE supervisors (
  super_emp int(11) unsigned NOT NULL default '0',
  super_super int(11) unsigned NOT NULL default '0',
  KEY super_emp (super_emp),
  CONSTRAINT supervisors_ibfk_1 FOREIGN KEY (super_emp) REFERENCES users (user_id) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

--
-- Table structure for table `types`
--

DROP TABLE IF EXISTS types;
CREATE TABLE `types` (
  type_id int(10) unsigned NOT NULL auto_increment,
  type_name varchar(10) NOT NULL default '',
  PRIMARY KEY  (type_id)
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

--
-- Table structure for table `users`
--

DROP TABLE IF EXISTS users;
CREATE TABLE users (
  user_id int(10) unsigned NOT NULL auto_increment,
  user_name varchar(10) NOT NULL default '',
  user_pass varchar(40) NOT NULL default '',
  user_first varchar(10) NOT NULL default '',
  user_last varchar(20) NOT NULL default '',
  user_email varchar(50) NOT NULL default '',
  user_phone1 bigint(20) NOT NULL default '0',
  user_phone2 bigint(20) default NULL,
  user_type int(11) unsigned NOT NULL default '0',
  user_pay_rate float(5,2) default '0.00',
  PRIMARY KEY  (user_id),
  KEY user_type (user_type),
  CONSTRAINT users_ibfk_1 FOREIGN KEY (user_type) REFERENCES `types` (type_id) ON DELETE NO ACTION ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

