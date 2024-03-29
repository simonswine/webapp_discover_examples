<?php
/***************************************************************
*  Copyright notice
*
*  (c) 1999-2005 Kasper Skaarhoj (kasperYYYY@typo3.com)
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
 * This is the MAIN DOCUMENT of the TypoScript driven standard front-end (from the "cms" extension)
 * Basically put this is the "index.php" script which all requests for TYPO3 delivered pages goes to in the frontend (the website)
 * The script configures constants, includes libraries and does a little logic here and there in order to instantiate the right classes to create the webpage.
 * All the real data processing goes on in the "tslib/" classes which this script will include and use as needed.
 *
 * On UNIX: You should create a symlink to this file from the directory from which you want your TYPO3 website to run (which is ../)
 * ln -s tslib/index_ts.php index.php
 *
 * On Windows this file should copied to "index.php" in your website root (which is ../)
 *
 * $Id: index_ts.php,v 1.16.2.1 2005/05/19 16:52:46 newjuggle Exp $
 * Revised for TYPO3 3.6 June/2003 by Kasper Skaarhoj
 *
 * @author	Kasper Skaarhoj <kasperYYYY@typo3.com>
 * @package TYPO3
 * @subpackage tslib
 */

// *******************************
// Set error reporting
// *******************************
error_reporting (E_ALL ^ E_NOTICE);


// ******************
// Constants defined
// ******************
$TYPO3_MISC['microtime_start'] = microtime();
define('TYPO3_OS', stristr(PHP_OS,'win')&&!stristr(PHP_OS,'darwin')?'WIN':'');
define('TYPO3_MODE','FE');
define('PATH_thisScript',str_replace('//','/', str_replace('\\','/', (php_sapi_name()=='cgi'||php_sapi_name()=='isapi' ||php_sapi_name()=='cgi-fcgi')&&($_SERVER['ORIG_PATH_TRANSLATED']?$_SERVER['ORIG_PATH_TRANSLATED']:$_SERVER['PATH_TRANSLATED'])? ($_SERVER['ORIG_PATH_TRANSLATED']?$_SERVER['ORIG_PATH_TRANSLATED']:$_SERVER['PATH_TRANSLATED']):($_SERVER['ORIG_SCRIPT_FILENAME']?$_SERVER['ORIG_SCRIPT_FILENAME']:$_SERVER['SCRIPT_FILENAME']))));

define('PATH_site', dirname(PATH_thisScript).'/');
define('PATH_t3lib', PATH_site.'t3lib/');
define('PATH_tslib', PATH_site.'tslib/');
define('PATH_typo3conf', PATH_site.'typo3conf/');
define('TYPO3_mainDir', 'typo3/');		// This is the directory of the backend administration for the sites of this TYPO3 installation.

if (!@is_dir(PATH_typo3conf))	die('Cannot find configuration. This file is probably executed from the wrong location.');

// *********************
// Timetracking started
// *********************
require_once(PATH_t3lib.'class.t3lib_timetrack.php');
$TT = new t3lib_timeTrack;
$TT->start();
$TT->push('','Script start');


// *********************
// Mandatory libraries included
// *********************
$TT->push('Include class t3lib_db, t3lib_div, t3lib_extmgm','');
	require_once(PATH_t3lib.'class.t3lib_div.php');
	require_once(PATH_t3lib.'class.t3lib_extmgm.php');
$TT->pull();



// **********************
// Include configuration
// **********************
$TT->push('Include config files','');
require(PATH_t3lib.'config_default.php');
if (!defined ('TYPO3_db')) 	die ('The configuration file was not included.');	// the name of the TYPO3 database is stored in this constant. Here the inclusion of the config-file is verified by checking if this var is set.
if (!t3lib_extMgm::isLoaded('cms'))	die('<strong>Error:</strong> The main frontend extension "cms" was not loaded. Enable it in the extension manager in the backend.');

require_once(PATH_t3lib.'class.t3lib_db.php');
$TYPO3_DB = t3lib_div::makeInstance('t3lib_DB');

$CLIENT = t3lib_div::clientInfo();				// Set to the browser: net / msie if 4+ browsers
$TT->pull();


// *********************
// Libraries included
// *********************
$TT->push('Include Frontend libraries','');
	require_once(PATH_tslib.'class.tslib_fe.php');
	require_once(PATH_t3lib.'class.t3lib_page.php');
	require_once(PATH_t3lib.'class.t3lib_userauth.php');
	require_once(PATH_tslib.'class.tslib_feuserauth.php');
	require_once(PATH_t3lib.'class.t3lib_tstemplate.php');
	require_once(PATH_t3lib.'class.t3lib_cs.php');
$TT->pull();


// *******************************
// Checking environment
// *******************************
if (t3lib_div::int_from_ver(phpversion())<4001000)	die ('TYPO3 runs with PHP4.1.0+ only');

if (isset($_POST['GLOBALS']) || isset($_GET['GLOBALS']))	die('You cannot set the GLOBALS-array from outside the script.');
if (!get_magic_quotes_gpc())	{
	$TT->push('Add slashes to GET/POST arrays','');
	t3lib_div::addSlashesOnArray($_GET);
	t3lib_div::addSlashesOnArray($_POST);
	$HTTP_GET_VARS = $_GET;
	$HTTP_POST_VARS = $_POST;
	$TT->pull();
}

// ***********************************
// Create $TSFE object (TSFE = TypoScript Front End)
// Connecting to database
// ***********************************
$temp_TSFEclassName = t3lib_div::makeInstanceClassName('tslib_fe');
$TSFE = new $temp_TSFEclassName(
		$TYPO3_CONF_VARS,
		t3lib_div::_GP('id'),
		t3lib_div::_GP('type'),
		t3lib_div::_GP('no_cache'),
		t3lib_div::_GP('cHash'),
		t3lib_div::_GP('jumpurl'),
		t3lib_div::_GP('MP'),
		t3lib_div::_GP('RDCT')
	);
$TSFE->connectToMySQL();
if ($TSFE->RDCT)	{$TSFE->sendRedirect();}


// *******************
// output compression
// *******************
if ($TYPO3_CONF_VARS['FE']['compressionLevel'])	{
	ob_start();
	require_once(PATH_t3lib.'class.gzip_encode.php');
}

// *********
// FE_USER
// *********
$TT->push('Front End user initialized','');
	$TSFE->initFEuser();
$TT->pull();

// *********
// BE_USER
// *********
$BE_USER='';
if ($_COOKIE['be_typo_user']) {		// If the backend cookie is set, we proceed and checks if a backend user is logged in.
	$TYPO3_MISC['microtime_BE_USER_start'] = microtime();
	$TT->push('Back End user initialized','');
		require_once (PATH_t3lib.'class.t3lib_befunc.php');
		require_once (PATH_t3lib.'class.t3lib_userauthgroup.php');
		require_once (PATH_t3lib.'class.t3lib_beuserauth.php');
		require_once (PATH_t3lib.'class.t3lib_tsfebeuserauth.php');

			// the value this->formfield_status is set to empty in order to disable login-attempts to the backend account through this script
		$BE_USER = t3lib_div::makeInstance('t3lib_tsfeBeUserAuth');	// New backend user object
		$BE_USER->OS = TYPO3_OS;
		$BE_USER->start();			// Object is initialized
		$BE_USER->unpack_uc('');
		if ($BE_USER->user['uid'])	{
			$BE_USER->fetchGroupData();
			$TSFE->beUserLogin = 1;
		}
		if ($BE_USER->checkLockToIP() && $BE_USER->checkBackendAccessSettingsFromInitPhp())	{
			$BE_USER->extInitFeAdmin();
			if ($BE_USER->extAdmEnabled)	{
				require_once(t3lib_extMgm::extPath('lang').'lang.php');
				$LANG = t3lib_div::makeInstance('language');
				$LANG->init($BE_USER->uc['lang']);

				$BE_USER->extSaveFeAdminConfig();
					// Setting some values based on the admin panel
				$TSFE->forceTemplateParsing = $BE_USER->extGetFeAdminValue('tsdebug', 'forceTemplateParsing');
				$TSFE->displayEditIcons = $BE_USER->extGetFeAdminValue('edit', 'displayIcons');
				$TSFE->displayFieldEditIcons = $BE_USER->extGetFeAdminValue('edit', 'displayFieldIcons');

				if (t3lib_div::_GP('ADMCMD_editIcons'))	{
					$TSFE->displayFieldEditIcons=1;
					$BE_USER->uc['TSFE_adminConfig']['edit_editNoPopup']=1;
				}
				if (t3lib_div::_GP('ADMCMD_simUser'))	{
					$BE_USER->uc['TSFE_adminConfig']['preview_simulateUserGroup']=intval(t3lib_div::_GP('ADMCMD_simUser'));
					$BE_USER->ext_forcePreview=1;
				}
				if (t3lib_div::_GP('ADMCMD_simTime'))	{
					$BE_USER->uc['TSFE_adminConfig']['preview_simulateDate']=intval(t3lib_div::_GP('ADMCMD_simTime'));
					$BE_USER->ext_forcePreview=1;
				}

					// Include classes for editing IF editing module in Admin Panel is open
				if (($BE_USER->extAdmModuleEnabled('edit') && $BE_USER->extIsAdmMenuOpen('edit')) || $TSFE->displayEditIcons == 1)	{
					$TSFE->includeTCA();
					if ($BE_USER->extIsEditAction())	{
						require_once (PATH_t3lib.'class.t3lib_tcemain.php');
						$BE_USER->extEditAction();
					}
					if ($BE_USER->extIsFormShown())	{
						require_once(PATH_t3lib.'class.t3lib_tceforms.php');
						require_once(PATH_t3lib.'class.t3lib_iconworks.php');
						require_once(PATH_t3lib.'class.t3lib_loaddbgroup.php');
						require_once(PATH_t3lib.'class.t3lib_transferdata.php');
					}
				}

				if ($TSFE->forceTemplateParsing || $TSFE->displayEditIcons || $TSFE->displayFieldEditIcons)	{ $TSFE->set_no_cache(); }
			}

	//		$WEBMOUNTS = (string)($BE_USER->groupData['webmounts'])!='' ? explode(',',$BE_USER->groupData['webmounts']) : Array();
	//		$FILEMOUNTS = $BE_USER->groupData['filemounts'];
		} else {	// Unset the user initialization.
			$BE_USER='';
			$TSFE->beUserLogin=0;
		}
	$TT->pull();
	$TYPO3_MISC['microtime_BE_USER_end'] = microtime();
}


// *****************************************
// Proces the ID, type and other parameters
// After this point we have an array, $page in TSFE, which is the page-record of the current page, $id
// *****************************************
$TT->push('Process ID','');
	$TSFE->checkAlternativeIdMethods();
	$TSFE->clear_preview();
	$TSFE->determineId();

		// Now, if there is a backend user logged in and he has NO access to this page, then re-evaluate the id shown!
	if ($TSFE->beUserLogin && !$BE_USER->extPageReadAccess($TSFE->page))	{

			// Remove user
		unset($BE_USER);
		$TSFE->beUserLogin = 0;

			// Re-evaluate the page-id.
		$TSFE->checkAlternativeIdMethods();
		$TSFE->clear_preview();
		$TSFE->determineId();
	}
	$TSFE->makeCacheHash();
$TT->pull();


// *******************************************
// Get compressed $TCA-Array();
// After this, we should now have a valid $TCA, though minimized
// *******************************************
$TSFE->getCompressedTCarray();


// ********************************
// Starts the template
// *******************************
$TT->push('Start Template','');
	$TSFE->initTemplate();
$TT->pull();


// ********************************
// Get from cache
// *******************************
$TT->push('Get Page from cache','');
	$TSFE->getFromCache();
$TT->pull();


// ******************************************************
// Get config if not already gotten
// After this, we should have a valid config-array ready
// ******************************************************
$TSFE->getConfigArray();


// ********************************
// Convert POST data to internal "renderCharset" if different from the metaCharset:
// *******************************
$TSFE->convPOSTCharset();


// *******************************************
// Setting the internal var, sys_language_uid + locale settings
// *******************************************
$TSFE->settingLanguage();
$TSFE->settingLocale();


// ********************************
// Check Submission of data.
// This is done at this point, because we need the config values
// *******************************
switch($TSFE->checkDataSubmission())	{
	case 'email':
		require_once(PATH_t3lib.'class.t3lib_htmlmail.php');
		require_once(PATH_t3lib.'class.t3lib_formmail.php');
		$TSFE->sendFormmail();
	break;
	case 'fe_tce':
		require_once(PATH_tslib.'class.tslib_fetce.php');
		$TSFE->includeTCA();
		$TT->push('fe_tce','');
		$TSFE->fe_tce();
		$TT->pull();
	break;
}


// ********************************
// Check JumpUrl
// *******************************
$TSFE->checkJumpUrl();


// ********************************
// Generate page
// *******************************
$TSFE->setUrlIdToken();

$TT->push('Page generation','');
if ($TSFE->doXHTML_cleaning())	{require_once(PATH_t3lib.'class.t3lib_parsehtml.php');}
if ($TSFE->isGeneratePage())	{
		$TSFE->generatePage_preProcessing();
		$temp_theScript=$TSFE->generatePage_whichScript();

		if ($temp_theScript)	{
			include($temp_theScript);
		} else {
			require_once(PATH_tslib.'class.tslib_pagegen.php');
			include(PATH_tslib.'pagegen.php');
		}
		$TSFE->generatePage_postProcessing();
} elseif ($TSFE->isINTincScript())	{
	require_once(PATH_tslib.'class.tslib_pagegen.php');
	include(PATH_tslib.'pagegen.php');
}
$TT->pull();


// ********************************
// $GLOBALS['TSFE']->config['INTincScript']
// *******************************
if ($TSFE->isINTincScript())		{
	$TT->push('Non-cached objects','');
		$INTiS_config = $GLOBALS['TSFE']->config['INTincScript'];

			// Special feature: Include libraries
		$TT->push('Include libraries');
		foreach($INTiS_config as $INTiS_cPart)	{
			if ($INTiS_cPart['conf']['includeLibs'])	{
				$INTiS_resourceList = t3lib_div::trimExplode(',',$INTiS_cPart['conf']['includeLibs'],1);
				$GLOBALS['TT']->setTSlogMessage('Files for inclusion: "'.implode(', ',$INTiS_resourceList).'"');

				foreach($INTiS_resourceList as $INTiS_theLib)	{
					$INTiS_incFile = $GLOBALS['TSFE']->tmpl->getFileName($INTiS_theLib);
					if ($INTiS_incFile)	{
						require_once('./'.$INTiS_incFile);
					} else {
						$GLOBALS['TT']->setTSlogMessage('Include file "'.$INTiS_theLib.'" did not exist!',2);
					}
				}
			}
		}
		$TT->pull();
		$TSFE->INTincScript();
	$TT->pull();
}

// ***************
// Output content
// ***************
if ($TSFE->isOutputting())	{
	$TT->push('Print Content','');
	$TSFE->processOutput();

	// ***************************************
	// Outputs content / Includes EXT scripts
	// ***************************************
	if ($TSFE->isEXTincScript())	{
		$TT->push('External PHP-script','');
				// Important global variables here are $EXTiS_*, they must not be overridden in include-scripts!!!
			$EXTiS_config = $GLOBALS['TSFE']->config['EXTincScript'];
			$EXTiS_splitC = explode('<!--EXT_SCRIPT.',$GLOBALS['TSFE']->content);			// Splits content with the key.

				// Special feature: Include libraries
			reset($EXTiS_config);
			while(list(,$EXTiS_cPart)=each($EXTiS_config))	{
				if ($EXTiS_cPart['conf']['includeLibs'])	{
					$EXTiS_resourceList = t3lib_div::trimExplode(',',$EXTiS_cPart['conf']['includeLibs'],1);
					$GLOBALS['TT']->setTSlogMessage('Files for inclusion: "'.implode(', ',$EXTiS_resourceList).'"');
					reset($EXTiS_resourceList);
					while(list(,$EXTiS_theLib)=each($EXTiS_resourceList))	{
						$EXTiS_incFile=$GLOBALS['TSFE']->tmpl->getFileName($EXTiS_theLib);
						if ($EXTiS_incFile)	{
							require_once($EXTiS_incFile);
						} else {
							$GLOBALS['TT']->setTSlogMessage('Include file "'.$EXTiS_theLib.'" did not exist!',2);
						}
					}
				}
			}

			reset($EXTiS_splitC);
			while(list($EXTiS_c,$EXTiS_cPart)=each($EXTiS_splitC))	{
				if (substr($EXTiS_cPart,32,3)=='-->')	{	// If the split had a comment-end after 32 characters it's probably a split-string
					$EXTiS_key = 'EXT_SCRIPT.'.substr($EXTiS_cPart,0,32);
					if (is_array($EXTiS_config[$EXTiS_key]))	{
						$REC = $EXTiS_config[$EXTiS_key]['data'];
						$CONF = $EXTiS_config[$EXTiS_key]['conf'];
						$content='';
						include($EXTiS_config[$EXTiS_key]['file']);
						echo $content;	// The script MAY return content in $content or the script may just output the result directly!
					}
					echo substr($EXTiS_cPart,35);
				} else {
					echo ($c?'<!--EXT_SCRIPT.':'').$EXTiS_cPart;
				}
			}

		$TT->pull();
	} else {
		echo $GLOBALS['TSFE']->content;
	}
	$TT->pull();
}


// ********************************
// Store session data for fe_users
// ********************************
$TSFE->storeSessionData();


// ***********
// Statistics
// ***********
$TYPO3_MISC['microtime_end'] = microtime();
$TSFE->setParseTime();
if ($TSFE->isOutputting() && ($TSFE->TYPO3_CONF_VARS['FE']['debug'] || $TSFE->config['config']['debug']))	{
	echo '
<!-- Parsetime: '.$TSFE->scriptParseTime.' ms-->';
}
$TSFE->statistics();


// ***************
// Check JumpUrl
// ***************
$TSFE->jumpurl();


// *************
// Preview info
// *************
$TSFE->previewInfo();


// ******************
// Publishing static
// ******************
if (is_object($BE_USER))	{
	if ($BE_USER->extAdmModuleEnabled('publish') && $BE_USER->extPublishList)	{
		include_once(PATH_tslib.'publish.php');
	}
}


// ********************
// Finish timetracking
// ********************
$TT->pull();


// ******************
// beLoginLinkIPList
// ******************
echo $GLOBALS['TSFE']->beLoginLinkIPList();


// *************
// Admin panel
// *************
if (is_object($BE_USER)
	&& $GLOBALS['TSFE']->beUserLogin
	&& $GLOBALS['TSFE']->config['config']['admPanel']
	&& $BE_USER->extAdmEnabled
//	&& $BE_USER->extPageReadAccess($GLOBALS['TSFE']->page)	// This is already done, if there is a BE_USER object at this point!
	&& !$BE_USER->extAdminConfig['hide'])	{
		echo $BE_USER->extPrintFeAdminDialog();
}


// *************
// Debugging Output
// *************
if(@is_callable(array($error,'debugOutput'))) {
	$error->debugOutput();
}
if (TYPO3_DLOG)	t3lib_div::devLog('END of FRONTEND session','',0,array('_FLUSH'=>TRUE));


// *************
// Compressions
// *************
if ($TYPO3_CONF_VARS['FE']['compressionLevel'])	{
	new gzip_encode($TYPO3_CONF_VARS['FE']['compressionLevel'], false, $GLOBALS['TYPO3_CONF_VARS']['FE']['compressionDebugInfo']);
}

?>
