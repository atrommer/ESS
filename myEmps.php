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
// $Id: myEmps.php,v 1.7 2006/02/03 20:24:34 atrommer Exp $
checkUser($_SESSION['USERTYPE'], 2);
// grab the emps based on the filter
if (isset($_REQUEST['f'])){ $filter = $_REQUEST['f'];} else { $filter = 1; }
$aEmployees = getMyEmployees($_SESSION['USERID'], $filter);

doHeader("My Employees");
?>
<a href="newUser.php">Add new employee?</a><br>
<span class="contactInfo"><a href="myEmps.php?f=1">Show Active</a> | <a href="myEmps.php?f=2">Show Inactive</a> | <a href="myEmps.php?f=0">Show All</a></span><br>
<?	
if (!count($aEmployees)){ // we have no coworkers
	print "<br><i>You currently have no employees assigned to you!</i><br>";
} else {
foreach($aEmployees as $emp){ 

$aPhone1 = formatPhoneNum($emp->user_phone1);
$aPhone2 = formatPhoneNum($emp->user_phone2);

?>
<hr align="left">
<table width="100%" border="0" cellpadding="2" class="contactInfo">
      <tr>
        <td class="contactInfoName"><?="$emp->user_first  $emp->user_last" ?></td>
		<td align="right"><a href="editUser.php?u_id=<?=$emp->user_id?>&amp;action=edit">Edit?</a>
		<a href="editUser.php?u_id=<?=$emp->user_id?>&amp;action=del">Delete?</a>
		<a href="viewSched.php?user=<?=$emp->user_id?>">View Availibility?</a>
		<a href="assumeUser.php?user=<?=$emp->user_id?>">Assume User?</a>
		</td>
      </tr>
      <tr>
        <td width="31%"><?=$emp->user_name?>
        <div align="left"></div></td>
        <td width="69%"><div align="left"><a href="mailto:<?=$emp->user_email?>">
        <?=$emp->user_email?>
        </a></div></td>
      </tr>
      <tr>
        <td><? print ("($aPhone1[0]) $aPhone1[1]-$aPhone1[2]"); ?>
        <div align="left"></div></td>
        <td><? strlen($aPhone2[0])?print("($aPhone2[0]) $aPhone2[1]-$aPhone2[2]"):print""; ?>
        <div align="left"></div></td>
      </tr>
</table>
<?	} 
}?>



<?
doFooter();
?>