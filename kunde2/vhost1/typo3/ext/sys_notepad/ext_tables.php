<?php
if (!defined ('TYPO3_MODE')) 	die ('Access denied.');

if (TYPO3_MODE=='BE')	{
	t3lib_extMgm::insertModuleFunction(
		'user_task',
		'tx_sysnotepad',
		t3lib_extMgm::extPath($_EXTKEY).'class.tx_sysnotepad.php',
		'LLL:EXT:sys_notepad/locallang.php:mod_note'
	);
}
?>