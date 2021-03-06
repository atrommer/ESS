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
// $Id: viewPool.php,v 1.2 2005/10/30 22:37:19 atrommer Exp $
checkUser($_SESSION['USERTYPE'], 2);

// check for add
if ($_REQUEST['add']){
	addUserToSuper($_SESSION['USERID'], $_REQUEST['add']);
	redirect("viewPool.php");	
}

// grab all employees
$oAllEmps = getAllEmps();

// grab super's emps
$oMyEmps = getMyEmployees($_SESSION['USERID']);

doHeader("Employee Pool");
?>
Below are all the employees stored in ESS.  
Click on an employee to add them to your management.
Once an employee has been added to your management, you can edit them just like normal.
<? doFooter(); ?>