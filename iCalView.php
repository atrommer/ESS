<?php require_once('common.php');
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
require_once('classes/class.ical.inc.php');
// $Id: iCalView.php,v 1.3 2005/10/30 22:37:19 atrommer Exp $

// if we have a uid, grab their schedule
// if we have aid, grab area schedule
// otherwise, do nothing

if ($_REQUEST['uid']){
	$oSched = getMySched($_REQUEST['uid'], 2);
} elseif ($_REQUEST['aid']){
	$oSched = getAreaSched($_REQUEST['aid'],2);
} else {
	accessDenied("Invalid user or area!");	
}

$iCal = (object) new iCal('', 0); // (ProgrammID, Method [1 = Publish | 0 = Request])

$categories = array('Work','Party');
// main loop for the events
foreach ($oSched as $Sched){
	$aAssign = array();
	$oEventEmps = getEventEmps($Sched->event_id);
	// add all event emps to the attendees
	foreach($oEventEmps as $Emp){
		$aTemp = array($Emp->user_first .' '.$Emp->user_last => $Emp->user_email.",1");
		$aAssign = array_merge($aAssign, $aTemp);	
	}
	
	$iCal->addEvent(
					'', // Organizer
					strtotime($Sched->event_date .' '. $Sched->event_start), // Start Time (timestamp; for an allday event the startdate has to start at YYYY-mm-dd 00:00:00)
					strtotime($Sched->event_date .' '. $Sched->event_end), // End Time (write 'allday' for an allday event instead of a timestamp)
					$Sched->area_name, // Location
					0, // Transparancy (0 = OPAQUE | 1 = TRANSPARENT)
					$categories, // Array with Strings
					'Log into ESS for more details!', // Description
					$Sched->event_name, // Title
					1, // Class (0 = PRIVATE | 1 = PUBLIC | 2 = CONFIDENTIAL)
					$aAssign, // Array (key = attendee name, value = e-mail, second value = role of the attendee [0 = CHAIR | 1 = REQ | 2 = OPT | 3 =NON])
					5, // Priority = 0-9
					0, // frequency: 0 = once, secoundly - yearly = 1-7
					1, // recurrency end: ('' = forever | integer = number of times | timestring = explicit date)
					1, // Interval for frequency (every 2,3,4 weeks...)
					'', // Array with the number of the days the event accures (example: array(0,1,5) = Sunday, Monday, Friday
					0, // Startday of the Week ( 0 = Sunday - 6 = Saturday)
					'', // exeption dates: Array with timestamps of dates that should not be includes in the recurring event
					'',  // Sets the time in minutes an alarm appears before the event in the programm. no alarm if empty string or 0
					1, // Status of the event (0 = TENTATIVE, 1 = CONFIRMED, 2 = CANCELLED)
					'http://'.$host . $docroot, // optional URL for that event
					'en', // Language of the Strings
	                '' // Optional UID for this event
				   );
} // end of main loop
			   
$iCal->outputFile('ics');
?>