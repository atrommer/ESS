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
// $Id: icalInfo.php,v 1.2 2005/10/30 22:37:19 atrommer Exp $
checkUser($_SESSION['USERTYPE'], 1);

// if > user, grab avail areas, too
if ($_SESSION['USERTYPE'] > 1){ 
	$oAreas = getAreas($_SESSION['USERID']);	
}

doHeader("How to use the iCalendar feeds");
?>
<table cellspacing="0" cellpadding="10" border="0">
<tr>
	<td>
	ESS allows you to point <a href="http://www.apple.com/macosx/features/ical/" target="_blank">Apple iCal</a>, 
	<a href="http://www.mozilla.org/projects/calendar/sunbird.html" target="_blank">Mozilla Sunbird</a>, 
	<a href="http://www.microsoft.com/outlook" target="_blank">Microsoft Outlook</a>, and any other ICS-enabled 
	application to view your personal and master schedules right in your calendar program of choice.  Additionally,
	with iCal or Sunbird, you can point the application at the addresses listed below to "subscribe" to the schedules 
	and recieve updates automatically.  Please view your application's documentation on how to add an ICS feed.
	</td>
</tr>
<tr>
	<td class="contactInfoName">Your personal feed: 
	<a href="http://<?=$host . $docroot . "/iCalView.php?uid=".$_SESSION['USERID'] ?>">
	http://<?=$host . $docroot . "/iCalView.php?uid=".$_SESSION['USERID'] ?></a>
	</td>
</tr>
<? // iterate through the area masters, too
foreach ($oAreas as $Area){
	?>
<tr>
	<td class="contactInfoName"><?=$Area->area_name ?>'s feed:
	<a href="http://<?=$host . $docroot . "/iCalView.php?aid=".$Area->area_id ?>">
	http://<?=$host . $docroot . "/iCalView.php?aid=".$Area->area_id ?></a>
	</td>
</tr>
	<?
}
?>
</table>

<? doFooter(); ?>