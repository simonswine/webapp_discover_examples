<?php
/***************************************************************
*  Copyright notice
*
*  (c) 1999-2004 Kasper Skaarhoj (kasperYYYY@typo3.com)
*  All rights reserved
*
*  This script is part of the TYPO3 project. The TYPO3 project is
*  free software; you can redistribute it and/or modify
*  it under the terms of the GNU General Public License as published by
*  the Free Software Foundation; either version 2 of the License, or
*  (at your option) any later version.
*
*  The GNU General Public License can be found at
*  http://www.gnu.org/copyleft/gpl.html.
*  A copy is found in the textfile GPL.txt and important notices to the license
*  from the author is found in LICENSE.txt distributed with these scripts.
*
*
*  This script is distributed in the hope that it will be useful,
*  but WITHOUT ANY WARRANTY; without even the implied warranty of
*  MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
*  GNU General Public License for more details.
*
*  This copyright notice MUST APPEAR in all copies of the script!
***************************************************************/
/**
 * Dynamic configuation of the system-related tables, typ. sys_* series
 *
 * $Id: tbl_cms.php,v 1.12 2004/09/13 22:57:35 typo3 Exp $
 *
 * @author	Kasper Skaarhoj <kasperYYYY@typo3.com>
 */





// ******************************************************************
// fe_users
//
// FrontEnd users - login on the website
// ******************************************************************
$TCA['fe_users'] = Array (
	'ctrl' => $TCA['fe_users']['ctrl'],
	'interface' => Array (
		'showRecordFieldList' => 'username,password,usergroup,lockToDomain,name,title,address,zip,city,country,email,www,telephone,fax,disable,starttime,endtime'
	),
	'feInterface' => $TCA['fe_users']['feInterface'],
	'columns' => Array (
		'username' => Array (
			'label' => 'LLL:EXT:cms/locallang_tca.php:fe_users.username',
			'config' => Array (
				'type' => 'input',
				'size' => '20',
				'max' => '50',
				'eval' => 'nospace,lower,uniqueInPid,required'
			)
		),
		'password' => Array (
			'label' => 'LLL:EXT:cms/locallang_tca.php:fe_users.password',
			'config' => Array (
				'type' => 'input',
				'size' => '10',
				'max' => '40',
				'eval' => 'nospace,lower,required'
			)
		),
		'usergroup' => Array (
			'label' => 'LLL:EXT:cms/locallang_tca.php:fe_users.usergroup',
			'config' => Array (
				'type' => 'select',
				'foreign_table' => 'fe_groups',
				'size' => '3',
				'minitems' => '1',
				'maxitems' => '50'
			)
		),
		'lockToDomain' => Array (
			'exclude' => 1,
			'label' => 'LLL:EXT:cms/locallang_tca.php:fe_users.lockToDomain',
			'config' => Array (
				'type' => 'input',
				'size' => '20',
				'eval' => 'trim',
				'max' => '50',
				'checkbox' => ''
			)
		),
		'name' => Array (
			'exclude' => 1,
			'label' => 'LLL:EXT:lang/locallang_general.php:LGL.name',
			'config' => Array (
				'type' => 'input',
				'size' => '40',
				'eval' => 'trim',
				'max' => '80'
			)
		),
		'address' => Array (
			'exclude' => 1,
			'label' => 'LLL:EXT:lang/locallang_general.php:LGL.address',
			'config' => Array (
				'type' => 'text',
				'cols' => '20',
				'rows' => '3'
			)
		),
		'telephone' => Array (
			'exclude' => 1,
			'label' => 'LLL:EXT:lang/locallang_general.php:LGL.phone',
			'config' => Array (
				'type' => 'input',
				'eval' => 'trim',
				'size' => '20',
				'max' => '20'
			)
		),
		'fax' => Array (
			'exclude' => 1,
			'label' => 'LLL:EXT:lang/locallang_general.php:LGL.fax',
			'config' => Array (
				'type' => 'input',
				'size' => '20',
				'eval' => 'trim',
				'max' => '20'
			)
		),
		'email' => Array (
			'exclude' => 1,
			'label' => 'LLL:EXT:lang/locallang_general.php:LGL.email',
			'config' => Array (
				'type' => 'input',
				'size' => '40',
				'eval' => 'trim',
				'max' => '80'
			)
		),
		'title' => Array (
			'exclude' => 1,
			'label' => 'LLL:EXT:lang/locallang_general.php:LGL.title',
			'config' => Array (
				'type' => 'input',
				'size' => '20',
				'eval' => 'trim',
				'max' => '40'
			)
		),
		'zip' => Array (
			'exclude' => 1,
			'label' => 'LLL:EXT:lang/locallang_general.php:LGL.zip',
			'config' => Array (
				'type' => 'input',
				'eval' => 'trim',
				'size' => '10',
				'max' => '10'
			)
		),
		'city' => Array (
			'exclude' => 1,
			'label' => 'LLL:EXT:lang/locallang_general.php:LGL.city',
			'config' => Array (
				'type' => 'input',
				'size' => '20',
				'eval' => 'trim',
				'max' => '50'
			)
		),
		'country' => Array (
			'exclude' => 1,
			'label' => 'LLL:EXT:lang/locallang_general.php:LGL.country',
			'config' => Array (
				'type' => 'input',
				'size' => '20',
				'eval' => 'trim',
				'max' => '40'
			)
		),
		'www' => Array (
			'exclude' => 1,
			'label' => 'LLL:EXT:lang/locallang_general.php:LGL.www',
			'config' => Array (
				'type' => 'input',
				'eval' => 'trim',
				'size' => '20',
				'max' => '80'
			)
		),
		'company' => Array (
			'exclude' => 1,
			'label' => 'LLL:EXT:lang/locallang_general.php:LGL.company',
			'config' => Array (
				'type' => 'input',
				'eval' => 'trim',
				'size' => '20',
				'max' => '80'
			)
		),
		'image' => Array (
			'exclude' => 1,
			'label' => 'LLL:EXT:lang/locallang_general.php:LGL.image',
			'config' => Array (
				'type' => 'group',
				'internal_type' => 'file',
				'allowed' => $GLOBALS['TYPO3_CONF_VARS']['GFX']['imagefile_ext'],
				'max_size' => '1000',
				'uploadfolder' => 'uploads/pics',
				'show_thumbs' => '1',
				'size' => '3',
				'maxitems' => '6',
				'minitems' => '0'
			)
		),
		'disable' => Array (
			'exclude' => 1,
			'label' => 'LLL:EXT:lang/locallang_general.php:LGL.disable',
			'config' => Array (
				'type' => 'check'
			)
		),
		'starttime' => Array (
			'exclude' => 1,
			'label' => 'LLL:EXT:lang/locallang_general.php:LGL.starttime',
			'config' => Array (
				'type' => 'input',
				'size' => '8',
				'max' => '20',
				'eval' => 'date',
				'default' => '0',
				'checkbox' => '0'
			)
		),
		'endtime' => Array (
			'exclude' => 1,
			'label' => 'LLL:EXT:lang/locallang_general.php:LGL.endtime',
			'config' => Array (
				'type' => 'input',
				'size' => '8',
				'max' => '20',
				'eval' => 'date',
				'checkbox' => '0',
				'default' => '0',
				'range' => Array (
					'upper' => mktime(0,0,0,12,31,2020),
					'lower' => mktime(0,0,0,date('m')-1,date('d'),date('Y'))
				)
			)
		),
		'TSconfig' => Array (
			'exclude' => 1,
			'label' => 'TSconfig:',
			'config' => Array (
				'type' => 'text',
				'cols' => '40',
				'rows' => '5',
				'wizards' => Array(
					'_PADDING' => 4,
					'0' => Array(
#						'type' => t3lib_extMgm::isLoaded('tsconfig_help')?'popup':'',
						'title' => 'TSconfig QuickReference',
						'script' => 'wizard_tsconfig.php?mode=fe_users',
						'icon' => 'wizard_tsconfig.gif',
						'JSopenParams' => 'height=500,width=780,status=0,menubar=0,scrollbars=1',
					)
				)
			)
		)
	),
	'types' => Array (
		'0' => Array('showitem' => 'username;;;;2-2-2, password, usergroup, lockToDomain, --div--, name;;2;;3-3-3, address, zip, city, country, telephone, fax, email, www, image;;;;4-4-4, --div--, TSconfig;;;;5-5-5')
	),
	'palettes' => Array (
		'1' => Array('showitem' => 'disable, starttime, endtime'),
		'2' => Array('showitem' => 'title,company')
	)
);





// ******************************************************************
// fe_groups
//
// FrontEnd usergroups - Membership of these determines access to elements
// ******************************************************************
$TCA['fe_groups'] = Array (
	'ctrl' => $TCA['fe_groups']['ctrl'],
	'interface' => Array (
		'showRecordFieldList' => 'title,hidden,lockToDomain,description'
	),
	'columns' => Array (
		'hidden' => Array (
			'label' => 'LLL:EXT:lang/locallang_general.php:LGL.disable',
			'exclude' => 1,
			'config' => Array (
				'type' => 'check',
				'default' => '0'
			)
		),
		'title' => Array (
			'label' => 'LLL:EXT:cms/locallang_tca.php:fe_groups.title',
			'config' => Array (
				'type' => 'input',
				'size' => '20',
				'max' => '20',
				'eval' => 'trim,required'
			)
		),
		'lockToDomain' => Array (
			'exclude' => 1,
			'label' => 'LLL:EXT:cms/locallang_tca.php:fe_groups.lockToDomain',
			'config' => Array (
				'type' => 'input',
				'size' => '20',
				'eval' => 'trim',
				'max' => '50',
				'checkbox' => ''
			)
		),
		'description' => Array (
			'label' => 'LLL:EXT:lang/locallang_general.php:LGL.description',
			'config' => Array (
				'type' => 'text',
				'rows' => 5,
				'cols' => 48
			)
		),
		'TSconfig' => Array (
			'exclude' => 1,
			'label' => 'TSconfig:',
			'config' => Array (
				'type' => 'text',
				'cols' => '40',
				'rows' => '5',
				'wizards' => Array(
					'_PADDING' => 4,
					'0' => Array(
#						'type' => t3lib_extMgm::isLoaded('tsconfig_help')?'popup':'',
						'title' => 'TSconfig QuickReference',
						'script' => 'wizard_tsconfig.php?mode=fe_users',
						'icon' => 'wizard_tsconfig.gif',
						'JSopenParams' => 'height=500,width=780,status=0,menubar=0,scrollbars=1',
					)
				)
			)
		)
	),
	'types' => Array (
		'0' => Array('showitem' => 'hidden;;;;1-1-1,title;;;;2-2-2,lockToDomain,description;;;;3-3-3, --div--, TSconfig;;;;5-5-5')
	)
);




// ******************************************************************
// sys_domain
// ******************************************************************
$TCA['sys_domain'] = Array (
	'ctrl' => $TCA['sys_domain']['ctrl'],
	'interface' => Array (
		'showRecordFieldList' => 'hidden,domainName,redirectTo'
	),
	'columns' => Array (
		'domainName' => Array (
			'label' => 'LLL:EXT:cms/locallang_tca.php:sys_domain.domainName',
			'config' => Array (
				'type' => 'input',
				'size' => '35',
				'max' => '80',
				'eval' => 'required,unique,lower,trim'
			)
		),
		'redirectTo' => Array (
			'label' => 'LLL:EXT:cms/locallang_tca.php:sys_domain.redirectTo',
			'config' => Array (
				'type' => 'input',
				'size' => '35',
				'max' => '120',
				'checkbox' => '',
				'default' => '',
				'eval' => 'trim'
			)
		),
		'hidden' => Array (
			'label' => 'LLL:EXT:lang/locallang_general.php:LGL.disable',
			'exclude' => 1,
			'config' => Array (
				'type' => 'check',
				'default' => '0'
			)
		),
		'prepend_params' => Array (
			'label' => 'LLL:EXT:cms/locallang_tca.php:sys_domain.prepend_params',
			'exclude' => 1,
			'config' => Array (
				'type' => 'check',
				'default' => '0'
			)
		)
	),
	'types' => Array (
		'1' => Array('showitem' => 'hidden;;;;1-1-1,domainName;;1;;3-3-3,prepend_params')
	),
	'palettes' => Array (
		'1' => Array('showitem' => 'redirectTo')
	)
);





// ******************************************************************
// pages_language_overlay
// ******************************************************************
$TCA['pages_language_overlay'] = Array (
	'ctrl' => $TCA['pages_language_overlay']['ctrl'],
	'interface' => Array (
		'showRecordFieldList' => 'title,hidden,starttime,endtime,keywords,description,abstract'
	),
	'columns' => Array (
		'hidden' => Array (
			'exclude' => 1,
			'label' => 'LLL:EXT:lang/locallang_general.php:LGL.hidden',
			'config' => Array (
				'type' => 'check',
				'default' => '1'
			)
		),
		'starttime' => Array (
			'exclude' => 1,
			'label' => 'LLL:EXT:lang/locallang_general.php:LGL.starttime',
			'config' => Array (
				'type' => 'input',
				'size' => '8',
				'max' => '20',
				'eval' => 'date',
				'checkbox' => '0',
				'default' => '0'
			)
		),
		'endtime' => Array (
			'exclude' => 1,
			'label' => 'LLL:EXT:lang/locallang_general.php:LGL.endtime',
			'config' => Array (
				'type' => 'input',
				'size' => '8',
				'max' => '20',
				'eval' => 'date',
				'checkbox' => '0',
				'default' => '0',
				'range' => Array (
					'upper' => mktime(0,0,0,12,31,2020),
					'lower' => mktime(0,0,0,date('m')-1,date('d'),date('Y'))
				)
			)
		),
		'title' => Array (
			'label' => $TCA['pages']['columns']['title']['label'],
			'config' => Array (
				'type' => 'input',
				'size' => '30',
				'max' => '256',
				'eval' => 'required'
			)
		),
		'subtitle' => Array (
			'exclude' => 1,
			'label' => $TCA['pages']['columns']['subtitle']['label'],
			'config' => Array (
				'type' => 'input',
				'size' => '30',
				'max' => '256',
				'eval' => ''
			)
		),
		'nav_title' => Array (
			'exclude' => 1,
			'label' => $TCA['pages']['columns']['nav_title']['label'],
			'config' => Array (
				'type' => 'input',
				'size' => '30',
				'max' => '256',
				'checkbox' => '',
				'eval' => 'trim'
			)
		),
		'keywords' => Array (
			'exclude' => 1,
			'label' => $TCA['pages']['columns']['keywords']['label'],
			'config' => Array (
				'type' => 'text',
				'cols' => '40',
				'rows' => '3'
			)
		),
		'description' => Array (
			'exclude' => 1,
			'label' => $TCA['pages']['columns']['description']['label'],
			'config' => Array (
				'type' => 'input',
				'size' => '40',
				'eval' => 'trim'
			)
		),
		'abstract' => Array (
			'exclude' => 1,
			'label' => $TCA['pages']['columns']['abstract']['label'],
			'config' => Array (
				'type' => 'text',
				'cols' => '40',
				'rows' => '3'
			)
		),
		'author' => Array (
			'exclude' => 1,
			'label' => $TCA['pages']['columns']['author']['label'],
			'config' => Array (
				'type' => 'input',
				'size' => '20',
				'eval' => 'trim',
				'max' => '80'
			)
		),
		'author_email' => Array (
			'exclude' => 1,
			'label' => $TCA['pages']['columns']['author']['label'],
			'config' => Array (
				'type' => 'input',
				'size' => '20',
				'eval' => 'trim',
				'max' => '80'
			)
		),
		'media' => Array (
			'exclude' => 1,
			'label' => $TCA['pages']['columns']['media']['label'],
			'config' => Array (
				'type' => 'group',
				'internal_type' => 'file',
				'allowed' => $TCA['pages']['columns']['media']['config']['allowed'],
				'max_size' => '2000',
				'uploadfolder' => 'uploads/media',
				'show_thumbs' => '1',
				'size' => '3',
				'maxitems' => '5',
				'minitems' => '0'
			)
		),
		'sys_language_uid' => Array (
			'label' => 'LLL:EXT:lang/locallang_general.php:LGL.language',
			'config' => Array (
				'type' => 'select',
				'foreign_table' => 'sys_language',
				'foreign_table_where' => 'ORDER BY sys_language.title',
				'items' => Array(
					Array('LLL:EXT:lang/locallang_general.php:LGL.default_value',0)
				)
			)
		),
		'tx_impexp_origuid' => Array('config'=>array('type'=>'passthrough')),
		'l18n_diffsource' => Array('config'=>array('type'=>'passthrough')),
		't3ver_label' => Array (
			'label' => 'LLL:EXT:lang/locallang_general.php:LGL.versionLabel',
			'config' => Array (
				'type' => 'input',
				'size' => '30',
				'max' => '30',
			)
		),
	),
	'types' => Array (
		'0' => Array('showitem' => 'hidden;;;;1-1-1, sys_language_uid, title;;;;2-2-2, subtitle, nav_title, --div--, abstract;;5;;3-3-3, keywords, description, media;;;;4-4-4')
	),
	'palettes' => Array (
		'1' => Array('showitem' => 'starttime,endtime'),
		'5' => Array('showitem' => 'author,author_email')
	)
);



// ******************************************************************
// sys_template
// ******************************************************************
$TCA['sys_template'] = Array (
	'ctrl' => $TCA['sys_template']['ctrl'],
	'interface' => Array (
		'showRecordFieldList' => 'title,clear,root,include_static,basedOn,nextLevel,resources,sitetitle,description,hidden,starttime,endtime'
	),
	'columns' => Array (
		'title' => Array (
			'label' => 'Template title:',
			'config' => Array (
				'type' => 'input',
				'size' => '25',
				'max' => '256',
				'eval' => 'required'
			)
		),
		'hidden' => Array (
			'label' => 'Deactivated:',
			'exclude' => 1,
			'config' => Array (
				'type' => 'check',
				'default' => '0'
			)
		),
		'starttime' => Array (
			'label' => 'Start:',
			'exclude' => 1,
			'config' => Array (
				'type' => 'input',
				'size' => '8',
				'max' => '20',
				'eval' => 'date',
				'checkbox' => '0',
				'default' => '0'
			)
		),
		'endtime' => Array (
			'label' => 'Stop:',
			'exclude' => 1,
			'config' => Array (
				'type' => 'input',
				'size' => '8',
				'max' => '20',
				'eval' => 'date',
				'checkbox' => '0',
				'default' => '0',
				'range' => Array (
					'upper' => mktime(0,0,0,12,31,2020),
					'lower' => mktime(0,0,0,date('m')-1,date('d'),date('Y'))
				)
			)
		),
		'root' => Array (
			'label' => 'Rootlevel:',
			'config' => Array (
				'type' => 'check'
			)
		),
		'clear' => Array (
			'label' => 'Clear:',
			'config' => Array (
				'type' => 'check',
				'items' => Array (
					Array('Constants', ''),
					Array('Setup', '')
				),
				'cols' => 2
			)
		),
		'sitetitle' => Array (
			'label' => 'Website title:',
			'config' => Array (
				'type' => 'input',
				'size' => '25',
				'max' => '256'
			)
		),
		'constants' => Array (
			'label' => 'Constants:',
			'config' => Array (
				'type' => 'text',
				'cols' => '48',
				'rows' => '10',
				'wrap' => 'OFF'
			)
		),
		'resources' => Array (
			'label' => 'Resources:',
			'config' => Array (
				'type' => 'group',
				'internal_type' => 'file',
				'allowed' => $GLOBALS['TYPO3_CONF_VARS']['GFX']['imagefile_ext'].',html,htm,ttf,pfb,pfm,txt,css,tmpl,inc,ico',
				'max_size' => '1000',
				'uploadfolder' => 'uploads/tf',
				'show_thumbs' => '1',
				'size' => '7',
				'maxitems' => '100',
				'minitems' => '0'
			)
		),
		'nextLevel' => Array (
			'label' => 'Template on next level:',
			'config' => Array (
				'type' => 'group',
				'internal_type' => 'db',
				'allowed' => 'sys_template',
				'show_thumbs' => '1',
				'size' => '3',
				'maxitems' => '1',
				'minitems' => '0',
				'default' => ''
			)
		),
		'include_static' => Array (
			'label' => 'Include static:',
			'config' => Array (
				'type' => 'select',
				'foreign_table' => 'static_template',
				'foreign_table_where' => 'ORDER BY static_template.title DESC',
				'size' => 10,
				'maxitems' => 20,
				'default' => ''
			)
		),
		'include_static_file' => Array(
			'label' => 'Include static (from extensions):',
			'config' => Array (
				'type' => 'select',
				'size' => 10,
				'maxitems' => 20,
				'items' => Array (
				)
			)
		),
		'basedOn' => Array (
			'label' => 'Include basis template:',
			'config' => Array (
				'type' => 'group',
				'internal_type' => 'db',
				'allowed' => 'sys_template',
				'show_thumbs' => '1',
				'size' => '3',
				'maxitems' => '50',
				'autoSizeMax' => 10,
				'minitems' => '0',
				'default' => '',
				'wizards' => Array(
					'_PADDING' => 4,
					'_VERTICAL' => 1,
					'edit' => Array(
						'type' => 'popup',
						'title' => 'Edit filemount',
						'script' => 'wizard_edit.php',
						'popup_onlyOpenIfSelected' => 1,
						'icon' => 'edit2.gif',
						'JSopenParams' => 'height=350,width=580,status=0,menubar=0,scrollbars=1',
					),
					'add' => Array(
						'type' => 'script',
						'title' => 'Add new basis template',
						'icon' => 'add.gif',
						'params' => Array(
							'table'=>'sys_template',
							'pid' => '###CURRENT_PID###',
							'setValue' => 'prepend'
						),
						'script' => 'wizard_add.php',
					)
				)
			)
		),
		'includeStaticAfterBasedOn' => Array (
			'label' => 'Include static AFTER basedOn:',
			'exclude' => 1,
			'config' => Array (
				'type' => 'check',
				'default' => '0'
			)
		),
		'config' => Array (
			'label' => 'Setup:',
			'config' => Array (
				'type' => 'text',
				'rows' => 10,
				'cols' => 48,
				'wizards' => Array(
					'_PADDING' => 4,
					'0' => Array(
#						'type' => t3lib_extMgm::isLoaded('tsconfig_help')?'popup':'',
						'title' => 'TSref online',
						'script' => 'wizard_tsconfig.php?mode=tsref',
						'icon' => 'wizard_tsconfig.gif',
						'JSopenParams' => 'height=500,width=780,status=0,menubar=0,scrollbars=1',
					)
				),
				'wrap' => 'OFF'
			)
		),
		'editorcfg' => Array (
			'label' => 'Backend Editor Configuration:',
			'config' => Array (
				'type' => 'text',
				'rows' => 4,
				'cols' => 48,
				'wrap' => 'OFF'
			)
		),
		'description' => Array (
			'label' => 'Description:',
			'config' => Array (
				'type' => 'text',
				'rows' => 10,
				'cols' => 48
			)
		),
		'static_file_mode' => Array (
			'label' => 'Static template files from T3 Extensions:',
			'config' => Array (
				'type' => 'select',
				'items' => Array (
					Array('Default (Include before if Root-flag is set)', '0'),
					Array('Always include before this template record', '1'),
					Array('Never include before this template record', '2'),
				),
				'default' => '0'
			)
		),
		'tx_impexp_origuid' => Array('config'=>array('type'=>'passthrough')),
		't3ver_label' => Array (
			'label' => 'LLL:EXT:lang/locallang_general.php:LGL.versionLabel',
			'config' => Array (
				'type' => 'input',
				'size' => '30',
				'max' => '30',
			)
		),
	),
	'types' => Array (
		'1' => Array('showitem' => 'title;;1;;2-2-2, sitetitle, constants;;;;3-3-3, config, resources, clear, root, --div--, include_static;;2;;5-5-5, include_static_file;;2, basedOn;;2, static_file_mode, nextLevel, --div--, description;;;;5-5-5, editorcfg')
	),
	'palettes' => Array (
		'1' => Array('showitem' => 'hidden,starttime,endtime'),
		'2' => Array('showitem' => 'includeStaticAfterBasedOn')
	)
);





// ******************************************************************
// static_template
// ******************************************************************
$TCA['static_template'] = Array (
	'ctrl' => $TCA['static_template']['ctrl'],
	'interface' => Array (
		'showRecordFieldList' => 'title,include_static,description'
	),
	'columns' => Array (
		'title' => Array (
			'label' => 'Template title:',
			'config' => Array (
				'type' => 'input',
				'size' => '25',
				'max' => '256',
				'eval' => 'required'
			)
		),
		'constants' => Array (
			'label' => 'Constants:',
			'config' => Array (
				'type' => 'text',
				'cols' => '48',
				'rows' => '10',
				'wrap' => 'OFF'
			)
		),
		'include_static' => Array (
			'label' => 'Include static:',
			'config' => Array (
				'type' => 'select',
				'foreign_table' => 'static_template',
				'foreign_table_where' => 'ORDER BY static_template.title',
				'size' => 10,
				'maxitems' => 20,
				'default' => ''
			)
		),
		'config' => Array (
			'label' => 'Setup:',
			'config' => Array (
				'type' => 'text',
				'rows' => 10,
				'cols' => 48,
				'wrap' => 'OFF'
			)
		),
		'editorcfg' => Array (
			'label' => 'Backend Editor Configuration:',
			'config' => Array (
				'type' => 'text',
				'rows' => 4,
				'cols' => 48,
				'wrap' => 'OFF'
			)
		),
		'description' => Array (
			'label' => 'Description:',
			'config' => Array (
				'type' => 'text',
				'rows' => 10,
				'cols' => 48
			)
		)
	),
	'types' => Array (
		'1' => Array('showitem' => 'title;;;;2-2-2, constants;;;;3-3-3, config, include_static;;;;5-5-5, description;;;;5-5-5, editorcfg')
	)
);



?>