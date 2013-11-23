<?php
if (!defined ('TYPO3_MODE')) 	die ('Access denied.');

if (TYPO3_MODE=='BE')	{
	t3lib_extMgm::insertModuleFunction(
		'web_info',
		'tx_cms_webinfo_hits',
		t3lib_extMgm::extPath('cms').'web_info/class.tx_cms_webinfo.php',
		'LLL:EXT:sys_stat/locallang.php:mod_sys_stat'
	);
}
?>