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
// $Id: emailEmps.php,v 1.3 2005/10/30 22:37:19 atrommer Exp $
checkUser($_SESSION['USERTYPE'], 1);

// check for postback
if ($_POST['isPostback']){
	emailEmps($_POST, $_SESSION['USERID']);	
	redirect();
}
// grab based on usertype
// if >= super, get owned emps and all supers
// otherwise, only coworkers on parents
if ($_SESSION['USERTYPE']>1){ // get super case
	$oMyEmps = getMyEmployees($_SESSION['USERID']);
	$oSupers = getSupervisors();
} else {
	$oMyEmps = getMyCowork($_SESSION['USERID']);
	$oSupers = getMySupers($_SESSION['USERID']);
}
doHeader("Bulk Email");
?>
Please enter the subject, body, and select all or some of the employees you would like to email.
<br/>
<form name="frmEmail" action="emailEmps.php" method="post">
<input type="hidden" name="isPostback" value="1">
<table border="0" cellpadding="1" cellspacing="0" width="100%" class="contactInfo">
<tr>
	<td colspan=4 class="contactInfoName">Employees:</td>
</tr>
<?  // first show the emps
	// we want to only show 4 emps a line, so we'll hit another <tr> on 4
	$iCount = 0;
	print "<tr>\n";
	foreach ($oMyEmps as $Emp){
		$iCount++;
	?>
		<td><input type="checkbox" name="cbMail[]" value="<?=$Emp->user_id ?>"> <?=$Emp->user_first ?> <?=$Emp->user_last?></td>
	<?
		if ($iCount == 4){
			print "</tr>\n<tr>";	
			$iCount = 0;
		}
	}
	print "</tr>";
?>
<tr>
	<td colspan=4 class="contactInfoName">Supervisors:</td>
</tr>
<?  // now show the supers
	// we want to only show 4 emps a line, so we'll hit another <tr> on 4
	$iCount = 0;
	print "<tr>\n";
	foreach ($oSupers as $Super){
		$iCount++;
	?>
		<td><input type="checkbox" name="cbMail[]" value="<?=$Super->user_id ?>"> <?=$Super->user_first ?> <?=$Super->user_last?></td>
	<?
		if ($iCount == 4){
			print "</tr>\n<tr>";	
			$iCount = 0;
		}
	}
	print "</tr>";
?>
</table>
<br>
<table border="0" cellpadding="1" cellspacing="0" width="100%">
<tr class="evenRow">
	<td>Subject:</td>
	<td><input type="text" name="tbSubj"></td>
</tr>
<tr>
	<td>Body:</td>
	<td><textarea name="taBody" rows="5" cols="50"></textarea></td>
</tr>
<tr><td colspan="2" align="center"><input type="submit" name="submit" value="Send Mail?"></td></tr>
</table>

</form>
<? doFooter(); ?>