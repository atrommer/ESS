<?php
// $Id: common.php,v 1.29 2006/02/03 20:24:34 atrommer Exp $
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
require 'DB.php';
require("classes/class.phpmailer.php");
session_start();
// change this to reflect your path to ess
$docroot = "/~atrommer/dev/essroot";


// set up our db connection
// edit this with your username, password, and db name
$db = DB::connect('mysql://root:dev@localhost/mpac');

// change the following to match your mail settings
$mail = new PHPMailer();
$mail->Host     = "smtp.host.tld";
$mail->Mailer   = "smtp";


// if this is production (not yet!) set to false
$DEV = true;
if (DB::isError($db)) { die("Can't connect to database: " . $db->getMessage()); }
// set up db error handling
$db->setErrorHandling(PEAR_ERROR_DIE);
// grab the db rows as objects
$db->setFetchMode(DB_FETCHMODE_OBJECT);

// globals
$gaDOW = array('Sunday','Monday','Tuesday','Wednesday','Thursday','Friday','Saturday');
$host = strlen($_SERVER['HTTP_HOST'])?$_SERVER['HTTP_HOST']:$_SERVER['SERVER_NAME'];
$fullpath = $host . $docroot; // append the current location to the host
$tinymce = "tinymce/jscripts/tiny_mce";

function addUserToSuper($iSuper, $iEmp){
	global $db;
	
	$db->query("insert into supervisors values($iEmp, $iSuper)");	
}

function getAllEmps(){
	global $db;
	return ($db->getAll(
		"select user_id, user_first, user_last" .
		" from users" .
		" order by user_last asc"
	));	
}

function delPos($iPosID){
	global $db;
	
	$db->query("delete from positions where pos_id=$iPosID");	
}

function addPos($aPostData, $iUID){
	global $db;
	
	// first, check if passed in user owns area
	$oAreas = getAreas($iUID, $aPostData['hdArea']);
	if (!$oAreas){ accessDenied("Invalid user and area combination!"); }
	
	// now we're clean
	// grab area pos
	$oAreaPos = getAreaPos($aPostData['hdArea']);
	foreach ($oAreaPos as $Pos){
		$db->query(
			"update positions" .
			" set pos_name=".sanitizeInput($aPostData['tbName'.$Pos->pos_id]).
			", pos_desc=".sanitizeInput($aPostData['taDesc'.$Pos->pos_id]).
			" where pos_id=".$Pos->pos_id
		);	
	}
	
	// now do an add if not null
	if ($aPostData['tbNName'] && $aPostData['taNDesc']){
		$db->query(
			"insert into positions values(null, ".$aPostData['hdArea'].", ".sanitizeInput($aPostData['tbNName']).",".sanitizeInput($aPostData['taNDesc']).")"
		);	
	}	
}

function emailEmps($aPostData, $iUID){
	global $db;
	global $mail;
	
	// grab cur user vals
	$oSender = getUserVals($iUID);
	// grab emps for current user
	$oMyEmps = getMyEmployees($iUID);
	
	$mail->From = $oSender->user_email;
	$mail->FromName = $oSender->user_first .' '. $oSender->user_last;
	$mail->Subject  = ($aPostData['tbSubj']);
	$mail->Body = ($aPostData['taBody']);
	
	foreach ($aPostData['cbMail'] as $MailID){
		$oEmp = getUserVals($MailID);
		$mail->AddAddress($oEmp->user_email, $oEmp->user_first.' '.$oEmp->user_last);
	}
	
	// send the message
	if(!$mail->Send())
         accessDenied("There has been an error sending mail! ");
    $mail->ClearAddresses();
    $mail->ClearAttachments();
}

function checkEventPos($iUID, $iEID, $iPID){
	/*
	 * This function returns the positionID, if any
	 * for a given user and event
	 */	
 	global $db;
 	return $db->getOne("select count(*) from assignments" .
 			"			where assign_uid=$iUID" .
 			"			and assign_eid = $iEID" .
 			"			and assign_pid = $iPID");
}

function deleteEvent($iEventID){
	global $db;
	global $DEV;
	global $mail;
	global $host;
	global $docroot;
	// get sender details
	$oSender = getUserVals($_SESSION['USERID']);
	
	// get event details
	$oEventDetails = getEventDetails($iEventID);
	
	// get event emps
	$oEEmps = getEventEmps($iEventID);
	
	// set up email basics
	$mail->From     = $oSender->user_email;
	$mail->FromName = $oSender->user_first .' '. $oSender->user_last;
	$mail->Subject  = $oEventDetails->event_name. ' has been canceled!';
	// the message
	$sBody = 'The event, '.$oEventDetails->event_name.', on '.date("l, F jS, Y", strtotime($oEventDetails->event_date)); 
	$sBody .= ' from '. date("g:i a", strtotime($oEventDetails->event_start)) .' to '. date("g:i a", strtotime($oEventDetails->event_end));
	$sBody .= ' has been canceled.  Please plan accordingly.';
	$sBody .= "\nPlease see http://".$host.$docroot." for more details.";
	$sBody .= "\nThanks, \n" . $oSender->user_first .' '. $oSender->user_last;
	
	$mail->Body = $sBody;
	// add the scheduled emps to the list
	foreach ($oEEmps as $Emp){
		$mail->AddAddress($Emp->user_email, $Emp->user_first.' '.$Emp->user_last);	
	}
	if(!$mail->Send())
         accessDenied("There has been an error sending mail!");
    $mail->ClearAddresses();
    $mail->ClearAttachments();
    
	$db->query("delete from events where event_id=$iEventID");	
}

function updateNotes($iEID, $sComments){
	global $db;
	$db->query("update events set event_comments=$sComments where event_id=$iEID");
}

function schedEvent($postData, $iEventID=''){
	global $mail;
	global $host;
	global $docroot;
	// take the input vals, call addEvent, and call addAssign
	// if we have an event_id, we do an update, else, we just add
	if ($iEventID){
		$iEID = $iEventID;
		// we update the event, delete the currently assigned emps, and re-add the sub'd ones
		updateEvent($iEventID, sanitizeInput($postData['dDate']), sanitizeInput($postData['dStartTime']), 
			sanitizeInput($postData['dEndTime']), sanitizeInput($postData['area']), 
			sanitizeInput($postData['tbName']));
		// now do the delete
		delAssign($iEventID);
	} else {
	// first add the event and get back the eventID
	$iEID = addEvent(sanitizeInput($postData['dDate']), sanitizeInput($postData['dStartTime']), 
			sanitizeInput($postData['dEndTime']), sanitizeInput($postData['area']), 
			sanitizeInput($postData['tbName']));
	}
	// now we grab the positions for the area
	// and iterate through the emp lists, adding the assignments
	$oPositions = getAreaPos($postData['area']);
	$oEmps = getMyEmployees($_SESSION['USERID']);
	
	// grab data for email
	// get sender details
	$oSender = getUserVals($_SESSION['USERID']);
	// get event details
	$oEventDetails = getEventDetails($iEID);
	
	// set up email basics
	$mail->From     = $oSender->user_email;
	$mail->FromName = $oSender->user_first .' '. $oSender->user_last;
	// if updated event, change subj line
	if ($iEventID){ $sSubj = "UPDATED: ";} else { $sSubj = "";}
	$sSubj .= 'You have been scheduled for '. $oEventDetails->event_name;
	$mail->Subject  = $sSubj;
	// the message
	// handle updated event
	if ($iEventID){ $sBody = "The following event has been UPDATED!\n";} else { $sBody = "";}
	$sBody .= "You have been scheduled for the following:\n";
	$sBody .= "Event Name: ". $oEventDetails->event_name."\n";
	$sBody .= "Event Date: ".date("l, F jS, Y", strtotime($oEventDetails->event_date))."\n"; 
	$sBody .= "From: ". date("g:i a", strtotime($oEventDetails->event_start)) ." To: ". date("g:i a", strtotime($oEventDetails->event_end))."\n";
	$sBody .= "\nPlease see http://".$host.$docroot." for more details.\n";
	$sBody .= "Thanks, \n" . $oSender->user_first ." ". $oSender->user_last;
	// append to mail
	$mail->Body = $sBody;
	// iterate list
	$bAssigned = false; // this flag flips once an assignment is made
	foreach ($oEmps as $Emp){
		if ($_POST['rad'.$Emp->user_id]){
			$bAssigned = true; // flip the flag
			addAssign($Emp->user_id, $_POST['rad'.$Emp->user_id], $iEID);
			$mail->AddAddress($Emp->user_email, $Emp->user_first ." ".$Emp->user_last);
		}	
	}
	// now check for curUser assign
	if ($_POST['rad'.$_SESSION['USERID']]){
		addAssign($_SESSION['USERID'], $_POST['rad'.$_SESSION['USERID']], $iEID);
	}	
	
	// send mail if checkbox checked and if >= 1 emp assigned
	if ($postData['chkMail'] && $bAssigned){ // send the mail
		if(!$mail->Send())
			accessDenied("There has been an error sending mail!");
		$mail->ClearAddresses();
		$mail->ClearAttachments();
	}
	
	return $iEID;
}

function delAssign($iEventID){
	// this simply deletes the current assignment for a given event
	global $db;
	$db->query("delete from assignments where assign_eid=$iEventID");
}

function updateEvent($iEventID, $dDate, $dStart, $dEnd, $iArea, $sName){
	/*
	 * updated: 20040120 to kill comments - not needed
	 */
	global $db;
	$db->query("update events" .
			"	set event_start=$dStart, event_end=$dEnd, event_area_id=$iArea, " .
			"	event_name=$sName, event_date=$dDate" .
			"	where event_id=$iEventID");
}

function addAssign($iUID, $iPID, $iEID){
	// simple insert for assignments
	global $db;
	$db->query("insert into assignments values(null, $iUID, $iPID, $iEID)");
}

function addEvent($dDate, $dStart, $dEnd, $iArea, $sName, $sComments=''){
	global $db;
	$db->query("insert into events values(null, $dStart, $dEnd, $iArea, $sName, 
				'".$sComments."', $dDate)");
	// return back the eventID
	return $db->getOne("select last_insert_id()");
}

function addArea($aData){
	global $db;
	foreach ($aData as $Data => $value){
		$aClean[] = sanitizeInput($value);
	}
	// first we do the areas insert
	$db->query("insert into areas values (null,$aClean[0],$aClean[1], null)");
	
	// now get the new area_id
	$iAreaID = $db->getOne("select last_insert_id()");
	
	// now do the supervisor updates
	foreach ($aData['msSupers'] as $Super){
		$Super = sanitizeInput($Super);
		$q = $db->query("insert into areasuper values(null,$iAreaID,$Super)");
	}
}

function getPosDetails($iPID){
	global $db;
	return $db->getRow(
		"select pos_name, pos_desc " .
		"from positions " .
		"where pos_id=$iPID");	
}

function getAreaPos($areaID){
	global $db;
	return $db->getAll("select pos_id, pos_name, pos_desc from positions
						where pos_area_id=$areaID");
}

function getMySched($iUID, $iDate=''){
	global $db;
	
	if ($iDate==1){ // filter current month
		$sFilt = "and event_date between '".date("Y-m-01")."' and '".date("Y-m-01", strtotime("+1 month"))."'";
	} elseif($iDate==2){ // show all events
		$sFilt = "";
	}else { // filter by today forward 
		$sFilt = "and event_date >= '".date("Y-m-d")."'";
	}
	return $db->getAll("" .
		"SELECT 
		  `events`.`event_id`,
		  `events`.`event_start`,
		  `events`.`event_end`,
		  `events`.`event_name`,
		  `events`.`event_date`,
		  `events`.`event_area_id`,
		  `positions`.`pos_name`,
		  `areas`.`area_name`
		FROM
		  `events`
		  INNER JOIN `assignments` ON (`events`.`event_id` = `assignments`.`assign_eid`)
		  INNER JOIN `positions` ON (`assignments`.`assign_pid` = `positions`.`pos_id`)
		  INNER JOIN `areas` ON (`events`.`event_area_id` = `areas`.`area_id`)
		WHERE
		  `assignments`.`assign_uid` = $iUID
		 ".$sFilt."
		ORDER BY
		  `events`.`event_date`,events.event_start");
}

function getEventDetails($iEventID){
	global $db;
	return $db->getRow("select event_id, event_start, event_end, " .
						"	event_name, event_comments, event_area_id, event_date " .
						"from events " .
						"where event_id=$iEventID");
}

function getAreaSched($iAID,$iDate=""){
	/* changes: 20050118: changed order by
	 * 
	 */
	global $db;	
	
	if ($iDate==1){ // filter current month
		$sFilt = "and event_date between '".date("Y-m-01")."' and '".date("Y-m-01", strtotime("+1 month"))."'";
	} elseif($iDate==2){ // show all events
		$sFilt = "";
	}else { // filter by today forward 
		$sFilt = "and event_date >= '".date("Y-m-d")."'";
	}
	//die($sFilt);
	return $db->getAll("SELECT events.event_id, 
							events.event_start, 
							events.event_end, 
							events.event_name,
							events.event_area_id,
							events.event_date,
							areas.area_name		
						FROM events 
							INNER JOIN areas ON (events.event_area_id = areas.area_id)	
						WHERE events.event_area_id = $iAID
						".$sFilt."
						ORDER BY events.event_date,events.event_start ASC");
}

function getDayOff($iUID){
	/*
	 * Updated to only pull in days >= today
	 */
	global $db;
	$today = time();
	return $db->getAll("select day_id, day_start, day_end, day_desc from dayoff " .
						"where day_uid=$iUID " .
						"and day_end >= '" . date("Y/m/d",$today) . "' " .
						"order by day_start");	
}

function delDayOff($iUID, $iDID){
	// checks owner and deletes day off request
	global $db;
	$db->query("delete from dayoff where day_uid=$iUID and day_id=$iDID");	
}
function newDayOff($iUID, $dStart, $dEnd, $sDesc){
	global $db;
	// clean up input
	$dStart = sanitizeInput($dStart);
	$dEnd = sanitizeInput($dEnd);
	$sDesc = sanitizeInput($sDesc);
	$db->query("insert into dayoff values(null,$iUID, $dStart, $dEnd, $sDesc)");	
}

function getAreas($iUID,$iAreaID=''){
	global $db;
	$sql = "SELECT areas.area_id, areas.area_name, areas.area_desc
			FROM areas INNER JOIN areasuper ON areas.area_id = areasuper.as_area_id
			WHERE areasuper.as_uid=$iUID";
			
	if (strlen($iAreaID)){ // filter for specified area
		$sql .= " and areas.area_id=$iAreaID";
	}		
	
	return $db->getAll($sql);
}

function drawMonthDD($dInit=''){
	if ($dInit){
		$dInit = date("n", strtotime($dInit));
	} else {
		$dInit = date("n");
	}
	
	for ($i=1; $i<13; $i++){
		$sTemp = $i;
		if ($sTemp<10){ $sTemp = '0' . $i;}
		if ($dInit == $i){
			print "<option value=\"$sTemp\" selected=selected>$i</option>\n";
		}else{
			print "<option value=\"$sTemp\">$i</option>\n";
		}
	}  
}

function drawDayDD($dInit=''){
	if ($dInit){
		$dInit = date("j", strtotime($dInit));
	} else {
		$dInit = date("j");
	}
	for ($i=1; $i<32; $i++){
		$sTemp = $i;
		if ($sTemp<10){ $sTemp = '0' . $i;}
		if ($dInit == $i){
			print "<option value=\"$sTemp\" selected=selected>$i</option>\n";
		} else {
			print "<option value=\"$sTemp\">$i</option>\n";
		}
	}
}

function drawYearDD($dInit=''){
	$dCurYear = date("Y");
	if ($dInit){
		$dInit = date("Y", strtotime($dInit));
	} else {
		$dInit = $dCurYear;
	}
	for ($i=$dCurYear-1; $i<$dCurYear+2; $i++){
		if ($dInit == $i){
			print "<option value=\"$i\" selected=selected>$i</option>\n";
		} else {
			print "<option value=\"$i\">$i</option>\n";
		}
	}
}

function drawTimeDD($dInit=''){
	for ($i=0; $i < 60*24; $i+=30) {
		$timestamp = strtotime("+$i minute", "6:00");
		$display = date("g:ia",$timestamp);
		$key = date("H:i",$timestamp);
		if ($dInit == $key){
			print "<option value=\"$key\" selected=selected>$display</option>\n";
		} else {
			print "<option value=\"$key\">$display</option>\n";
		}
	}
}

function delAvail($iUID, $iAID){
	global $db;
	$q = $db->query("delete from availabletimes where avail_id=$iAID and avail_uid=$iUID");
}

function getAvail($iUID, $sDay){
	global $db;
	return $db->getAll("select avail_id,avail_start,avail_end from availabletimes where
		avail_uid=$iUID and avail_day='" . $sDay."' order by avail_start");

}

function updateAvail($iUID, $aData){
	global $db;

	for ($iDay=0;$iDay < 7; $iDay++){
		// only do insert if start and end times != 0
		if (($aData['selStart'.$iDay] != 'none') && ($aData['selEnd'.$iDay] != 'none')){
			$q=$db->query("insert into availabletimes values (null,$iUID,$iDay,'" . $aData['selStart'.$iDay] ."','". $aData['selEnd'.$iDay] . "')");
		} elseif ($aData['cbAll'.$iDay]){ // add entry for all day
			$q= $db->query("insert into availabletimes values (null,$iUID,$iDay,'00:00:01','23:59:59')");
		}
	}
}

function formatPhoneNum($iPhone){
/*	if a phone number exists, format it
	using the (area) prefix - suffix format
*/
	if ($iPhone){
		return array(substr($iPhone,0,3),substr($iPhone,3,3),substr($iPhone,6,4));
	}
}

function accessDenied($sMsg="You are not allowed to view this page!"){
	doHeader("Access Denied!");
	print "<span class=errorMsg>$sMsg</span>";
	doFooter();
	die();
}

function checkUser($userType, $typeAllowed){
	if ($userType < $typeAllowed)
	{	accessDenied(); }
}

function getUserVals($iUID){
	global $db;
	$q = $db->query("select * from users where user_id=$iUID");
	return $q->fetchRow();
}

function updateCurrentUser($aNewVals, $iUID, $iType, $sUName){
/* 	this function first cleans up the values, then the values update the user table.  
	after that, the user entries are cleared from the supervisors table, 
	then the supervisors from the multiselect
	are iterated and put into the supervisors table.
*/
	global $db;
	
	// first, we want the phone numbers separate from the parsing since we cat them together
	$phone1 = $aNewVals['tbPhone1A'] . $aNewVals['tbPhone1B'] . $aNewVals['tbPhone1C'];
	$phone2 = $aNewVals['tbPhone2A'] . $aNewVals['tbPhone2B'] . $aNewVals['tbPhone2C'];	
	
	// clean up input
	foreach ($aNewVals as $field => $value) {
		$aClean[] = sanitizeInput($value);
	}
	// and now the phone numbers
	$phone1 = sanitizeInput($phone1);
	$phone2 = sanitizeInput($phone2);

	// now we do the update on users
	// check and see if pw updated
	$sPass = "";
	if ($aNewVals['tbPass']){
		// hash the pass
		$aClean[1] = sanitizeInput(crypt($aClean[1], sanitizeInput($sUName)));
		$sPass = "user_pass=$aClean[1],";
	} 
	$sql = "update users " .
			"set user_first=$aClean[2],user_last=$aClean[3],$sPass
			user_email=$aClean[4],user_phone1=$phone1,user_phone2=$phone2 " .
			"where user_id=$iUID";
	// hit the db
	$q = $db->query($sql);
	
	// updates are good!  go home
	redirect("myInfo.php");
}

function updateUser($aNewVals){
/* 	this function first cleans up the values, then the values update the user table.  
	after that, the user entries are cleared from the supervisors table, 
	then the supervisors from the multiselect
	are iterated and put into the supervisors table.
*/
	global $db;
	
	// first, we want the phone numbers separate from the parsing since we cat them together
	$phone1 = $aNewVals['tbPhone1A'] . $aNewVals['tbPhone1B'] . $aNewVals['tbPhone1C'];
	$phone2 = $aNewVals['tbPhone2A'] . $aNewVals['tbPhone2B'] . $aNewVals['tbPhone2C'];	
	
	// clean up input
	foreach ($aNewVals as $field => $value) {
		$aClean[] = sanitizeInput($value);
	}
	// and now the phone numbers
	$phone1 = sanitizeInput($phone1);
	$phone2 = sanitizeInput($phone2);
	
	// get the username for the selected user for pw salting
	$sUserName = $db->getOne("select user_name from users where user_id=$aClean[0]");
	// check and see if pw updated
	$sPass = "";
	if ($aNewVals['tbPass']){
		// crypt the password
		$aClean[1] = sanitizeInput(crypt($aClean[1],sanitizeInput($sUserName)));
		// pw update sql statement
		$sPass = "user_pass=$aClean[1],";
	} 
	$sql = "update users set user_first=$aClean[2],user_last=$aClean[3],$sPass
			user_email=$aClean[4],user_phone1=$phone1,user_phone2=$phone2,user_type=$aClean[12],
			user_pay_rate=$aClean[13],user_inactive=$aClean[14]" .
			" where user_id=$aClean[0]";

	// now we do the update on users
	print("before the update");
	$q = $db->query($sql);

	// next we clear current entries on the emp side of the supervisors table
	if ($aNewVals['msSupers'] != 0){ // we allow for someone to have no super (top tier)
		$q = $db->query("delete from supervisors where super_emp=$aClean[0]");
		
		// now we parse the supers and do those inserts
		foreach ($aNewVals['msSupers'] as $Super) {
			$q = $db->query("insert into supervisors values($aClean[0],$Super)");
		}
	}
	// updates are good!  go home
	return;
}

function deleteUser($iUID){
	/*
	 * updated to inactivate the user rather than delete
	 * this way the history remains on their assignments.
	 * will add an admin "delete forever" function
	 */
	global $db;

	// now delete from the user table, no turning back now!
	//$q = $db->query("delete from users where user_id=$iUID");
	$q = $db->query("update users set user_inactive=1 where user_id=$iUID");
}

function addUser($aNewVals){
/* 	this function first cleans up the values, then it calls goodUsername() to
	make sure the username isn't already taken.  if that pases, then the values
	are input into the user table.  after that, the supervisors from the multiselect
	are iterated and put into the supervisors table.
*/
	global $db;
	
	// first, we want the phone numbers separate from the parsing since we cat them together
	$phone1 = $aNewVals['tbPhone1A'] . $aNewVals['tbPhone1B'] . $aNewVals['tbPhone1C'];
	$phone2 = $aNewVals['tbPhone2A'] . $aNewVals['tbPhone2B'] . $aNewVals['tbPhone2C'];	
	
	// clean up input
	foreach ($aNewVals as $field => $value) {
		$aClean[] = sanitizeInput($value);
	}
	// and now the phone numbers
	$phone1 = sanitizeInput($phone1);
	$phone2 = sanitizeInput($phone2);
	
	// check for dupe username
	if (!goodUsername($aClean[0])){ // we have a dupe
		return "This username already exists, please choose another!";
	}
	
	// crypt the pass
	$aClean[1] = sanitizeInput(crypt($aClean[1],$aClean[0]));
	
	// now we do the insert on users
	$q = $db->query("insert into users(user_id, user_name, user_pass, " .
			"user_first, user_last, user_email, user_phone1, user_phone2, user_type, user_pay_rate, user_inactive)" .
			" values (null,$aClean[0],$aClean[1],$aClean[2],$aClean[3],$aClean[4]," .
			"$phone1,$phone2,$aClean[12],$aClean[13],0)");
	
	// now we get the new user_id
	$newUID = $db->getOne("select user_id from users where user_name=$aClean[0]");
	
	// now we parse the supers and do those inserts
	foreach ($aNewVals['msSupers'] as $Super) {
		$q = $db->query("insert into supervisors values($newUID,$Super)");
	}
	
	// updates are good!  go home
	redirect("myEmps.php");
}

function getEmpSupervisors($iUserID){
	global $db;
	
	return $db->getAll("select super_super from supervisors where super_emp=$iUserID");
}

function goodUsername($sUsername) {
/* 	this function takes a string and checks for a match
	to make sure a username doesn't appear twice.
*/
	global $db;
	$q = $db->query("select user_name from users where user_name = $sUsername");
	$row = $q->fetchRow();
	if ($row->user_name == "") { // not found in db
		return 1;
	} else { return 0; }
}

function assumeUser($iUID){
	global $db;
	$oDetails = $db->getRow("select user_id, user_name, user_type from users where user_id=$iUID");
	$_SESSION['USERID'] = $oDetails->user_id;
	$_SESSION['USERNAME'] = $oDetails->user_name;
	$_SESSION['USERTYPE'] = $oDetails->user_type;
	redirect();	
}

function loginUser($sUsername, $sPass) {
	// updated to handle inactive user
	global $db;
	
	// clean up input
	$sUsername = sanitizeInput($sUsername);
	$sPass = sanitizeInput($sPass);

	// salt up the pass
	$sPass = crypt($sPass,$sUsername);
	
	// get the match
	$q = $db->query("select user_id, user_name, user_pass, user_type from users where user_name = $sUsername and user_inactive <> 1");
	$row = $q->fetchRow();
	if ($row->user_pass != $sPass) { // not found in db
		return false;
	} else { // we have a good login
		// now we store user creds to the session
		$_SESSION['USERID'] = $row->user_id;
		$_SESSION['USERNAME'] = $row->user_name;
		$_SESSION['USERTYPE'] = $row->user_type;
		redirect("mySchedule.php");
	}
}

function redirect($to='')
{
	global $docroot;
	global $fullpath;
	$schema = $_SERVER['SERVER_PORT'] == '443' ? 'https' : 'http';
	if (headers_sent()) return false;
	else
	{
		header("HTTP/1.1 301 Moved Permanently");
		// header("HTTP/1.1 302 Found");
		// header("HTTP/1.1 303 See Other");
		header("Location: $schema://$fullpath/$to");
		exit();
	}
}

function getChildSupers($iUserID){
	//updated for inactive users
	global $db;
	
	return $db->getAll("select user_id, user_first,
					user_last 
					from users
					inner join supervisors on (users.user_id = supervisors.super_emp) 
					where supervisors.super_super = $iUserID " .
					"and users.user_type>1 and user_inactive <> 1 order by user_last");
}

function getMySupers($iUserID){
	// updated for inactive users
	global $db;
	
	return $db->getAll("select user_id, user_name, user_first,
						user_last, user_email, user_phone1, user_phone2 
						from users
						inner join supervisors on (users.user_id = supervisors.super_super) 
						where supervisors.super_emp = $iUserID and user_inactive <> 1 order by user_last");
}

function getEventEmps($iEID, $iPID=''){
	global $db;
	$sSQL = "select user_last, user_first, user_id, user_email from users
			where users.user_id in (
				select assign_uid from assignments 
				where assign_eid=" .$iEID;
	if ($iPID){ $sSQL .= " and assign_pid=" .$iPID;}
	$sSQL .= " ) order by user_last";
	return $db->getAll($sSQL);
}

function getAreaSuper($iAID){
	global $db;
	
	return $db->getAll("select as_uid from areasuper" .
			"			where as_area_id=$iAID");	
}

function updateArea($aData){
	global $db;
	$iAreaID = $aData['hdArea'];
	// first update the area info
	$db->query("update areas " .
			"set area_name=".sanitizeInput($aData['tbName']).", area_desc=".sanitizeInput($aData['tbDesc']).", " .
				"area_templ=".sanitizeInput($aData['taTempl'])." " .
			"where area_id=$iAreaID");
	
	// clear the areasuper list
	$db->query("delete from areasuper where as_area_id=$iAreaID");
	// now do the supervisor updates
	foreach ($aData['msSupers'] as $Super){
		$Super = sanitizeInput($Super);
		$db->query("insert into areasuper values(null,$iAreaID,$Super)");
	}
}

function getAreaTempl($iAID){
	global $db;
	return $db->getOne("select area_templ from areas where area_id=".$iAID);
}

/* This function returns an object that holds the users
 * without conflicts for the given day and time.
 *
 */	
function checkEmpConflict($dStart, $dEnd, $sDay, $iUID, $dDate){
	//updated for dayoff logic in hours
	global $db;
	$dDateTime = ($dDate .' '. $dStart);
	$sSQL = "select distinct user_id
			from users 
			inner join availabletimes on (users.user_id = availabletimes.avail_uid)
			where 	(('". $dEnd ."'<= availabletimes.avail_end) and
					('". $dStart ."'>= availabletimes.avail_start))
			and availabletimes.avail_day='". $sDay ."'
			and users.user_id=$iUID
			and users.user_id not in (select day_uid from dayoff where '".$dDateTime."' >= day_start and '".$dDateTime."' <= day_end)
			order by user_last asc";

	return $db->getAll($sSQL);
}

function getMyEmployees($iUserID, $iFilter = 1){
	// updated to handle inactive users
	global $db;
	switch ($iFilter){
		default: 	$sWhere = "and users.user_inactive <> 1 ";
					break;
		case 0:		$sWhere = "";
					break;
		case 2:		$sWhere = "and users.user_inactive = 1 ";
					break;
	}
	return $db->getAll("select user_id, user_name, user_first,
						user_last, user_email, user_phone1, user_phone2 
						from users
						inner join supervisors on (users.user_id = supervisors.super_emp) 
						where supervisors.super_super = $iUserID $sWhere" .
						"order by user_last");
}

function getMyCowork($iUID){
	global $db;
	return $db->getAll("select distinct user_id, user_name, user_first,
						user_last, user_email, user_phone1, user_phone2 
						from users
						where user_id in
							(select assign_uid
							from assignments
							where assign_eid in " .
									"(select assign_eid " .
									"from assignments " .
									"where assign_uid=$iUID) " .
							"and assign_uid!=$iUID) " .
							"and user_inactive <> 1 " .
						"order by user_last")	;
}

function sanitizeInput($badVal){
	global $db;
	$badVal = $db->quoteSmart(trim($badVal));
	//$badVal = strtr($badVal, array('_' => '\_', '%' => '\%'));
	return $badVal;
}

function getUserTypes($iType){
	global $db;
	return $db->getAll("select type_id, type_name from types where type_id <= $iType");
}

function getSuperDetails($iFilter=1){
	global $db;
	switch ($iFilter){
		default: 	$sWhere = "and user_inactive <> 1 ";
					break;
		case 0:		$sWhere = "";
					break;
		case 2:		$sWhere = "and user_inactive = 1 ";
					break;
	}
	return $db->getAll("select user_id, user_name, user_first, user_last, user_email, user_phone1, user_phone2 from users
		where user_type>1 $sWhere" .
		"order by user_last");
}

function getSupervisors(){
	global $db;
	return $db->getAll('select user_id, user_first, user_last from users where user_type>1 and user_inactive <> 1 order by user_last');
}

function doHeader($sTitle, $sTA='', $sOnLoad=''){
	/*
	 * Updated to draw from the tinymce 
	 */
	 
	global $docroot;
	global $tinymce;
?>
<html>
<head>
	<title>MPAC ESS | <? print $sTitle ?></title>	
    <link href="global.css" rel="stylesheet" type="text/css">
    <? 	// the following is for our fancy wysiwyg editor 
    		// only show it if we have an element passed in
    	if ($sTA){
    ?>
		<script language="javascript" type="text/javascript" src="<?=$tinymce?>/tiny_mce_gzip.php"></script>
		<script language="javascript" type="text/javascript">
		tinyMCE.init({
			theme : "advanced",
			mode : "textareas",
			editor_selector : "editor",
			plugins : "table,save,advhr,advimage,advlink,emotions,iespell,insertdatetime,preview,zoom,flash,searchreplace,print,contextmenu,paste,directionality,fullscreen",
			theme_advanced_buttons1_add_before : "save,newdocument,separator",
			theme_advanced_buttons1_add : "fontselect,fontsizeselect",
			theme_advanced_buttons2_add : "separator,insertdate,inserttime,preview,separator,forecolor,backcolor",
			theme_advanced_buttons2_add_before: "cut,copy,paste,pastetext,pasteword,separator,search,replace,separator",
			theme_advanced_buttons3_add_before : "tablecontrols,separator",
			theme_advanced_buttons3_add : "emotions,iespell,flash,advhr,separator,print,separator,ltr,rtl,separator,fullscreen",
			theme_advanced_toolbar_location : "top",
			theme_advanced_toolbar_align : "left",
			theme_advanced_path_location : "bottom",
			content_css : "example_data/example_word.css",
		    plugin_insertdate_dateFormat : "%Y-%m-%d",
		    plugin_insertdate_timeFormat : "%H:%M:%S",
			extended_valid_elements : "img[class|src|border=0|alt|title|hspace|vspace|width|height|align|onmouseover|onmouseout|name],hr[class|width|size|noshade],font[face|size|color|style],span[class|align|style]",
			external_link_list_url : "example_data/example_link_list.js",
			external_image_list_url : "example_data/example_image_list.js",
			flash_external_list_url : "example_data/example_flash_list.js",
			file_browser_callback : "mcFileManager.filebrowserCallBack",
			paste_auto_cleanup_on_paste : true,
			paste_convert_headers_to_strong : true
		});
		</script>
	<? // end of fancy wysiwyg editor
    	}
    	?>
</head>
<body>
<img src="images/header.gif" width="700" height="100">
<br>

<?
if (isset($_SESSION['USERNAME'])) { ?>
<table width="700" border="0" cellpadding="0" cellspacing="0">
<tr class="footer">
	<td align="left">Welcome back, <?= $_SESSION['USERNAME'] ?>! <a href="logout.php">Log out?</a></td>
	<td align="right"><? print (date("l, F jS, Y") . ' at ' . date("g:i a")) ?></td>
</tr>
</table>
<? } ?>
<table width="700" border="0" cellpadding="0" cellspacing="0">
  <tr align="left" valign="top">
  	<? if (isset($_SESSION['USERNAME'])) { //we don't want the links to show if not logged in ?>
    <td class="nav" width="150"><? doNav() ?></td>
	<? } ?>
    <td width="550">
<? 
}

function doNav(){
?>
		<a href="mySchedule.php">My Schedule</a><p>
		<a href="myAvail.php">My Availability</a><p>
		<a href="myDayOff.php">My Days Off</a><p>
		<? // special links for supers
		if ($_SESSION['USERTYPE'] > 1) {
		?>
	    <a href="myEmps.php">My Employees</a><p>
	    <a href="assignEmps.php">Manage Schedules</a><p>
	    <a href="newUser.php">Add New Employee</a><p>
		<a href="viewSupers.php">Show Supervisors</a><p>
		<? } else { // employee only links?>
	    <a href="mySupers.php">My Supervisors</a><p>
	    <a href="myCoworkers.php">My Coworkers</a><p>
		<? } ?>
		<a href="emailEmps.php">Email Employees</a><p>
	    <a href="myInfo.php">My Info</a><p>
	    <a href="icalInfo.php">iCal Feed</a><p>
<?
}

function doFooter(){
?>
	</td>
  </tr>
  <tr><td colspan="2" class="footer">&copy;2004, 2005, 2006 Andrew Trommer</td></tr>
  <tr><td colspan="2" class="footer"><a href="http://sourceforge.net/tracker/?func=add&amp;group_id=126499&amp;atid=705914" target="_blank" title="Submit a bug">
  Submit a bug</td></tr>
  <tr><td colspan="2" class="footer">This software is distributed under the <a href="http://www.gnu.org/licenses/gpl.txt" target='_blank' title="View GPL">GPL</a></td></tr>
</table>
</body>
</html>
<? 
}
?>
