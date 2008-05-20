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
// $Id: viewSched.php,v 1.7 2005/10/30 22:37:19 atrommer Exp $
checkUser($_SESSION['USERTYPE'], 2);
if (empty($_REQUEST['user'])){ // keep people from navigating here directly
	accessDenied("Please choose a user first.");
}

$oEmp = getUserVals($_REQUEST['user']);
if (empty($oEmp)){ // this would happen if they arbitrarily typed in a num in the url
	accessDenied("You selected an invalid user!");
}
if (!$_REQUEST['doPop']){
	doHeader("Viewing $oEmp->user_first $oEmp->user_last's schedule");
} else {
	?>
	<html>
	<head>
	<title>Viewing <?=$oEmp->user_first .' '. $oEmp->user_last ?>'s schedule</title>
	<link href="global.css" rel="stylesheet" type="text/css">
	</head>
	
	<body>
	<?
}
?>
Displaying availibility for <? print $oEmp->user_first . " " . $oEmp->user_last; ?>:<br>
<?
// draw day grid
print "<table cellpadding=\"0\" cellspacing=\"0\" border=\"0\" align=center><tr>";

for ($iDay=0; $iDay<7; $iDay++){
	print "<tr><td class=contactInfoName align=center>" . $gaDOW[$iDay] . "</td></tr>";
	$oEmpAvail = getAvail($_REQUEST['user'], $iDay);
	if (count($oEmpAvail)){
		foreach ($oEmpAvail as $Block){
			print "<tr><td>" . date("g:ia", strtotime($Block->avail_start)) . " - ";
			print date("g:ia", strtotime($Block->avail_end)) . "</td></tr>";
			}
	} else { print "<tr><td><i>Not Available</i></td></tr>"; }
	next($gaDOW);
}

print "</tr></table>";
?>
<hr>
Days requested off:
<br>
<?
$oDaysOff = getDayOff($_REQUEST['user']);
 if (!count($oDaysOff)){
	print "<br><i>No days requested off</i><br>";
}
foreach ($oDaysOff as $Day){ ?>
	<table width="100%" border="0" cellpadding="2" class="contactInfo">
	<tr>
	<td class="contactInfoName"><? print(date("D, n/d/y", strtotime($Day->day_start))) ?> 
	thru <? print(date("D, n/d/y", strtotime($Day->day_end))) ?></td>
	</tr>
	<tr><td><?=$Day->day_desc ?></td></tr>
	</table>
	<hr>
<? } ?>


<?doFooter();
?>
