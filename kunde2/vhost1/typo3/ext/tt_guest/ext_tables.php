<?php
if (!defined ('TYPO3_MODE')) 	die ('Access denied.');

$TCA['tt_guest'] = Array (
	'ctrl' => Array (
		'label' => 'title',
		'default_sortby' => 'ORDER BY crdate DESC',
		'tstamp' => 'tstamp',
		'crdate' => 'crdate',
		'delete' => 'deleted',
		'prependAtCopy' => 'LLL:EXT:lang/locallang_general.php:LGL.prependAtCopy',
		'enablecolumns' => Array (
			'disabled' => 'hidden'
		),
		'title' => 'LLL:EXT:tt_guest/locallang_tca.php:tt_guest',
		'iconfile' => t3lib_extMgm::extRelPath($_EXTKEY).'ext_icon.gif',
		'dynamicConfigFile' => t3lib_extMgm::extPath($_EXTKEY).'tca.php'
	)
);
t3lib_extMgm::addPlugin(Array('LLL:EXT:tt_guest/locallang_tca.php:pi', '3'));
t3lib_extMgm::allowTableOnStandardPages('tt_guest');
t3lib_extMgm::addToInsertRecords('tt_guest');
if (TYPO3_MODE=='BE')	{
	$TBE_MODULES_EXT['xMOD_db_new_content_el']['addElClasses']['tx_ttguest_wizicon'] = 
		t3lib_extMgm::extPath($_EXTKEY).'class.tx_ttguest_wizicon.php';
}

t3lib_extMgm::addLLrefForTCAdescr('tt_guest','EXT:tt_guest/locallang_csh_ttguest.php');
?>