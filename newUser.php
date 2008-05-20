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
// $Id: newUser.php,v 1.4 2005/10/30 22:37:19 atrommer Exp $
checkUser($_SESSION['USERTYPE'], 2);
doHeader("Add new user");

if ($_POST['isPostback']) {
	// now we process the form
	$defaults = $_POST;
	
	// validation and trimming
	$_POST['tbUsername'] = trim($_POST['tbUsername']);
	$_POST['tbPass'] = trim($_POST['tbPass']);
	$_POST['tbFirst'] = trim($_POST['tbFirst']);
	$_POST['tbLast'] = trim($_POST['tbLast']);
	$_POST['tbEmail'] = trim($_POST['tbEmail']);
	
	if (strlen($_POST['tbUsername']) < 4){ 	$errors[] = "Your username must be more than 4 characters long"; }
	if (strlen($_POST['tbPass']) < 4){ 		$errors[] = "Your password must be more than 4 characters long"; }
	if (strlen($_POST['tbFirst']) == 0) { 	$errors[] = "You must enter a first name"; }
	if (strlen($_POST['tbLast']) == 0) { 	$errors[] = "You must enter a last name"; }
	if (! preg_match('/^[^@\s]+@([-a-z0-9]+\.)+[a-z]{2,}$/i', $_POST['tbEmail'])) { 
											$errors[] = "You must enter a valid email address"; }
	if (strlen(strval(intval($_POST['tbPhone1A'] . $_POST['tbPhone1B'] . $_POST['tbPhone1C']))) < 10) {
											$errors[] = "Please enter a valid primary phone number"; }
	if (strlen($_POST['tbPhone2A'] . $_POST['tbPhone2B'] . $_POST['tbPhone2C'])){
		if (strlen(strval(intval($_POST['tbPhone2A'] . $_POST['tbPhone2B'] . $_POST['tbPhone2C']))) < 10) {
											$errors[] = "Please enter a valid secondary phone number"; }
	}
	if ($_POST['tbPay'] != strval(floatval($_POST['tbPay']))) {
											$errors[] = "You must enter a valid hourly rate"; }
	
	
	if (!$errors){ // our input is clean, go ahead and submit
		$sInsertError = addUser($_POST);
	}
}




// get the list of supervisors
$aSupers = getSupervisors();

// get the user types < the current user
$aTypes = getUserTypes($_SESSION['USERTYPE']);


?>
<form action="<?=$_SERVER['PHP_SELF'] ?>" method="post" name="frmAddUser"><table width="100%"  border="0" cellpadding="0" cellspacing="0">
	
  <tr>
    <td colspan="2"><strong>Add New User </strong></td>
  </tr>
  <? if (isset($sInsertError)){ print "<tr><td colspan=2><span class=errorMsg>$sInsertError</span></td></tr>";} 
	if ($errors) { 
		print "<tr><td colspan=2 class=errorMsg><strong>Please correct the following errors:</strong><br><ul><li>";
		print implode('</li><li>', $errors);
		print "</li></td></tr>";
	}  
  ?>
  <tr class="evenRow">
    <td width="26%">Username </td>
    <td width="74%"><input name="tbUsername" type="text" id="tbUsername" size="10" maxlength="20" value="<?=$defaults['tbUsername']?>">
    </td>
  </tr>
  <tr>
    <td>User Password </td>
    <td><input name="tbPass" type="password" id="tbPass" size="10" maxlength="20"></td>
  </tr>
  <tr class="evenRow">
    <td>First Name </td>
    <td><input name="tbFirst" type="text" id="tbFirst" size="10" maxlength="20" value="<?=$defaults['tbFirst']?>"></td>
  </tr>
  <tr>
    <td>Last Name </td>
    <td><input name="tbLast" type="text" id="tbLast" size="10" maxlength="20" value="<?=$defaults['tbLast']?>"></td>
  </tr>
  <tr>
    <td>Email Address</td>
    <td><input name="tbEmail" type="text" id="tbEmail" size="20" maxlength="50" value="<?=$defaults['tbEmail']?>"></td>
  </tr>
  <tr class="evenRow">
    <td>Phone 1 </td>
    <td>
      (<input name="tbPhone1A" type="text" id="tbPhone1A" size="3" maxlength="3" value="<?=$defaults['tbPhone1A']?>">)
      
      <input name="tbPhone1B" type="text" id="tbPhone1B" size="3" maxlength="3" value="<?=$defaults['tbPhone1B']?>">
      -
      <input name="tbPhone1C" type="text" id="tbPhone1C" size="4" maxlength="4" value="<?=$defaults['tbPhone1C']?>"></td>
  </tr>
  <tr>
    <td>Phone 2 </td>
    <td>
      (<input name="tbPhone2A" type="text" id="tbPhone2A" size="3" maxlength="3" value="<?=$defaults['tbPhone2A']?>">)
      
      <input name="tbPhone2B" type="text" id="tbPhone2B" size="3" maxlength="3" value="<?=$defaults['tbPhone2B']?>">
      -
      <input name="tbPhone2C" type="text" id="tbPhone2C" size="4" maxlength="4" value="<?=$defaults['tbPhone2C']?>"></td>
  </tr>
  <tr class="evenRow">
    <td>Supervisors</td>
    <td><select name="msSupers[]" size="4" multiple id="msSupers">
	<? foreach ($aSupers as $Super) {
		if ($Super->user_id == $_SESSION['USERID']){
			print ("<option value=\"$Super->user_id\" selected>$Super->user_first $Super->user_last</option>");
		} else {
			print ("<option value=\"$Super->user_id\">$Super->user_first $Super->user_last</option>");
		}
	} ?>
    </select></td>
  </tr>
  <tr>
    <td>Account Type </td>
    <td><select name="ddType" id="ddType">
	<? foreach ($aTypes as $Type) {
		print ("<option value=\"$Type->type_id\">$Type->type_name</option>");
	} ?>
    </select></td>
  </tr>
  <tr class="evenRow">
    <td>Hourly Rate </td>
    <td>$
      <input name="tbPay" type="text" id="tbPay" size="6" maxlength="6" value="<?=$defaults['tbPay']?>"></td>
  </tr>
  <tr>
    <td><input name="isPostback" type="hidden" id="isPostback" value="1">
      <input name="btSubmit" type="submit" id="btSubmit" value="Add User?"></td>
    <td>&nbsp;</td>
  </tr>
  <tr>
    <td>&nbsp;</td>
    <td>&nbsp;</td>
  </tr>
  <tr>
    <td>&nbsp;</td>
    <td>&nbsp;</td>
  </tr>
  <tr>
    <td>&nbsp;</td>
    <td>&nbsp;</td>
  </tr>
</table></form>
<p>&nbsp;</p>
<? doFooter(); ?>