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
// $Id: assignEmps.php,v 1.6 2005/10/30 22:37:19 atrommer Exp $
checkUser($_SESSION['USERTYPE'], 2);
doHeader("Please choose an area");
// get the areas the user is assigned to
$oAreas = getAreas($_SESSION['USERID']);

// check for postback
if ($_POST['isPostback']){
	addArea($_POST);
	redirect("assignEmps.php");
}
?>
This is the first step in assigning an employee.  Please select the area that you wish to assign employees to.<br \>
<?
// display the areas assigned to the user and have them make their selections
if (count($oAreas)==0){ // they have no areas assigned to them
?>
<span class="contactInfoName">You currently have no areas assigned to you!</span>
<? }
foreach ($oAreas as $Area){
?>
<hr align="left">
	<table width="100%"  border="0" cellspacing="0" cellpadding="0" class="contactInfo">
  <tr>
    <td class="contactInfoName"><?=$Area->area_name?></td>
    <td align="right"><a href="editArea.php?area=<?=$Area->area_id?>">Edit Area?</a> | <a href="editPos.php?area=<?=$Area->area_id?>">Edit Positions?</a> | <a href="#">Delete Area?</a></td>
  </tr>
  <tr>
    <td colspan="2"><?=$Area->area_desc?></td>
  </tr>
  <tr>
  	<td colspan="2"><a href="editSched.php?area=<?=$Area->area_id?>">Manage This Schedule</a></td>
  </tr>
</table>

<? } 

if ($_SESSION['USERTYPE'] == 2){ // grab the supers child supers
	$oSupers = getChildSupers($_SESSION['USERID']);
} else {
	$oSupers = getSupervisors();
}
?>
<br><br>
<form name="frmAddArea" action="<?=$_SERVER['PHP_SELF'] ?>" method="post">
	<table width="100%"  border="0" cellspacing="0" cellpadding="0">
  <tr>
    <td colspan="2" valign="top"><strong>Add New Area</strong></td>
  </tr>
  <tr class="evenRow">
    <td valign="top">Area Name</td>
    <td><input name="tbName" type="text" id="tbName" size="10" maxlength="20" ></td>
  </tr>
  <tr>
    <td valign="top">Area Description</td>
    <td><textarea name="tbDesc" cols="30" rows="4" id="tbDesc"></textarea></td>
  </tr>
  <tr class="evenRow">
    <td valign="top">Area Owners </td>
    <td><select name="msSupers[]" size="4" multiple id="msSupers[]">
	<? foreach ($oSupers as $Super) {
			print ("<option value=\"$Super->user_id\">$Super->user_first $Super->user_last</option>\n");
	} 
	print ("<option value=\"".$_SESSION['USERID']."\" selected>Yourself</option>\n");
		
	?>
    </select><input type="hidden" name="isPostback" value="1"></td>
  </tr>
  <tr><td colspan="2" align="center"><input name="btSubmit" type="submit" value="Add Area?"></td></tr>
</table>

</form>
<? doFooter(); ?>