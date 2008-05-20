<?php require('common.php');
/*    
 *  This program is free software; you can redistribute it and/or modify
 *  it under the terms of the GNU General Public License as published by
 *  the Free Software Foundation; either version 2 of the License, or
 *  (at your option) any later version.
 *
 *  This program is distributed in the hope that it will be useful,
 *  but WITHOUT ANY WARRANTY; without even the implied warranty of
 *  MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 *  GNU General Public License for more details.
 *
 *  You should have received a copy of the GNU General Public License
 *  along with this program; if not, write to the Free Software
 *  Foundation, Inc., 51 Franklin St, Fifth Floor, Boston, MA  02110-1301  USA
*/
// $Id: dbsetup.php,v 1.5 2005/10/30 22:37:19 atrommer Exp $
	// please put the vals you need in the
	// following variables
	$username = sanitizeInput('atrommer');
	$password = sanitizeInput('test');
	$first = sanitizeInput('Andrew');
	$last = sanitizeInput('Trommer');
	$email = sanitizeInput('agt0004@unt.edu');
	$phone = sanitizeInput('5154509478');
	$pay = sanitizeInput('11.50');
	
	//print 'Creating Tables';
	global $db;
	/*$db->query("DROP TABLE IF EXISTS areas;
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

");
	print '<br>Tables created sucessfully';
	*/
	print '<br>Setting up user '.$username;
	// now we insert the initial admin you specified
	
	$newPW = sanitizeInput(crypt($password, $username));
	$db->query("insert into users values(null,$username, $newPW, $first, $last, $email, 
		$phone, null, 3, $pay)");
	
	print '<br>User created sucessfully!<br>';
	print '<a href=index.php>Click here to log in</a>';
?>
