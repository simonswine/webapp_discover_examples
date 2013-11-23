<?php
if (!defined ('TYPO3_MODE')) 	die ('Access denied.');

if (TYPO3_MODE=='BE')	{
	t3lib_extMgm::insertModuleFunction(
		'user_task',
		'tx_systodos',
		t3lib_extMgm::extPath($_EXTKEY).'class.tx_systodos.php',
		'LLL:EXT:sys_todos/locallang.php:pi_todo'
	);
}
?>