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
// $Id: index.php,v 1.7 2006/01/29 08:59:18 atrommer Exp $
$bGoodUser = true;
if ($_POST['isPostback']){ 
	$bGoodUser = loginUser($_POST['tbUsername'], $_POST['tbPass']);
}
if (isset($_SESSION['USERNAME'])) { redirect("mySchedule.php"); }
doHeader("Please Login", null, "self.focus(); document.frmLogin.tbUsername.focus();");

?>

<? if (!$bGoodUser){ print "<span class=errorMsg>Login Failed!  Please try again!</span>";} ?>
<form action="<?php print $_SERVER['PHP_SELF']; ?>" method="post" name="frmLogin" id="frmLogin">
  <input name="tbUsername" type="text" id="tbUsername" value="" tabindex="1">
  <input name="tbPass" type="password" id="tbPass" value="" tabindex="2">
  <input type="submit" name="Submit" value="Log In" tabindex="3">
  <input name="isPostback" type="hidden" id="isPostback" value="1">
</form>
<br>
New features (1/29/06): 
<ul>
<li>Users can be activated/deactivated (replaces deletion, now with creamy nougat center)</li>
<li>My Employees screen allows filtering of all, active, or inactive users</li>
<li>You can now simply click on "All Day" in your availibility instead of choosing the times</li>
<li>Edit Schedule page shows the total number of events for that view</li>
</ul>
Please send "wishlist" ideas to <a href="mailto:atrommer@gmail.com">atrommer@gmail.com</a> and bugs to the buglist link.  
If you <strong>are</strong> submitting a bug, please either create an account on SourceForge or leave your contact info 
so I can actually get back to you.  Thanks!
<br>
<? doFooter(); ?>
