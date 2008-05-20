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
// $Id: mySchedule.php,v 1.14 2006/02/03 20:24:34 atrommer Exp $
checkUser($_SESSION['USERTYPE'], 1);
doHeader("My Schedule");
// get the user's schedule
if (isset($_REQUEST['getDesc'])){ // display event description
	$oEvent = getEventDetails($_REQUEST['getDesc']);
	?>
		<b><?= $oEvent->event_name ?></b>
		<br>
		  <span class="contactInfo"><?= date("l, F jS, Y", strtotime($oEvent->event_date))?> 
		  (<?= date("g:i a", strtotime($oEvent->event_start)) ?> 
		  - 
		  <?= date("g:i a", strtotime($oEvent->event_end))?>)</span>
		<p>  
          <?=$oEvent->event_comments?>
          <?
} else { // just display the schedule

	$oSched = getMySched($_SESSION['USERID'], $_REQUEST['filter']);
	// print out the results
	if (count($oSched)){ //display rows 
		?><span class="contactInfo"><a href="printSched.php" target="_blank">Printable Schedule</a> | 
		<? if ($_REQUEST['filter']!=1){?>
		<a href="mySchedule.php?filter=1">Filter for current month</a>
		<? } else { print "Filtering for current month";}?> |
		<? if ($_REQUEST['filter']){?>
		<a href="mySchedule.php">Filter for today forward</a>
		<? } else { print "Filtering for today forward";}?> | 
		<? if ($_REQUEST['filter']!=2){?>
		<a href="mySchedule.php?filter=2">Show all of my events</a>
		<? } else { print "Showing all events";}?>
	
		</span>
		<br>An <b>*</b> denotes schedule override.
		<?
		// loop through all events w/filters
		foreach ($oSched as $Sched){
			$oAreaPos = getAreaPos($Sched->event_area_id);
			?>
			</p>
			<hr align="left">
			<?=$Sched->event_name?>
			<table width="100%" border="0" cellpadding="2" class="contactInfo">
				  <tr>
					<td class="contactInfoName"><? print(date("D, n/d", strtotime($Sched->event_date))) ?></td>
					<td><a href="mySchedule.php?getDesc=<?=$Sched->event_id ?>">View Description?</a>
					<? if ($_SESSION['USERTYPE']>1){ // show the edit link
					?><a href="editNotes.php?area=<?=$Sched->event_area_id ?>&amp;event=<?=$Sched->event_id ?>">Edit Notes?</a><?
					} ?>
					</td>
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
				  	$sDay = date("w", strtotime($Sched->event_date));
				  	?>
				  	<td><table border="0" cellpadding="2" class="contactInfo">
				  	<tr><td colspan=2><strong><?=$Pos->pos_name ?></strong></td></tr><?
				  	$bRow = true;
				  	$oEventEmps = getEventEmps($Sched->event_id, $Pos->pos_id);
				  	if (count($oEventEmps)){ // we have emps, display them
					  	foreach($oEventEmps as $Emp){
					  		$bRow = !$bRow;
					  		print "<tr><td>";
					  		if ($_SESSION['USERTYPE']>1){ // grab override data if super or higher
				  				$oMatch = checkEmpConflict($Sched->event_start , $Sched->event_end , $sDay , $Emp->user_id , $Sched->event_date);
				  				
				  				if (!count($oMatch)){ ?>
			  						* <? } ?>
			  					<a href="viewSched.php?user=<?=$Emp->user_id?>&amp;doPop=1" target=_blank title="Click for availibility">
				  				<?=$Emp->user_first . ' ' . $Emp->user_last ?></a>
				  			<?	
				  			} else {
				  				print $Emp->user_first . ' ' . $Emp->user_last;
					  		}

				  			print "</td></tr>"; 
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
		} // end of event foreach
	} else { // tell the user they have free time
		?>You are currently not assigned any shifts.<?
	}
}

doFooter(); ?>
