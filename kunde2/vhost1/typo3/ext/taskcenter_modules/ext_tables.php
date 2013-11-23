<?php
if (!defined ('TYPO3_MODE')) 	die ('Access denied.');

if (TYPO3_MODE=='BE')	{
	t3lib_extMgm::insertModuleFunction(
		'user_task',
		'tx_taskcentermodules',
		t3lib_extMgm::extPath($_EXTKEY).'class.tx_taskcentermodules.php',
		'LLL:EXT:taskcenter_modules/locallang.php:tx_taskcentermodules'
	);
}
?>