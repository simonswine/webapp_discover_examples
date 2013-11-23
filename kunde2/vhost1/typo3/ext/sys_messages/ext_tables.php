<?php
if (!defined ('TYPO3_MODE')) 	die ('Access denied.');

if (TYPO3_MODE=='BE')	{
	t3lib_extMgm::insertModuleFunction(
		'user_task',
		'tx_sysmessages',
		t3lib_extMgm::extPath($_EXTKEY).'class.tx_sysmessages.php',
		'LLL:EXT:sys_messages/locallang.php:mod_messages'
	);
}
?>