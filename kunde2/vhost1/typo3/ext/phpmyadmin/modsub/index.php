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
 *
 * Redirect script for the third party module, phpMyAdmin
 *
 * This 'module' serves also as an example of how to integrate third party modules in TYPO3.
 * In this case phpMyAdmin you must include the file 'extModInclude.php' from the config-file of phpMyAdmin in order to make TYPO3 authenticate the user.
 * If you don't do that, there is basically free access (or at least access control on phpMyAdmin level) if one could guess the position of phpMyAdmin.
 * In addition the script 'extModInclude.php' is used to manipulate some settings for phpMyAdmin so the database, server, username and password is set from the TYPO3 configuration
 *
 * If you need to make modules which are nothing but links to external script, please see how the 'Install Tool' module is configured - this is merely a link!
 *
 * @author	Kasper Skårhøj <kasperYYYY@typo3.com>
 */

 
 // Regular initialization. Must check if the user has access to the module. This determines if it appears in the menu and if this script will redirect.
unset($MCONF);
require ('conf.php');
require ($BACK_PATH.'init.php');
require ($BACK_PATH.'template.php');
$BE_USER->modAccess($MCONF,1);


// ***************************
// Script Classes
// ***************************
class SC_mod_tools_phpadmin_index {
	var $MCONF=array();
	var $MOD_MENU=array();
	var $MOD_SETTINGS=array();
	var $doc;	

	function main()	{
		global $BE_USER,$LANG,$BACK_PATH,$TCA_DESCR,$TCA,$CLIENT,$TYPO3_CONF_VARS;
		$this->MCONF = $GLOBALS['MCONF'];

		if ($this->MCONF['phpMyAdminSubDir'] && @is_dir($this->MCONF['phpMyAdminSubDir']))	{	// If phpMyAdmin is configured in the conf.php script, we continue to load it...
				// Mapping language keys for phpMyAdmin
		
			//dk|de|no|it|fr|es|nl|cz|pl|si
		
			$LANG_KEY_MAP = Array(
				'dk'=>'da',
				'de'=>'de',
				'no'=>'no',
				'it'=>'it',
				'fr'=>'fr',
				'es'=>'es',
				'nl'=>'nl',
				'cz'=>'cs-iso',
				'pl'=>'pl',
				'si'=>'sk'
			);
			
			$LANG_KEY = $LANG_KEY_MAP[$LANG->lang];
			if (!$LANG_KEY)	$LANG_KEY='en';
				
				// Redirecting, setting default database
			$redirect = $this->MCONF['phpMyAdminSubDir'].$this->MCONF['phpMyAdminScript'].'?lang='.$LANG_KEY.'&db='.urlencode(TYPO3_db);
			header('Location: '.$redirect); // Un-comment this line and enter the actual name of the subdirectory of your phpMyAdmin install!
		} else {	// No configuration set:
		
			$this->doc = t3lib_div::makeInstance('mediumDoc');
			$this->doc->backPath = $BACK_PATH;
			$this->content=$this->doc->startPage($LANG->getLL('title'));
			$this->content.=('
		<h3>phpMyAdmin module was not installed</h3>
		'.($this->MCONF['phpMyAdminSubDir'] && !@is_dir($this->MCONF['phpMyAdminSubDir'])?'<hr /><strong>ERROR: The directory, '.$this->MCONF['phpMyAdminSubDir'].', was NOT found!</strong><HR>':'').'
		<ol>
		<li>First, install phpMyAdmin in a subdir to this module (eg. typo3/mod/tools/phpadmin/<strong>phpMyAdmin-2.2.6/</strong>)</li>
		<li><font color=red><strong>Very important for security:</strong></font> Secondly, alter the phpMyAdmin file "config.inc.php" by inserting this line in the very bottom: <br /><br />
		include("../extModInclude.php");<br /><br />This file will override some of the phpMyAdmin configuration.</li>
		<li>Then alter "conf.php" by un-commenting the line that defines the module is installed. Enter the correct path here as well!</li>
		</ol>
			');
			$this->content.=$this->doc->endPage();
		}
	}
	function printContent()	{
		echo $this->content	;
	}
}

// Include extension?
if (defined('TYPO3_MODE') && $TYPO3_CONF_VARS[TYPO3_MODE]['XCLASS']['ext/phpmyadmin/modsub/index.php'])	{
	include_once($TYPO3_CONF_VARS[TYPO3_MODE]['XCLASS']['ext/phpmyadmin/modsub/index.php']);
}









// Make instance:
$SOBE = t3lib_div::makeInstance('SC_mod_tools_phpadmin_index');
$SOBE->main();
$SOBE->printContent();
?>