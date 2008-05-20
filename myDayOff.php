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
// $Id: myDayOff.php,v 1.3 2006/02/02 03:31:56 atrommer Exp $
checkUser($_SESSION['USERTYPE'], 1);

// check for postback
if ($_POST['isPostback']){
	$dStart = $_POST['ddSYear'] .'-'. $_POST['ddSMo'] .'-'. $_POST['ddSDay'].' '.$_POST['ddSTime'];
	$dEnd = $_POST['ddEYear'] .'-'. $_POST['ddEMo'] .'-'. $_POST['ddEDay'].' '.$_POST['ddETime'];	
	newDayOff($_SESSION['USERID'], $dStart, $dEnd, $_POST['tbDesc']);
	redirect("myDayOff.php");
}

//check for delete
if ($_REQUEST['del']){
	delDayOff($_SESSION['USERID'], $_REQUEST['del']);
	redirect("myDayOff.php");
}
doHeader("My Days Off");
$oDaysOff = getDayOff($_SESSION['USERID']);
?>
<strong>My Days Off</strong><br>
<? if (!count($oDaysOff)){
	print "<br><i>You have not submitted a day off</i><br>";
} ?>
<form method="post" name="frmDayOff" action="<?=$_SERVER['PHP_SELF'] ?>">
<? foreach ($oDaysOff as $Day){ ?>
	<table width="100%" border="0" cellpadding="2" class="contactInfo">
	<tr>
	<td class="contactInfoName"><? print(date("D, n/d/y, g:ia", strtotime($Day->day_start))) ?> 
	thru <? print(date("D, n/d/y, g:ia", strtotime($Day->day_end))) ?></td>
	<td><a href="myDayOff.php?del=<?=$Day->day_id ?>">Delete Day?</a></td>
	</tr>
	<tr><td colspan=2><?=$Day->day_desc ?></td></tr>
	</table>
	<hr>
<? } ?>
<strong>Add a new day off:</strong>
<table width="100%" border="0" cellpadding="2" class="contactInfo">
	<input type="hidden" name="isPostback" value="1">
	<tr>
		<td>Start Day:</td>
		<td>
		<? // draw the ddowns for dmy hm 
		?>
		<select name="ddSMo">
		<? drawMonthDD(); ?>
		</select>
		<select name="ddSDay">
		<? drawDayDD(); ?>
		</select>
		<select name="ddSYear">
		<? drawYearDD(); ?>
		</select>
		<select name="ddSTime">
		<? drawTimeDD("00:00"); ?>
		</select>
		</td>
	</tr>
	<tr>
		<td>End Day:</td>
		<td>
		<? // draw the ddowns for dmy hm 
		?>
		<select name="ddEMo">
		<? drawMonthDD(); ?>
		</select>
		<select name="ddEDay">
		<? drawDayDD(); ?>
		</select>
		<select name="ddEYear">
		<? drawYearDD(); ?>
		</select>
		<select name="ddETime">
		<? drawTimeDD("23:30"); ?>
		</select>
		</td>
	</tr>
	<tr>
		<td>Description:</td>
		<td><input type="text" name="tbDesc"></td>
	</tr>
	<tr>
		<td colspan=2 align=center><input type="submit" value="Add Day Off?"></td>
</table>
 	
  
</form>
<? doFooter(); ?>