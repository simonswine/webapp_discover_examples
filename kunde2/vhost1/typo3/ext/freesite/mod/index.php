<?php
/***************************************************************
*  Copyright notice
*  
*  (c) 1999-2004 Kasper Skaarhoj (kasperYYYY@typo3.com)
*  All rights reserved
*
*  This script is part of the TYPO3 project. The TYPO3 project is 
*  free software; you can redistribute it and/or modify
*  it under the terms of the GNU General Public License as published by
*  the Free Software Foundation; either version 2 of the License, or
*  (at your option) any later version.
* 
*  The GNU General Public License can be found at
*  http://www.gnu.org/copyleft/gpl.html.
*  A copy is found in the textfile GPL.txt and important notices to the license 
*  from the author is found in LICENSE.txt distributed with these scripts.
*
* 
*  This script is distributed in the hope that it will be useful,
*  but WITHOUT ANY WARRANTY; without even the implied warranty of
*  MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
*  GNU General Public License for more details.
*
*  This copyright notice MUST APPEAR in all copies of the script!
***************************************************************/
/** 
 * Module: Freesite
 *
 * This module is used to automatically create a new website in the database.
 *
 * Quickly create a new website in TYPO3 with a template and dummy-pages, plus create a new user/group for that website.
 * 
 * 
 * DESCRIPTION:
 * This script displays a form, where you can enter a name,email,username,password for a new user/group that will be created in the typo3-database. Also directories for fileadministration can be created.
 * With the user a new page in TYPO3 is created also. This page would normally serve as a website for the user, and the user is given access to this page when he logs in.
 * You can also choose a template for the site and a default dummy-content page-structure.
 * 
 * LOCATION:
 * Must be in a folder in "typo3conf/", default is "typo3conf/freesite/".
 * You might want to password protect this page, as it enables the user to create new sites in the typo3-database.
 * 
 * @author	Kasper Skårhøj <kasperYYYY@typo3.com>
 */

	// Allow module to be executed allthough there are no user. 
define("TYPO3_PROCEED_IF_NO_USER", 1);


unset($MCONF);
require ("conf.php");
require ($BACK_PATH."init.php");
require ($BACK_PATH."template.php");
require_once ("class.freesite.php");
require_once (PATH_t3lib."class.t3lib_tcemain.php");


// ONLY if template details is shown:
if (t3lib_div::_GP("infoUid"))	{
	require_once (PATH_t3lib."class.t3lib_tstemplate.php");
	require_once (PATH_t3lib."class.t3lib_page.php");
	require_once (PATH_t3lib."class.t3lib_tsparser_ext.php");
}


// **********************
// Creating main object
// **********************
$freesite = t3lib_div::makeInstance("freesite_admin");


// **********************
// Which action?
// **********************
$script = t3lib_div::_GP("script");
switch($script)	{
	case "template":
		$freesite->printSelect("selectTemplate");
	break;
	case "pages":
		$freesite->printSelect("selectPages");
	break;
	default:
		$freesite->main();
	break;
}

?>