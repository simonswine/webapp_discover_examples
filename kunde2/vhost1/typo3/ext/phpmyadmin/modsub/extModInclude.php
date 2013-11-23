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
 * @author	Kasper Skårhøj <kasperYYYY@typo3.com>
 */


// This includes the TYPO3 module configuration as required:
unset($MCONF);
$MCONF['extModInclude']=1;
include ('../conf.php');

// It phpMyAdmin is congured for, proceed
if ($MCONF['phpMyAdminSubDir'])	{	// If phpMyAdmin is configured in the conf.php script, we continue to load it...
		// Now, the init.php script is included. This loads a lot of stuff and includes TYPO3 specific libraries which we don't need directly for the application.
		// However this is the only way to get the TYPO3 backend user authenticated so we know if this user is actually allowed to access this module!
	require ($BACK_PATH.'init.php');
	require ($BACK_PATH.'template.php');
	$BE_USER->modAccess($MCONF,1);

		// By now the TYPO3 backend user is authenticated and if that failed and error is outputted and the script has exited.
		// So now we go on, altering certain configoptions of phpMyAdmin (for details here, see the phpMyAdmin config-file, config.inc.php)

			// Set some interface values:
		$cfg['LeftWidth']           = 200;          // left frame width
		$cfg['LeftBgColor']         = $TBE_TEMPLATE->bgColor;    // background color for the left frame
		$cfg['RightBgColor']        = $TBE_TEMPLATE->bgColor;    // background color for the right frame
		$cfg['ThBgcolor']           = $TBE_TEMPLATE->bgColor5;    // table header row colour
		$cfg['BgcolorOne']          = $TBE_TEMPLATE->bgColor4;    // table data row colour
		$cfg['BgcolorTwo']          = t3lib_div::modifyHTMLColor($TBE_TEMPLATE->bgColor4,+20,+20,+20);    // table data row colour, alternate
		$cfg['BrowsePointerColor']  = $TBE_TEMPLATE->bgColor6;    // color of the pointer in browse mode
		$cfg['LeftPointerColor']    = $TBE_TEMPLATE->bgColor6;    // color of the pointer in left frame
		$cfg['DefaultDisplay']      = 'horizontal'; // default display direction (horizontal|vertical)
		$cfg['ShowBlob']              = TRUE;  // display blob field contents

			// Setting the database information, using 'server1'. The other servers are NOT configured or reset, so you may set then independantly if you like.
		$cfg['Servers'][1]['host']          = TYPO3_db_host; // MySQL hostname
		$cfg['Servers'][1]['user']          = TYPO3_db_username;      // MySQL user (only needed with basic auth)
		$cfg['Servers'][1]['password']      = TYPO3_db_password;          // MySQL password (only needed with basic auth)
		$cfg['Servers'][1]['only_db']       = TYPO3_db;          // If set to a db-name, only this db is accessible

		$cfg['PmaAbsoluteUri'] = t3lib_div::getIndpEnv('TYPO3_REQUEST_DIR');
} else {
	die ('phpMyAdmin was not configured in mod/tools/phpadmin/conf.php!');
}

?>