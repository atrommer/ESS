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
// $Id: editEvent.php,v 1.4 2005/10/30 22:37:19 atrommer Exp $
checkUser($_SESSION['USERTYPE'], 2);
if (!$_REQUEST['event']){
	// throw an error, we need an event to edit
	accessDenied("Please select an event to edit first!");
}
$oEvent = getEventDetails($_REQUEST['event']);
doHeader("Editing Event");
?>
<form name="frmAddEvent" action="editSchedDetails.php" method="post">
	<input type="hidden" name="isSubmit">
	<input type="hidden" name="area" value="<?=$oEvent->event_area_id?>">
	<input type="hidden" name="event_id" value="<?=$oEvent->event_id?>">
	<strong>Edit a new event:</strong>
	<table cellpadding="0" cellspacing="0" border="0">
	<tr>
		<td>Event Name:</td>
		<td><input type="text" name="tbName" value="<?=$oEvent->event_name?>"></td>
	</tr>
	<tr class=evenRow>
		<td>Event Date (M/D/Y):</td>
		<td>
		<? // draw the ddowns for dmy hm ?>
		<select name="ddMonth">
		<? drawMonthDD($oEvent->event_date); ?>
		</select>
		<select name="ddDay">
		<? drawDayDD($oEvent->event_date); ?>
		</select>
		<select name="ddYear">
		<? drawYearDD($oEvent->event_date); ?>
		</select>
		</td>
	</tr>
	<tr>
		<td>Start Time:</td>
		<td>
		<select name="ddStartTime">
		<? drawTimeDD(date("H:i", strtotime($oEvent->event_start))); // fixed format string 
		?>
		</select>
		</td>
	</tr>
	<tr class=evenRow><td>End Time:</td>
		<td>
			<select name="ddEndTime">
			<? drawTimeDD(date("H:i", strtotime($oEvent->event_end))); // fixed format string
			?>
			</select>
		</td></tr>
	<tr><td colspan="2" align="center"><input type="submit" value="Next >" name="submit"></td></tr>
	</table>
</form>
<? doFooter(); ?>
