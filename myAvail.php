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
// $Id: myAvail.php,v 1.6 2006/02/03 20:24:34 atrommer Exp $
checkUser($_SESSION['USERTYPE'], 1);

// check page for processing
if ($_POST['isPostback']){
	updateAvail($_SESSION['USERID'], $_POST);
	redirect("myAvail.php");
}
if ($_REQUEST['del']){
	delAvail($_SESSION['USERID'],$_REQUEST['id']);
	redirect("myAvail.php");
}

doHeader("My Availibility");

?>
<p><strong>My Availibility</strong>
</p>
<p>This page allows you to modify your general weekly schedule. Please block out times that you are available for work. Once you save one set of blocks, you can add a new set. You may also delete any blocks from your weekly schedule. </p>
<form method="post" name="frmAvail" action="<?=$_SERVER['PHP_SELF'] ?>">
  <table width="100%" border="0" cellpadding="0" cellspacing="0">
    <tr>
      <td width="152" align="left" valign="top"><strong>Day of Week </strong></td>
      <td width="538" align="left" valign="top"><strong>Available Times </strong></td>
    </tr>
<?
$bRow = true;
for ($iDay=0; $iDay < 7; $iDay++){
	$bRow = !$bRow;
	
?>
    <tr<? if ($bRow){ print " class=evenRow";}?>>
      <td align="left" valign="top"><?=$gaDOW[$iDay]?></td>
      <td align="left" valign="top">
	  <table width="100%" cellpadding="0" cellspacing="0" border="0" class="availDetail">
	  <tr>
	  	<td width="50%"><strong>Start</strong></td>
	  	<td colspan="2"><strong>End</strong></td>
	  	<td>&nbsp;</td>
		</tr>
		<? 
			$oSchedule = getAvail($_SESSION['USERID'], $iDay);
			foreach ($oSchedule as $Sched){
				//if (!(($Sched->avail_start=='00:00:00') && ($Sched->avail_end=='00:00:00'))){
				print "<tr><td>" . date("g:ia",strtotime($Sched->avail_start)) 
					. "</td><td>" . date("g:ia",strtotime($Sched->avail_end)) 
					. "</td><td><a href=\"myAvail.php?del=1&amp;id=$Sched->avail_id\">Delete?</a></td></tr>";
				//}
			}
		?>
		<tr>
		<td>
		  <select name="selStart<?=$iDay?>" id="selMStart<?=$iDay?>">
          <option value="none" selected>none</option>
		<? drawTimeDD(); ?>
        </select>
		</td><td>
        <select name="selEnd<?=$iDay?>" id="selEnd<?=$iDay?>">
          <option value="none" selected>none</option>
			<? drawTimeDD(); ?>
          </select>
		</td>
		<td>All Day? <input type="checkbox" name="cbAll<?=$iDay?>" value="1"></td>
	  </tr>
	  </table>
</td>
    </tr>
<? } ?>
    <tr>
      <td colspan="2" align="left" valign="top"><div align="center">
        <input name="isPostback" type="hidden" id="isPostback" value="1">
        <input type="submit" name="Submit" value="Add Times?">
      </div></td>
    </tr>
  </table>
</form>
<? doFooter(); ?>