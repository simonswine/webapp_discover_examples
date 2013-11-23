<?php
if (!defined ('TYPO3_MODE')) 	die ('Access denied.');

$TCA['tt_calender'] = Array (
	'ctrl' => Array (
		'label' => 'title',
		'default_sortby' => 'ORDER BY date',
		'tstamp' => 'tstamp',
		'prependAtCopy' => 'LLL:EXT:lang/locallang_general.php:LGL.prependAtCopy',
		'delete' => 'deleted',
		'type' => 'type',
		'enablecolumns' => Array (
			'disabled' => 'hidden',
			'starttime' => 'starttime',
			'endtime' => 'endtime'
		),
		'mainpalette' => 1,
		'typeicon_column' => 'type',
		'typeicons' => Array (
			'0' => 'tt_calender.gif',
			'1' => 'tt_calender_todo.gif'
		),
		'title' => 'LLL:EXT:tt_calender/locallang_tca.php:tt_calender',
		'useColumnsForDefaultValues' => 'type',
		'mainpalette' => 1,
		'iconfile' => t3lib_extMgm::extRelPath($_EXTKEY).'ext_icon.gif',
		'dynamicConfigFile' => t3lib_extMgm::extPath($_EXTKEY).'tca.php'
	)
);
$TCA['tt_calender_cat'] = Array (
	'ctrl' => Array (
		'label' => 'title',
		'tstamp' => 'tstamp',
		'delete' => 'deleted',
		'prependAtCopy' => 'LLL:EXT:lang/locallang_general.php:LGL.prependAtCopy',
		'crdate' => 'crdate',
		'title' => 'LLL:EXT:tt_calender/locallang_tca.php:tt_calender_cat',
		'dynamicConfigFile' => t3lib_extMgm::extPath($_EXTKEY).'tca.php'
	)
);
t3lib_extMgm::addPlugin(Array('LLL:EXT:tt_calender/locallang_tca.php:tt_calender', '7'));
t3lib_extMgm::allowTableOnStandardPages('tt_calender');
t3lib_extMgm::addToInsertRecords('tt_calender');

t3lib_extMgm::addLLrefForTCAdescr('tt_calender','EXT:tt_calender/locallang_csh_ttcalen.php');
t3lib_extMgm::addLLrefForTCAdescr('tt_calender_cat','EXT:tt_calender/locallang_csh_ttcalenc.php');
?>