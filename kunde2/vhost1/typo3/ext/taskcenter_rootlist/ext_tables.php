<?php
if (!defined ('TYPO3_MODE')) 	die ('Access denied.');

if (TYPO3_MODE=='BE')	{
	t3lib_extMgm::insertModuleFunction(
		'user_task',
		'tx_taskcenterrootlist',
		t3lib_extMgm::extPath($_EXTKEY).'class.tx_taskcenterrootlist.php',
		'LLL:EXT:taskcenter_rootlist/locallang.php:mod_rootlist'
	);
}
?>