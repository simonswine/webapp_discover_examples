<?php
if (!defined ('TYPO3_MODE')) 	die ('Access denied.');

if (TYPO3_MODE=='BE')	{
	$TCA['sys_workflows'] = Array (
		'ctrl' => Array (
			'label' => 'title',
			'tstamp' => 'tstamp',
			'default_sortby' => 'ORDER BY title',
			'prependAtCopy' => 'LLL:EXT:lang/locallang_general.php:LGL.prependAtCopy',
			'title' => 'LLL:EXT:sys_workflows/locallang_tca.php:sys_workflows',
			'crdate' => 'crdate',
			'cruser_id' => 'cruser_id',
			'adminOnly' => 1,
			'rootLevel' => 1,
			'enablecolumns' => Array (
				'disabled' => 'hidden'
			),
			'type' => 'final_set_perms',
			'iconfile' => t3lib_extMgm::extRelPath($_EXTKEY).'ext_icon.gif',
			'dynamicConfigFile' => t3lib_extMgm::extPath($_EXTKEY).'tca.php'
		)
	);
}

t3lib_extMgm::addLLrefForTCAdescr('sys_workflows','EXT:sys_workflows/locallang_csh_sysworkf.php');
?>