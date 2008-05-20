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
// $Id: viewSupers.php,v 1.6 2006/02/03 20:24:34 atrommer Exp $
checkUser($_SESSION['USERTYPE'], 2);
doHeader("View all Supervisors");

// grab the supers based on the filter
if (isset($_REQUEST['f'])){ $filter = $_REQUEST['f'];} else { $filter = 1; }
$aSupers = getSuperDetails($filter);
?><span class="contactInfo"><a href="viewSupers.php?f=1">Show Active</a> | <a href="viewSupers.php?f=2">Show Inactive</a> | <a href="viewSupers.php?f=0">Show All</a></span><br>
<?	foreach($aSupers as $emp){ 

$aPhone1 = formatPhoneNum($emp->user_phone1);
$aPhone2 = formatPhoneNum($emp->user_phone2);

?>

<hr align="left">
<table width="100%" border="0" cellpadding="2" class="contactInfo">
      <tr>
        <td class="contactInfoName"><?="$emp->user_first  $emp->user_last" ?></td>
		<td align="right">
		<? // if admin, then display edit options
		if ($_SESSION['USERTYPE'] > 2){ ?>
		<a href="editUser.php?u_id=<?=$emp->user_id?>&amp;action=edit">Edit?</a>
		<a href="editUser.php?u_id=<?=$emp->user_id?>&amp;action=del">Delete?</a>
		<a href="assumeAdmin.php?u_id=<?=$emp->user_id?>">Assume?</a>
		<? } else { ?> &nbsp; <? } ?>
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
<?	} ?>

<? doFooter(); ?>