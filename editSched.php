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
// $Id: editSched.php,v 1.17 2006/02/03 20:24:34 atrommer Exp $
checkUser($_SESSION['USERTYPE'], 2);
// get the area attributes
$oAreaDetails = getAreas($_SESSION['USERID'],$_REQUEST['area']);

// make sure a row is returned, if not, user is being bad
if (!count($oAreaDetails)){ // no areas, tried to circumvent sec, yell at them
	accessDenied("You tried to access an area you aren't assigned to!");
} elseif (!$_REQUEST['area']){
	// force people through assignEmps
	accessDenied("Please choose an area to edit first through Manage Schedules");
}

if ($_POST['confirmDelete']){
	deleteEvent($_POST['event']);
	redirect("editSched.php?area=".$_POST['area']);	
}

function deleteConfirm($iArea, $iEvent){
	$aEventDetails = getEventDetails($iEvent);
?>
	<form id="frmDelete" name="frmDelete" method="post" action="<?=$_SERVER['PHP_SELF'] ?>">
		<input type="hidden" name="area" value="<?=$iArea ?>">
		<input type="hidden" name="event" value="<?=$iEvent ?>">
		<input type="hidden" name="confirmDelete" value="1">
		<input type="submit" name="delete" value="Click here to delete <?=$aEventDetails->event_name ?>" >
	</form>
<?
}

// now that we're clean, get the schedule for the area
// pass it the selected date from the drop down
// method will default to current day forward if !postback
$oAreaSched = getAreaSched($_REQUEST['area'], $_REQUEST['filter']);

doHeader("Editing Schedule for ".$oAreaDetails[0]->area_name);
if ($_REQUEST['del']){
	deleteConfirm($_REQUEST['area'], $_REQUEST['event']);
}
?><span class="contactInfo"><a href="printSched.php?area=<?=$_REQUEST['area'] ?>" target="_blank">Printable Schedule</a> |
<? if ($_REQUEST['filter']!=1){?>
<a href="editSched.php?area=<?=$_REQUEST['area'] ?>&amp;filter=1">Filter for current month</a>
<? } else { print "Filtering for current month";}?> |
<? if ($_REQUEST['filter']){?>
<a href="editSched.php?area=<?=$_REQUEST['area'] ?>">Filter for today forward</a>
<? } else { print "Filtering for today forward";}?> | 
<? if ($_REQUEST['filter']!=2){?>
<a href="editSched.php?area=<?=$_REQUEST['area'] ?>&amp;filter=2">Show all events</a>
<? } else { print "Showing all events";}?></span>
<?

// show the add event dialog
// here the user selects a datetime, and names the event
// and then we'll pull entries that match
?>
<table width="100%" cellpadding="2" cellspacing="0">
<tr>
<? // and the add form goes to the right 
?>
<td valign="top">
<form name="frmAddEvent" action="editSchedDetails.php" method="post">
	<input type="hidden" name="isSubmit">
	<input type="hidden" name="area" value="<?=$_REQUEST[area]?>">
	<strong>Add a new event:</strong>
	<table cellpadding="0" cellspacing="0" border="0">
	<tr>
		<td>Event Name:</td>
		<td><input type="text" name="tbName"></td>
	</tr>
	<tr class=evenRow>
		<td>Event Date (M/D/Y):</td>
		<td>
		<? // draw the ddowns for dmy hm ?>
		<select name="ddMonth">
		<? drawMonthDD(); ?>
		</select>
		<select name="ddDay">
		<? drawDayDD(); ?>
		</select>
		<select name="ddYear">
		<? drawYearDD(); ?>
		</select>
		</td>
	</tr>
	<tr>
		<td>Start Time:</td>
		<td>
		<select name="ddStartTime">
		<? drawTimeDD('18:00'); ?>
		</select>
		</td>
	</tr>
	<tr class=evenRow><td>End Time:</td>
		<td>
			<select name="ddEndTime">
			<? drawTimeDD('22:00'); ?>
			</select>
		</td></tr>
	<tr><td colspan="2" align="center"><input type="submit" value="Next >" name="submit"></td></tr>
	</table>
</form>
</td>
</tr>
<tr>
<td>
Below are the events for <?=$oAreaDetails[0]->area_name?>.
<br>An <b>*</b> denotes a schedule override.
<?
if (!count($oAreaSched)){ // let user know we have no rows
	print "<br><i>You haven't created a schedule for this area yet.</i>";
} ?>
<br>Total events for this view: <strong><?=count($oAreaSched) ?></strong><br>
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
			<td><a href="editNotes.php?event=<?=$Sched->event_id ?>&amp;area=<?=$_REQUEST['area'] ?>">Edit Notes?</a>
			<a href="editEvent.php?event=<?=$Sched->event_id ?>">Edit Event?</a>
			<a href="editSched.php?del=1&amp;event=<?=$Sched->event_id?>&amp;area=<?=$_REQUEST['area']?>">Delete?</a></td>
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
			  		$sDay = date("w", strtotime($Sched->event_date));
			  		$oMatch = checkEmpConflict($Sched->event_start , $Sched->event_end , $sDay , $Emp->user_id , $Sched->event_date);
			  		$bRow = !$bRow;
			  		?>
			  		<tr<? if ($bRow){ print " class=evenRow";}?>>
			  			<td colspan=2>
			  			<? if (!count($oMatch)){ ?>
			  				*
			  			<? } ?>
			  			<a href="viewSched.php?user=<?=$Emp->user_id?>&amp;doPop=1" target=_blank title="Click for availibility">
			  			<?=$Emp->user_first . ' ' . $Emp->user_last ?></a>
			  			</td>
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
<? doFooter(); ?>