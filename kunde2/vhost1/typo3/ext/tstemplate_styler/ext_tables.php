<?php
if (!defined ('TYPO3_MODE')) 	die ('Access denied.');

if (TYPO3_MODE=='BE')	{
	t3lib_extMgm::insertModuleFunction(
		'web_ts',		
		'tx_tstemplatestyler_modfunc1',
		t3lib_extMgm::extPath($_EXTKEY).'modfunc1/class.tx_tstemplatestyler_modfunc1.php',
		'CSS Styler'
	);
}
?>