<?php
if (!defined ('TYPO3_MODE')) 	die ('Access denied.');

if (TYPO3_MODE=='BE')	{
	t3lib_extMgm::insertModuleFunction(
		'web_func',
		'tx_cmsplaintextimport_webfunc',
		t3lib_extMgm::extPath($_EXTKEY).'class.tx_cmsplaintextimport_webfunc.php',
		'LLL:EXT:cms_plaintext_import/locallang.php:menu_1'
	);
}
?>