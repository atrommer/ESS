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
// $Id: editArea.php,v 1.4 2005/10/30 22:37:19 atrommer Exp $
checkUser($_SESSION['USERTYPE'], 2);

/*
 * This page takes a post from the assignemps page
 * and allows a super to edit the details of the area.
 * It also allows them to call the editPos page for position edits
 */
// check for postback
if ($_POST['isPostback']){ // do the updates
	updateArea($_POST);
	redirect('assignEmps.php');
}

// first we get the area details
$oAreaDetails = getAreas($_SESSION['USERID'],$_REQUEST['area']);

// now get the area template
$sATempl = getAreaTempl($_REQUEST['area']);

// next get the cur super's super list
if ($_SESSION['USERTYPE'] == 2){ // grab the supers child supers
	$oSupers = getChildSupers($_SESSION['USERID']);
} else {
	$oSupers = getSupervisors();
}

// get the area supers
$oAreaSuper = getAreaSuper($_REQUEST['area']);

// parse through the object into an array
foreach($oAreaSuper as $Super){
	$aSelectedSupers[] = $Super->as_uid;	
}
doHeader("Area Edit of ".$oAreaDetails[0]->area_name, 'taTempl');
?>
This page allows you to edit the selected area's name, description, and template.
The template is used when creating event notes.
<form name="frmArea" action="editArea.php" method="post">
	<input type=hidden name=hdArea value="<?=$_REQUEST['area']?>">
	<table width="100%"  border="0" cellspacing="0" cellpadding="0">
	  <tr>
	    <td colspan="2" valign="top"><strong>Edit Area</strong></td>
	  </tr>
	  <tr class="evenRow">
	    <td valign="top">Area Name</td>
	    <td><input name="tbName" type="text" id="tbName" size="10" maxlength="20" value="<?=$oAreaDetails[0]->area_name?>"></td>
	  </tr>
	  <tr>
	    <td valign="top">Area Description</td>
	    <td><textarea name="tbDesc" cols="30" rows="4" id="tbDesc"><?=$oAreaDetails[0]->area_desc?></textarea></td>
	  </tr>
	  <tr class="evenRow">
	    <td valign="top">Area Owners </td>
	    <td>
	    	<select name="msSupers[]" size="6" multiple id="msSupers[]">
		<? foreach ($oSupers as $Super) {
			if (in_array($Super->user_id,$aSelectedSupers)){
				print ("<option value=\"$Super->user_id\" selected>$Super->user_first $Super->user_last</option>\n");
			} else {
				print ("<option value=\"$Super->user_id\">$Super->user_first $Super->user_last</option>\n");
			}	
		
		} 
		if($_SESSION['USERTYPE'] < 3){ print ("<option value=\"".$_SESSION['USERID']."\" selected>Yourself</option>\n");}
			
		?>
	    </select>
	    </td>
	   </tr>
	   <tr>
	   <td>Template</td>
	   <td>
		<textarea id="taTempl" name="taTempl" cols="100" rows="25" class="editor"><?
			print $sATempl;
		?></textarea> 
	    <input type="hidden" name="isPostback" value="1">
	    </td>
	  </tr>
	  <tr><td colspan="2" align="center"><input name="btSubmit" type="submit" value="Save Edits?"></td></tr>
	</table>
	
</form>
<?
doFooter();
?>
