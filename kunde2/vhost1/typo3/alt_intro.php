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
 * 'About modules' script - the default start-up module.
 * Will display the list of main- and sub-modules available to the user.
 * Each module will be show with description and a link to the module.
 *
 * $Id: alt_intro.php,v 1.12.2.1 2004/09/19 21:23:31 mundaun Exp $
 * Revised for TYPO3 3.6 2/2003 by Kasper Skaarhoj
 * XHTML compliant
 *
 * @author	Kasper Skaarhoj <kasperYYYY@typo3.com>
 */
/**
 * [CLASS/FUNCTION INDEX of SCRIPT]
 *
 *
 *
 *   75: class SC_alt_intro
 *   84:     function init()
 *   97:     function main()
 *  150:     function printContent()
 *
 * TOTAL FUNCTIONS: 3
 * (This index is automatically created/updated by the extension "extdeveval")
 *
 */


require ('init.php');
require ('template.php');
require_once (PATH_t3lib.'class.t3lib_loadmodules.php');
require_once (PATH_t3lib.'class.t3lib_basicfilefunc.php');
require_once ('class.alt_menu_functions.inc');
$LANG->includeLLFile('EXT:lang/locallang_alt_intro.xml');








/**
 * Script Class for the introduction screen, alias "About > Modules" which shows the description of each available module for the user.
 *
 * @author	Kasper Skaarhoj <kasperYYYY@typo3.com>
 * @package TYPO3
 * @subpackage core
 */
class SC_alt_intro {
	var $loadModules;
	var $content;

	/**
	 * Initialization of script class
	 *
	 * @return	void
	 */
	function init()	{
		global $TBE_MODULES;

			// Loads the available backend modules so we can create the description overview.
		$this->loadModules = t3lib_div::makeInstance('t3lib_loadModules');
		$this->loadModules->load($TBE_MODULES);
	}

	/**
	 * Main content - displaying the module descriptions
	 *
	 * @return	void
	 */
	function main()	{
		global $BE_USER,$LANG,$TYPO3_CONF_VARS;
		global $TBE_TEMPLATE;

		$alt_menuObj = t3lib_div::makeInstance('alt_menu_functions');

		$TBE_TEMPLATE->docType = 'xhtml_trans';
		$TBE_TEMPLATE->divClass = $TBE_TEMPLATE->bodyTagId;
		$this->content.= $TBE_TEMPLATE->startPage('About modules');

		$this->content.= sprintf('
			<h1>%s<br />%s</h1>

			<p>%s</p>
			<p>&nbsp;%s</p>
			<p>%s</p>',
			'TYPO3 '.TYPO3_version,
			$LANG->getLL('introtext'),
			t3lib_BEfunc::TYPO3_copyRightNotice(),
			$this->displayWarningIfPasswordIsDefault(),
			$LANG->getLL('introtext2')
			);




			// Printing the description of the modules available
		$this->content.=$alt_menuObj->topMenu($this->loadModules->modules,0,'',1);
		$this->content.='<br />';

			// end text: 'Features may vary depending on your website and permissions'
		$this->content.='<p class="c-features"><em>('.$LANG->getLL('endText').')</em></p>';
		$this->content.='<hr />';

			// Logged in user, eg: 'You're logged in as user: admin (John Doe, john@doe.email)'
		$this->content.='<p class="c-user">'.
				htmlspecialchars($LANG->getLL('userInfo')).
				sprintf(' <strong>%s</strong> (%s)',
						$BE_USER->user['username'],
						(implode(', ',array($BE_USER->user['realName'],$BE_USER->user['email'])))
						).
				'</p>
				<br />
				<br />';

			// End page
		$this->content.= $TBE_TEMPLATE->endPage();
	}

	/**
	 * Outputting the accumulated content to screen
	 *
	 * @return	void
	 */
	function printContent()	{
		echo $this->content;
	}

	/**
	 * Display a warning message if the Install Tool Password is still the default: joh316
	 * The warning is only displayed to admin users
	 *
	 * @return	void
	 */
	function displayWarningIfPasswordIsDefault()	{
		if($GLOBALS['BE_USER']->isAdmin() && $GLOBALS['TYPO3_CONF_VARS']['BE']['installToolPassword']==md5('joh316')) {
			return '&nbsp;</p>
			<p class="typo3-red" style="font-weight: bold;">Security warning: The password of your install tool is still "joh316" which is the default.<br />
			It is highly recommended to change this immediately.<br />
			&nbsp;';
		} else {
			return '';
		}
	}
}

// Include extension?
if (defined('TYPO3_MODE') && $TYPO3_CONF_VARS[TYPO3_MODE]['XCLASS']['typo3/alt_intro.php'])	{
	include_once($TYPO3_CONF_VARS[TYPO3_MODE]['XCLASS']['typo3/alt_intro.php']);
}












// Make instance:
$SOBE = t3lib_div::makeInstance('SC_alt_intro');
$SOBE->init();
$SOBE->main();
$SOBE->printContent();
?>