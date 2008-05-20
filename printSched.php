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
// $Id: printSched.php,v 1.5 2005/10/30 22:37:19 atrommer Exp $
checkUser($_SESSION['USERTYPE'], 1);
if ($_REQUEST['area']){
	//format for area schedule
	printArea($_REQUEST['area']);
} else {
	printUser();
}

function printArea($iArea){
	// displays the area schedule
	$oAreaDetails = getAreas($_SESSION['USERID'],$iArea);

	// make sure a row is returned, if not, user is being bad
	if (!count($oAreaDetails)){ // no areas, tried to circumvent sec, yell at them
		accessDenied("You tried to access an area you aren't assigned to!");
	}
	
	$oAreaSched = getAreaSched($_REQUEST['area']);
	if (!count($oAreaSched)){ // let user know we have no rows
		print "You haven't created a schedule for this area yet.";
	}
	?>
	<html>
	<head>
	<title>Viewing Schedule for <?=$oAreaDetails[0]->area_name ?></title>
	<link href="global.css" rel="stylesheet" type="text/css">
	</head>
	
	<body>
	<table width="100%" cellpadding="2" cellspacing="0">
		<tr>
		<td width="50%">
		Below are the events for <?=$oAreaDetails[0]->area_name?>.
		<?
		// Area events go to the left
		foreach ($oAreaSched as $Sched){
			// get area positions
			$oAreaPos = getAreaPos($_REQUEST['area']);
		?>
			<br>
			<hr align="left">
			<?=$Sched->event_name?>
			<table width="100%" border="0" cellpadding="2" class="contactInfo">
				  <tr>
					<td class="contactInfoName"><? print(date("D, n/d/y", strtotime($Sched->event_date))) ?></td>
					</tr>
				  <tr>
					<td>Start: <?= date("g:i a", strtotime($Sched->event_start))?>
					<div align="left"></div></td>
					<td>End: <?= date("g:i a", strtotime($Sched->event_end)) ?></td>
				  </tr>
				  <tr valign="top">
				  <? // now we go through each pos and list the scheduled emps
				  foreach($oAreaPos as $Pos){
				  	?>
				  	<td><table border="0" cellpadding="2" class="contactInfo">
				  	<tr><td colspan=2><strong><?=$Pos->pos_name ?></strong></td></tr><?
				  	$bRow = true;
				  	$oEventEmps = getEventEmps($Sched->event_id, $Pos->pos_id);
				  	if (count($oEventEmps)){ // we have emps, display them
					  	foreach($oEventEmps as $Emp){
					  		$bRow = !$bRow;
					  		?>
					  		<tr<? if ($bRow){ print " class=evenRow";}?>>
					  			<td colspan=2><?=$Emp->user_first . ' ' . $Emp->user_last ?></td>
					  		</tr>
					  		<?
					  	}
				  	} else { //we don't, display msg
				  		print "<tr><td colspan=2><i>No employees scheduled yet</i></td></tr>";
				  	}
				  	?></table></td><?
				  } 
				  ?>
				  </tr>
			</table>
		<? } ?>
		</td>
		</tr>
	</table>
	<?
	
}

function printUser(){
	// displays a users schedule in printable form
	$oSched = getMySched($_SESSION['USERID']);
	// print out the results
	?>
	<html>
	<head>
	<title>Viewing Your Schedule</title>
	<link href="global.css" rel="stylesheet" type="text/css">
	</head>
	
	<body>
	<table width="100%" cellpadding="2" cellspacing="0">
	<tr>
	<td>
	<?
	if (count($oSched)){ //display rows 
		foreach ($oSched as $Sched){
			$oAreaPos = getAreaPos($Sched->event_area_id);
			?>
			</p>
			<hr align="left">
			<?=$Sched->event_name?>
			<br>
			<table width="100%" border="0" cellpadding="2" class="contactInfo">
				  <tr>
					<td class="contactInfoName"><? print(date("D, n/d", strtotime($Sched->event_date))) ?></td>
				</tr>
				  <tr>
					<td width="31%">Start: <?= date("g:i a", strtotime($Sched->event_start))?>
					<div align="left"></div></td>
					<td width="69%">End: <?= date("g:i a", strtotime($Sched->event_end)) ?></td>
				  </tr>
				  <tr>
					<td>Position: <?=$Sched->pos_name ?></td>
					<td>Location: <?=$Sched->area_name ?></td>
				  </tr>
				  <tr valign="top">
					<? // now we go through each pos and list the scheduled emps
				  foreach($oAreaPos as $Pos){
				  	?>
				  	<td><table border="0" cellpadding="2" class="contactInfo">
				  	<tr><td colspan=2><strong><?=$Pos->pos_name ?></strong></td></tr><?
				  	$bRow = true;
				  	$oEventEmps = getEventEmps($Sched->event_id, $Pos->pos_id);
				  	if (count($oEventEmps)){ // we have emps, display them
					  	foreach($oEventEmps as $Emp){
					  		$bRow = !$bRow;
					  		?>
					  		<tr<? if ($bRow){ print " class=evenRow";}?>>
					  			<td colspan=2><?=$Emp->user_first . ' ' . $Emp->user_last ?></td>
					  		</tr>
					  		<?
					  	}
				  	} else { //we don't, display msg
				  		print "<tr><td colspan=2><i>No employees scheduled yet</i></td></tr>";
				  	}
				  	?></table></td><?
				  } 
				  ?>
				  </tr>
			</table>
			<?
		}
	} else { // tell the user they have free time
		?>You are currently not assigned any shifts.<?
	}
	?>
	</td></tr></table>
	<?
}

doFooter();
?>