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
// $Id: editSchedDetails.php,v 1.17 2006/02/03 20:24:34 atrommer Exp $
checkUser($_SESSION['USERTYPE'], 2);

if (!$_POST){
	accessDenied("Please return to Manage Schedules to edit a schedule.");
}

if ($_POST['isPostBack']){
	$iEID = schedEvent($_POST, $_POST['event_id']);
	redirect("editNotes.php?event=".$iEID ."&area=".$_POST['area']);
}

// now we cat the dates together
$dDate = $_POST['ddYear'] .'-'. $_POST['ddMonth'] .'-'. $_POST['ddDay'];
$dStart = date("H:i",strtotime($_POST['ddStartTime']));
$dEnd = date("H:i",strtotime($_POST['ddEndTime']));
$sDay = date("w", strtotime($dDate));
$dStartFull = $_POST['ddYear'] .'-'. $_POST['ddMonth'] .'-'. $_POST['ddDay'].' '.$dStart;

// get the area attributes
$oAreaDetails = getAreas($_SESSION['USERID'],$_REQUEST['area']);
$oAreaPos = getAreaPos($_REQUEST[area]);

// make sure a row is returned, if not, user is being bad
if (!count($oAreaDetails)){ // no areas, tried to circumvent sec, yell at them
	accessDenied("You tried to access an area you aren't assigned to!");
}
$oMyEmps = getMyEmployees($_SESSION['USERID']);

doHeader("Editing " .$_POST['tbName']);
?>
The following list shows all of your employees.  Green means they are available, red means they aren't.
<br>Use the radio buttons to assign your employees and press "Assign" to save the event and assignments.
<br>
<span class="contactInfo"><?= date("l, F jS, Y", strtotime($dDate))?> 
		  (<?= date("g:i a", strtotime($dStart)) ?> 
		  - 
		  <?= date("g:i a", strtotime($dEnd)) ?>)</span>
 <p>
<form name="frmEditSched" action="editSchedDetails.php" method="post">
<input type="hidden" name="isPostBack" value="1">
<input type="hidden" name="tbName" value="<?=$_POST['tbName']?>">
<input type="hidden" name="dDate" value="<?=$dDate?>">
<input type="hidden" name="dStartTime" value="<?=$dStart?>">
<input type="hidden" name="dEndTime" value="<?=$dEnd?>">
<input type="hidden" name="area" value="<?=$_POST['area']?>">
<input type="hidden" name="event_id" value="<?=$_POST['event_id'] ?>">
<table cellpadding="0" cellspacing="0" border="0" width=100% align="center">
<tr class="contactInfoName"><td width=50%>Name</td><td colspan="<?=count($oAreaPos)+1 ?>" align=center>Assignments</td></tr>
<tr class="contactInfoName"><td>&nbsp;</td><td>None</td>
<? foreach($oAreaPos as $Pos){
	print "<td align=center>$Pos->pos_name</td>\n";	
}
?>
</tr>
<?
foreach ($oMyEmps as $Emp){
	//check avail
	print "<tr ";
	$oMatch = checkEmpConflict($dStart, $dEnd, $sDay, $Emp->user_id, $dDate);
	if (count($oMatch)){
		print "class=\"empAvail\"";
	} else { print "class=\"empNotAvail\""; }
	print ">\n	<td>\n\t\t<a href=\"viewSched.php?user=$Emp->user_id&amp;doPop=1\" target=_blank title=\"Click for availibility\">$Emp->user_first $Emp->user_last</a>\n\t</td>\n";
	// now do the positions
	print "	<td align=center><input type=radio name=rad$Emp->user_id value=0 checked></td>\n";
	$oAreaPos = getAreaPos($_REQUEST[area]);
	foreach ($oAreaPos as $Pos){
		// first, we check to see if the user is already assigned to the current pos
		// this only happens on an existing event, obviously
		$sChecked = null;
		if ($_POST['event_id']){
			if (checkEventPos($Emp->user_id, $_POST['event_id'], $Pos->pos_id)){ //then we have a match, set the radio to checked
				$sChecked = " checked";
			}	
		}
		print "	<td align=center><input type=radio name=rad$Emp->user_id value=$Pos->pos_id $sChecked></td>\n";
	}
	print "</tr>\n";
}
?>
<tr><td><a href="viewSched.php?user=<?=$_SESSION['USERID'] ?>&amp;doPop=1" target=_blank title="Click for availibility">Yourself</a></td>
<?
	print "<td align=center><input type=radio name=rad" .$_SESSION['USERID']. " value=0 checked></td>";
	$oAreaPos = getAreaPos($_REQUEST[area]);
	foreach ($oAreaPos as $Pos){
		print "<td align=center><input type=radio name=rad" .$_SESSION['USERID']. " value=$Pos->pos_id></td>\n";
	}
	print "</tr>";
?>
<tr class="evenRow" align="center"><td colspan="<?=count($oAreaPos)+2?>"><input type="checkbox" name= "chkMail" value="1"> Mail users?</td></tr>
<tr class="evenRow" align="center"><td colspan="<?=count($oAreaPos)+2?>"><input type="submit" value="Assign?"></td></tr>
</table>
</form>
<? doFooter(); ?>