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
// $Id: editNotes.php,v 1.9 2005/10/30 22:37:19 atrommer Exp $
checkUser($_SESSION['USERTYPE'], 2);
// check for postback
if ($_POST['isPostback']){
	updateNotes($_POST['hdEvent'], sanitizeInput(($_POST['taComments'])));
	redirect('editSched.php?area='.$_POST['area']);
}

if (isset($_REQUEST['event'])){ 
	$oEvent = getEventDetails($_REQUEST['event']);
	// if we don't have a month set, pull it from area
	if (strlen($oEvent->event_comments)){
		$sNotes = $oEvent->event_comments;	
	} else {
		$sNotes = getAreaTempl($_REQUEST['area']);
	}
} else {
	accessDenied("Please choose an event to edit first using Manage Schedules");
}
doHeader("Editing notes for ".$oEvent->event_name, 'taComments');
?>

		<b><?=$oEvent->event_name?></b>
		<br>
		  <span class="contactInfo"><?= date("l, F jS, Y", strtotime($oEvent->event_date))?> 
		  (<?= date("g:i a", strtotime($oEvent->event_start)) ?> 
		  - 
		  <?= date("g:i a", strtotime($oEvent->event_end))?>)</span>
		<p>
		<form name="frmEditNotes" action="editNotes.php" method="post">
       		<input type="hidden" name="hdEvent" value="<?=$_REQUEST['event']?>">
       		<input type="hidden" name="area" value="<?=$_REQUEST['area'] ?>">
			<input type="hidden" name="isPostback" value="1">
			<textarea id="taComments" name="taComments" cols="100" rows="25" class="editor"><?
			print $sNotes;
			?></textarea> 
			<p align="center"><input type="submit" name="btSubmit" value="Save Changes?"></p>
		</form>  
<? doFooter(); ?>
