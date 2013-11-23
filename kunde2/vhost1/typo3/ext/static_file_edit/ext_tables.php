<?php
if (!defined ('TYPO3_MODE')) 	die ('Access denied.');

if (TYPO3_MODE=='BE')	{
	$TCA['sys_staticfile_edit'] = Array (
		'ctrl' => Array (
			'label' => 'edit_file',
			'default_sortby' => 'ORDER BY edit_file',
			'tstamp' => 'tstamp',
			'crdate' => 'crdate',
			'cruser_id' => 'cruser',
			'delete' => 'deleted',
			'title' => 'LLL:EXT:static_file_edit/locallang_tca.php:sys_staticfile_edit',
			'iconfile' => t3lib_extMgm::extRelPath($_EXTKEY).'ext_icon.gif',
			'dynamicConfigFile' => t3lib_extMgm::extPath($_EXTKEY).'tca.php'
		)
	);
}

t3lib_extMgm::addLLrefForTCAdescr('sys_staticfile_edit','EXT:static_file_edit/locallang_csh_sysstfe.php');
?>