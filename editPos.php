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
// $Id: editPos.php,v 1.4 2006/02/03 20:24:34 atrommer Exp $
checkUser($_SESSION['USERTYPE'], 2);

// check for postback and handle a delete
if ($_POST['isPostback']){
	// do updates
	addPos($_POST, $_SESSION['USERID']);
	redirect('editPos.php?area=' . $_POST['area']);	
}

if ($_POST['confirmDelete']){
	delPos($_POST['pos']);
	redirect('editPos.php?area=' . $_POST['area']);	
}

// if request is empty, make user select area first
if (!$_REQUEST['area']){ accessDenied("Please go through Manage Schedules to edit positions!");}

// grab area details
$oAreaDetails = getAreas($_SESSION['USERID'],$_REQUEST['area']);

// kick user out if details don't exist
if (!$oAreaDetails){ accessDenied("You do not have access to this function!");}

// now that we are clean, grab the pos
$oAreaPos = getAreaPos($_REQUEST['area']);

// prompts user for del confirm
function deleteConfirm($iArea, $iPos){
	$oPosDetails = getPosDetails($iPos);
?>
	Warning! <b>Deleting</b> a position will orphan any assignments using this position.
	Do this only as a last resort!<br>
	<form id="frmDelete" name="frmDelete" method="post" action="<?=$_SERVER['PHP_SELF'] ?>">
		<input type="hidden" name="area" value="<?=$iArea ?>">
		<input type="hidden" name="pos" value="<?=$iPos ?>">
		<input type="hidden" name="confirmDelete" value="1">
		<input type="submit" name="delete" value="Click here to delete the <?=$oPosDetails->pos_name ?> position">
	</form>
<?
}

doHeader("Editing Positions for ".$oAreaDetails[0]->area_name);
// catch delete request and prompt
if ($_REQUEST['del']){
	deleteConfirm($_REQUEST['area'], $_REQUEST['pos']);
}

?>
This page allows you to create, edit, and delete positions for the <b><?=$oAreaDetails[0]->area_name?></b> area.
<br>Simply make the changes and additions that you want and click <b>Update</b>.
<br>

<form name="frmPos" action="editPos.php" method="post">
	<input type="hidden" name="hdArea" value="<?=$_REQUEST['area']?>">
	<input type="hidden" name="isPostback" value="1">
<? foreach($oAreaPos as $Pos){ ?>
	<br>
	<hr align="left">
	<table width="100%" border="0" cellpadding="2" class="contactInfo">
		  <tr>
			<td class="contactInfoName"><input type="text" name="tbName<?=$Pos->pos_id?>" value="<?=$Pos->pos_name?>"></td>
			<td>
				<a href="editPos.php?pos=<?=$Pos->pos_id ?>&amp;area=<?=$_REQUEST['area'] ?>&amp;del=1">Delete Position?</a>
			</td>
		  </tr>
		  <tr>
			<td colspan=2><textarea name="taDesc<?=$Pos->pos_id?>" cols="30" rows="4"><?
				print($Pos->pos_desc);
			?></textarea>
			</td>
		</tr>
	</table>
<? } ?>
<br>
Add New Position:
<table width="100%" border="0" cellpadding="0" class="contactInfo">
	<tr class="contactInfoName">
		<td>Position Name:</td><td><input type="text" name="tbNName" maxlenght="20"></td>
	</tr>
	<tr>
		<td>Description:</td><td><textarea name="taNDesc" cols="30" rows="4"></textarea></td>
	</tr>
</table>
<center><input type="submit" value="Update?" name="submit"></center>
</form>
  
<? doFooter(); ?>