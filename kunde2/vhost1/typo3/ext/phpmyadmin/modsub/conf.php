<?php

// Setting specific configuration for the third part module:
$MCONF['phpMyAdminScript'] = '';	// or 'main.php','db_details.php'	// Enter the script to load, if any
$MCONF['phpMyAdminSubDir']='';
if (!isset($MCONF['extModInclude']))	{$MCONF['extModInclude']=0;}

## UN-COMMENT THIS LINE to activate phpMyAdmin! Please make sure the path is correct!
## (LOWERCASE!!)
$MCONF['phpMyAdminSubDir'] = 'phpmyadmin-2.5.6-rc1/';			// Enter the subdirectory of the scripts (LOWERCASE!!)






// Almost regular configuration of the module. Only if $MCONF['extModInclude'] is set, then phpMyAdminSubDir is prepended to the TYPO3_MOD_PATH and '../' to BACK_PATH. If this is not correct, init.php will exit!
define('TYPO3_MOD_PATH', 'ext/phpmyadmin/modsub/'.($MCONF['extModInclude']?$MCONF['phpMyAdminSubDir']:''));
$BACK_PATH='../../../'.($MCONF['extModInclude']?'../':'');

$MLANG['default']['tabs_images']['tab'] = 'thirdparty_db.gif';
$MLANG['default']['ll_ref']='LLL:EXT:phpmyadmin/modsub/locallang_mod.php';

$MCONF['script']='index.php';
$MCONF['access']='admin';
$MCONF['name']='tools_txphpmyadmin';
?>